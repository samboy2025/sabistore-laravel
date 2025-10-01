<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ResellerCommission
 *
 * Represents a commission earned by a reseller for a successful sale.
 *
 * @package App\Models
 * @property int $id
 * @property int $reseller_id The ID of the user who earned the commission.
 * @property int $order_id The ID of the order that generated the commission.
 * @property int $product_id The ID of the product that was sold.
 * @property int $vendor_id The ID of the vendor whose product was sold.
 * @property int $reseller_link_id The ID of the reseller link used for the sale.
 * @property float $commission_amount The total amount of the commission earned.
 * @property float $commission_percentage The percentage of the sale that was earned as commission.
 * @property float $order_total The total amount of the order.
 * @property string $status The status of the commission (e.g., 'pending', 'approved', 'paid', 'rejected').
 * @property \Illuminate\Support\Carbon $earned_at Timestamp when the commission was earned (usually on order completion).
 * @property \Illuminate\Support\Carbon|null $approved_at Timestamp when the commission was approved for payout.
 * @property \Illuminate\Support\Carbon|null $paid_at Timestamp when the commission was paid out to the reseller.
 * @property int|null $approved_by The ID of the admin user who approved the commission.
 * @property string|null $notes Additional notes about the commission (e.g., reason for rejection).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $reseller The user who earned the commission.
 * @property-read User $vendor The vendor of the product.
 * @property-read Order $order The order that generated the commission.
 * @property-read Product $product The product that was sold.
 * @property-read ResellerLink $resellerLink The reseller link used for the sale.
 * @property-read User|null $approvedBy The admin who approved the commission.
 */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * The "booted" method of the model.
     *
     * Automatically sets the earned_at timestamp when a new commission is created.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($commission) {
            if (empty($commission->earned_at)) {
                $commission->earned_at = now();
            }
        });
    }

    /**
     * Get the reseller (user) that earned the commission.
     *
     * @return BelongsTo
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the vendor (user) whose product was sold.
     *
     * @return BelongsTo
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the order that generated the commission.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that was sold.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the reseller link used for the sale.
     *
     * @return BelongsTo
     */
    public function resellerLink(): BelongsTo
    {
        return $this->belongsTo(ResellerLink::class);
    }

    /**
     * Get the admin user who approved the commission.
     *
     * @return BelongsTo
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending commissions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved commissions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include paid commissions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to filter commissions by a specific reseller.
     *
     * @param Builder $query
     * @param int $resellerId The ID of the reseller.
     * @return Builder
     */
    public function scopeForReseller(Builder $query, int $resellerId): Builder
    {
        return $query->where('reseller_id', $resellerId);
    }

    /**
     * Scope a query to filter commissions by a specific vendor.
     *
     * @param Builder $query
     * @param int $vendorId The ID of the vendor.
     * @return Builder
     */
    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Approve the commission for payout.
     *
     * @param int|null $approvedBy The ID of the admin user approving the commission.
     * @return void
     */
    public function approve(int $approvedBy = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Mark the commission as paid.
     *
     * @return void
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Reject the commission.
     *
     * @param string|null $reason The reason for rejecting the commission.
     * @return void
     */
    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason,
        ]);
    }
}
