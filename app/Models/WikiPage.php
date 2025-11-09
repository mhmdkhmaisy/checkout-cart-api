<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WikiPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'category',
        'description',
        'content',
        'order',
        'published',
        'icon'
    ];

    protected $casts = [
        'published' => 'boolean',
        'order' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('title', 'asc');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function getAllCategories()
    {
        return static::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    public function generateTableOfContents()
    {
        preg_match_all('/<h([2-3])(?:[^>]*)id="([^"]*)"[^>]*>(.+?)<\/h\1>/i', $this->content, $matches, PREG_SET_ORDER);
        
        $toc = [];
        foreach ($matches as $match) {
            $toc[] = [
                'level' => (int)$match[1],
                'id' => $match[2],
                'text' => strip_tags($match[3])
            ];
        }
        
        return $toc;
    }
}
