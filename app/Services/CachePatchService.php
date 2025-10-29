<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use App\Models\CachePatch;

class CachePatchService
{
    const PATCH_THRESHOLD = 15;

    public function generatePatchFromDatabase(?string $baseVersion = null): array
    {
        $currentVersion = CachePatch::getLatestVersion();
        $newVersion = CachePatch::incrementVersion($currentVersion);

        // Build new files list from database
        $newFiles = [];
        $cacheFiles = \App\Models\CacheFile::files()->get();
        foreach ($cacheFiles as $file) {
            $relativePath = $file->relative_path ? ($file->relative_path . '/' . $file->filename) : $file->filename;
            $newFiles[$relativePath] = $file->hash;
        }
        
        $oldManifest = [];
        if ($currentVersion) {
            $manifestPath = "cache/manifests/{$currentVersion}.json";
            if (Storage::exists($manifestPath)) {
                $oldManifest = json_decode(Storage::get($manifestPath), true) ?? [];
            } else {
                $latestBase = CachePatch::base()->latest()->first();
                $patches = CachePatch::patches()->get()
                    ->filter(function($patch) use ($currentVersion) {
                        return version_compare($patch->version, $currentVersion, '<=');
                    })
                    ->sortBy(function($patch) {
                        return $patch->version;
                    }, SORT_NATURAL);
                
                $oldManifest = $latestBase ? $latestBase->file_manifest : [];
                foreach ($patches as $patch) {
                    foreach ($patch->file_manifest as $file => $hash) {
                        $oldManifest[$file] = $hash;
                    }
                }
            }
        }

        // Detect added/changed files
        $diff = [];
        foreach ($newFiles as $path => $hash) {
            if (!isset($oldManifest[$path]) || $oldManifest[$path] !== $hash) {
                $diff[$path] = $hash;
            }
        }

        // Detect removed files
        $removed = [];
        foreach ($oldManifest as $path => $hash) {
            if (!isset($newFiles[$path])) {
                $removed[] = $path;
            }
        }

        // If no changes detected (no additions, no modifications, no removals), skip patch creation
        if (empty($diff) && empty($removed) && $currentVersion) {
            return [
                'no_changes' => true,
                'version' => $currentVersion,
                'message' => 'No changes detected - patch creation skipped'
            ];
        }

        $isFirstPatch = !$currentVersion;
        
        // Create delta patch file
        $deltaZipPath = $isFirstPatch ? "patches/base_{$newVersion}.zip" : "patches/delta_{$currentVersion}_{$newVersion}.zip";
        $filesToZip = $isFirstPatch ? $newFiles : $diff;
        $this->createZipInPublic(array_keys($filesToZip), $deltaZipPath);

        $zipFullPath = public_path($deltaZipPath);
        $zipSize = file_exists($zipFullPath) ? filesize($zipFullPath) : 0;

        $manifestPath = "cache/manifests/{$newVersion}.json";
        Storage::put($manifestPath, json_encode($newFiles));

        // Update or create the combined patch for new users
        if (!$isFirstPatch) {
            $this->updateCombinedPatch($deltaZipPath, $newVersion);
        } else {
            // For first patch, combined = base
            $combinedPath = "patches/combined_0.0.0_{$newVersion}.zip";
            if (!file_exists(public_path($combinedPath))) {
                copy(public_path($deltaZipPath), public_path($combinedPath));
            }
        }

        // Update the manifest file
        $this->updatePatchManifest();

        return [
            'version' => $newVersion,
            'base_version' => $currentVersion,
            'path' => $deltaZipPath,
            'manifest' => $manifestPath,
            'file_manifest' => $isFirstPatch ? $newFiles : $diff,
            'diff' => $diff,
            'file_count' => count($isFirstPatch ? $newFiles : $diff),
            'size' => $zipSize,
            'is_base' => $isFirstPatch,
        ];
    }

    public function scanDir(string $dir): array
    {
        $result = [];
        $basePath = Storage::path($dir);
        
        if (!is_dir($basePath)) {
            return $result;
        }

        $files = Storage::allFiles($dir);
        
        foreach ($files as $file) {
            $relativePath = str_replace($dir . '/', '', $file);
            
            if (str_starts_with($relativePath, 'patches/') || 
                str_starts_with($relativePath, 'manifests/') ||
                str_starts_with($relativePath, 'combined/') ||
                str_starts_with($relativePath, 'temp/')) {
                continue;
            }
            
            $fullPath = Storage::path($file);
            if (file_exists($fullPath)) {
                $result[$relativePath] = md5_file($fullPath);
            }
        }
        
        return $result;
    }

