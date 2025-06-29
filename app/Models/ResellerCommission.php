<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'order_id',
        'product_id',
        'vendor_id',
        'reseller_link_id',
        'commission_amount',
        'commission_percentage',
        'order_total',
        'status',
        'earned_at',
        'approved_at',
        'paid_at',
        'approved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'commission_amount' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'order_total' => 'decimal:2',
            'earned_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commission) {
            if (empty($commission->earned_at)) {
                $commission->earned_at = now();
            }
        });
    }

    /**
     * Get the reseller (user) that earned the commission
     */
    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the vendor (user) that pays the commission
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the order that generated the commission
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that was sold
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the reseller link used
     */
    public function resellerLink()
    {
        return $this->belongsTo(ResellerLink::class);
    }

    /**
     * Get the admin who approved the commission
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to pending commissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to approved commissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to paid commissions
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope by reseller
     */
    public function scopeForReseller($query, $resellerId)
    {
        return $query->where('reseller_id', $resellerId);
    }

    /**
     * Scope by vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Approve commission
     */
    public function approve($approvedBy = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Mark commission as paid
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Reject commission
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason,
        ]);
    }
}
