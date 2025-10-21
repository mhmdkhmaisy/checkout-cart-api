<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'server_id',
        'payment_method',
        'payment_id',
        'paypal_capture_id', // Added for PayPal capture ID storage
        'amount',
        'currency',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    /**
     * Boot method to handle cascade deletes at application level
     * since we removed the database FK constraint to avoid transaction issues
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($order) {
            // Delete all associated order items when order is deleted
            $order->items()->delete();
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Find order by PayPal order ID or capture ID.
     * Prefers capture_id if both exist.
     */
    public static function findByPayPalId(string $paypalId): ?self
    {
        // Try capture_id first
        $order = self::where('paypal_capture_id', $paypalId)->first();
        if ($order) {
            return $order;
        }

        // Fallback to payment_id
        return self::where('payment_id', $paypalId)->first();
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('total_qty');
    }

    public function getUnclaimedItemsAttribute()
    {
        return $this->items()->notClaimed()->get();
    }

    public function getClaimedItemsAttribute()
    {
        return $this->items()->claimed()->get();
    }

    public function getClaimStateAttribute()
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) {
            return 'no_items';
        }
        
        $claimedItems = $this->items()->claimed()->count();
        
        if ($claimedItems === 0) {
            return 'unclaimed';
        } elseif ($claimedItems === $totalItems) {
            return 'fully_claimed';
        } else {
            return 'partially_claimed';
        }
    }

    public function isFullyClaimed()
    {
        return $this->items()->count() > 0 && $this->items()->notClaimed()->count() === 0;
    }

    public function hasUnclaimedItems()
    {
        return $this->items()->notClaimed()->count() > 0;
    }
}