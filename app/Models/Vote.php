<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'callback_date',
        'started',
        'ip_address',
        'site_id',
        'uid',
        'claimed'
    ];

    protected $casts = [
        'callback_date' => 'datetime',
        'started' => 'datetime',
        'claimed' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(VoteSite::class, 'site_id');
    }

    public function scopeUnclaimed($query)
    {
        return $query->where('claimed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('callback_date');
    }

    public function scopeRecent($query, $hours = 12)
    {
        return $query->where('started', '>=', Carbon::now()->subHours($hours));
    }

    public function canVoteAgain($hours = 12)
    {
        if (!$this->callback_date) {
            return $this->started->addHours($hours)->isPast();
        }
        
        return $this->callback_date->addHours($hours)->isPast();
    }

    public function timeUntilNextVote($hours = 12)
    {
        if (!$this->callback_date) {
            $nextVoteTime = $this->started->addHours($hours);
        } else {
            $nextVoteTime = $this->callback_date->addHours($hours);
        }

        return $nextVoteTime->isFuture() ? $nextVoteTime->diffInSeconds() : 0;
    }
}