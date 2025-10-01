<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Payment
 *
 * Represents a payment transaction in the system.
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id The ID of the user who made the payment.
 * @property int|null $order_id The ID of the order associated with the payment, if applicable.
 * @property string $reference A unique reference for the transaction.
 * @property string $type The type of payment (e.g., 'membership', 'product_purchase').
 * @property float $amount The amount of the payment.
 * @property string $currency The currency of the payment (e.g., 'NGN').
 * @property string $status The status of the payment (e.g., 'pending', 'success', 'failed').
 * @property string $gateway The payment gateway used (e.g., 'paystack').
 * @property array|null $gateway_response The raw response from the payment gateway.
 * @property \Illuminate\Support\Carbon|null $paid_at Timestamp when the payment was successfully completed.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user The user who made the payment.
 * @property-read Order|null $order The order associated with the payment.
 */
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
