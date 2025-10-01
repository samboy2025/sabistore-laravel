<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Order
 *
 * Represents a customer order for a product.
 *
 * @package App\Models
 * @property int $id
 * @property string $order_number A unique identifier for the order.
 * @property int $buyer_id The ID of the user who placed the order.
 * @property int $product_id The ID of the product that was ordered.
 * @property int $shop_id The ID of the shop that fulfilled the order.
 * @property int|null $reseller_link_id The ID of the reseller link used for the purchase, if any.
 * @property int $quantity The number of units of the product ordered.
 * @property float $unit_price The price of a single unit of the product at the time of order.
 * @property float $total_price The total price for the order (quantity * unit_price).
 * @property float|null $commission_amount The commission amount for the reseller, if applicable.
 * @property string $status The current status of the order (e.g., 'pending', 'confirmed', 'shipped', 'delivered').
 * @property string $payment_status The current payment status (e.g., 'pending', 'paid', 'failed').
 * @property string|null $order_type The type of order (e.g., 'physical', 'digital').
 * @property string|null $notes Additional notes from the buyer.
 * @property array|null $shipping_address The shipping address for physical products.
 * @property string|null $phone The contact phone number for the order.
 * @property \Illuminate\Support\Carbon|null $confirmed_at Timestamp when the order was confirmed.
 * @property \Illuminate\Support\Carbon|null $shipped_at Timestamp when the order was shipped.
 * @property \Illuminate\Support\Carbon|null $delivered_at Timestamp when the order was delivered.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $buyer The user who placed the order.
 * @property-read Product $product The product that was ordered.
 * @property-read Shop $shop The shop that received the order.
 * @property-read ResellerLink|null $resellerLink The reseller link associated with the order.
 * @property-read Payment|null $payment The payment record for this order.
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'buyer_id',
        'product_id',
        'shop_id',
        'reseller_link_id',
        'quantity',
        'unit_price',
        'total_price',
        'commission_amount',
        'status',
        'payment_status',
        'order_type',
        'notes',
        'shipping_address',
        'phone',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'shipping_address' => 'array',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * Automatically generates a unique order number when a new order is created.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the buyer (user) who placed the order.
     *
     * @return BelongsTo
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the product that was ordered.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the shop that received the order.
     *
     * @return BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the reseller link if the order came from a reseller.
     *
     * @return BelongsTo
     */
    public function resellerLink(): BelongsTo
    {
        return $this->belongsTo(ResellerLink::class);
    }

    /**
     * Get the payment associated with this order.
     *
     * @return HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope a query to only include pending orders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed orders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include delivered orders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope a query to only include paid orders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Mark the order as confirmed.
     *
     * @return void
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark the order as shipped.
     *
     * @return void
     */
    public function markAsShipped(): void
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    /**
     * Mark the order as delivered.
     *
     * This also triggers the processing of any applicable reseller commissions.
     *
     * @return void
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Process reseller commission if applicable
        if ($this->reseller_id && !$this->commission_paid && $this->reseller_commission > 0) {
            $commissionService = app(\App\Services\CommissionService::class);
            $commissionService->processResellerCommission($this);
        }
    }
}
