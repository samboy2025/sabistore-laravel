<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'reference',
        'type',
        'amount',
        'currency',
        'status',
        'gateway',
        'gateway_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the user that made the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the payment
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope to only membership payments
     */
    public function scopeMembership($query)
    {
        return $query->where('type', 'membership');
    }

    /**
     * Scope to only product purchase payments
     */
    public function scopeProductPurchase($query)
    {
        return $query->where('type', 'product_purchase');
    }

    /**
     * Scope to only successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Mark payment as successful
     */
    public function markAsSuccessful()
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);

        // If this is a membership payment, activate the user's membership
        if ($this->type === 'membership') {
            $this->user->update([
                'membership_active' => true,
                'membership_paid_at' => now(),
            ]);
        }
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }
}
