<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadChunk extends Model
{
    protected $fillable = [
        'upload_session_id',
        'chunk_number',
        'chunk_size',
        'chunk_hash',
        'uploaded_at'
    ];

    protected $casts = [
        'chunk_number' => 'integer',
        'chunk_size' => 'integer',
        'uploaded_at' => 'datetime'
    ];

    public function uploadSession(): BelongsTo
    {
        return $this->belongsTo(UploadSession::class);
    }
}
