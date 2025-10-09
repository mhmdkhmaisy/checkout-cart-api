<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'os',
        'version',
        'file_path',
        'original_filename',
        'size',
        'hash',
        'enabled',
        'changelog'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'size' => 'integer'
    ];

    public const OS_TYPES = [
        'windows' => 'Windows',
        'macos' => 'macOS',
        'linux' => 'Linux',
        'standalone' => 'Standalone JAR'
    ];

    public const FILE_EXTENSIONS = [
        'windows' => '.exe',
        'macos' => '.dmg',
        'linux' => '.AppImage',
        'standalone' => '.jar'
    ];

    /**
     * Get the display name for the OS
     */
    public function getOsDisplayAttribute()
    {
        return self::OS_TYPES[$this->os] ?? $this->os;
    }

    /**
     * Get the expected file extension for this OS
     */
    public function getExpectedExtensionAttribute()
    {
        return self::FILE_EXTENSIONS[$this->os] ?? '';
    }

    /**
     * Get the file size in human readable format
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the download URL
     */
    public function getDownloadUrlAttribute()
    {
        return route('client.download', ['os' => $this->os, 'version' => $this->version]);
    }

    /**
     * Get the latest enabled client for each OS
     */
    public static function getLatestClients()
    {
        return self::where('enabled', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('os')
            ->map(function ($clients) {
                return $clients->first();
            });
    }

    /**
     * Generate manifest for launcher
     */
    public static function generateManifest()
    {
        $clients = self::getLatestClients();
        
        $manifest = [
            'latest' => [],
            'files' => []
        ];

        foreach ($clients as $os => $client) {
            $manifest['latest'][$os] = $client->version;
            $manifest['files'][] = [
                'os' => $os,
                'version' => $client->version,
                'url' => url('storage/clients/' . basename($client->file_path)),
                'size' => $client->size,
                'hash' => $client->hash,
                'changelog' => $client->changelog
            ];
        }

        return $manifest;
    }

    /**
     * Auto-increment version number
     */
    public static function getNextVersion($os)
    {
        $latest = self::where('os', $os)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latest) {
            return '0.1.0';
        }

        $parts = explode('.', $latest->version);
        if (count($parts) !== 3) {
            return '0.1.0';
        }

        $parts[2] = (int)$parts[2] + 1;
        return implode('.', $parts);
    }
}