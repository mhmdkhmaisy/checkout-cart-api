<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Update extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'client_update'
    ];

    protected $casts = [
        'client_update' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($update) {
            if (empty($update->slug)) {
                $update->slug = Str::slug($update->title);
                
                $count = 1;
                while (static::where('slug', $update->slug)->exists()) {
                    $update->slug = Str::slug($update->title) . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function getContentArrayAttribute()
    {
        return json_decode($this->content, true);
    }
}
