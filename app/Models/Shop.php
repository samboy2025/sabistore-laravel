<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'badge_id',
        'name',
        'slug',
        'description',
        'whatsapp_number',
        'logo_path',
        'banner_path',
        'video_path',
        'business_category',
        'is_active',
        'setup_completed',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'setup_completed' => 'boolean',
            'social_links' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shop) {
            if (empty($shop->slug)) {
                $shop->slug = Str::slug($shop->name);
            }
        });

        static::updating(function ($shop) {
            if ($shop->isDirty('name')) {
                $shop->slug = Str::slug($shop->name);
            }
        });
    }

    /**
     * Get the vendor that owns the shop
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the badge associated with the shop
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get the products for the shop
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the orders for the shop
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    /**
     * Get the reseller links for the shop
     */
    public function resellerLinks()
    {
        return $this->hasManyThrough(ResellerLink::class, Product::class);
    }

    /**
     * Get the subdomain URL
     */
    public function getSubdomainUrlAttribute()
    {
        return "https://{$this->slug}." . config('app.domain');
    }

    /**
     * Get the WhatsApp link
     */
    public function getWhatsappLinkAttribute()
    {
        return "https://wa.me/{$this->whatsapp_number}";
    }

    /**
     * Scope to only active shops
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only completed shops
     */
    public function scopeCompleted($query)
    {
        return $query->where('setup_completed', true);
    }
}