    public function createZipFromDatabase(array $relativePaths, string $zipPath): bool
    {
        $zip = new ZipArchive;
        $fullPath = Storage::path($zipPath);
        
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($relativePaths as $relativePath) {
            // Parse relative path to get directory and filename
            $pathParts = explode('/', $relativePath);
            $filename = array_pop($pathParts);
            $directoryPath = !empty($pathParts) ? implode('/', $pathParts) : null;
            
            // Find file in database
            $cacheFile = \App\Models\CacheFile::where('filename', $filename)
                ->where('relative_path', $directoryPath)
                ->first();
            
            if ($cacheFile && file_exists(storage_path('app/' . $cacheFile->path))) {
                $sourceFile = storage_path('app/' . $cacheFile->path);
                $zip->addFile($sourceFile, $relativePath);
            }
        }

        $zip->close();
        return true;
    }

    private function createZipInPublic(array $relativePaths, string $zipPath): bool
    {
        $zip = new ZipArchive;
        $fullPath = public_path($zipPath);
        
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($relativePaths as $relativePath) {
            // Parse relative path to get directory and filename
            $pathParts = explode('/', $relativePath);
            $filename = array_pop($pathParts);
            $directoryPath = !empty($pathParts) ? implode('/', $pathParts) : null;
            
            // Find file in database
            $cacheFile = \App\Models\CacheFile::where('filename', $filename)
                ->where('relative_path', $directoryPath)
                ->first();
            
            if ($cacheFile && file_exists(storage_path('app/' . $cacheFile->path))) {
                $sourceFile = storage_path('app/' . $cacheFile->path);
                $zip->addFile($sourceFile, $relativePath);
            }
        }

        $zip->close();
        return true;
    }
    
    public function createZip(string $baseDir, array $files, string $zipPath): bool
    {
        $zip = new ZipArchive;
        $fullPath = Storage::path($zipPath);
        
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($files as $file) {
            $sourceFile = Storage::path("{$baseDir}/{$file}");
            if (file_exists($sourceFile)) {
                $zip->addFile($sourceFile, $file);
            }
        }

        $zip->close();
        return true;
    }

    private function createZipInPublicFromTemp(string $baseDir, array $files, string $zipPath): bool
    {
        $zip = new ZipArchive;
        $fullPath = public_path($zipPath);
        
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($files as $file) {
            $sourceFile = Storage::path("{$baseDir}/{$file}");
            if (file_exists($sourceFile)) {
                $zip->addFile($sourceFile, $file);
            }
        }

        $zip->close();
        return true;
    }

    public function shouldMergePatches(): bool
    {
        $patchCount = CachePatch::patches()->count();
        return $patchCount >= self::PATCH_THRESHOLD;
    }

    public function mergePatches(): ?CachePatch
    {
        $latestBase = CachePatch::base()->latest()->first();
        $patches = CachePatch::patches()->orderBy('created_at', 'asc')->get();

        if ($patches->isEmpty()) {
            return null;
        }

        $latestVersion = CachePatch::getLatestVersion();
        $manifestPath = "cache/manifests/{$latestVersion}.json";
        
        if (Storage::exists($manifestPath)) {
            $fullManifest = json_decode(Storage::get($manifestPath), true) ?? [];
        } else {
            $fullManifest = $latestBase ? $latestBase->file_manifest : [];
            
            foreach ($patches as $patch) {
                foreach ($patch->file_manifest as $file => $hash) {
                    $fullManifest[$file] = $hash;
                }
            }
        }

        $parts = explode('.', $latestVersion);
        $parts[0] = (int)$parts[0] + 1;
        $parts[1] = 0;
        $parts[2] = 0;
        $newBaseVersion = implode('.', $parts);

        $tempDir = 'cache/temp/merge_' . time();
        Storage::makeDirectory($tempDir);

        foreach ($fullManifest as $file => $hash) {
            $extracted = false;
            
            foreach ($patches->reverse() as $patch) {
                if (isset($patch->file_manifest[$file])) {
                    if ($this->extractFileFromZip($patch->path, $file, "{$tempDir}/{$file}")) {
                        $extracted = true;
                        break;
                    }
                }
            }
            
            if (!$extracted && $latestBase && isset($latestBase->file_manifest[$file])) {
                $this->extractFileFromZip($latestBase->path, $file, "{$tempDir}/{$file}");
            }
        }

        $newZipPath = "patches/{$newBaseVersion}.zip";
        $this->createZipInPublicFromTemp($tempDir, array_keys($fullManifest), $newZipPath);

        Storage::deleteDirectory($tempDir);

        $zipFullPath = public_path($newZipPath);
        $zipSize = file_exists($zipFullPath) ? filesize($zipFullPath) : 0;

        $newManifestPath = "cache/manifests/{$newBaseVersion}.json";
        Storage::put($newManifestPath, json_encode($fullManifest));

        $newPatch = CachePatch::create([
            'version' => $newBaseVersion,
            'base_version' => null,
            'path' => $newZipPath,
            'file_manifest' => $fullManifest,
            'file_count' => count($fullManifest),
            'size' => $zipSize,
            'is_base' => true,
        ]);

        foreach ($patches as $patch) {
            $patch->deleteFile();
            $patch->delete();
        }

        if ($latestBase) {
            $latestBase->deleteFile();
            $latestBase->delete();
        }

        // Update manifest after merge
        $this->updatePatchManifest();

        return $newPatch;
    }

