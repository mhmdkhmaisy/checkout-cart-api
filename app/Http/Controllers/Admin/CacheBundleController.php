<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CacheBundle;
use App\Models\CachePatch;
use App\Services\CachePatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CacheBundleController extends Controller
{
    /**
     * Display cache bundles listing
     */
    public function index()
    {
        $bundles = CacheBundle::orderBy('created_at', 'desc')->paginate(20);
        
        $totalSize = CacheBundle::sum('size');
        $activeBundles = CacheBundle::active()->count();
        $expiredBundles = CacheBundle::expired()->count();
        
        $patches = CachePatch::latest()->get();
        $latestVersion = CachePatch::getLatestVersion();
        $basePatches = CachePatch::base()->count();
        $incrementalPatches = CachePatch::patches()->count();
        $totalPatchSize = CachePatch::sum('size');
        
        $patchService = new CachePatchService();
        $canMerge = $patchService->shouldMergePatches();

        return view('admin.cache.bundles', compact(
            'bundles', 'totalSize', 'activeBundles', 'expiredBundles',
            'patches', 'latestVersion', 'basePatches', 'incrementalPatches', 
            'totalPatchSize', 'canMerge'
        ));
    }

    /**
     * Delete a specific bundle
     */
    public function destroy(CacheBundle $bundle)
    {
        try {
            $bundle->deleteFile();
            $bundle->delete();

            return redirect()->route('admin.cache.bundles')
                ->with('success', 'Bundle deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Failed to delete bundle: ' . $e->getMessage());
        }
    }

    /**
     * Clear all expired bundles
     */
    public function clearExpired()
    {
        try {
            Artisan::call('cache:cleanup-bundles');
            $output = Artisan::output();

            return redirect()->route('admin.cache.bundles')
                ->with('success', 'Expired bundles cleared. ' . trim($output));

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Failed to clear expired bundles: ' . $e->getMessage());
        }
    }

    /**
     * Clear all bundles (active and expired)
     */
    public function clearAll()
    {
        try {
            $bundles = CacheBundle::all();
            $count = $bundles->count();

            foreach ($bundles as $bundle) {
                $bundle->deleteFile();
                $bundle->delete();
            }

            return redirect()->route('admin.cache.bundles')
                ->with('success', "All {$count} bundles cleared successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Failed to clear all bundles: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific bundle
     */
    public function download(CacheBundle $bundle)
    {
        if (!$bundle->existsOnDisk()) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Bundle file not found on disk.');
        }

        if ($bundle->isExpired()) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Cannot download expired bundle.');
        }

        return response()->download($bundle->full_path);
    }

    public function getLatestVersion()
    {
        $latestVersion = CachePatch::getLatestVersion();
        $patches = CachePatch::latest()->get();

        return response()->json([
            'latest_version' => $latestVersion ?? '0.0.0',
            'patches' => $patches->map(function($patch) {
                return [
                    'version' => $patch->version,
                    'is_base' => $patch->is_base,
                    'size' => $patch->size,
                    'file_count' => $patch->file_count,
                    'created_at' => $patch->created_at->toISOString(),
                ];
            })
        ]);
    }

    public function checkForUpdates(Request $request)
    {
        $request->validate([
            'current_version' => 'required|string'
        ]);

        $currentVersion = $request->input('current_version');
        $latestVersion = CachePatch::getLatestVersion();

        if (!$latestVersion || version_compare($currentVersion, $latestVersion) >= 0) {
            return response()->json([
                'has_updates' => false,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion ?? $currentVersion
            ]);
        }

        $patches = CachePatch::all()
            ->filter(function($patch) use ($currentVersion) {
                return version_compare($patch->version, $currentVersion, '>');
            })
            ->sortBy(function($patch) {
                return $patch->version;
            }, SORT_NATURAL)
            ->values();

        return response()->json([
            'has_updates' => true,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'patches' => $patches->map(function($patch) {
                return [
                    'version' => $patch->version,
                    'path' => $patch->path,
                    'size' => $patch->size,
                    'file_count' => $patch->file_count,
                ];
            })
        ]);
    }

    public function downloadPatch(CachePatch $patch)
    {
        if (!$patch->existsOnDisk()) {
            return response()->json([
                'error' => 'Patch file not found on disk.'
            ], 404);
        }

        return response()->download($patch->full_path, "patch_{$patch->version}.zip");
    }

    public function downloadCombinedPatches(Request $request)
    {
        $request->validate([
            'from_version' => 'required|string'
        ]);

        $fromVersion = $request->input('from_version');
        $patchService = new CachePatchService();
        
        $combinedPath = $patchService->combinePatchesForDownload($fromVersion);

        if (!$combinedPath) {
            return response()->json([
                'error' => 'No patches available for the specified version.'
            ], 404);
        }

        $fullPath = storage_path('app/' . $combinedPath);
        
        if (!file_exists($fullPath)) {
            return response()->json([
                'error' => 'Combined patch file could not be created.'
            ], 500);
        }

        return response()->download($fullPath, "patches_{$fromVersion}_to_" . CachePatch::getLatestVersion() . ".zip");
    }

    public function mergePatches()
    {
        try {
            $patchService = new CachePatchService();
            
            if (!$patchService->shouldMergePatches()) {
                return redirect()->route('admin.cache.bundles')
                    ->with('info', 'Not enough patches to merge yet (threshold is ' . CachePatchService::PATCH_THRESHOLD . ').');
            }

            $newBase = $patchService->mergePatches();

            if ($newBase) {
                return redirect()->route('admin.cache.bundles')
                    ->with('success', "Patches merged successfully into new base version {$newBase->version}.");
            }

            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Failed to merge patches.');

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Failed to merge patches: ' . $e->getMessage());
        }
    }

    public function deletePatch(CachePatch $patch)
    {
        try {
            if ($patch->is_base) {
                return redirect()->route('admin.cache.bundles')
                    ->with('error', 'Cannot delete base patches. Please merge patches first.');
            }

            $patch->deleteFile();
            $patch->delete();

            return redirect()->route('admin.cache.bundles')
                ->with('success', "Patch {$patch->version} deleted successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.bundles')
                ->with('error', 'Failed to delete patch: ' . $e->getMessage());
        }
    }
}