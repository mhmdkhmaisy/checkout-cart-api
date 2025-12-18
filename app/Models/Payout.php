<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'team_member_id',
        'paypal_email',
        'gross_amount',
        'net_amount',
        'payout_amount',
        'percentage',
        'currency',
        'paypal_batch_id',
        'paypal_payout_item_id',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function teamMember()
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public static function hasPayoutForOrder(int $orderId): bool
    {
        return static::where('order_id', $orderId)->exists();
    }
}
