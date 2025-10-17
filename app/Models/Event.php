<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'rewards',
        'start_at',
        'end_at',
        'image',
        'status'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeNotEnded($query)
    {
        return $query->whereIn('status', ['upcoming', 'active']);
    }

    public function getRewardsArrayAttribute()
    {
        return json_decode($this->rewards, true) ?: [];
    }

    public function updateStatus()
    {
        $now = Carbon::now();
        
        if ($this->end_at && $now->greaterThan($this->end_at)) {
            $this->status = 'ended';
        } elseif ($now->greaterThanOrEqualTo($this->start_at) && (!$this->end_at || $now->lessThan($this->end_at))) {
            $this->status = 'active';
        } else {
            $this->status = 'upcoming';
        }
        
        $this->save();
    }
}
