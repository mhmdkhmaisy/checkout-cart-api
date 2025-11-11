<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'username',
        'claim_count',
        'total_spent_during_promo',
        'last_claimed_at',
        'claimed_ingame',
        'claimable_at',
    ];

    protected $casts = [
        'total_spent_during_promo' => 'decimal:2',
        'last_claimed_at' => 'datetime',
        'claimed_ingame' => 'boolean',
        'claimable_at' => 'datetime',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function getProgressPercentAttribute()
    {
        if (!$this->promotion) {
            return 0;
        }

        return min(100, ($this->total_spent_during_promo / $this->promotion->min_amount) * 100);
    }

    public function canClaim()
    {
        if (!$this->promotion) {
            return false;
        }

        $promo = $this->promotion;

        if (!$promo->isCurrentlyActive()) {
            return false;
        }

        if ($promo->hasReachedGlobalLimit()) {
            return false;
        }

        if ($promo->claim_limit_per_user && $this->claim_count >= $promo->claim_limit_per_user) {
            return false;
        }

        if ($this->total_spent_during_promo < $promo->min_amount) {
            return false;
        }

        return true;
    }

    public function isClaimable()
    {
        return $this->claimable_at !== null && $this->canClaim();
    }
}
