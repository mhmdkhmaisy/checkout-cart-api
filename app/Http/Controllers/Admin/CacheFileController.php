<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CacheFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PharData;

class CacheFileController extends Controller
{
    /**
     * Display cache files listing with directory structure
     */
    public function index(Request $request)
    {
        $query = CacheFile::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('filename', 'LIKE', "%{$search}%")
                  ->orWhere('relative_path', 'LIKE', "%{$search}%")
                  ->orWhere('mime_type', 'LIKE', "%{$search}%");
            });
        }

        // Filter by file type
        if ($request->filled('type_filter')) {
            $typeFilter = $request->get('type_filter');
            if ($typeFilter === 'files') {
                $query->where('file_type', 'file');
            } elseif ($typeFilter === 'directories') {
                $query->where('file_type', 'directory');
            } else {
                // Filter by file extension
                $query->where('filename', 'LIKE', "%.{$typeFilter}");
            }
        }

        // Sorting
        $sortBy = $request->get('sort', 'filename');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['filename', 'size', 'created_at', 'relative_path', 'file_type'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('relative_path')->orderBy('filename');
        }

        $files = $query->paginate(50)->appends($request->query());

        $totalSize = CacheFile::files()->sum('size');
        $totalFiles = CacheFile::files()->count();
        $totalDirectories = CacheFile::directories()->count();

        // Get unique file extensions for filter dropdown
        $fileExtensions = CacheFile::files()
            ->selectRaw('LOWER(SUBSTRING_INDEX(filename, ".", -1)) as extension')
            ->groupBy('extension')
            ->pluck('extension')
            ->filter()
            ->sort()
            ->values();

        return view('admin.cache.index', compact('files', 'totalSize', 'totalFiles', 'totalDirectories', 'fileExtensions'));
    }

    /**
     * Check for duplicate files before upload
     */
    public function checkDuplicates(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*.name' => 'required|string',
            'files.*.size' => 'required|integer',
            'files.*.relative_path' => 'nullable|string'
        ]);

        $duplicates = [];
        $files = $request->input('files');

        foreach ($files as $index => $fileData) {
            $relativePath = $fileData['relative_path'] ?? null;
            
            $existing = CacheFile::where('filename', $fileData['name'])
                ->where('relative_path', $relativePath)
                ->first();
            
            if ($existing) {
                $duplicates[] = [
                    'index' => $index,
                    'filename' => $fileData['name'],
                    'existing_size' => $existing->size,
                    'new_size' => $fileData['size'],
                    'existing_hash' => $existing->hash,
                    'can_skip' => true // Will be determined by hash comparison during upload
                ];
            }
        }

        return response()->json([
            'duplicates' => $duplicates,
            'total_files' => count($files),
            'duplicate_count' => count($duplicates)
        ]);
    }

    /**
     * Show upload form
     */
    public function create()
    {
        return view('admin.cache.upload');
    }

    /**
     * Handle enhanced multi-file and folder upload with structure preservation
     */
    public function store(Request $request)
    {
        // Increase memory and time limits for large uploads
        ini_set('memory_limit', '2G');
        set_time_limit(600); // 10 minutes

        $request->validate([
            'files.*' => 'required|file|max:1048576', // 1GB max per file - supports ALL file types
            'folders.*' => 'file|max:1048576', // For folder uploads
            'preserve_structure' => 'boolean'
        ]);

        $uploadedFiles = [];
        $skippedFiles = [];
        $errors = [];
        $preserveStructure = $request->input('preserve_structure', true);
        $relativePaths = $request->input('relative_paths', []);
        $recordsToInsert = [];

        // Handle individual files with enhanced multi-file support
        if ($request->hasFile('files')) {
            $files = $request->file('files');

            // OPTIMIZED: Batch process files to reduce DB queries
            $fileData = [];
            foreach ($files as $index => $file) {
                $relativePath = isset($relativePaths[$index]) ? $relativePaths[$index] : null;
                $fileData[] = [
                    'file' => $file,
                    'relativePath' => $relativePath,
                    'index' => $index
                ];
            }

            // Process in optimized batch
            $result = $this->processBatchUpload($fileData, $preserveStructure);
            $uploadedFiles = array_merge($uploadedFiles, $result['uploaded']);
            $skippedFiles = array_merge($skippedFiles, $result['skipped']);
            $errors = array_merge($errors, $result['errors']);
        }

        // Handle folder uploads (ZIP files)
        if ($request->hasFile('folders')) {
            foreach ($request->file('folders') as $zipFile) {
                try {
                    $result = $this->processZipUpload($zipFile, $preserveStructure);
                    $uploadedFiles = array_merge($uploadedFiles, $result['uploaded']);
                    $skippedFiles = array_merge($skippedFiles, $result['skipped']);
                    $errors = array_merge($errors, $result['errors']);
                } catch (\Exception $e) {
                    $errors[] = "Failed to process folder '{$zipFile->getClientOriginalName()}': " . $e->getMessage();
                }
            }
        }

        // OPTIMIZED: Regenerate manifest only ONCE at the end
        if (!empty($uploadedFiles)) {
            try {
                Artisan::call('cache:generate-manifest');
            } catch (\Exception $e) {
                $errors[] = "Files uploaded but manifest generation failed: " . $e->getMessage();
            }
        }

        // Prepare response messages
        $messages = [];
        if (!empty($uploadedFiles)) {
            $messages[] = 'Successfully uploaded: ' . implode(', ', array_slice($uploadedFiles, 0, 10)) . 
                         (count($uploadedFiles) > 10 ? ' and ' . (count($uploadedFiles) - 10) . ' more files' : '');
        }
        if (!empty($skippedFiles)) {
            $messages[] = 'Skipped (identical): ' . implode(', ', array_slice($skippedFiles, 0, 5)) . 
                         (count($skippedFiles) > 5 ? ' and ' . (count($skippedFiles) - 5) . ' more files' : '');
        }
        if (!empty($errors)) {
            $messages[] = 'Errors: ' . implode(' | ', array_slice($errors, 0, 5)) . 
                         (count($errors) > 5 ? ' and ' . (count($errors) - 5) . ' more errors' : '');
        }

        return response()->json([
            'success' => !empty($uploadedFiles) || !empty($skippedFiles),
            'message' => implode(' | ', $messages),
            'uploaded_count' => count($uploadedFiles),
            'skipped_count' => count($skippedFiles),
            'error_count' => count($errors)
        ]);
    }

    /**
     * Handle TAR file upload with real-time extraction progress
     */
    public function storeTar(Request $request)
    {
        $request->validate([
            'tar_file' => 'required|file|max:2097152', // 2GB max for TAR files
            'preserve_structure' => 'boolean'
        ]);

        $tarFile = $request->file('tar_file');
        $preserveStructure = $request->input('preserve_structure', true);
        $extractionId = uniqid('tar_extract_');

        try {
            // Store the TAR file temporarily
            $tempPath = $tarFile->storeAs('temp_tar', $extractionId . '_' . $tarFile->getClientOriginalName());
            $fullTempPath = storage_path('app/' . $tempPath);

            // Initialize extraction progress tracking
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'starting',
                'processed' => 0,
                'total' => 0,
                'files_count' => 0,
                'started_at' => now()
            ], 3600); // 1 hour cache

            // Start background extraction process
            $this->extractTarInBackground($fullTempPath, $extractionId, $preserveStructure);

            return response()->json([
                'success' => true,
                'message' => 'TAR file uploaded successfully. Extraction started.',
                'extraction_id' => $extractionId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process TAR file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract individual archive files (ZIP, RAR, 7Z, etc.)
     */
    public function extractFile(Request $request)
    {
        $request->validate([
            'file_id' => 'required|integer|exists:cache_files,id'
        ]);

        $file = CacheFile::findOrFail($request->file_id);
        
        if (!$file->existsOnDisk()) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on disk'
            ], 404);
        }

        $extension = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
        $extractableExtensions = ['zip', 'tar', 'gz', 'rar', '7z', 'tgz'];
        
        if (!in_array($extension, $extractableExtensions)) {
            return response()->json([
                'success' => false,
                'message' => 'File type not supported for extraction'
            ], 400);
        }

        $extractionId = uniqid('extract_');
        
        try {
            // Initialize extraction progress tracking
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'starting',
                'processed' => 0,
                'total' => 0,
                'files_count' => 0,
                'started_at' => now()
            ], 3600);

            // Start background extraction process
            $this->extractFileInBackground($file, $extractionId);

            return response()->json([
                'success' => true,
                'message' => 'Extraction started successfully.',
                'extraction_id' => $extractionId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start extraction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get TAR extraction progress
     */
    public function extractionProgress(Request $request)
    {
        $extractionId = $request->get('id');
        
        if (!$extractionId) {
            return response()->json(['error' => 'Extraction ID required'], 400);
        }

        $progress = Cache::get("extraction_progress_{$extractionId}");
        
        if (!$progress) {
            return response()->json(['error' => 'Extraction not found'], 404);
        }

        return response()->json($progress);
    }

    /**
     * Delete all cache files
     */
    public function deleteAll(Request $request)
    {
        try {
            $files = CacheFile::all();
            $deletedCount = 0;

            foreach ($files as $file) {
                if ($file->existsOnDisk()) {
                    Storage::delete($file->path);
                }
                $file->delete();
                $deletedCount++;
            }

            // Regenerate manifest
            Artisan::call('cache:generate-manifest');

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted all {$deletedCount} files.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete all files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract individual file in background with progress tracking
     */
    private function extractFileInBackground($file, $extractionId)
    {
        try {
            // Update status to processing
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'processing',
                'processed' => 0,
                'total' => 0,
                'files_count' => 0,
                'started_at' => now()
            ], 3600);

            $uploadedFiles = [];
            $skippedFiles = [];
            $errors = [];
            
            // Create extraction directory
            $extractPath = storage_path('app/temp_extract_' . $extractionId);
            
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $filePath = storage_path('app/' . $file->path);
            $extension = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));

            // Handle different archive formats
            if ($extension === 'zip') {
                $this->extractZipFile($filePath, $extractPath);
            } elseif (in_array($extension, ['tar', 'gz', 'tgz'])) {
                $this->extractTarFile($filePath, $extractPath);
            } elseif ($extension === 'rar') {
                $this->extractRarFile($filePath, $extractPath);
            } elseif ($extension === '7z') {
                $this->extract7zFile($filePath, $extractPath);
            }

            // Count total files first
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            $totalFiles = iterator_count($iterator);
            
            // Reset iterator for processing
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $processed = 0;
            
            // Update total count
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'processing',
                'processed' => 0,
                'total' => $totalFiles,
                'files_count' => 0,
                'started_at' => now()
            ], 3600);

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    try {
                        // CRITICAL FIX: Preserve full directory structure
                        $fullPath = $fileInfo->getPathname();
                        $relativePath = str_replace($extractPath . DIRECTORY_SEPARATOR, '', $fullPath);
                        // Convert backslashes to forward slashes and ensure proper structure
                        $relativePath = str_replace('\\', '/', $relativePath);
                        
                        $result = $this->processExtractedFile($fileInfo, $relativePath);
                        if ($result['success']) {
                            if ($result['skipped']) {
                                $skippedFiles[] = $result['filename'];
                            } else {
                                $uploadedFiles[] = $result['filename'];
                            }
                        } else {
                            $errors[] = $result['error'];
                        }
                        
                        $processed++;
                        
                        // Update progress every 10 files or on last file
                        if ($processed % 10 === 0 || $processed === $totalFiles) {
                            Cache::put("extraction_progress_{$extractionId}", [
                                'status' => 'processing',
                                'processed' => $processed,
                                'total' => $totalFiles,
                                'files_count' => count($uploadedFiles),
                                'skipped_count' => count($skippedFiles),
                                'started_at' => now()
                            ], 3600);
                        }
                        
                    } catch (\Exception $e) {
                        $errors[] = "Failed to process '{$fileInfo->getFilename()}': " . $e->getMessage();
                        $processed++;
                    }
                }
            }

            // Clean up temporary files
            $this->deleteDirectory($extractPath);

            // Auto-regenerate manifest if any files were uploaded
            if (!empty($uploadedFiles)) {
                try {
                    Artisan::call('cache:generate-manifest');
                } catch (\Exception $e) {
                    $errors[] = "Files extracted but manifest generation failed: " . $e->getMessage();
                }
            }

            // Mark as completed
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'completed',
                'processed' => $totalFiles,
                'total' => $totalFiles,
                'files_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'errors_count' => count($errors),
                'completed_at' => now()
            ], 3600);

        } catch (\Exception $e) {
            // Mark as failed
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now()
            ], 3600);
        }
    }

    /**
     * Extract ZIP file
     */
    private function extractZipFile($filePath, $extractPath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \Exception('Failed to open ZIP file');
        }
    }

    /**
     * Extract TAR file
     */
    private function extractTarFile($filePath, $extractPath)
    {
        $phar = new PharData($filePath);
        $phar->extractTo($extractPath);
    }

    /**
     * Extract RAR file (requires rar extension)
     */
    private function extractRarFile($filePath, $extractPath)
    {
        if (!extension_loaded('rar')) {
            throw new \Exception('RAR extension not available. Please install php-rar extension.');
        }

        $rar = rar_open($filePath);
        if (!$rar) {
            throw new \Exception('Failed to open RAR file');
        }

        $entries = rar_list($rar);
        foreach ($entries as $entry) {
            if (!$entry->isDirectory()) {
                $entry->extract($extractPath);
            }
        }
        rar_close($rar);
    }

    /**
     * Extract 7Z file (requires system 7zip command)
     */
    private function extract7zFile($filePath, $extractPath)
    {
        $command = "7z x " . escapeshellarg($filePath) . " -o" . escapeshellarg($extractPath) . " -y";
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Failed to extract 7Z file. Make sure 7zip is installed.');
        }
    }

    /**
     * Extract TAR file in background with progress tracking
     */
    private function extractTarInBackground($tarPath, $extractionId, $preserveStructure = true)
    {
        try {
            // Update status to processing
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'processing',
                'processed' => 0,
                'total' => 0,
                'files_count' => 0,
                'started_at' => now()
            ], 3600);

            $uploadedFiles = [];
            $skippedFiles = [];
            $errors = [];
            
            // Create extraction directory
            $extractPath = storage_path('app/temp_extract_' . $extractionId);
            
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            // Handle different TAR formats
            $isGzipped = str_ends_with(strtolower($tarPath), '.gz') || str_ends_with(strtolower($tarPath), '.tgz');
            
            if ($isGzipped) {
                // Handle .tar.gz or .tgz files
                $phar = new PharData($tarPath);
                $phar->extractTo($extractPath);
            } else {
                // Handle regular .tar files
                $phar = new PharData($tarPath);
                $phar->extractTo($extractPath);
            }

            // Count total files first
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            $totalFiles = iterator_count($iterator);
            
            // Reset iterator for processing
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $processed = 0;
            
            // Update total count
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'processing',
                'processed' => 0,
                'total' => $totalFiles,
                'files_count' => 0,
                'started_at' => now()
            ], 3600);

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    try {
                        // CRITICAL FIX: Preserve full directory structure for TAR files
                        if ($preserveStructure) {
                            $fullPath = $fileInfo->getPathname();
                            $relativePath = str_replace($extractPath . DIRECTORY_SEPARATOR, '', $fullPath);
                            // Convert backslashes to forward slashes and ensure proper structure
                            $relativePath = str_replace('\\', '/', $relativePath);
                        } else {
                            $relativePath = null;
                        }
                        
                        $result = $this->processExtractedFile($fileInfo, $relativePath);
                        if ($result['success']) {
                            if ($result['skipped']) {
                                $skippedFiles[] = $result['filename'];
                            } else {
                                $uploadedFiles[] = $result['filename'];
                            }
                        } else {
                            $errors[] = $result['error'];
                        }
                        
                        $processed++;
                        
                        // Update progress every 10 files or on last file
                        if ($processed % 10 === 0 || $processed === $totalFiles) {
                            Cache::put("extraction_progress_{$extractionId}", [
                                'status' => 'processing',
                                'processed' => $processed,
                                'total' => $totalFiles,
                                'files_count' => count($uploadedFiles),
                                'skipped_count' => count($skippedFiles),
                                'started_at' => now()
                            ], 3600);
                        }
                        
                    } catch (\Exception $e) {
                        $errors[] = "Failed to process '{$fileInfo->getFilename()}': " . $e->getMessage();
                        $processed++;
                    }
                }
            }

            // Clean up temporary files
            $this->deleteDirectory($extractPath);
            unlink($tarPath);

            // Auto-regenerate manifest if any files were uploaded
            if (!empty($uploadedFiles)) {
                try {
                    Artisan::call('cache:generate-manifest');
                } catch (\Exception $e) {
                    $errors[] = "Files extracted but manifest generation failed: " . $e->getMessage();
                }
            }

            // Mark as completed
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'completed',
                'processed' => $totalFiles,
                'total' => $totalFiles,
                'files_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'errors_count' => count($errors),
                'completed_at' => now()
            ], 3600);

        } catch (\Exception $e) {
            // Mark as failed
            Cache::put("extraction_progress_{$extractionId}", [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now()
            ], 3600);
        }
    }

    /**
     * OPTIMIZED: Batch process multiple file uploads to reduce DB queries
     */
    private function processBatchUpload(array $fileData, $preserveStructure = true): array
    {
        $uploadedFiles = [];
        $skippedFiles = [];
        $errors = [];

        // OPTIMIZED: Build all filenames and paths first
        $checkData = [];
        foreach ($fileData as $data) {
            $originalName = $data['file']->getClientOriginalName();
            $relativePath = $data['relativePath'];
            $fullRelativePath = ($relativePath && $preserveStructure) ? $relativePath : null;

            $checkData[] = [
                'filename' => $originalName,
                'relative_path' => $fullRelativePath,
                'file' => $data['file']
            ];
        }

        // OPTIMIZED: Single query to check for existing files
        $existingFiles = CacheFile::where(function($query) use ($checkData) {
            foreach ($checkData as $data) {
                $query->orWhere(function($q) use ($data) {
                    $q->where('filename', $data['filename'])
                      ->where('relative_path', $data['relative_path']);
                });
            }
        })->get()->keyBy(function($item) {
            return $item->filename . '|' . ($item->relative_path ?? '');
        });

        // Process each file
        $recordsToUpsert = [];
        foreach ($checkData as $data) {
            try {
                $file = $data['file'];
                $originalName = $data['filename'];
                $fullRelativePath = $data['relative_path'];

                // OPTIMIZED: Only compute hash if needed for duplicate check
                $key = $originalName . '|' . ($fullRelativePath ?? '');
                $existing = $existingFiles->get($key);

                // Store file first
                $storagePath = 'cache_files/' . uniqid() . '_' . $originalName;
                $path = $file->storeAs('cache_files', basename($storagePath));

                // Compute hash after storage (can be done async if needed)
                $hash = hash_file('sha256', $file->getRealPath());
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';

                // Check if identical
                if ($existing && $existing->hash === $hash) {
                    // Delete the newly stored file since it's a duplicate
                    Storage::delete($path);
                    $skippedFiles[] = $originalName;
                    continue;
                }

                // Extract directory path
                $directoryPath = null;
                if ($fullRelativePath) {
                    $pathParts = explode('/', $fullRelativePath);
                    array_pop($pathParts);
                    $directoryPath = implode('/', $pathParts);
                }

                // Prepare record for batch upsert
                $recordsToUpsert[] = [
                    'filename' => $originalName,
                    'relative_path' => $fullRelativePath,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'hash' => $hash,
                    'file_type' => 'file',
                    'mime_type' => $mimeType,
                    'metadata' => json_encode([
                        'original_name' => $originalName,
                        'relative_path' => $fullRelativePath,
                        'directory_path' => $directoryPath,
                        'file_extension' => pathinfo($originalName, PATHINFO_EXTENSION),
                        'upload_time' => now()->toISOString(),
                        'supports_all_types' => true
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $uploadedFiles[] = $originalName;

            } catch (\Exception $e) {
                $errors[] = "Failed to upload '{$data['filename']}': " . $e->getMessage();
            }
        }

        // OPTIMIZED: Single batch upsert for all files
        if (!empty($recordsToUpsert)) {
            CacheFile::upsert(
                $recordsToUpsert,
                ['filename', 'relative_path'],
                ['path', 'size', 'hash', 'file_type', 'mime_type', 'metadata', 'updated_at']
            );
        }

        return [
            'uploaded' => $uploadedFiles,
            'skipped' => $skippedFiles,
            'errors' => $errors
        ];
    }

    /**
     * Process a single uploaded file with enhanced support for all file types
     */
    private function processUploadedFile($file, $relativePath = null, $preserveStructure = true): array
    {
        $originalName = $file->getClientOriginalName();
        $hash = hash_file('sha256', $file->getRealPath());
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        // CRITICAL FIX: Enhanced relative path handling for folder uploads
        if ($relativePath && $preserveStructure) {
            // Preserve the EXACT full relative path including directory structure
            $fullRelativePath = $relativePath;
        } else {
            $fullRelativePath = null;
        }

        // Check if identical file already exists (same hash)
        $existing = CacheFile::where('filename', $originalName)
            ->where('relative_path', $fullRelativePath)
            ->first();

        if ($existing && $existing->hash === $hash) {
            return [
                'success' => true,
                'skipped' => true,
                'filename' => $originalName,
                'reason' => 'Identical file already exists'
            ];
        }

        // Enhanced storage path generation
        $storagePath = 'cache_files/' . uniqid() . '_' . $originalName;
        $path = $file->storeAs('cache_files', basename($storagePath));

        // Extract directory path from relative path for metadata
        $directoryPath = null;
        if ($fullRelativePath) {
            $pathParts = explode('/', $fullRelativePath);
            array_pop($pathParts); // Remove filename
            $directoryPath = implode('/', $pathParts);
        }

        // Create or update database record with enhanced metadata (overwrite if hash differs)
        CacheFile::updateOrCreate(
            [
                'filename' => $originalName,
                'relative_path' => $fullRelativePath
            ],
            [
                'path' => $path,
                'size' => $file->getSize(),
                'hash' => $hash,
                'file_type' => 'file',
                'mime_type' => $mimeType,
                'metadata' => [
                    'original_name' => $originalName,
                    'relative_path' => $fullRelativePath,
                    'directory_path' => $directoryPath,
                    'file_extension' => pathinfo($originalName, PATHINFO_EXTENSION),
                    'upload_time' => now()->toISOString(),
                    'supports_all_types' => true
                ]
            ]
        );

        return [
            'success' => true,
            'skipped' => false,
            'filename' => $originalName
        ];
    }

    /**
     * Enhanced ZIP file processing with better directory structure handling
     */
    private function processZipUpload($zipFile, $preserveStructure = true): array
    {
        $uploadedFiles = [];
        $skippedFiles = [];
        $errors = [];
        
        $zip = new ZipArchive();
        $tempPath = $zipFile->getRealPath();
        
        if ($zip->open($tempPath) === TRUE) {
            $extractPath = storage_path('app/temp_extract_' . uniqid());
            
            // Extract to temporary directory
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Process extracted files with enhanced directory traversal
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    try {
                        // CRITICAL FIX: Preserve full directory structure
                        if ($preserveStructure) {
                            $fullPath = $fileInfo->getPathname();
                            $relativePath = str_replace($extractPath . DIRECTORY_SEPARATOR, '', $fullPath);
                            // Convert backslashes to forward slashes and ensure proper structure
                            $relativePath = str_replace('\\', '/', $relativePath);
                        } else {
                            $relativePath = null;
                        }
                        
                        $result = $this->processExtractedFile($fileInfo, $relativePath);
                        if ($result['success']) {
                            if ($result['skipped']) {
                                $skippedFiles[] = $result['filename'];
                            } else {
                                $uploadedFiles[] = $result['filename'];
                            }
                        } else {
                            $errors[] = $result['error'];
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Failed to process '{$fileInfo->getFilename()}': " . $e->getMessage();
                    }
                }
            }
            
            // Clean up temporary directory
            $this->deleteDirectory($extractPath);
        } else {
            $errors[] = "Failed to open ZIP file: " . $zipFile->getClientOriginalName();
        }
        
        return [
            'uploaded' => $uploadedFiles,
            'skipped' => $skippedFiles,
            'errors' => $errors
        ];
    }

    /**
     * Enhanced extracted file processing with better metadata
     */
    private function processExtractedFile($fileInfo, $relativePath = null): array
    {
        $filename = $fileInfo->getFilename();
        $hash = hash_file('sha256', $fileInfo->getRealPath());
        $mimeType = mime_content_type($fileInfo->getRealPath()) ?: 'application/octet-stream';
        
        // Check if identical file already exists (same hash)
        $existing = CacheFile::where('filename', $filename)
            ->where('relative_path', $relativePath)
            ->first();
        
        if ($existing && $existing->hash === $hash) {
            return [
                'success' => true,
                'skipped' => true,
                'filename' => $filename,
                'reason' => 'Identical file already exists'
            ];
        }

        // Enhanced storage with unique naming
        $storagePath = 'cache_files/' . uniqid() . '_' . $filename;
        $destinationPath = storage_path('app/' . $storagePath);
        
        // Ensure directory exists
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        copy($fileInfo->getRealPath(), $destinationPath);
        
        // Extract directory path from relative path for metadata
        $directoryPath = null;
        if ($relativePath) {
            $pathParts = explode('/', $relativePath);
            array_pop($pathParts); // Remove filename
            $directoryPath = implode('/', $pathParts);
        }
        
        // Create or update database record with enhanced metadata (overwrite if hash differs)
        CacheFile::updateOrCreate(
            [
                'filename' => $filename,
                'relative_path' => $relativePath
            ],
            [
                'path' => $storagePath,
                'size' => $fileInfo->getSize(),
                'hash' => $hash,
                'file_type' => 'file',
                'mime_type' => $mimeType,
                'metadata' => [
                    'original_path' => $relativePath,
                    'directory_path' => $directoryPath,
                    'file_extension' => pathinfo($filename, PATHINFO_EXTENSION),
                    'extracted_from_archive' => true,
                    'upload_time' => now()->toISOString(),
                    'supports_all_types' => true
                ]
            ]
        );

        return [
            'success' => true,
            'skipped' => false,
            'filename' => $filename
        ];
    }

    /**
     * Delete a cache file
     */
    public function destroy(CacheFile $cacheFile)
    {
        try {
            // Delete file from storage
            if ($cacheFile->existsOnDisk()) {
                Storage::delete($cacheFile->path);
            }

            // Delete database record
            $filename = $cacheFile->filename;
            $cacheFile->delete();

            // Regenerate manifest
            Artisan::call('cache:generate-manifest');

            return redirect()->route('admin.cache.index')
                ->with('success', "Cache file '{$filename}' deleted successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.index')
                ->with('error', 'Failed to delete cache file: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate manifest manually
     */
    public function regenerateManifest()
    {
        try {
            Artisan::call('cache:generate-manifest');
            return redirect()->route('admin.cache.index')
                ->with('success', 'Cache manifest regenerated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.cache.index')
                ->with('error', 'Failed to regenerate manifest: ' . $e->getMessage());
        }
    }

    /**
     * Download manifest file
     */
    public function downloadManifest()
    {
        $manifestPath = storage_path('app/manifests/cache_manifest.json');
        
        if (!file_exists($manifestPath)) {
            return redirect()->route('admin.cache.index')
                ->with('error', 'Manifest file not found. Please regenerate it first.');
        }

        return response()->download($manifestPath, 'cache_manifest.json');
    }

    /**
     * Enhanced bulk delete with better error handling
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:cache_files,id'
        ]);

        try {
            $files = CacheFile::whereIn('id', $request->file_ids)->get();
            $deletedCount = 0;

            foreach ($files as $file) {
                if ($file->existsOnDisk()) {
                    Storage::delete($file->path);
                }
                $file->delete();
                $deletedCount++;
            }

            // Regenerate manifest
            Artisan::call('cache:generate-manifest');

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} files.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete files: ' . $e->getMessage()
            ], 500);
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