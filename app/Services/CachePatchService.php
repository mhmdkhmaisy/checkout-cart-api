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
        $zipPath = "cache/patches/{$newVersion}.zip";
        $filesToZip = $isFirstPatch ? $newFiles : $diff;
        $this->createZipFromDatabase(array_keys($filesToZip), $zipPath);

        $zipFullPath = Storage::path($zipPath);
        $zipSize = file_exists($zipFullPath) ? filesize($zipFullPath) : 0;

        $manifestPath = "cache/manifests/{$newVersion}.json";
        Storage::put($manifestPath, json_encode($newFiles));

        return [
            'version' => $newVersion,
            'base_version' => $currentVersion,
            'path' => $zipPath,
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

        $newZipPath = "cache/patches/{$newBaseVersion}.zip";
        $this->createZip($tempDir, array_keys($fullManifest), $newZipPath);

        Storage::deleteDirectory($tempDir);

        $zipFullPath = Storage::path($newZipPath);
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
        $patches = CachePatch::all()
            ->filter(function($patch) use ($fromVersion) {
                return version_compare($patch->version, $fromVersion, '>');
            })
            ->sortBy(function($patch) {
                return $patch->version;
            }, SORT_NATURAL);

        if ($patches->isEmpty()) {
            return null;
        }

        $latestVersion = CachePatch::getLatestVersion();
        $combinedZipPath = "cache/combined/from_{$fromVersion}_to_{$latestVersion}.zip";

        // Check if cached version already exists
        if (Storage::exists($combinedZipPath)) {
            return $combinedZipPath;
        }

        $zip = new ZipArchive;
        $fullPath = Storage::path($combinedZipPath);
        
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $addedFiles = [];
        
        foreach ($patches->reverse() as $patch) {
            $patchZip = new ZipArchive;
            if ($patchZip->open(Storage::path($patch->path)) === true) {
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
        return $combinedZipPath;
    }
}