    private function extractFileFromZip(string $zipPath, string $file, string $destination): bool
    {
        $zip = new ZipArchive;
        $fullZipPath = Storage::path($zipPath);
        
        if ($zip->open($fullZipPath) !== true) {
            return false;
        }

        $destinationPath = Storage::path($destination);
        $directory = dirname($destinationPath);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $content = $zip->getFromName($file);
        if ($content !== false) {
            file_put_contents($destinationPath, $content);
            $zip->close();
            return true;
        }

        $zip->close();
        return false;
    }

    public function combinePatchesForDownload(string $fromVersion): ?string
    {
        $latestVersion = CachePatch::getLatestVersion();
        
        if (!$latestVersion) {
            return null;
        }

        // Special case: new user (version 0.0.0) - serve the pre-built combined patch
        if ($fromVersion === '0.0.0') {
            $combinedPath = "patches/combined_0.0.0_{$latestVersion}.zip";
            if (file_exists(public_path($combinedPath))) {
                return $combinedPath;
            }
            // Fallback to building it if it doesn't exist
            return $this->buildCombinedPatchLegacy($fromVersion);
        }

        // Get all patches newer than the client's version, sorted by version
        $patches = CachePatch::all()
            ->filter(function($patch) use ($fromVersion) {
                return version_compare($patch->version, $fromVersion, '>');
            })
            ->sortBy(function($patch) {
                return $patch->version;
            }, SORT_NATURAL)
            ->values();

        if ($patches->isEmpty()) {
            return null;
        }

        // Optimize: If there's a base patch in the filtered set, remove any incremental patches
        // older than it (they're redundant since the base already includes everything)
        $baseIndex = $patches->search(function($patch) {
            return $patch->is_base;
        });

        if ($baseIndex !== false) {
            // Keep only the base patch and any patches newer than it
            $patches = $patches->slice($baseIndex)->values();
        }

        // If only ONE patch is needed, serve it directly
        if ($patches->count() === 1) {
            $singlePatch = $patches->first();
            if ($singlePatch->existsOnDisk()) {
                return $singlePatch->path;
            }
            return null;
        }

        // Multiple patches needed - build combined version on-the-fly
        return $this->buildCombinedPatchLegacy($fromVersion);
    }

