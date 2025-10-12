<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CacheBundle;
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

        return view('admin.cache.bundles', compact('bundles', 'totalSize', 'activeBundles', 'expiredBundles'));
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
}