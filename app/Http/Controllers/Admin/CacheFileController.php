<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CacheFile;
use App\Models\CachePatch;
use App\Models\UploadSession;
use App\Services\CachePatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        $currentPath = $request->get('path', '');
        $currentPath = trim($currentPath, '/');
        
        $query = CacheFile::query();

        // Path-based filtering for directory navigation
        // Note: relative_path is the directory path, NOT including the filename
        if (!$request->filled('search') && !$request->filled('type_filter')) {
            if (empty($currentPath)) {
                // At root: get all files (buildDirectoryView will filter to show only root-level items)
                // No filtering needed here
            } else {
                // In a directory: get files in this directory or subdirectories
                // Files in "foo" have relative_path = "foo" (direct children)
                // Files in "foo/bar" have relative_path = "foo/bar" (nested)
                $query->where(function($q) use ($currentPath) {
                    $q->where('relative_path', $currentPath)
                      ->orWhere('relative_path', 'LIKE', $currentPath . '/%');
                });
            }
        }

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

        // Get all files for current path
        $allFiles = $query->get();
        
        // Process files to show only current directory level
        $filesAndFolders = $this->buildDirectoryView($allFiles, $currentPath);
        
        // Apply sorting
        $sortBy = $request->get('sort', 'filename');
        $sortDirection = $request->get('direction', 'asc');
        
        $filesAndFolders = $filesAndFolders->sortBy(function($item) use ($sortBy) {
            if ($sortBy === 'filename') {
                return strtolower($item->filename);
            } elseif ($sortBy === 'size') {
                return $item->file_type === 'directory' ? 0 : ($item->size ?? 0);
            } elseif ($sortBy === 'created_at') {
                return $item->created_at;
            } elseif ($sortBy === 'file_type') {
                return $item->file_type === 'directory' ? 'a' : 'b';
            }
            return $item->filename;
        }, SORT_REGULAR, $sortDirection === 'desc');

        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 50;
        $files = new \Illuminate\Pagination\LengthAwarePaginator(
            $filesAndFolders->forPage($page, $perPage),
            $filesAndFolders->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

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

        // Patch system data
        $patches = CachePatch::latest()->get();
        $latestVersion = CachePatch::getLatestVersion();
        $basePatches = CachePatch::base()->count();
        $incrementalPatches = CachePatch::patches()->count();
        $totalPatchSize = CachePatch::sum('size');
        
        $patchService = new CachePatchService();
        $canMerge = $patchService->shouldMergePatches();

        return view('admin.cache.index', compact(
            'files', 'totalSize', 'totalFiles', 'totalDirectories', 'fileExtensions', 'currentPath',
            'patches', 'latestVersion', 'basePatches', 'incrementalPatches', 'totalPatchSize', 'canMerge'
        ));
    }
    
    /**
     * Build directory view showing only items at current level
     */
    private function buildDirectoryView($allFiles, $currentPath)
    {
        $items = collect();
        $seenFolders = [];
        
        foreach ($allFiles as $file) {
            // relative_path is the directory path (NOT including filename)
            $fileDirPath = $file->relative_path ?? '';
            
            // Handle real directory records (file_type='directory')
            if ($file->file_type === 'directory') {
                // Compute the full path for this directory
                $dirFullPath = $fileDirPath ? $fileDirPath . '/' . $file->filename : $file->filename;
                
                // Check if this directory belongs at the current level
                $dirParentPath = $fileDirPath;
                if ($dirParentPath === $currentPath) {
                    // This directory is a direct child of current path
                    $file->navigation_path = $dirFullPath;
                    $items->push($file);
                    $seenFolders[$dirFullPath] = true;
                }
                continue;
            }
            
            if (empty($currentPath)) {
                // At root level - show files with no directory path and create virtual folders
                if (empty($fileDirPath)) {
                    // File at root level (relative_path is null or empty)
                    $items->push($file);
                } else {
                    // File is in a subdirectory - create virtual folder entry for first segment
                    $firstFolder = explode('/', $fileDirPath)[0];
                    if (!isset($seenFolders[$firstFolder])) {
                        $seenFolders[$firstFolder] = true;
                        $folderItem = new \stdClass();
                        $folderItem->id = 'folder_' . md5($firstFolder);
                        $folderItem->filename = $firstFolder;
                        $folderItem->relative_path = $firstFolder;
                        $folderItem->navigation_path = $firstFolder;
                        $folderItem->file_type = 'directory';
                        $folderItem->size = 0;
                        $folderItem->created_at = $file->created_at;
                        $folderItem->updated_at = $file->updated_at;
                        $items->push($folderItem);
                    }
                }
            } else {
                // In a subdirectory - show files in this directory and subfolders
                if ($fileDirPath === $currentPath) {
                    // File directly in current directory
                    $items->push($file);
                } elseif (strpos($fileDirPath, $currentPath . '/') === 0) {
                    // File is in a subdirectory of current path
                    $remainder = substr($fileDirPath, strlen($currentPath) + 1);
                    $nextFolder = explode('/', $remainder)[0];
                    $fullFolderPath = $currentPath . '/' . $nextFolder;
                    
                    if (!isset($seenFolders[$fullFolderPath])) {
                        $seenFolders[$fullFolderPath] = true;
                        $folderItem = new \stdClass();
                        $folderItem->id = 'folder_' . md5($fullFolderPath);
                        $folderItem->filename = $nextFolder;
                        $folderItem->relative_path = $fullFolderPath;
                        $folderItem->navigation_path = $fullFolderPath;
                        $folderItem->file_type = 'directory';
                        $folderItem->size = 0;
                        $folderItem->created_at = $file->created_at;
                        $folderItem->updated_at = $file->updated_at;
                        $items->push($folderItem);
                    }
                }
            }
        }
        
        return $items;
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
            
            // Normalize relative_path: extract directory path only (strip filename)
            $directoryPath = null;
            if ($relativePath) {
                $pathParts = explode('/', $relativePath);
                array_pop($pathParts); // Remove filename
                $directoryPath = implode('/', $pathParts);
                $directoryPath = $directoryPath ?: null;
            }
            
            $existing = CacheFile::where('filename', $fileData['name'])
                ->where('relative_path', $directoryPath)
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
        // Log IMMEDIATELY - even before try-catch
        Log::info('Cache upload store() method called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'has_files' => $request->hasFile('files'),
            'has_folders' => $request->hasFile('folders'),
            'all_inputs' => array_keys($request->all())
        ]);

        try {
            // Increase memory and time limits for large uploads
            ini_set('memory_limit', '2G');
            set_time_limit(600); // 10 minutes

            Log::info('Cache file upload started', [
                'has_files' => $request->hasFile('files'),
                'has_folders' => $request->hasFile('folders'),
                'preserve_structure' => $request->input('preserve_structure', true),
                'current_path' => $request->input('current_path', '')
            ]);

            try {
                $request->validate([
                    'files.*' => 'required|file|max:1048576', // 1GB max per file - supports ALL file types
                    'folders.*' => 'file|max:1048576', // For folder uploads
                    'preserve_structure' => 'boolean',
                    'current_path' => 'nullable|string'
                ]);
                Log::info('Cache upload validation passed');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Cache upload validation failed', [
                    'errors' => $e->errors(),
                    'message' => $e->getMessage()
                ]);
                throw $e;
            }

            $uploadedFiles = [];
            $skippedFiles = [];
            $errors = [];
            $preserveStructure = $request->input('preserve_structure', true);
            $relativePaths = $request->input('relative_paths', []);
            $recordsToInsert = [];
            $currentPath = $request->input('current_path', '');

        // Handle individual files with enhanced multi-file support
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            
            Log::info('Processing file uploads', [
                'file_count' => count($files),
                'preserve_structure' => $preserveStructure
            ]);

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
            try {
                $result = $this->processBatchUpload($fileData, $preserveStructure, $currentPath);
                $uploadedFiles = array_merge($uploadedFiles, $result['uploaded']);
                $skippedFiles = array_merge($skippedFiles, $result['skipped']);
                $errors = array_merge($errors, $result['errors']);
                
                Log::info('File upload batch completed', [
                    'uploaded' => count($result['uploaded']),
                    'skipped' => count($result['skipped']),
                    'errors' => count($result['errors'])
                ]);
            } catch (\Exception $e) {
                $errorMsg = "Batch upload failed: " . $e->getMessage();
                Log::error('Cache batch upload failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file_count' => count($fileData)
                ]);
                $errors[] = $errorMsg;
            }
        }

        // Handle folder uploads (ZIP files)
        if ($request->hasFile('folders')) {
            foreach ($request->file('folders') as $zipFile) {
                try {
                    $result = $this->processZipUpload($zipFile, $preserveStructure, $currentPath);
                    $uploadedFiles = array_merge($uploadedFiles, $result['uploaded']);
                    $skippedFiles = array_merge($skippedFiles, $result['skipped']);
                    $errors = array_merge($errors, $result['errors']);
                } catch (\Exception $e) {
                    $errorMsg = "Failed to process folder '{$zipFile->getClientOriginalName()}': " . $e->getMessage();
                    Log::error('Cache folder upload failed', [
                        'folder' => $zipFile->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[] = $errorMsg;
                }
            }
        }

        // OPTIMIZED: Skip manifest generation during batch uploads to prevent multiple patches
        // Frontend will call finalizeUpload() once all batches are complete
        // Only auto-regenerate if this is NOT a batch upload (no relative_paths parameter)
        // OR if this is part of a chunked upload session (is_chunked_session flag)
        $isBatchUpload = $request->has('relative_paths');
        $isChunkedSession = $request->input('is_chunked_session', false);
        
        if (!empty($uploadedFiles) && !$isBatchUpload && !$isChunkedSession) {
            try {
                Artisan::call('cache:generate-manifest');
            } catch (\Exception $e) {
                $errorMsg = "Files uploaded but manifest/patch generation failed: " . $e->getMessage();
                Log::error('Cache manifest generation failed after upload', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'uploaded_count' => count($uploadedFiles)
                ]);
                $errors[] = $errorMsg;
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

            Log::info('Cache file upload completed', [
                'uploaded_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'error_count' => count($errors)
            ]);

            $response = response()->json([
                'success' => !empty($uploadedFiles) || !empty($skippedFiles),
                'message' => implode(' | ', $messages),
                'uploaded_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'error_count' => count($errors)
            ]);
            
            Log::info('Cache upload response prepared', [
                'success' => $response->getData()->success,
                'uploaded' => count($uploadedFiles),
                'skipped' => count($skippedFiles),
                'errors' => count($errors)
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Cache upload failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => [
                    'has_files' => $request->hasFile('files'),
                    'has_folders' => $request->hasFile('folders')
                ]
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'uploaded_count' => 0,
                'skipped_count' => 0,
                'error_count' => 1
            ], 500);
        }
    }

    /**
     * Finalize batch upload - generate manifest/patch once after all batches complete
     */
    public function finalizeUpload(Request $request)
    {
        try {
            Log::info('Finalizing batch upload - generating manifest');
            Artisan::call('cache:generate-manifest');
            Log::info('Batch upload finalized successfully');
            return response()->json([
                'success' => true,
                'message' => 'Upload finalized and manifest/patch generated successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Cache finalize upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate manifest/patch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize chunked upload - creates temporary directory and returns upload session
     */
    public function chunkedUploadInit(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string',
                'total_size' => 'required|integer',
                'total_chunks' => 'required|integer',
                'relative_path' => 'nullable|string'
            ]);

            $uploadKey = uniqid('chunked_');
            $tempDir = storage_path('app/temp_chunked/' . $uploadKey);
            
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Create upload session in database
            $uploadSession = UploadSession::create([
                'upload_key' => $uploadKey,
                'filename' => $request->input('filename'),
                'total_size' => $request->input('total_size'),
                'total_chunks' => $request->input('total_chunks'),
                'relative_path' => $request->input('relative_path', ''),
                'received_chunks' => [],
                'temp_dir' => $tempDir,
                'status' => 'uploading'
            ]);

            Log::info('Chunked upload initialized', [
                'upload_key' => $uploadKey,
                'filename' => $request->input('filename'),
                'total_size' => $request->input('total_size'),
                'total_chunks' => $request->input('total_chunks')
            ]);

            return response()->json([
                'success' => true,
                'upload_id' => $uploadKey,
                'message' => 'Upload session created'
            ]);
        } catch (\Exception $e) {
            Log::error('Chunked upload init failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle individual chunk upload
     */
    public function chunkedUpload(Request $request)
    {
        try {
            $request->validate([
                'upload_id' => 'required|string',
                'chunk_index' => 'required|integer',
                'chunk' => 'required|file'
            ]);

            $uploadKey = $request->input('upload_id');
            $chunkIndex = $request->input('chunk_index');
            $chunk = $request->file('chunk');

            // Get upload session from database
            $uploadSession = UploadSession::where('upload_key', $uploadKey)->first();
            if (!$uploadSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload session not found'
                ], 404);
            }

            // Get chunk size BEFORE moving (file object becomes invalid after move)
            $chunkSize = $chunk->getSize();

            // Save chunk to temporary directory
            $chunk->move($uploadSession->temp_dir, "chunk_{$chunkIndex}");

            // Update received chunks list
            $receivedChunks = $uploadSession->received_chunks ?? [];
            $receivedChunks[] = $chunkIndex;
            $receivedChunks = array_unique($receivedChunks);
            sort($receivedChunks);
            
            $uploadSession->update([
                'received_chunks' => $receivedChunks,
                'uploaded_size' => $uploadSession->uploaded_size + $chunkSize
            ]);

            Log::info('Chunk received', [
                'upload_key' => $uploadKey,
                'chunk_index' => $chunkIndex,
                'received_count' => count($receivedChunks),
                'total_chunks' => $uploadSession->total_chunks
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chunk uploaded successfully',
                'received_chunks' => count($receivedChunks),
                'total_chunks' => $uploadSession->total_chunks
            ]);
        } catch (\Exception $e) {
            Log::error('Chunk upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload chunk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete chunked upload - reassemble chunks and process file
     */
    public function chunkedUploadComplete(Request $request)
    {
        try {
            $request->validate([
                'upload_id' => 'required|string',
                'preserve_structure' => 'boolean',
                'current_path' => 'nullable|string'
            ]);

            $uploadKey = $request->input('upload_id');
            $preserveStructure = $request->input('preserve_structure', true);
            $currentPath = $request->input('current_path', '');

            // Get upload session from database
            $uploadSession = UploadSession::where('upload_key', $uploadKey)->first();
            if (!$uploadSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload session not found'
                ], 404);
            }

            // Verify all chunks received
            $receivedChunks = $uploadSession->received_chunks ?? [];
            if (count($receivedChunks) !== $uploadSession->total_chunks) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not all chunks received',
                    'received' => count($receivedChunks),
                    'expected' => $uploadSession->total_chunks
                ], 400);
            }

            // Reassemble file from chunks
            $finalPath = $uploadSession->temp_dir . '/' . $uploadSession->filename;
            $finalFile = fopen($finalPath, 'wb');

            for ($i = 0; $i < $uploadSession->total_chunks; $i++) {
                $chunkPath = $uploadSession->temp_dir . "/chunk_{$i}";
                if (!file_exists($chunkPath)) {
                    fclose($finalFile);
                    $uploadSession->markAsFailed("Missing chunk: {$i}");
                    throw new \Exception("Missing chunk: {$i}");
                }
                $chunkData = file_get_contents($chunkPath);
                fwrite($finalFile, $chunkData);
                unlink($chunkPath); // Delete chunk after merging
            }

            fclose($finalFile);

            Log::info('File reassembled from chunks', [
                'upload_key' => $uploadKey,
                'filename' => $uploadSession->filename,
                'file_size' => filesize($finalPath)
            ]);

            // Check if this is a ZIP file meant for extraction (not to be stored as a cache file)
            $extension = strtolower(pathinfo($uploadSession->filename, PATHINFO_EXTENSION));
            $isZipForExtraction = $extension === 'zip';

            // Initialize result counters
            $uploadedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            if ($isZipForExtraction) {
                // For ZIP files: DO NOT add to database - only keep for extraction
                Log::info('ZIP file reassembled - ready for extraction, NOT adding to cache files', [
                    'upload_key' => $uploadKey,
                    'filename' => $uploadSession->filename,
                    'file_path' => $finalPath
                ]);
                
                // Just store the file path for zipExtractPatch to use later
                $uploadSession->update([
                    'metadata' => array_merge($uploadSession->metadata ?? [], [
                        'final_file_path' => $finalPath,
                        'is_zip_for_extraction' => true
                    ])
                ]);
            } else {
                // For non-ZIP files: Process normally and add to database
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $finalPath,
                    $uploadSession->filename,
                    mime_content_type($finalPath),
                    null,
                    true
                );

                $relativePath = $uploadSession->relative_path ?: null;
                $fileData = [[
                    'file' => $uploadedFile,
                    'relativePath' => $relativePath,
                    'index' => 0
                ]];

                $result = $this->processBatchUpload($fileData, $preserveStructure, $currentPath);
                
                $uploadedCount = count($result['uploaded']);
                $skippedCount = count($result['skipped']);
                $errorCount = count($result['errors']);

                // Store the final file path in metadata
                $uploadSession->update([
                    'metadata' => array_merge($uploadSession->metadata ?? [], [
                        'final_file_path' => $finalPath,
                        'processing_result' => [
                            'uploaded' => $uploadedCount,
                            'skipped' => $skippedCount,
                            'errors' => $errorCount
                        ]
                    ])
                ]);
            }
            
            // Mark as completed
            $uploadSession->markAsCompleted();
            
            // Delete chunk files to save space
            $chunksDir = $uploadSession->temp_dir;
            if (file_exists($chunksDir)) {
                $files = glob($chunksDir . '/chunk_*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            Log::info('Chunked upload completed', [
                'upload_key' => $uploadKey,
                'filename' => $uploadSession->filename,
                'is_zip_for_extraction' => $isZipForExtraction,
                'uploaded' => $uploadedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount
            ]);

            return response()->json([
                'success' => true,
                'message' => $isZipForExtraction 
                    ? 'ZIP file uploaded successfully. Ready for extraction.' 
                    : 'File uploaded successfully',
                'uploaded_count' => $uploadedCount,
                'skipped_count' => $skippedCount,
                'error_count' => $errorCount
            ]);
        } catch (\Exception $e) {
            Log::error('Chunked upload complete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Mark upload as failed if session exists
            if (isset($uploadSession)) {
                $uploadSession->markAsFailed($e->getMessage());
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle TAR file upload with real-time extraction progress
     */
    public function storeTar(Request $request)
    {
        $request->validate([
            'tar_file' => 'required|file|max:2097152', // 2GB max for TAR files
            'preserve_structure' => 'boolean',
            'current_path' => 'nullable|string'
        ]);

        $tarFile = $request->file('tar_file');
        $preserveStructure = $request->input('preserve_structure', true);
        $currentPath = $request->input('current_path', '');
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
            $this->extractTarInBackground($fullTempPath, $extractionId, $preserveStructure, $currentPath);

            return response()->json([
                'success' => true,
                'message' => 'TAR file uploaded successfully. Extraction started.',
                'extraction_id' => $extractionId
            ]);

        } catch (\Exception $e) {
            Log::error('Cache TAR file upload failed', [
                'file' => $tarFile->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
     * Upload ZIP → Extract → Generate Patch → Cleanup (synchronous, no background job)
     */
    public function zipExtractPatch(Request $request)
    {
        set_time_limit(600); // 10 minutes for large ZIPs
        ini_set('memory_limit', '2G');

        try {
            $request->validate([
                'upload_id' => 'required|string'
            ]);

            $uploadKey = $request->input('upload_id');
            
            // Get upload session from database
            $uploadSession = UploadSession::where('upload_key', $uploadKey)->first();
            if (!$uploadSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload session not found'
                ], 404);
            }

            // Get the file path from metadata (stored by chunkedUploadComplete)
            $metadata = $uploadSession->metadata ?? [];
            $zipPath = $metadata['final_file_path'] ?? ($uploadSession->temp_dir . '/' . $uploadSession->filename);
            
            // Verify the file exists
            if (!file_exists($zipPath)) {
                Log::error('ZIP file not found for extraction', [
                    'upload_key' => $uploadKey,
                    'expected_path' => $zipPath,
                    'temp_dir' => $uploadSession->temp_dir,
                    'filename' => $uploadSession->filename,
                    'metadata' => $metadata
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'ZIP file not found. Please complete upload first.'
                ], 404);
            }

            $tempDir = storage_path('app/temp_zip_extract_' . uniqid());
            
            Log::info('ZIP extraction started (synchronous)', [
                'upload_key' => $uploadKey,
                'zip_path' => $zipPath
            ]);

            // Step 1: Extract ZIP
            if (!mkdir($tempDir, 0755, true)) {
                throw new \Exception('Failed to create extraction directory');
            }

            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open ZIP file');
            }

            $extractedCount = $zip->numFiles;
            $zip->extractTo($tempDir);
            $zip->close();

            // Step 2: Process extracted files and add to database
            $uploadedFiles = [];
            $skippedFiles = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

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
                }
            }

            // Step 3: Generate patch
            $patchService = new CachePatchService();
            $patchData = $patchService->generatePatchFromDatabase();
            
            $patchVersion = null;
            if (!isset($patchData['no_changes']) || !$patchData['no_changes']) {
                CachePatch::create([
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

            // Step 4: Cleanup
            Storage::deleteDirectory('temp_zips');
            Storage::deleteDirectory('temp_uploads');
            $this->deleteDirectory($tempDir);
            
            // Clean up upload session temp directory
            if (is_dir($uploadSession->temp_dir)) {
                $this->deleteDirectory($uploadSession->temp_dir);
            }
            
            // Mark upload session as completed
            $uploadSession->markAsCompleted();

            Log::info('ZIP extraction completed successfully (synchronous)', [
                'upload_key' => $uploadKey,
                'extracted_count' => $extractedCount,
                'uploaded_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'patch_version' => $patchVersion
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ZIP file processed successfully',
                'extracted_count' => $extractedCount,
                'file_count' => count($uploadedFiles),
                'skipped_count' => count($skippedFiles),
                'patch_version' => $patchVersion
            ]);

        } catch (\Exception $e) {
            // Cleanup on error
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            
            Storage::deleteDirectory('temp_zips');
            Storage::deleteDirectory('temp_uploads');
            
            if (isset($uploadSession) && is_dir($uploadSession->temp_dir)) {
                $this->deleteDirectory($uploadSession->temp_dir);
            }

            Log::error('ZIP → Extract → Patch failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process ZIP file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ZIP extraction progress (simplified - no tracking)
     */
    public function zipExtractionProgress(Request $request)
    {
        return response()->json([
            'status' => 'processing',
            'message' => 'Extraction is processing in the background. Please check your patch list for completion.'
        ]);
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
                        $errorMsg = "Failed to process '{$fileInfo->getFilename()}': " . $e->getMessage();
                        Log::error('Cache file extraction processing failed', [
                            'file' => $fileInfo->getFilename(),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $errors[] = $errorMsg;
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
                    $errorMsg = "Files extracted but manifest generation failed: " . $e->getMessage();
                    Log::error('Cache manifest generation failed after extraction', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'uploaded_count' => count($uploadedFiles)
                    ]);
                    $errors[] = $errorMsg;
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
            Log::error('Cache file extraction failed', [
                'extraction_id' => $extractionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            Log::error('Cache ZIP extraction failed', [
                'file_path' => $filePath,
                'extract_path' => $extractPath
            ]);
            throw new \Exception('Failed to open ZIP file');
        }
    }

    /**
     * Extract TAR file
     */
    private function extractTarFile($filePath, $extractPath)
    {
        try {
            $phar = new PharData($filePath);
            $phar->extractTo($extractPath);
        } catch (\Exception $e) {
            Log::error('Cache TAR extraction failed', [
                'file_path' => $filePath,
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Extract RAR file (requires rar extension)
     */
    private function extractRarFile($filePath, $extractPath)
    {
        if (!extension_loaded('rar')) {
            Log::error('Cache RAR extraction failed - extension not loaded', [
                'file_path' => $filePath
            ]);
            throw new \Exception('RAR extension not available. Please install php-rar extension.');
        }

        $rar = rar_open($filePath);
        if (!$rar) {
            Log::error('Cache RAR extraction failed - cannot open file', [
                'file_path' => $filePath,
                'extract_path' => $extractPath
            ]);
            throw new \Exception('Failed to open RAR file');
        }

        try {
            $entries = rar_list($rar);
            foreach ($entries as $entry) {
                if (!$entry->isDirectory()) {
                    $entry->extract($extractPath);
                }
            }
            rar_close($rar);
        } catch (\Exception $e) {
            Log::error('Cache RAR extraction failed during extract', [
                'file_path' => $filePath,
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            rar_close($rar);
            throw $e;
        }
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
            Log::error('Cache 7Z extraction failed', [
                'file_path' => $filePath,
                'extract_path' => $extractPath,
                'return_code' => $returnCode,
                'output' => implode("\n", $output)
            ]);
            throw new \Exception('Failed to extract 7Z file. Make sure 7zip is installed.');
        }
    }

    /**
     * Extract TAR file in background with progress tracking
     */
    private function extractTarInBackground($tarPath, $extractionId, $preserveStructure = true, $currentPath = '')
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
                        // CRITICAL FIX: Preserve directory structure, NOT including filename
                        $directoryPath = null;
                        if ($preserveStructure) {
                            $fullPath = $fileInfo->getPathname();
                            $fullRelativePath = str_replace($extractPath . DIRECTORY_SEPARATOR, '', $fullPath);
                            // Convert backslashes to forward slashes
                            $fullRelativePath = str_replace('\\', '/', $fullRelativePath);

                            // Extract ONLY the directory path, excluding the filename
                            $pathParts = explode('/', $fullRelativePath);
                            array_pop($pathParts); // Remove filename
                            $directoryPath = !empty($pathParts) ? implode('/', $pathParts) : null;

                            // Prepend current path if present
                            if ($currentPath) {
                                $directoryPath = $directoryPath ? ($currentPath . '/' . $directoryPath) : $currentPath;
                            }
                        }

                        $result = $this->processExtractedFile($fileInfo, $directoryPath);
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
                        $errorMsg = "Failed to process '{$fileInfo->getFilename()}': " . $e->getMessage();
                        Log::error('Cache TAR file processing failed', [
                            'file' => $fileInfo->getFilename(),
                            'extraction_id' => $extractionId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $errors[] = $errorMsg;
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
                    $errorMsg = "Files extracted but manifest generation failed: " . $e->getMessage();
                    Log::error('Cache manifest generation failed after TAR extraction', [
                        'extraction_id' => $extractionId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'uploaded_count' => count($uploadedFiles)
                    ]);
                    $errors[] = $errorMsg;
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
            Log::error('Cache TAR extraction failed', [
                'extraction_id' => $extractionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
    private function processBatchUpload(array $fileData, $preserveStructure = true, $currentPath = ''): array
    {
        $uploadedFiles = [];
        $skippedFiles = [];
        $errors = [];

        // OPTIMIZED: Build all filenames and paths first
        $checkData = [];
        foreach ($fileData as $data) {
            $originalName = $data['file']->getClientOriginalName();
            $relativePath = $data['relativePath'];
            // Prepend current path to relative path if present
            if ($relativePath && $preserveStructure) {
                $fullRelativePath = $currentPath ? ($currentPath . '/' . $relativePath) : $relativePath;
            } elseif ($currentPath && !$relativePath) {
                // No relative path but we're in a subdirectory - use current path
                $fullRelativePath = $currentPath;
            } else {
                $fullRelativePath = null;
            }

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

                // Store file in temporary location for patch processing
                if ($fullRelativePath) {
                    // Sanitize the relative path to prevent directory traversal
                    $safePath = str_replace(['..', '\\'], ['', '/'], $fullRelativePath);
                    $safePath = trim($safePath, '/');

                    // Extract directory path (excluding filename)
                    $pathParts = explode('/', $safePath);
                    array_pop($pathParts); // Remove filename
                    $directory = implode('/', $pathParts);

                    if ($directory) {
                        $storagePath = 'temp_uploads/' . $directory . '/' . $originalName;
                        $storageDir = 'temp_uploads/' . $directory;
                    } else {
                        $storagePath = 'temp_uploads/' . $originalName;
                        $storageDir = 'temp_uploads';
                    }
                    $path = $file->storeAs($storageDir, $originalName);
                } elseif ($currentPath) {
                    // Upload to current directory
                    $safePath = str_replace(['..', '\\'], ['', '/'], $currentPath);
                    $safePath = trim($safePath, '/');
                    $storagePath = 'temp_uploads/' . $safePath . '/' . $originalName;
                    $storageDir = 'temp_uploads/' . $safePath;
                    $path = $file->storeAs($storageDir, $originalName);
                } else {
                    // Flat storage with unique ID
                    $storagePath = 'temp_uploads/' . uniqid() . '_' . $originalName;
                    $path = $file->storeAs('temp_uploads', basename($storagePath));
                }

                // PERFORMANCE FIX: Skip hashing during upload for maximum speed
                // Only hash if we need to check for duplicates
                $hash = null;
                if ($existing) {
                    // Only compute hash to compare with existing file
                    $fileSize = $file->getSize();
                    
                    // Quick size check first - if sizes differ, not a duplicate
                    if ($existing->size !== $fileSize) {
                        // Different size, definitely not duplicate - use MD5 (fast)
                        $hash = md5_file($file->getRealPath());
                    } else {
                        // Same size, need SHA256 to check for duplicates
                        $hash = hash_file('sha256', $file->getRealPath());
                        
                        if ($existing->hash === $hash) {
                            // Identical file - skip it
                            Storage::delete($path);
                            $skippedFiles[] = $originalName;
                            continue;
                        }
                    }
                } else {
                    // No existing file - use MD5 for speed (10x faster than SHA256)
                    $hash = md5_file($file->getRealPath());
                }
                
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';

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
                    'relative_path' => $directoryPath ?: null,
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
                $errorMsg = "Failed to upload '{$data['filename']}': " . $e->getMessage();
                Log::error('Cache batch file upload failed', [
                    'file' => $data['filename'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errors[] = $errorMsg;
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

        // Extract directory path from relative path (excluding filename)
        $directoryPath = null;
        if ($relativePath && $preserveStructure) {
            $pathParts = explode('/', $relativePath);
            array_pop($pathParts); // Remove filename
            $directoryPath = implode('/', $pathParts);
            $directoryPath = $directoryPath ?: null;
        }

        // Check if identical file already exists (same hash)
        $existing = CacheFile::where('filename', $originalName)
            ->where('relative_path', $directoryPath)
            ->first();

        if ($existing && $existing->hash === $hash) {
            return [
                'success' => true,
                'skipped' => true,
                'filename' => $originalName,
                'reason' => 'Identical file already exists'
            ];
        }

        // Store in temporary location for patch processing
        $storagePath = 'temp_uploads/' . uniqid() . '_' . $originalName;
        $path = $file->storeAs('temp_uploads', basename($storagePath));

        // Create or update database record with enhanced metadata (overwrite if hash differs)
        CacheFile::updateOrCreate(
            [
                'filename' => $originalName,
                'relative_path' => $directoryPath ?: null
            ],
            [
                'path' => $path,
                'size' => $file->getSize(),
                'hash' => $hash,
                'file_type' => 'file',
                'mime_type' => $mimeType,
                'metadata' => [
                    'original_name' => $originalName,
                    'relative_path' => $relativePath,
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
    private function processZipUpload($zipFile, $preserveStructure = true, $currentPath = ''): array
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
                        // CRITICAL FIX: Preserve directory structure, NOT including filename
                        $directoryPath = null;
                        if ($preserveStructure) {
                            $fullPath = $fileInfo->getPathname();
                            $fullRelativePath = str_replace($extractPath . DIRECTORY_SEPARATOR, '', $fullPath);
                            // Convert backslashes to forward slashes
                            $fullRelativePath = str_replace('\\', '/', $fullRelativePath);

                            // Extract ONLY the directory path, excluding the filename
                            $pathParts = explode('/', $fullRelativePath);
                            array_pop($pathParts); // Remove filename
                            $directoryPath = !empty($pathParts) ? implode('/', $pathParts) : null;

                            // Prepend current path if present
                            if ($currentPath) {
                                $directoryPath = $directoryPath ? ($currentPath . '/' . $directoryPath) : $currentPath;
                            }
                        }

                        $result = $this->processExtractedFile($fileInfo, $directoryPath);
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
                        $errorMsg = "Failed to process '{$fileInfo->getFilename()}': " . $e->getMessage();
                        Log::error('Cache ZIP file processing failed', [
                            'file' => $fileInfo->getFilename(),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $errors[] = $errorMsg;
                    }
                }
            }
            
            // Clean up temporary directory
            $this->deleteDirectory($extractPath);
        } else {
            $errorMsg = "Failed to open ZIP file: " . $zipFile->getClientOriginalName();
            Log::error('Cache ZIP file open failed', [
                'file' => $zipFile->getClientOriginalName()
            ]);
            $errors[] = $errorMsg;
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
        
        // Extract directory path from relative path (excluding filename)
        $directoryPath = null;
        if ($relativePath) {
            $pathParts = explode('/', $relativePath);
            array_pop($pathParts); // Remove filename
            $directoryPath = implode('/', $pathParts);
            $directoryPath = $directoryPath ?: null;
        }
        
        // Check if identical file already exists (same hash) using directory path
        $existing = CacheFile::where('filename', $filename)
            ->where('relative_path', $directoryPath)
            ->first();
        
        if ($existing && $existing->hash === $hash) {
            return [
                'success' => true,
                'skipped' => true,
                'filename' => $filename,
                'reason' => 'Identical file already exists'
            ];
        }

        // PRESERVE DIRECTORY STRUCTURE: Store files temporarily for patch processing
        if ($directoryPath) {
            // Sanitize path to prevent directory traversal
            $safePath = str_replace(['..', '\\'], ['', '/'], $directoryPath);
            $safePath = trim($safePath, '/');
            $storagePath = 'temp_uploads/' . $safePath . '/' . $filename;
            $storageDir = 'temp_uploads/' . $safePath;
        } else {
            // Root level file
            $storagePath = 'temp_uploads/' . $filename;
            $storageDir = 'temp_uploads';
        }
        
        $destinationPath = storage_path('app/' . $storagePath);
        
        // Ensure directory exists
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        copy($fileInfo->getRealPath(), $destinationPath);
        
        // Create or update database record with enhanced metadata (overwrite if hash differs)
        CacheFile::updateOrCreate(
            [
                'filename' => $filename,
                'relative_path' => $directoryPath
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
     * Recursively delete directory with error handling for locked files
     */
    private function deleteDirectory($dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            try {
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    @unlink($path);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete file during cleanup', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            }
        }

        try {
            @rmdir($dir);
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to delete directory during cleanup', [
                'dir' => $dir,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ===========================
    // PATCH SYSTEM METHODS
    // ===========================

    public function getLatestVersion()
    {
        $latestVersion = CachePatch::getLatestVersion();
        $patches = CachePatch::latest()->get();

        return response()->json([
            'latest_version' => $latestVersion ?? '0.0.0',
            'patches' => $patches->map(function($patch) {
                return [
                    'version' => $patch->version,
                    'is_base' => $patch->is_base,
                    'size' => $patch->size,
                    'file_count' => $patch->file_count,
                    'created_at' => $patch->created_at->toISOString(),
                ];
            })
        ]);
    }

    public function checkForUpdates(Request $request)
    {
        $request->validate([
            'current_version' => 'required|string'
        ]);

        $currentVersion = $request->input('current_version');
        $latestVersion = CachePatch::getLatestVersion();

        if (!$latestVersion || version_compare($currentVersion, $latestVersion) >= 0) {
            return response()->json([
                'has_updates' => false,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion ?? $currentVersion
            ]);
        }

        $patches = CachePatch::all()
            ->filter(function($patch) use ($currentVersion) {
                return version_compare($patch->version, $currentVersion, '>');
            })
            ->sortBy(function($patch) {
                return $patch->version;
            }, SORT_NATURAL)
            ->values();

        return response()->json([
            'has_updates' => true,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'patches' => $patches->map(function($patch) {
                return [
                    'version' => $patch->version,
                    'path' => $patch->path,
                    'size' => $patch->size,
                    'file_count' => $patch->file_count,
                ];
            })
        ]);
    }

    public function downloadPatch(CachePatch $patch)
    {
        if (!$patch->existsOnDisk()) {
            return response()->json([
                'error' => 'Patch file not found on disk.'
            ], 404);
        }

        return response()->download($patch->full_path, "patch_{$patch->version}.zip");
    }

    public function downloadCombinedPatches(Request $request)
    {
        $request->validate([
            'from_version' => 'required|string'
        ]);

        $fromVersion = $request->input('from_version');
        $patchService = new CachePatchService();
        
        $combinedPath = $patchService->combinePatchesForDownload($fromVersion);

        if (!$combinedPath) {
            return response()->json([
                'error' => 'No patches available for the specified version.'
            ], 404);
        }

        $fullPath = storage_path('app/' . $combinedPath);
        
        if (!file_exists($fullPath)) {
            return response()->json([
                'error' => 'Combined patch file could not be created.'
            ], 500);
        }

        return response()->download($fullPath, "patches_{$fromVersion}_to_" . CachePatch::getLatestVersion() . ".zip");
    }

    public function mergePatches()
    {
        try {
            $patchService = new CachePatchService();
            
            if (!$patchService->shouldMergePatches()) {
                return redirect()->route('admin.cache.index')
                    ->with('info', 'Not enough patches to merge yet (threshold is ' . CachePatchService::PATCH_THRESHOLD . ').');
            }

            $newBase = $patchService->mergePatches();

            if ($newBase) {
                return redirect()->route('admin.cache.index')
                    ->with('success', "Patches merged successfully into new base version {$newBase->version}.");
            }

            return redirect()->route('admin.cache.index')
                ->with('error', 'Failed to merge patches.');

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.index')
                ->with('error', 'Failed to merge patches: ' . $e->getMessage());
        }
    }

    public function deletePatch(CachePatch $patch)
    {
        try {
            if ($patch->is_base) {
                return redirect()->route('admin.cache.index')
                    ->with('error', 'Cannot delete base patches. Please merge patches first.');
            }

            $patch->deleteFile();
            $patch->delete();

            return redirect()->route('admin.cache.index')
                ->with('success', "Patch {$patch->version} deleted successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.cache.index')
                ->with('error', 'Failed to delete patch: ' . $e->getMessage());
        }
    }

    public function clearAllPatches()
    {
        try {
            $patches = CachePatch::all();
            $patchCount = $patches->count();
            
            // Also clear cache files
            $cacheFiles = CacheFile::all();
            $fileCount = $cacheFiles->count();

            if ($patchCount === 0 && $fileCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patches or files to clear.'
                ]);
            }

            // Delete all patches
            foreach ($patches as $patch) {
                $patch->deleteFile();
                $patch->delete();
            }

            // Delete all cache files
            foreach ($cacheFiles as $file) {
                if ($file->existsOnDisk()) {
                    Storage::delete($file->path);
                }
                $file->delete();
            }

            Storage::deleteDirectory('cache/patches');
            Storage::deleteDirectory('cache/manifests');
            Storage::deleteDirectory('cache/files');
            Storage::makeDirectory('cache/patches');
            Storage::makeDirectory('cache/manifests');
            Storage::makeDirectory('cache/files');

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$patchCount} patches and {$fileCount} cache files. Next upload will create a new base patch.",
                'cleared_patches' => $patchCount,
                'cleared_files' => $fileCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear patches: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare two patches and return differences
     */
    public function comparePatches(Request $request)
    {
        try {
            $fromPatchId = $request->get('from');
            $toPatchId = $request->get('to');
            
            if (!$fromPatchId || !$toPatchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Both patch IDs are required'
                ], 400);
            }
            
            $fromPatch = CachePatch::find($fromPatchId);
            $toPatch = CachePatch::find($toPatchId);
            
            if (!$fromPatch || !$toPatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or both patches not found'
                ], 404);
            }
            
            $fromManifest = $fromPatch->file_manifest ?? [];
            $toManifest = $toPatch->file_manifest ?? [];
            
            // Calculate differences
            $added = [];
            $removed = [];
            $modified = [];
            
            // Find added and modified files
            foreach ($toManifest as $file => $hash) {
                if (!isset($fromManifest[$file])) {
                    $added[] = $file;
                } elseif ($fromManifest[$file] !== $hash) {
                    $modified[] = [
                        'file' => $file,
                        'old_hash' => $fromManifest[$file],
                        'new_hash' => $hash
                    ];
                }
            }
            
            // Find removed files
            foreach ($fromManifest as $file => $hash) {
                if (!isset($toManifest[$file])) {
                    $removed[] = $file;
                }
            }
            
            return response()->json([
                'success' => true,
                'from_patch' => [
                    'id' => $fromPatch->id,
                    'version' => $fromPatch->version
                ],
                'to_patch' => [
                    'id' => $toPatch->id,
                    'version' => $toPatch->version
                ],
                'added' => $added,
                'removed' => $removed,
                'modified' => $modified
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate changelog for a patch
     */
    public function generateChangelog(CachePatch $patch)
    {
        try {
            return response()->json([
                'success' => true,
                'version' => $patch->version,
                'is_base' => $patch->is_base,
                'base_version' => $patch->base_version,
                'file_count' => $patch->file_count,
                'file_manifest' => $patch->file_manifest ?? [],
                'size' => $patch->size,
                'created_at' => $patch->created_at->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file change history across all patches
     */
    public function getFileHistory(Request $request)
    {
        try {
            $filePath = $request->get('path');
            
            if (!$filePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'File path is required'
                ], 400);
            }
            
            $patches = CachePatch::latest()->get();
            $history = [];
            $previousHash = null;
            
            foreach ($patches as $patch) {
                $manifest = $patch->file_manifest ?? [];
                
                if (isset($manifest[$filePath])) {
                    $currentHash = $manifest[$filePath];
                    $status = 'exists';
                    
                    if ($previousHash === null) {
                        $status = 'added';
                    } elseif ($previousHash !== $currentHash) {
                        $status = 'modified';
                    }
                    
                    $history[] = [
                        'version' => $patch->version,
                        'hash' => $currentHash,
                        'status' => $status,
                        'created_at' => $patch->created_at->toISOString()
                    ];
                    
                    $previousHash = $currentHash;
                }
            }
            
            if (empty($history)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in any patch'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'file' => $filePath,
                'history' => array_reverse($history)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify patch integrity by checking file checksums
     */
    public function verifyIntegrity(CachePatch $patch)
    {
        try {
            $manifest = $patch->file_manifest ?? [];
            $valid = 0;
            $invalid = 0;
            $missing = 0;
            $invalidFiles = [];
            $missingFiles = [];
            
            foreach ($manifest as $filePath => $expectedHash) {
                // Check if file exists in cache_files table
                $file = CacheFile::where(function($query) use ($filePath) {
                    // Parse the file path
                    $parts = explode('/', $filePath);
                    $filename = array_pop($parts);
                    $relativePath = implode('/', $parts);
                    
                    $query->where('filename', $filename);
                    
                    if (empty($relativePath)) {
                        $query->whereNull('relative_path');
                    } else {
                        $query->where('relative_path', $relativePath);
                    }
                })->first();
                
                if (!$file) {
                    $missing++;
                    $missingFiles[] = $filePath;
                } elseif ($file->hash !== $expectedHash) {
                    $invalid++;
                    $invalidFiles[] = $filePath;
                } else {
                    $valid++;
                }
            }
            
            return response()->json([
                'success' => true,
                'total' => count($manifest),
                'valid' => $valid,
                'invalid' => $invalid,
                'missing' => $missing,
                'invalid_files' => $invalidFiles,
                'missing_files' => $missingFiles
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}