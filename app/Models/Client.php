<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'windows' => 'Windows Launcher',
        'minimal' => 'Minimal Jar Launcher',
        'standalone' => 'Standalone JAR'
    ];

    public const FILE_EXTENSIONS = [
        'windows' => '.exe',
        'minimal' => '.jar',
        'standalone' => '.jar'
    ];

    /**
     * Boot method to add model event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one enabled client per OS when saving
        static::saving(function ($client) {
            if ($client->enabled && $client->isDirty('enabled')) {
                // Disable other clients for the same OS
                static::where('os', $client->os)
                    ->where('id', '!=', $client->id ?? 0)
                    ->update(['enabled' => false]);
            }
        });
    }

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
     * Safely enable a client, ensuring only one per OS is enabled
     */
    public function enableForOs()
    {
        return DB::transaction(function () {
            // Disable all other clients for this OS
            static::where('os', $this->os)
                ->where('id', '!=', $this->id)
                ->update(['enabled' => false]);
            
            // Enable this client
            $this->update(['enabled' => true]);
            
            return $this;
        });
    }

    /**
     * Get the currently enabled client for a specific OS
     */
    public static function getEnabledForOs($os)
    {
        return static::where('os', $os)
            ->where('enabled', true)
            ->first();
    }

    /**
     * Validate that only one client per OS can be enabled
     */
    public static function validateUniqueEnabledPerOs()
    {
        $duplicates = DB::select("
            SELECT os, COUNT(*) as count 
            FROM clients 
            WHERE enabled = 1 
            GROUP BY os 
            HAVING count > 1
        ");

        if (!empty($duplicates)) {
            $issues = collect($duplicates)->map(function ($item) {
                return "OS '{$item->os}' has {$item->count} enabled clients";
            })->join(', ');
            
            throw new \Exception("Data integrity issue: {$issues}");
        }

        return true;
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