    /**
     * Legacy method to build combined patch on-the-fly for partial updates
     * Only used when user has an intermediate version
     */
    private function buildCombinedPatchLegacy(string $fromVersion): ?string
    {
        $latestVersion = CachePatch::getLatestVersion();
        $patches = CachePatch::all()
            ->filter(function($patch) use ($fromVersion) {
                return version_compare($patch->version, $fromVersion, '>');
            })
            ->sortBy(function($patch) {
                return $patch->version;
            }, SORT_NATURAL)
            ->values();

        if ($patches->isEmpty()) {
            return null;
        }

        $combinedZipPath = "patches/combined_from_{$fromVersion}_to_{$latestVersion}.zip";
        $publicPath = public_path($combinedZipPath);

        // Check if cached version already exists
        if (file_exists($publicPath)) {
            return $combinedZipPath;
        }

        // Add lock to prevent concurrent builds
        $lockPath = public_path("patches/.lock_from_{$fromVersion}_to_{$latestVersion}");
        $maxWaitAttempts = 15;
        $attempt = 0;

        while (file_exists($lockPath) && $attempt < $maxWaitAttempts) {
            sleep(2);
            $attempt++;
            
            if (file_exists($publicPath)) {
                return $combinedZipPath;
            }
        }

        if (file_exists($lockPath)) {
            $lockAge = time() - filemtime($lockPath);
            if ($lockAge > 120) {
                unlink($lockPath);
            }
        }

        $lockDir = dirname($lockPath);
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0755, true);
        }
        file_put_contents($lockPath, time());

        try {
            $zip = new ZipArchive;
            
            $directory = dirname($publicPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if ($zip->open($publicPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                if (file_exists($lockPath)) unlink($lockPath);
                return null;
            }

            $addedFiles = [];
            
            foreach ($patches->reverse() as $patch) {
                $patchZip = new ZipArchive;
                $patchPath = public_path($patch->path);
                if ($patchZip->open($patchPath) === true) {
                    for ($i = 0; $i < $patchZip->numFiles; $i++) {
                        $filename = $patchZip->getNameIndex($i);
                        
                        if (!in_array($filename, $addedFiles)) {
                            $content = $patchZip->getFromIndex($i);
                            $zip->addFromString($filename, $content);
                            $addedFiles[] = $filename;
                        }
                    }
                    $patchZip->close();
                }
            }

            $zip->close();
            
            if (file_exists($lockPath)) unlink($lockPath);
            
            return $combinedZipPath;
        } catch (\Exception $e) {
            if (file_exists($lockPath)) unlink($lockPath);
            throw $e;
        }
    }

    /**
     * Update combined patch incrementally by adding/replacing files from new delta
     * This avoids recreating the entire combined ZIP from scratch
     */
    private function updateCombinedPatch(string $deltaZipPath, string $newVersion): bool
    {
        // Find the current combined patch
        $patchesDir = public_path('patches');
        $combinedFiles = glob($patchesDir . '/combined_0.0.0_*.zip');
        
        $currentCombinedPath = null;
        if (!empty($combinedFiles)) {
            // Get the most recent combined file
            usort($combinedFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $currentCombinedPath = $combinedFiles[0];
        }

        $newCombinedPath = public_path("patches/combined_0.0.0_{$newVersion}.zip");

        // If no existing combined patch, create new one from delta
        if (!$currentCombinedPath || !file_exists($currentCombinedPath)) {
            return copy(public_path($deltaZipPath), $newCombinedPath);
        }

        // Copy current combined to new location first
        copy($currentCombinedPath, $newCombinedPath);

        // Open both ZIPs
        $combined = new ZipArchive();
        $delta = new ZipArchive();

        if ($combined->open($newCombinedPath) !== true) {
            return false;
        }

        if ($delta->open(public_path($deltaZipPath)) !== true) {
            $combined->close();
            return false;
        }

        // Loop over all files in delta and add/replace in combined
        for ($i = 0; $i < $delta->numFiles; $i++) {
            $filename = $delta->getNameIndex($i);
            $contents = $delta->getFromIndex($i);

            // Delete old file if exists (ZipArchive will replace on add)
            $combined->deleteName($filename);
            
            // Add new/updated file
            $combined->addFromString($filename, $contents);
        }

        $combined->close();
        $delta->close();

        // Delete old combined patch to save space
        if ($currentCombinedPath !== $newCombinedPath && file_exists($currentCombinedPath)) {
            unlink($currentCombinedPath);
        }

        return true;
    }

    /**
     * Generate and update the patch manifest file
     * This provides a single source of truth for all available patches
     */
    private function updatePatchManifest(): void
    {
        $this->updatePatchManifestPublic();
    }

    /**
     * Public method to update the patch manifest
     * Can be called from controllers
     */
    public function updatePatchManifestPublic(): void
    {
        $latestVersion = CachePatch::getLatestVersion();
        $patches = CachePatch::orderBy('created_at', 'asc')->get();

        $manifestData = [
            'latest_version' => $latestVersion ?? '0.0.0',
            'generated_at' => now()->toISOString(),
            'patches' => []
        ];

        // Add all individual patches
        foreach ($patches as $patch) {
            $manifestData['patches'][] = [
                'from' => $patch->base_version ?? '0.0.0',
                'to' => $patch->version,
                'file' => basename($patch->path),
                'size' => $patch->size,
                'file_count' => $patch->file_count,
                'is_base' => $patch->is_base,
                'created_at' => $patch->created_at->toISOString(),
            ];
        }

        // Add combined patch entry if it exists
        if ($latestVersion) {
            $combinedPath = public_path("patches/combined_0.0.0_{$latestVersion}.zip");
            if (file_exists($combinedPath)) {
                $manifestData['patches'][] = [
                    'from' => '0.0.0',
                    'to' => $latestVersion,
                    'file' => "combined_0.0.0_{$latestVersion}.zip",
                    'size' => filesize($combinedPath),
                    'file_count' => null,
                    'is_base' => false,
                    'is_combined' => true,
                    'created_at' => date('c', filemtime($combinedPath)),
                ];
            }
        }

        // Save manifest to public/patches/manifest.json
        $manifestPath = public_path('patches/manifest.json');
        $dir = dirname($manifestPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($manifestPath, json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Get patch manifest data
     */
    public function getPatchManifest(): ?array
    {
        $manifestPath = public_path('patches/manifest.json');
        
        if (!file_exists($manifestPath)) {
            return null;
        }

        $content = file_get_contents($manifestPath);
        return json_decode($content, true);
    }
}
