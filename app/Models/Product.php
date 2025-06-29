<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'title',
        'description',
        'price',
        'type',
        'images',
        'file_path',
        'is_resellable',
        'resell_commission_percent',
        'is_active',
        'tags',
        'stock_quantity',
        'weight',
        'dimensions',
        'views_count',
        'orders_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weight' => 'decimal:2',
            'images' => 'array',
            'dimensions' => 'array',
            'is_resellable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the shop that owns the product
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the orders for the product
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reseller links for the product
     */
    public function resellerLinks()
    {
        return $this->hasMany(ResellerLink::class);
    }

    /**
     * Get the WhatsApp order link
     */
    public function getWhatsappOrderLinkAttribute()
    {
        $message = urlencode("I'm interested in {$this->title} - ₦" . number_format($this->price, 2));
        return "https://wa.me/{$this->shop->whatsapp_number}?text={$message}";
    }

    /**
     * Get the product URL
     */
    public function getUrlAttribute()
    {
        return "https://{$this->shop->slug}." . config('app.domain') . "/products/{$this->id}";
    }

    /**
     * Scope to only active products
     */
    public function scopeActive($query)
    {
        return $query->where('products.is_active', true);
    }

    /**
     * Scope to only physical products
     */
    public function scopePhysical($query)
    {
        return $query->where('type', 'physical');
    }

    /**
     * Scope to only digital products
     */
    public function scopeDigital($query)
    {
        return $query->where('type', 'digital');
    }

    /**
     * Scope to only resellable products
     */
    public function scopeResellable($query)
    {
        return $query->where('is_resellable', true);
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment orders count
     */
    public function incrementOrders()
    {
        $this->increment('orders_count');
    }
}
