<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResellerLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'reseller_id',
        'code',
        'commission_rate',
        'total_earned',
        'clicks_count',
        'sales_count',
        'is_active',
        'last_clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'total_earned' => 'decimal:2',
            'is_active' => 'boolean',
            'last_clicked_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($resellerLink) {
            if (empty($resellerLink->code)) {
                $resellerLink->code = strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the product being resold
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the reseller (user)
     */
    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the orders that came through this reseller link
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope to only active reseller links
     */
    public function scopeActive($query)
    {
        return $query->where('reseller_links.is_active', true);
    }

    /**
     * Get the reseller URL
     */
    public function getUrlAttribute()
    {
        return $this->product->url . '?ref=' . $this->code;
    }

    /**
     * Record a click on this reseller link
     */
    public function recordClick()
    {
        $this->increment('clicks_count');
        $this->update(['last_clicked_at' => now()]);
    }

    /**
     * Record a sale through this reseller link
     */
    public function recordSale($commissionAmount)
    {
        $this->increment('sales_count');
        $this->increment('total_earned', $commissionAmount);
    }

    /**
     * Calculate commission for a given amount
     */
    public function calculateCommission($amount)
    {
        return ($amount * $this->commission_rate) / 100;
    }
}
