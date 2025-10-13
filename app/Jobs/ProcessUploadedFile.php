<?php

namespace App\Jobs;

use App\Models\CacheFile;
use App\Models\UploadSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessUploadedFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 3;

    public function __construct(
        public string $uploadKey,
        public string $tempFilePath,
        public string $filename,
        public ?string $relativePath = null,
        public array $metadata = []
    ) {
        // SECURITY: Sanitize filename to prevent directory traversal
        $this->filename = $this->sanitizeFilename($filename);
        if ($this->relativePath) {
            $this->relativePath = $this->sanitizePath($relativePath);
        }
    }

    /**
     * Sanitize filename to prevent directory traversal attacks
     */
    private function sanitizeFilename(string $filename): string
    {
        // Use basename() to extract just the filename, which handles most traversal attempts
        $filename = basename($filename);
        
        // Remove null bytes and any remaining directory separators
        // Note: We don't strip leading dots as dotfiles (.env, .gitignore) are legitimate
        $filename = str_replace(["\0", '/', '\\'], '', $filename);
        
        // Block filenames that are exactly "." or ".." (edge case)
        if ($filename === '.' || $filename === '..') {
            $filename = 'upload_' . uniqid() . '.dat';
        }
        
        // If filename is empty after sanitization, generate a safe one
        if (empty($filename)) {
            $filename = 'upload_' . uniqid() . '.dat';
        }
        
        return $filename;
    }

    /**
     * Sanitize relative path to prevent directory traversal
     */
    private function sanitizePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        
        // Normalize path separators to forward slashes
        $path = str_replace('\\', '/', $path);
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        // Split into segments
        $segments = explode('/', $path);
        $sanitized = [];
        
        foreach ($segments as $segment) {
            // Skip empty segments
            if ($segment === '' || $segment === null) {
                continue;
            }
            
            // Block parent directory references and current directory references
            if ($segment === '..' || $segment === '.') {
                continue;
            }
            
            // Remove null bytes from segment
            $segment = str_replace("\0", '', $segment);
            
            // Only add non-empty segments
            // Note: We allow dotfiles/dotdirs like ".config" as they are legitimate
            if ($segment !== '') {
                $sanitized[] = $segment;
            }
        }
        
        return empty($sanitized) ? null : implode('/', $sanitized);
    }

    public function handle(): void
    {
        try {
            $uploadSession = UploadSession::where('upload_key', $this->uploadKey)->first();
            
            if (!$uploadSession) {
                Log::error("Upload session not found: {$this->uploadKey}");
                return;
            }

            $uploadSession->update(['status' => 'processing']);

            $hash = hash_file('sha256', $this->tempFilePath);
            $mimeType = mime_content_type($this->tempFilePath) ?: 'application/octet-stream';
            $fileSize = filesize($this->tempFilePath);

            // SECURITY: Use sanitized values for database queries
            $safeFilename = $this->sanitizeFilename($this->filename);
            $safePath = $this->sanitizePath($this->relativePath);
            
            $existing = CacheFile::where('filename', $safeFilename)
                ->where('relative_path', $safePath)
                ->first();

            if ($existing && $existing->hash === $hash) {
                @unlink($this->tempFilePath);
                
                $uploadSession->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'metadata' => array_merge($this->metadata, [
                        'skipped' => true,
                        'reason' => 'Identical file already exists'
                    ])
                ]);

                dispatch(new RegenerateCacheManifest());
                return;
            }

            // SECURITY: Use sanitized filename with unique prefix to prevent collisions
            $safeFilename = $this->sanitizeFilename($this->filename);
            $storagePath = 'cache_files/' . uniqid() . '_' . $safeFilename;
            $destinationPath = storage_path('app/' . $storagePath);
            
            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            if (!rename($this->tempFilePath, $destinationPath)) {
                throw new \Exception('Failed to move file to final destination');
            }

            $directoryPath = null;
            if ($safePath) {
                $pathParts = explode('/', $safePath);
                array_pop($pathParts);
                $directoryPath = implode('/', $pathParts);
            }

            CacheFile::updateOrCreate(
                [
                    'filename' => $safeFilename,
                    'relative_path' => $safePath
                ],
                [
                    'path' => $storagePath,
                    'size' => $fileSize,
                    'hash' => $hash,
                    'file_type' => 'file',
                    'mime_type' => $mimeType,
                    'metadata' => array_merge([
                        'original_path' => $safePath,
                        'directory_path' => $directoryPath,
                        'file_extension' => pathinfo($safeFilename, PATHINFO_EXTENSION),
                        'uploaded_via_chunks' => true,
                        'upload_time' => now()->toISOString(),
                    ], $this->metadata)
                ]
            );

            $uploadSession->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            dispatch(new RegenerateCacheManifest());

        } catch (\Exception $e) {
            Log::error("File processing failed for {$this->uploadKey}: " . $e->getMessage());
            
            if (isset($uploadSession)) {
                $uploadSession->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            if (file_exists($this->tempFilePath)) {
                @unlink($this->tempFilePath);
            }

            throw $e;
        }
    }
}
