<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'reward_items',
        'min_amount',
        'bonus_type',
        'claim_limit_per_user',
        'global_claim_limit',
        'claimed_global',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected $casts = [
        'reward_items' => 'array',
        'min_amount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function claims()
    {
        return $this->hasMany(PromotionClaim::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now());
    }

    public function isExpired()
    {
        return now()->gt($this->end_at);
    }

    public function isUpcoming()
    {
        return now()->lt($this->start_at);
    }

    public function isCurrentlyActive()
    {
        return $this->is_active 
            && !$this->isExpired() 
            && !$this->isUpcoming();
    }

    public function hasReachedGlobalLimit()
    {
        return $this->global_claim_limit && $this->claimed_global >= $this->global_claim_limit;
    }

    public function getTimeRemainingAttribute()
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->isUpcoming()) {
            return 'Starts in ' . $this->start_at->diffForHumans();
        }

        return 'Ends in ' . $this->end_at->diffForHumans();
    }

    public function getProgressPercentAttribute()
    {
        if (!$this->global_claim_limit) {
            return 0;
        }

        return min(100, ($this->claimed_global / $this->global_claim_limit) * 100);
    }
}
