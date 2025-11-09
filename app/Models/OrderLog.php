<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'username',
        'status',
        'last_event',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected $appends = ['items', 'amount', 'currency'];

    // Add accessor for the view compatibility
    public function getActionAttribute()
    {
        return $this->last_event;
    }

    public function getDetailsAttribute()
    {
        if (is_array($this->payload)) {
            return json_encode($this->payload, JSON_PRETTY_PRINT);
        }
        return $this->payload ?: 'No details available';
    }

    public function getItemsAttribute()
    {
        return $this->order?->items ?? collect();
    }

    public function getAmountAttribute()
    {
        return $this->order?->amount ?? 0;
    }

    public function getCurrencyAttribute()
    {
        return $this->order?->currency ?? 'USD';
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}