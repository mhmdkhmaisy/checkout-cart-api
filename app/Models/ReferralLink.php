<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'target_url', 'is_active'];

    public function clicks()
    {
        return $this->hasMany(ReferralClick::class);
    }

    public function getUniqueClicksCountAttribute()
    {
        return $this->clicks()->distinct('ip_address')->count();
    }

    public function getTotalClicksCountAttribute()
    {
        return $this->clicks()->count();
    }
}
