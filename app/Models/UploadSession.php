<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UploadSession extends Model
{
    protected $fillable = [
        'upload_key',
        'filename',
        'total_size',
        'uploaded_size',
        'total_chunks',
        'received_chunks',
        'relative_path',
        'temp_dir',
        'status',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'total_size' => 'integer',
        'uploaded_size' => 'integer',
        'total_chunks' => 'integer',
        'received_chunks' => 'array',
        'metadata' => 'array'
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(UploadChunk::class);
    }

    public function isComplete(): bool
    {
        return count($this->received_chunks ?? []) === $this->total_chunks;
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error
        ]);
    }
}
