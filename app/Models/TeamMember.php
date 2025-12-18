<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'paypal_email',
        'percentage',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getTotalPercentage(): float
    {
        return static::active()->sum('percentage');
    }

    public static function validatePercentages(): bool
    {
        return static::getTotalPercentage() <= 100;
    }
}
