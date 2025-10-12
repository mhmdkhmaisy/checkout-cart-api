<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CacheFile;
use App\Models\CacheBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CacheController extends Controller
{
    /**
     * Get cache manifest with directory structure
     */
    public function manifest(): JsonResponse
    {
        try {
            $manifestPath = storage_path('app/manifests/cache_manifest.json');
            
            if (!file_exists($manifestPath)) {
                return response()->json([
                    'error' => 'Manifest not found',
                    'message' => 'Cache manifest has not been generated yet.'
                ], 404);
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);
            
            return response()->json($manifest);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load manifest',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get directory structure tree
     */
    public function directoryTree(): JsonResponse
    {
        try {
            $files = CacheFile::orderBy('relative_path')->get();
            $tree = $this->buildDirectoryTree($files);
            
            return response()->json([
                'success' => true,
                'data' => $tree,
                'stats' => [
                    'total_files' => CacheFile::files()->count(),
                    'total_directories' => CacheFile::directories()->count(),
                    'total_size' => CacheFile::files()->sum('size')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to build directory tree',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download cache files as compressed bundle with directory structure preserved
     */
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $requestedFiles = $request->query('files');
            $requestedPaths = $request->query('paths');
            $mode = $request->query('mode', 'full');
            $preserveStructure = $request->query('preserve_structure', 'true') === 'true';
            
            // Parse requested files and paths
            if ($requestedFiles && is_string($requestedFiles)) {
                $requestedFiles = explode(',', $requestedFiles);
            }
            if ($requestedPaths && is_string($requestedPaths)) {
                $requestedPaths = explode(',', $requestedPaths);
            }

            // Generate cache key including structure preference
            $cacheKey = md5(json_encode([
                'files' => $requestedFiles,
                'paths' => $requestedPaths,
                'mode' => $mode,
                'preserve_structure' => $preserveStructure
            ]));

            // Check for existing non-expired bundle
            $existing = CacheBundle::where('bundle_key', $cacheKey)
                ->where('expires_at', '>', now())
                ->first();

            if ($existing && $existing->existsOnDisk()) {
                return response()->download($existing->full_path);
            }

            // Get files to bundle
            $filesQuery = CacheFile::query();
            
            if ($mode !== 'full') {
                if ($requestedFiles) {
                    $filesQuery->whereIn('filename', $requestedFiles);
                }
                if ($requestedPaths) {
                    $filesQuery->orWhere(function($q) use ($requestedPaths) {
                        foreach ($requestedPaths as $path) {
                            $q->orWhere('relative_path', 'LIKE', $path . '%');
                        }
                    });
                }
            }
            
            $files = $filesQuery->files()->get(); // Only get actual files, not directories

            if ($files->isEmpty()) {
                return response()->json([
                    'error' => 'No files found',
                    'message' => 'No cache files match your request.'
                ], 404);
            }

            // Create compressed bundle with structure
            $bundlePath = $this->createStructuredBundle($files, $cacheKey, $preserveStructure);
            
            if (!$bundlePath) {
                return response()->json([
                    'error' => 'Bundle creation failed',
                    'message' => 'Failed to create compressed bundle.'
                ], 500);
            }

            // Store bundle info in database
            CacheBundle::create([
                'bundle_key' => $cacheKey,
                'file_list' => $files->pluck('filename')->toArray(),
                'path' => $bundlePath,
                'size' => filesize(storage_path("app/{$bundlePath}")),
                'expires_at' => now()->addDay(),
            ]);

            return response()->download(storage_path("app/{$bundlePath}"));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download individual cache file
     */
    public function downloadFile(Request $request, string $filename): BinaryFileResponse|JsonResponse
    {
        try {
            $relativePath = $request->query('path');
            
            $query = CacheFile::where('filename', $filename);
            if ($relativePath) {
                $query->where('relative_path', $relativePath);
            }
            
            $cacheFile = $query->first();

            if (!$cacheFile) {
                return response()->json([
                    'error' => 'File not found',
                    'message' => "Cache file '{$filename}' not found."
                ], 404);
            }

            if (!$cacheFile->existsOnDisk()) {
                return response()->json([
                    'error' => 'File missing',
                    'message' => "Cache file exists in database but not on disk."
                ], 404);
            }

            return response()->download($cacheFile->full_path, $filename);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics with directory breakdown
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_files' => CacheFile::files()->count(),
                'total_directories' => CacheFile::directories()->count(),
                'total_size' => CacheFile::files()->sum('size'),
                'active_bundles' => CacheBundle::active()->count(),
                'expired_bundles' => CacheBundle::expired()->count(),
                'last_updated' => CacheFile::max('updated_at'),
                'files_by_extension' => CacheFile::files()
                    ->selectRaw('
                        SUBSTRING_INDEX(filename, ".", -1) as extension, 
                        COUNT(*) as count,
                        SUM(size) as total_size
                    ')
                    ->groupBy('extension')
                    ->get(),
                'files_by_type' => CacheFile::selectRaw('
                        file_type,
                        COUNT(*) as count,
                        COALESCE(SUM(size), 0) as total_size
                    ')
                    ->groupBy('file_type')
                    ->get(),
                'directory_depth_stats' => $this->getDirectoryDepthStats()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search files and directories
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $type = $request->query('type', 'all'); // all, file, directory
            $extension = $request->query('extension');
            
            if (!$query) {
                return response()->json([
                    'error' => 'Query required',
                    'message' => 'Search query parameter is required.'
                ], 400);
            }

            $filesQuery = CacheFile::where(function($q) use ($query) {
                $q->where('filename', 'LIKE', "%{$query}%")
                  ->orWhere('relative_path', 'LIKE', "%{$query}%");
            });

            if ($type !== 'all') {
                $filesQuery->where('file_type', $type);
            }

            if ($extension) {
                $filesQuery->where('filename', 'LIKE', "%.{$extension}");
            }

            $results = $filesQuery->orderBy('relative_path')->get();

            return response()->json([
                'success' => true,
                'data' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create compressed bundle from files with directory structure preserved
     */
    private function createStructuredBundle($files, string $cacheKey, bool $preserveStructure = true): ?string
    {
        try {
            $bundleName = "bundle_{$cacheKey}";
            $tarPath = storage_path("app/cache_bundles/{$bundleName}.tar");
            $gzPath = "cache_bundles/{$bundleName}.tar.gz";
            
            // Ensure cache_bundles directory exists
            if (!is_dir(storage_path('app/cache_bundles'))) {
                mkdir(storage_path('app/cache_bundles'), 0755, true);
            }

            // Create tar archive
            $tar = new \PharData($tarPath);
            
            foreach ($files as $file) {
                if ($file->existsOnDisk()) {
                    // Determine the archive path
                    if ($preserveStructure && $file->relative_path) {
                        // Use the relative path to maintain directory structure
                        $archivePath = $file->relative_path;
                    } else {
                        // Flatten structure - just use filename
                        $archivePath = $file->filename;
                    }
                    
                    $tar->addFile($file->full_path, $archivePath);
                }
            }

            // Compress to gzip
            $tar->compress(\Phar::GZ);
            
            // Remove uncompressed tar file
            if (file_exists($tarPath)) {
                unlink($tarPath);
            }

            return $gzPath;

        } catch (\Exception $e) {
            \Log::error('Bundle creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build directory tree structure from files
     */
    private function buildDirectoryTree($files): array
    {
        $tree = [];
        
        foreach ($files as $file) {
            $path = $file->relative_path ?: $file->filename;
            $parts = explode('/', $path);
            $current = &$tree;
            
            // Build directory structure
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $dir = $parts[$i];
                if (!isset($current[$dir])) {
                    $current[$dir] = [
                        'type' => 'directory',
                        'name' => $dir,
                        'children' => []
                    ];
                }
                $current = &$current[$dir]['children'];
            }
            
            // Add the file
            $filename = end($parts);
            $current[$filename] = [
                'type' => $file->file_type,
                'name' => $filename,
                'size' => $file->size,
                'hash' => $file->hash,
                'mime_type' => $file->mime_type,
                'updated_at' => $file->updated_at
            ];
        }
        
        return array_values($tree);
    }

    /**
     * Get directory depth statistics
     */
    private function getDirectoryDepthStats(): array
    {
        $files = CacheFile::whereNotNull('relative_path')->get();
        $depthStats = [];
        
        foreach ($files as $file) {
            $depth = substr_count($file->relative_path, '/');
            if (!isset($depthStats[$depth])) {
                $depthStats[$depth] = 0;
            }
            $depthStats[$depth]++;
        }
        
        return $depthStats;
    }
}