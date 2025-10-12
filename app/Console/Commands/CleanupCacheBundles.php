<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CacheBundle;
use Illuminate\Support\Facades\Storage;

class CleanupCacheBundles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:cleanup-bundles {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Remove expired cache bundles from storage and database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('🧹 Starting cache bundle cleanup...');

            // Get expired bundles
            $expiredBundles = CacheBundle::expired()->get();
            
            if ($expiredBundles->isEmpty()) {
                $this->info('✨ No expired bundles found. Nothing to clean up.');
                return Command::SUCCESS;
            }

            $totalSize = $expiredBundles->sum('size');
            $count = $expiredBundles->count();

            $this->info("Found {$count} expired bundles ({$this->formatBytes($totalSize)})");

            // Ask for confirmation unless --force is used
            if (!$this->option('force')) {
                if (!$this->confirm('Do you want to proceed with cleanup?')) {
                    $this->info('Cleanup cancelled.');
                    return Command::SUCCESS;
                }
            }

            $deletedCount = 0;
            $deletedSize = 0;
            $errors = [];

            foreach ($expiredBundles as $bundle) {
                try {
                    // Delete file from disk
                    if ($bundle->existsOnDisk()) {
                        if ($bundle->deleteFile()) {
                            $deletedSize += $bundle->size;
                        } else {
                            $errors[] = "Failed to delete file: {$bundle->path}";
                        }
                    }

                    // Delete database record
                    $bundle->delete();
                    $deletedCount++;

                    $this->line("🗑️  Deleted: {$bundle->bundle_key} ({$this->formatBytes($bundle->size)})");

                } catch (\Exception $e) {
                    $errors[] = "Error deleting bundle {$bundle->id}: " . $e->getMessage();
                }
            }

            // Also clean up orphaned files (files on disk without database records)
            $this->cleanupOrphanedFiles();

            // Report results
            $this->info("✅ Cleanup completed!");
            $this->info("🗑️  Deleted bundles: {$deletedCount}");
            $this->info("💾 Space freed: " . $this->formatBytes($deletedSize));

            if (!empty($errors)) {
                $this->warn("⚠️  Errors encountered:");
                foreach ($errors as $error) {
                    $this->error("   • {$error}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clean up orphaned bundle files
     */
    private function cleanupOrphanedFiles(): void
    {
        try {
            $bundleDir = storage_path('app/cache_bundles');
            
            if (!is_dir($bundleDir)) {
                return;
            }

            $files = glob($bundleDir . '/*.tar.gz');
            $dbBundles = CacheBundle::pluck('path')->map(function ($path) {
                return storage_path('app/' . $path);
            })->toArray();

            $orphanedFiles = array_diff($files, $dbBundles);
            $orphanedCount = 0;
            $orphanedSize = 0;

            foreach ($orphanedFiles as $file) {
                if (is_file($file)) {
                    $size = filesize($file);
                    if (unlink($file)) {
                        $orphanedCount++;
                        $orphanedSize += $size;
                        $this->line("🧹 Removed orphaned file: " . basename($file));
                    }
                }
            }

            if ($orphanedCount > 0) {
                $this->info("🧹 Cleaned up {$orphanedCount} orphaned files ({$this->formatBytes($orphanedSize)})");
            }

        } catch (\Exception $e) {
            $this->warn("Warning: Failed to clean orphaned files: " . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}