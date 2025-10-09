<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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
        $request->validate([
            'os' => 'required|in:' . implode(',', array_keys(Client::OS_TYPES)),
            'file' => 'required|file|max:512000', // 500MB max
            'version' => 'nullable|string|regex:/^\d+\.\d+\.\d+$/',
            'changelog' => 'nullable|string|max:5000',
            'enabled' => 'boolean'
        ]);

        $file = $request->file('file');
        $os = $request->os;
        
        // Auto-generate version if not provided
        $version = $request->version ?: Client::getNextVersion($os);
        
        // Validate file extension
        $expectedExt = Client::FILE_EXTENSIONS[$os];
        if (!str_ends_with(strtolower($file->getClientOriginalName()), strtolower($expectedExt))) {
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

        // If this client is enabled, disable other clients for the same OS
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

        // Generate new manifest
        $this->generateManifest();

        return redirect()->route('admin.clients.index')
            ->with('success', "Client {$version} for {$client->os_display} uploaded successfully!");
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

        // If enabling this client, disable others for the same OS
        if ($request->boolean('enabled') && !$client->enabled) {
            Client::where('os', $client->os)->where('id', '!=', $client->id)->update(['enabled' => false]);
        }

        $client->update([
            'version' => $request->version,
            'changelog' => $request->changelog,
            'enabled' => $request->boolean('enabled')
        ]);

        // Regenerate manifest
        $this->generateManifest();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        // Delete the file
        if (Storage::exists($client->file_path)) {
            Storage::delete($client->file_path);
        }

        $client->delete();

        // Regenerate manifest
        $this->generateManifest();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully!');
    }

    public function toggle(Client $client)
    {
        // If enabling, disable other clients for the same OS
        if (!$client->enabled) {
            Client::where('os', $client->os)->where('id', '!=', $client->id)->update(['enabled' => false]);
        }

        $client->update(['enabled' => !$client->enabled]);

        // Regenerate manifest
        $this->generateManifest();

        $status = $client->enabled ? 'enabled' : 'disabled';
        return response()->json([
            'success' => true,
            'message' => "Client {$status} successfully!",
            'enabled' => $client->enabled
        ]);
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