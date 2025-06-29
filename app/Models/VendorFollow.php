<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorFollow extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'vendor_id',
        'followed_at',
    ];

    protected function casts(): array
    {
        return [
            'followed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($follow) {
            if (empty($follow->followed_at)) {
                $follow->followed_at = now();
            }
        });
    }

    /**
     * Get the buyer that follows
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the vendor being followed
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the vendor's shop
     */
    public function shop()
    {
        return $this->hasOneThrough(Shop::class, User::class, 'id', 'vendor_id', 'vendor_id', 'id');
    }

    /**
     * Scope for recent follows
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('followed_at', '>=', now()->subDays($days));
    }

    /**
     * Check if buyer follows vendor
     */
    public static function isFollowing($buyerId, $vendorId)
    {
        return static::where('buyer_id', $buyerId)
                    ->where('vendor_id', $vendorId)
                    ->exists();
    }

    /**
     * Follow a vendor
     */
    public static function follow($buyerId, $vendorId)
    {
        return static::firstOrCreate([
            'buyer_id' => $buyerId,
            'vendor_id' => $vendorId,
        ]);
    }

    /**
     * Unfollow a vendor
     */
    public static function unfollow($buyerId, $vendorId)
    {
        return static::where('buyer_id', $buyerId)
                    ->where('vendor_id', $vendorId)
                    ->delete();
    }
}
