<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'reference',
        'description',
        'status',
        'related_order_id',
        'related_user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related order
     */
    public function relatedOrder()
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }

    /**
     * Get the related user (for admin adjustments)
     */
    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific transaction types
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        $sign = $this->amount < 0 ? '-' : '+';
        return $sign . '₦' . number_format(abs($this->amount), 2);
    }

    /**
     * Get formatted balance after
     */
    public function getFormattedBalanceAfterAttribute()
    {
        return '₦' . number_format($this->balance_after, 2);
    }

    /**
     * Get transaction color based on type
     */
    public function getColorAttribute()
    {
        return match ($this->type) {
            'funding' => 'green',
            'commission' => 'blue',
            'purchase' => 'red',
            'withdrawal' => 'orange',
            'admin_adjustment' => 'purple',
            'refund' => 'green',
            default => 'gray',
        };
    }
}
