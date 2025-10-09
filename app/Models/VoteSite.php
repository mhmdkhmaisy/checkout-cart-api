<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'site_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function votes()
    {
        return $this->hasMany(Vote::class, 'site_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getVoteUrlAttribute()
    {
        return str_replace('{sid}', $this->site_id, $this->url);
    }

    public function generateVoteUrl($incentive)
    {
        $url = str_replace('{sid}', $this->site_id, $this->url);
        return str_replace('{incentive}', $incentive, $url);
    }
}