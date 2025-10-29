<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CachePatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'base_version',
        'path',
        'file_manifest',
        'file_count',
        'size',
        'is_base'
    ];

    protected $casts = [
        'file_manifest' => 'array',
        'file_count' => 'integer',
        'size' => 'integer',
        'is_base' => 'boolean'
    ];

    public function scopeBase($query)
    {
        return $query->where('is_base', true);
    }

    public function scopePatches($query)
    {
        return $query->where('is_base', false);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function existsOnDisk(): bool
    {
        return file_exists(public_path($this->path));
    }

    public function getFullPathAttribute(): string
    {
        return public_path($this->path);
    }

    public function getPublicUrlAttribute(): string
    {
        return url($this->path);
    }

    public function deleteFile(): bool
    {
        if ($this->existsOnDisk()) {
            return unlink($this->full_path);
        }
        return true;
    }

    public static function getLatestVersion(): ?string
    {
        $latest = self::latest()->first();
        return $latest ? $latest->version : null;
    }

    public static function incrementVersion(?string $currentVersion = null): string
    {
        if (!$currentVersion) {
            return '1.0.0';
        }

        $parts = explode('.', $currentVersion);
        $parts[2] = (int)$parts[2] + 1;
        return implode('.', $parts);
    }

    public static function getPatchesSince(string $version): array
    {
        $patches = self::all()
            ->filter(function($patch) use ($version) {
                return version_compare($patch->version, $version, '>');
            })
            ->sortBy(function($patch) {
                return $patch->version;
            }, SORT_NATURAL);
        
        return $patches->pluck('path')->toArray();
    }
    
    public static function compareVersions(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }
}
