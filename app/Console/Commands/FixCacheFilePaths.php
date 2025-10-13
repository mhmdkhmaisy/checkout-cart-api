<?php

namespace App\Console\Commands;

use App\Models\CacheFile;
use Illuminate\Console\Command;

class FixCacheFilePaths extends Command
{
    protected $signature = 'cache:fix-paths';
    
    protected $description = 'Fix relative_path values that incorrectly contain filenames';

    public function handle()
    {
        $this->info('🔍 Scanning for cache files with incorrect relative_path values...');
        
        // Get all files (not directories)
        $files = CacheFile::where('file_type', 'file')->get();
        
        $fixed = 0;
        $unchanged = 0;
        
        foreach ($files as $file) {
            $originalPath = $file->relative_path;
            
            // Skip if already null/empty (root files)
            if (empty($originalPath)) {
                $unchanged++;
                continue;
            }
            
            // Use metadata to validate if path needs fixing
            $metadata = $file->metadata ? json_decode(json_encode($file->metadata), true) : null;
            $metadataFullPath = $metadata['relative_path'] ?? null;
            
            // Case 1: Metadata has full path with filename (nested files)
            if ($metadataFullPath && str_ends_with($metadataFullPath, '/' . $file->filename)) {
                // Extract expected directory path from metadata
                $expectedDirPath = substr($metadataFullPath, 0, -(strlen('/' . $file->filename)));
                $expectedDirPath = empty($expectedDirPath) ? null : $expectedDirPath;
                
                // If current relative_path doesn't match expected directory path, fix it
                if ($originalPath !== $expectedDirPath) {
                    $file->relative_path = $expectedDirPath;
                    $file->save();
                    
                    $this->line("✅ Fixed: {$file->filename}");
                    $this->line("   Old path: {$originalPath}");
                    $this->line("   New path: " . ($expectedDirPath ?: '(root)'));
                    $fixed++;
                } else {
                    $unchanged++;
                }
            }
            // Case 2: Root-level file with metadata equal to filename (should be null)
            elseif ($metadataFullPath === $file->filename) {
                $file->relative_path = null;
                $file->save();
                
                $this->line("✅ Fixed root file: {$file->filename}");
                $this->line("   Old path: {$originalPath}");
                $this->line("   New path: (root)");
                $fixed++;
            }
            // Case 3: Single-segment path that matches filename (likely root file with incorrect path)
            elseif ($originalPath === $file->filename) {
                $file->relative_path = null;
                $file->save();
                
                $this->line("✅ Fixed root file: {$file->filename}");
                $this->line("   Old path: {$originalPath}");
                $this->line("   New path: (root)");
                $fixed++;
            }
            // Case 4: Multi-segment path ending with filename
            else {
                $segments = explode('/', $originalPath);
                $lastSegment = end($segments);
                
                // Only fix if it ends with the filename AND has more than one segment
                if ($lastSegment === $file->filename && count($segments) > 1) {
                    array_pop($segments);
                    $newPath = implode('/', $segments);
                    
                    $file->relative_path = empty($newPath) ? null : $newPath;
                    $file->save();
                    
                    $this->line("✅ Fixed: {$file->filename}");
                    $this->line("   Old path: {$originalPath}");
                    $this->line("   New path: " . ($newPath ?: '(root)'));
                    $fixed++;
                } else {
                    $unchanged++;
                }
            }
        }
        
        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   Fixed: {$fixed} files");
        $this->info("   Unchanged: {$unchanged} files");
        
        if ($fixed > 0) {
            $this->newLine();
            $this->warn('⚠️  Remember to regenerate the cache manifest:');
            $this->line('   php artisan cache:generate-manifest');
        }
        
        return Command::SUCCESS;
    }
}
