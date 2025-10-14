<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CacheFile;
use App\Models\CachePatch;
use App\Services\CachePatchService;
use Illuminate\Support\Facades\Storage;

class GenerateCacheManifest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:generate-manifest {--force : Force regeneration even if manifest exists}';

    /**
     * The console command description.
     */
    protected $description = 'Generate the public cache manifest with directory structure for client synchronization';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('🔄 Generating cache manifest with directory structure...');

            // Get all cache files with directory structure
            $files = CacheFile::files()
                ->orderBy('relative_path')
                ->orderBy('filename')
                ->get(['filename', 'relative_path', 'size', 'hash', 'file_type', 'mime_type', 'updated_at']);

            if ($files->isEmpty()) {
                $this->warn('⚠️  No cache files found in database.');
            }

            // Calculate statistics
            $totalSize = $files->sum('size');
            $totalFiles = $files->count();
            $totalDirectories = CacheFile::directories()->count();

            // Build directory structure
            $directoryTree = $this->buildDirectoryStructure($files);

            // Build manifest structure
            $manifest = [
                'version' => now()->format('YmdHis'),
                'generated_at' => now()->toISOString(),
                'total_files' => $totalFiles,
                'total_directories' => $totalDirectories,
                'total_size' => $totalSize,
                'structure' => [
                    'preserve_paths' => true,
                    'directory_tree' => $directoryTree,
                    'flat_files' => $files->map(function ($file) {
                        return [
                            'filename' => $file->filename,
                            'relative_path' => $file->relative_path,
                            'display_path' => $file->relative_path ?: $file->filename,
                            'size' => $file->size,
                            'hash' => $file->hash,
                            'mime_type' => $file->mime_type,
                            'updated_at' => $file->updated_at->toISOString(),
                        ];
                    })->values()->toArray()
                ],
                'files' => $files->map(function ($file) {
                    return [
                        'filename' => $file->filename,
                        'relative_path' => $file->relative_path,
                        'size' => $file->size,
                        'hash' => $file->hash,
                        'mime_type' => $file->mime_type,
                        'updated_at' => $file->updated_at->toISOString(),
                    ];
                })->values()->toArray(),
                'metadata' => [
                    'format_version' => '2.0',
                    'hash_algorithm' => 'sha256',
                    'compression' => 'tar.gz',
                    'supports_directory_structure' => true,
                    'api_endpoints' => [
                        'manifest' => '/api/cache/manifest',
                        'download' => '/api/cache/download',
                        'download_file' => '/api/cache/file/{filename}',
                        'directory_tree' => '/api/cache/directory-tree',
                        'search' => '/api/cache/search',
                        'stats' => '/api/cache/stats'
                    ]
                ],
                'statistics' => [
                    'files_by_extension' => $this->getFilesByExtension($files),
                    'files_by_mime_type' => $this->getFilesByMimeType($files),
                    'directory_depth_stats' => $this->getDirectoryDepthStats($files),
                    'size_distribution' => $this->getSizeDistribution($files)
                ]
            ];

            // Ensure manifests directory exists
            $manifestDir = storage_path('app/manifests');
            if (!is_dir($manifestDir)) {
                mkdir($manifestDir, 0755, true);
            }

            // Write manifest file
            $manifestPath = $manifestDir . '/cache_manifest.json';
            file_put_contents(
                $manifestPath,
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            // Also create a backup with timestamp
            $backupPath = $manifestDir . '/cache_manifest_' . $manifest['version'] . '.json';
            copy($manifestPath, $backupPath);

            // Clean up old manifest backups (keep only the last 10)
            $this->cleanupOldManifests($manifestDir);

            $this->info("✅ Cache manifest generated successfully!");
            $this->info("📁 Files: {$totalFiles}");
            $this->info("📂 Directories: {$totalDirectories}");
            $this->info("📦 Total Size: " . $this->formatBytes($totalSize));
            $this->info("🗂️  Manifest: {$manifestPath}");
            $this->info("💾 Backup: {$backupPath}");

            // Generate patch after manifest creation
            try {
                $this->info('');
                $this->info('🔄 Generating cache patch...');
                
                $patchService = new CachePatchService();
                $patchData = $patchService->generatePatch('cache_files');
                
                CachePatch::create([
                    'version' => $patchData['version'],
                    'base_version' => $patchData['base_version'],
                    'path' => $patchData['path'],
                    'file_manifest' => $patchData['file_manifest'],
                    'file_count' => $patchData['file_count'],
                    'size' => $patchData['size'],
                    'is_base' => $patchData['is_base'],
                ]);
                
                $patchType = $patchData['is_base'] ? 'Base' : 'Delta';
                $this->info("✅ {$patchType} patch v{$patchData['version']} generated successfully!");
                $this->info("📦 Patch files: {$patchData['file_count']}");
                $this->info("💾 Patch size: " . $this->formatBytes($patchData['size']));
                
                if ($patchService->shouldMergePatches()) {
                    $this->warn('⚠️  Merge recommended: ' . CachePatch::patches()->count() . ' incremental patches exist.');
                    $this->line('   Run: php artisan cache:merge-patches');
                }
            } catch (\Exception $e) {
                $this->warn('⚠️  Patch generation failed: ' . $e->getMessage());
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate cache manifest: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Build directory structure tree
     */
    private function buildDirectoryStructure($files): array
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
                        'path' => implode('/', array_slice($parts, 0, $i + 1)),
                        'children' => []
                    ];
                }
                $current = &$current[$dir]['children'];
            }
            
            // Add the file
            $filename = end($parts);
            $current[$filename] = [
                'type' => 'file',
                'name' => $filename,
                'path' => $path,
                'size' => $file->size,
                'hash' => $file->hash,
                'mime_type' => $file->mime_type,
                'updated_at' => $file->updated_at->toISOString()
            ];
        }
        
        return $this->convertTreeToArray($tree);
    }

    /**
     * Convert associative tree to indexed array
     */
    private function convertTreeToArray($tree): array
    {
        $result = [];
        foreach ($tree as $key => $value) {
            if (isset($value['children'])) {
                $value['children'] = $this->convertTreeToArray($value['children']);
            }
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Get files grouped by extension
     */
    private function getFilesByExtension($files): array
    {
        $extensions = [];
        foreach ($files as $file) {
            $ext = pathinfo($file->filename, PATHINFO_EXTENSION) ?: 'no-extension';
            if (!isset($extensions[$ext])) {
                $extensions[$ext] = ['count' => 0, 'total_size' => 0];
            }
            $extensions[$ext]['count']++;
            $extensions[$ext]['total_size'] += $file->size;
        }
        return $extensions;
    }

    /**
     * Get files grouped by MIME type
     */
    private function getFilesByMimeType($files): array
    {
        $mimeTypes = [];
        foreach ($files as $file) {
            $mimeType = $file->mime_type ?: 'unknown';
            if (!isset($mimeTypes[$mimeType])) {
                $mimeTypes[$mimeType] = ['count' => 0, 'total_size' => 0];
            }
            $mimeTypes[$mimeType]['count']++;
            $mimeTypes[$mimeType]['total_size'] += $file->size;
        }
        return $mimeTypes;
    }

    /**
     * Get directory depth statistics
     */
    private function getDirectoryDepthStats($files): array
    {
        $depthStats = [];
        foreach ($files as $file) {
            $depth = $file->relative_path ? substr_count($file->relative_path, '/') : 0;
            if (!isset($depthStats[$depth])) {
                $depthStats[$depth] = 0;
            }
            $depthStats[$depth]++;
        }
        return $depthStats;
    }

    /**
     * Get file size distribution
     */
    private function getSizeDistribution($files): array
    {
        $ranges = [
            'tiny' => ['min' => 0, 'max' => 1024, 'count' => 0, 'total_size' => 0], // < 1KB
            'small' => ['min' => 1024, 'max' => 102400, 'count' => 0, 'total_size' => 0], // 1KB - 100KB
            'medium' => ['min' => 102400, 'max' => 10485760, 'count' => 0, 'total_size' => 0], // 100KB - 10MB
            'large' => ['min' => 10485760, 'max' => 104857600, 'count' => 0, 'total_size' => 0], // 10MB - 100MB
            'huge' => ['min' => 104857600, 'max' => PHP_INT_MAX, 'count' => 0, 'total_size' => 0] // > 100MB
        ];

        foreach ($files as $file) {
            foreach ($ranges as $key => &$range) {
                if ($file->size >= $range['min'] && $file->size < $range['max']) {
                    $range['count']++;
                    $range['total_size'] += $file->size;
                    break;
                }
            }
        }

        return $ranges;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Clean up old manifest backups, keeping only the latest 10
     */
    private function cleanupOldManifests(string $manifestDir): void
    {
        $backupFiles = glob($manifestDir . '/cache_manifest_*.json');
        
        if (count($backupFiles) <= 10) {
            return; // Keep all if 10 or fewer
        }

        // Sort by modification time (newest first)
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Keep the 10 newest, delete the rest
        $filesToDelete = array_slice($backupFiles, 10);
        
        foreach ($filesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
                $this->line("🗑️  Deleted old manifest backup: " . basename($file));
            }
        }
        
        if (count($filesToDelete) > 0) {
            $this->info("✨ Cleaned up " . count($filesToDelete) . " old manifest backup(s)");
        }
    }
}