<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CacheBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_key',
        'file_list',
        'path',
        'size',
        'expires_at'
    ];

    protected $casts = [
        'file_list' => 'array',
        'size' => 'integer',
        'expires_at' => 'datetime'
    ];

    /**
     * Scope for non-expired bundles
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope for expired bundles
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if bundle is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get time until expiry
     */
    public function getTimeUntilExpiryAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        return $this->expires_at->diffForHumans();
    }

    /**
     * Get file count
     */
    public function getFileCountAttribute(): int
    {
        return count($this->file_list);
    }

    /**
     * Check if bundle file exists on disk
     */
    public function existsOnDisk(): bool
    {
        return file_exists(storage_path('app/' . $this->path));
    }

    /**
     * Get full file path
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->path);
    }

    /**
     * Delete bundle file from disk
     */
    public function deleteFile(): bool
    {
        if ($this->existsOnDisk()) {
            return unlink($this->full_path);
        }
        return true;
    }
}