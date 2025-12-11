<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('created_at', 'desc')->paginate(10);
        $latestClients = Client::getLatestClients();
        
        return view('admin.clients.index', compact('clients', 'latestClients'));
    }

    public function create()
    {
        $osTypes = Client::OS_TYPES;
        return view('admin.clients.create', compact('osTypes'));
    }

    public function store(Request $request)
    {
        \Log::info('Client upload started', [
            'os' => $request->os,
            'has_file' => $request->hasFile('file'),
            'version' => $request->version,
            'enabled' => $request->enabled,
            'valid_os_types' => array_keys(Client::OS_TYPES)
        ]);

        try {
            $request->validate([
                'os' => 'required|in:' . implode(',', array_keys(Client::OS_TYPES)),
                'file' => 'required|file|max:512000', // 500MB max
                'version' => 'nullable|string|regex:/^\d+\.\d+\.\d+$/',
                'changelog' => 'nullable|string|max:5000',
                'enabled' => 'boolean'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Client upload validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $file = $request->file('file');
        $os = $request->os;
        
        \Log::info('File received', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'os' => $os
        ]);
        
        // Auto-generate version if not provided
        $version = $request->version ?: Client::getNextVersion($os);
        
        \Log::info('Version determined', ['version' => $version]);
        
        // Validate file extension
        $expectedExt = Client::FILE_EXTENSIONS[$os] ?? null;
        if (!$expectedExt) {
            \Log::error('No file extension defined for OS', ['os' => $os, 'available' => Client::FILE_EXTENSIONS]);
            return back()->withErrors([
                'file' => "No file extension defined for OS: {$os}"
            ]);
        }
        
        if (!str_ends_with(strtolower($file->getClientOriginalName()), strtolower($expectedExt))) {
            \Log::error('File extension mismatch', [
                'expected' => $expectedExt,
                'got' => $file->getClientOriginalName()
            ]);
            return back()->withErrors([
                'file' => "File must have {$expectedExt} extension for {$os}"
            ]);
        }

        // Create clients directory if it doesn't exist
        $clientsPath = storage_path('app/public/clients');
        if (!File::exists($clientsPath)) {
            File::makeDirectory($clientsPath, 0755, true);
        }

        // Generate filename
        $filename = "client_{$os}_{$version}" . $expectedExt;
        $filePath = $file->storeAs('public/clients', $filename);
        
        // Calculate file hash
        $fullPath = Storage::path($filePath);
        $hash = hash_file('sha256', $fullPath);
        $size = $file->getSize();

        try {
            DB::beginTransaction();

            // If this client should be enabled, disable other clients for the same OS first
            if ($request->boolean('enabled', true)) {
                Client::where('os', $os)->update(['enabled' => false]);
            }

            // Create client record
            $client = Client::create([
                'os' => $os,
                'version' => $version,
                'file_path' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'size' => $size,
                'hash' => $hash,
                'enabled' => $request->boolean('enabled', true),
                'changelog' => $request->changelog
            ]);

            DB::commit();

            // Generate new manifest
            $this->generateManifest();

            return redirect()->route('admin.clients.index')
                ->with('success', "Client {$version} for {$client->os_display} uploaded successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Client upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up uploaded file if database operation failed
            if (isset($filePath) && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            
            return back()->withErrors([
                'error' => 'Failed to create client: ' . $e->getMessage()
            ]);
        }
    }

    public function show(Client $client)
    {
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $osTypes = Client::OS_TYPES;
        return view('admin.clients.edit', compact('client', 'osTypes'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'version' => 'required|string|regex:/^\d+\.\d+\.\d+$/',
            'changelog' => 'nullable|string|max:5000',
            'enabled' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // If enabling this client, disable others for the same OS first
            if ($request->boolean('enabled') && !$client->enabled) {
                Client::where('os', $client->os)->where('id', '!=', $client->id)->update(['enabled' => false]);
            }

            $client->update([
                'version' => $request->version,
                'changelog' => $request->changelog,
                'enabled' => $request->boolean('enabled')
            ]);

            DB::commit();

            // Regenerate manifest
            $this->generateManifest();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Client updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Failed to update client: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Client $client)
    {
        try {
            DB::beginTransaction();

            // Delete the file
            if (Storage::exists($client->file_path)) {
                Storage::delete($client->file_path);
            }

            $client->delete();

            DB::commit();

            // Regenerate manifest
            $this->generateManifest();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Client deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Failed to delete client: ' . $e->getMessage()
            ]);
        }
    }

    public function toggle(Client $client)
    {
        try {
            DB::beginTransaction();

            // If enabling, disable other clients for the same OS first
            if (!$client->enabled) {
                Client::where('os', $client->os)->where('id', '!=', $client->id)->update(['enabled' => false]);
            }

            $client->update(['enabled' => !$client->enabled]);

            DB::commit();

            // Regenerate manifest
            $this->generateManifest();

            $status = $client->enabled ? 'enabled' : 'disabled';
            return response()->json([
                'success' => true,
                'message' => "Client {$status} successfully!",
                'enabled' => $client->enabled
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle client: ' . $e->getMessage()
            ], 500);
        }
    }

    public function manifest()
    {
        $manifest = Client::generateManifest();
        return response()->json($manifest);
    }

    private function generateManifest()
    {
        $manifest = Client::generateManifest();
        
        // Save manifest to public directory
        $manifestPath = public_path('manifest.json');
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        
        return $manifest;
    }

    public function download($os, $version)
    {
        $client = Client::where('os', $os)
            ->where('version', $version)
            ->where('enabled', true)
            ->firstOrFail();

        if (!Storage::exists($client->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($client->file_path, $client->original_filename);
    }
}