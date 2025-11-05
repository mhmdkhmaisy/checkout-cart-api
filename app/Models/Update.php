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
        'excerpt',
        'featured_image',
        'category',
        'author',
        'meta_description',
        'client_update',
        'is_published',
        'is_featured',
        'is_pinned',
        'views',
        'published_at',
        'attached_to_update_id'
    ];

    protected $casts = [
        'client_update' => 'boolean',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'views' => 'integer',
        'published_at' => 'datetime',
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
            
            if (empty($update->published_at) && $update->is_published) {
                $update->published_at = now();
            }
        });

        static::updating(function ($update) {
            if ($update->isDirty('is_published') && $update->is_published && empty($update->published_at)) {
                $update->published_at = now();
            }
        });
    }
    
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
    
    public function incrementViews()
    {
        $this->increment('views');
    }
    
    public function getExcerptOrGenerateAttribute()
    {
        if (!empty($this->excerpt)) {
            return $this->excerpt;
        }
        
        $content = json_decode($this->content, true);
        if (isset($content['blocks'])) {
            foreach ($content['blocks'] as $block) {
                if ($block['type'] === 'paragraph' && !empty($block['data']['text'])) {
                    return Str::limit($block['data']['text'], 150);
                }
            }
        }
        
        return Str::limit($this->title, 150);
    }

    public function getContentArrayAttribute()
    {
        return json_decode($this->content, true);
    }
    
    public function hotfixes()
    {
        return $this->hasMany(Update::class, 'attached_to_update_id');
    }
    
    public function attachedToUpdate()
    {
        return $this->belongsTo(Update::class, 'attached_to_update_id');
    }
}
