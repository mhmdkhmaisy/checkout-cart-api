<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UploadSession extends Model
{
    protected $fillable = [
        'upload_key',
        'filename',
        'relative_path',
        'file_size',
        'uploaded_size',
        'mime_type',
        'tus_id',
        'status',
        'error_message',
        'metadata',
        'completed_at'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_size' => 'integer',
        'metadata' => 'array',
        'completed_at' => 'datetime'
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(UploadChunk::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->file_size == 0) {
            return 0;
        }
        return round(($this->uploaded_size / $this->file_size) * 100, 2);
    }
}
