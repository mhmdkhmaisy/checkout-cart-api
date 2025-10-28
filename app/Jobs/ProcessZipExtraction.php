<?php

namespace App\Jobs;

use App\Models\UploadSession;
use App\Services\CachePatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ProcessZipExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $zipPath;
    protected $uploadSession;
    protected $extractionId;

    /**
     * Create a new job instance.
     */
    public function __construct($zipPath, UploadSession $uploadSession, $extractionId)
    {
        $this->zipPath = $zipPath;
        $this->uploadSession = $uploadSession;
        $this->extractionId = $extractionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $tempDir = storage_path('app/temp_zip_extract_' . $this->extractionId);
            
            // Update progress: Extracting
            Cache::put("zip_extraction_progress_{$this->extractionId}", [
                'status' => 'extracting',
                'phase' => 'extraction',
                'processed' => 0,
                'total' => 0,
                'message' => 'Extracting ZIP file...',
                'started_at' => now()
            ], 3600);

            // Step 1: Extract ZIP
            if (!mkdir($tempDir, 0755, true)) {
                throw new \Exception('Failed to create extraction directory');
            }

            $zip = new ZipArchive;
            if ($zip->open($this->zipPath) !== true) {
                throw new \Exception('Failed to open ZIP file');
            }

            $totalFiles = $zip->numFiles;
            $zip->extractTo($tempDir);
            $zip->close();

            // Update progress: Processing files
            Cache::put("zip_extraction_progress_{$this->extractionId}", [
                'status' => 'processing',
                'phase' => 'processing_files',
                'processed' => 0,
                'total' => $totalFiles,
                'message' => 'Processing extracted files...',
                'started_at' => now()
            ], 3600);

            // Step 2: Process extracted files and add to database
            $uploadedFiles = [];
            $skippedFiles = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $processed = 0;
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $fullPath = $fileInfo->getPathname();
                    $relativePath = str_replace($tempDir . DIRECTORY_SEPARATOR, '', $fullPath);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    
                    $result = $this->processExtractedFile($fileInfo, $relativePath);
                    if ($result['success']) {
                        if ($result['skipped']) {
                            $skippedFiles[] = $result['filename'];
                        } else {
                            $uploadedFiles[] = $result['filename'];
                        }
                    }

                    $processed++;
                    
                    // Update progress every 10 files
                    if ($processed % 10 === 0 || $processed === $totalFiles) {
                        Cache::put("zip_extraction_progress_{$this->extractionId}", [
                            'status' => 'processing',
                            'phase' => 'processing_files',
                            'processed' => $processed,
                            'total' => $totalFiles,
                            'uploaded_count' => count($uploadedFiles),
                            'skipped_count' => count($skippedFiles),
                            'message' => "Processing files: {$processed}/{$totalFiles}",
                            'started_at' => now()
                        ], 3600);
                    }
                }
            }

            // Update progress: Generating patch
            Cache::put("zip_extraction_progress_{$this->extractionId}", [
                'status' => 'generating_patch',
                'phase' => 'patch_generation',
                'processed' => $totalFiles,
                'total' => $totalFiles,
                'uploaded_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'message' => 'Generating patch...',
                'started_at' => now()
            ], 3600);

            // Step 3: Generate patch
            $patchService = new CachePatchService();
            $patchData = $patchService->generatePatchFromDatabase();
            
            $patchVersion = null;
            if (!isset($patchData['no_changes']) || !$patchData['no_changes']) {
                \App\Models\CachePatch::create([
                    'version' => $patchData['version'],
                    'base_version' => $patchData['base_version'],
                    'path' => $patchData['path'],
                    'file_manifest' => $patchData['file_manifest'],
                    'file_count' => $patchData['file_count'],
                    'size' => $patchData['size'],
                    'is_base' => $patchData['is_base'],
                ]);
                $patchVersion = $patchData['version'];
            } else {
                $patchVersion = $patchData['version'];
            }

            // Update progress: Cleaning up
            Cache::put("zip_extraction_progress_{$this->extractionId}", [
                'status' => 'cleaning_up',
                'phase' => 'cleanup',
                'processed' => $totalFiles,
                'total' => $totalFiles,
                'uploaded_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'patch_version' => $patchVersion,
                'message' => 'Cleaning up temporary files...',
                'started_at' => now()
            ], 3600);

            // Step 4: Cleanup - FIXED: Clean up ALL temporary directories
            Storage::deleteDirectory('temp_zips');
            Storage::deleteDirectory('temp_uploads');
            $this->deleteDirectory($tempDir);
            
            // Clean up upload session temp directory
            if (is_dir($this->uploadSession->temp_dir)) {
                $this->deleteDirectory($this->uploadSession->temp_dir);
            }
            
            // Mark upload session as completed
            $this->uploadSession->markAsCompleted();

            // Mark as completed
            $completedData = [
                'status' => 'completed',
                'phase' => 'completed',
                'processed' => $totalFiles,
                'total' => $totalFiles,
                'uploaded_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'patch_version' => $patchVersion,
                'message' => 'Processing completed successfully',
                'completed_at' => now()
            ];
            
            Cache::put("zip_extraction_progress_{$this->extractionId}", $completedData, 3600);
            
            Log::info('ZIP extraction completed and cached', [
                'extraction_id' => $this->extractionId,
                'cache_key' => "zip_extraction_progress_{$this->extractionId}",
                'data' => $completedData
            ]);

        } catch (\Exception $e) {
            // Cleanup on error
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            
            Storage::deleteDirectory('temp_zips');
            Storage::deleteDirectory('temp_uploads');
            
            if (is_dir($this->uploadSession->temp_dir)) {
                $this->deleteDirectory($this->uploadSession->temp_dir);
            }

            Log::error('ZIP → Extract → Patch background processing failed', [
                'extraction_id' => $this->extractionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark as failed
            Cache::put("zip_extraction_progress_{$this->extractionId}", [
                'status' => 'failed',
                'phase' => 'error',
                'error' => $e->getMessage(),
                'failed_at' => now()
            ], 3600);
        }
    }

    /**
     * Process an extracted file and save to database
     */
    private function processExtractedFile($fileInfo, $relativePath)
    {
        try {
            $filename = $fileInfo->getFilename();
            $filePath = $fileInfo->getPathname();
            $fileSize = $fileInfo->getSize();
            $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
            $hash = md5_file($filePath);
            
            // Parse relative path to get directory path (excluding filename)
            $pathParts = explode('/', $relativePath);
            array_pop($pathParts); // Remove filename
            $directoryPath = !empty($pathParts) ? implode('/', $pathParts) : null;
            
            // Check if file already exists with same hash
            $existingFile = \App\Models\CacheFile::where('filename', $filename)
                ->where('relative_path', $directoryPath)
                ->first();
            
            if ($existingFile && $existingFile->hash === $hash) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'filename' => $filename,
                    'message' => 'File already exists with same content'
                ];
            }
            
            // Store file in Laravel storage
            $storagePath = 'cache/' . ($directoryPath ? $directoryPath . '/' : '') . $filename;
            Storage::put($storagePath, file_get_contents($filePath));
            
            // Save or update database record
            if ($existingFile) {
                $existingFile->update([
                    'path' => $storagePath,
                    'size' => $fileSize,
                    'hash' => $hash,
                    'mime_type' => $mimeType,
                    'file_type' => 'file'
                ]);
            } else {
                \App\Models\CacheFile::create([
                    'filename' => $filename,
                    'path' => $storagePath,
                    'relative_path' => $directoryPath,
                    'size' => $fileSize,
                    'hash' => $hash,
                    'mime_type' => $mimeType,
                    'file_type' => 'file'
                ]);
            }
            
            return [
                'success' => true,
                'skipped' => false,
                'filename' => $filename
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to process extracted file', [
                'file' => $fileInfo->getFilename(),
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'filename' => $fileInfo->getFilename()
            ];
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory($dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}
