<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'relative_path',
        'path',
        'size',
        'hash',
        'file_type',
        'mime_type',
        'metadata'
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array'
    ];

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
     * Get short hash for display
     */
    public function getShortHashAttribute(): string
    {
        return substr($this->hash, 0, 8) . '...';
    }

    /**
     * Check if file exists on disk
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
     * Get file extension
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Check if this is a directory entry
     */
    public function isDirectory(): bool
    {
        return $this->file_type === 'directory';
    }

    /**
     * Check if this is a file entry
     */
    public function isFile(): bool
    {
        return $this->file_type === 'file';
    }

    /**
     * Get the display path (relative_path or filename)
     */
    public function getDisplayPathAttribute(): string
    {
        return $this->relative_path ?: $this->filename;
    }

    /**
     * Get directory depth
     */
    public function getDirectoryDepthAttribute(): int
    {
        if (!$this->relative_path) {
            return 0;
        }
        return substr_count($this->relative_path, '/');
    }

    /**
     * Scope for files only
     */
    public function scopeFiles($query)
    {
        return $query->where('file_type', 'file');
    }

    /**
     * Scope for directories only
     */
    public function scopeDirectories($query)
    {
        return $query->where('file_type', 'directory');
    }

    /**
     * Scope for specific file types
     */
    public function scopeByFileType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Scope for files by extension
     */
    public function scopeByExtension($query, $extension)
    {
        return $query->where('filename', 'LIKE', "%.{$extension}");
    }
}