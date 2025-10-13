<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUploadedFile;
use App\Models\UploadSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TusPhp\Tus\Server as TusServer;

class ChunkedUploadController extends Controller
{
    public function handle(Request $request)
    {
        $uploadDir = storage_path('app/tus_uploads');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $server = new TusServer('file');
        $server->setApiPath('/admin/cache/chunked-upload')
               ->setUploadDir($uploadDir);

        $server->event()->addListener('tus-server.upload.created', function($event) {
            $file = $event->getFile();
            $fileMeta = $file->details();
            $metadata = $fileMeta['metadata'] ?? [];
            $fileKey = $file->getKey();
            
            UploadSession::create([
                'upload_key' => $fileKey,
                'filename' => $metadata['filename'] ?? 'unknown',
                'relative_path' => $metadata['relativePath'] ?? null,
                'file_size' => $fileMeta['size'] ?? 0,
                'uploaded_size' => 0,
                'tus_id' => $fileKey,
                'status' => 'uploading',
                'metadata' => $metadata
            ]);
        });

        $server->event()->addListener('tus-server.upload.progress', function($event) use ($request) {
            $file = $event->getFile();
            $fileMeta = $file->details();
            $fileKey = $file->getKey();
            
            $uploadSession = UploadSession::where('tus_id', $fileKey)->first();
            if ($uploadSession) {
                $uploadSession->update([
                    'uploaded_size' => $fileMeta['offset'] ?? 0
                ]);
            }
        });

        $server->event()->addListener('tus-server.upload.complete', function($event) {
            $file = $event->getFile();
            $fileMeta = $file->details();
            $uploadKey = $file->getKey();
            
            $uploadSession = UploadSession::where('tus_id', $uploadKey)->first();
            
            if ($uploadSession) {
                $tempPath = $file->getFilePath();
                $metadata = $fileMeta['metadata'] ?? [];
                
                dispatch(new ProcessUploadedFile(
                    $uploadSession->upload_key,
                    $tempPath,
                    $metadata['filename'] ?? $uploadSession->filename,
                    $metadata['relativePath'] ?? $uploadSession->relative_path,
                    $metadata
                ));
            }
        });

        return $server->serve();
    }

    public function status(Request $request)
    {
        $uploadKey = $request->get('upload_key');
        
        $session = UploadSession::where('upload_key', $uploadKey)
            ->orWhere('tus_id', $uploadKey)
            ->first();
        
        if (!$session) {
            return response()->json(['error' => 'Upload session not found'], 404);
        }

        return response()->json([
            'upload_key' => $session->upload_key,
            'filename' => $session->filename,
            'file_size' => $session->file_size,
            'uploaded_size' => $session->uploaded_size,
            'progress_percentage' => $session->progress_percentage,
            'status' => $session->status,
            'error_message' => $session->error_message,
            'completed_at' => $session->completed_at
        ]);
    }

    public function sessions(Request $request)
    {
        $sessions = UploadSession::whereIn('status', ['uploading', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'sessions' => $sessions->map(function($session) {
                return [
                    'upload_key' => $session->upload_key,
                    'filename' => $session->filename,
                    'progress_percentage' => $session->progress_percentage,
                    'status' => $session->status
                ];
            })
        ]);
    }
}
