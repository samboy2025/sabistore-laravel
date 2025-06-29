<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the buyer that placed the order
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the product that was ordered
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the shop that received the order
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the reseller link if order came from reseller
     */
    public function resellerLink()
    {
        return $this->belongsTo(ResellerLink::class);
    }

    /**
     * Get the payment for this order
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope to only pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to only confirmed orders
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to only delivered orders
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope to only paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Mark order as confirmed
     */
    public function markAsConfirmed()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped()
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered()
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
