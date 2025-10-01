<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Product
 *
 * Represents a product sold by a vendor in their shop.
 *
 * @package App\Models
 * @property int $id
 * @property int $shop_id The ID of the shop this product belongs to.
 * @property string $title The name or title of the product.
 * @property string $description A detailed description of the product.
 * @property float $price The price of the product.
 * @property string $type The type of product (e.g., 'physical', 'digital').
 * @property array|null $images An array of paths to product images.
 * @property string|null $file_path The storage path for a digital product's file.
 * @property bool $is_resellable Whether this product can be resold by other users.
 * @property float|null $resell_commission_percent The commission percentage for resellers.
 * @property bool $is_active Whether the product is currently active and visible in the shop.
 * @property string|null $tags Comma-separated tags for the product.
 * @property int|null $stock_quantity The quantity of the product in stock.
 * @property float|null $weight The weight of the product (for shipping).
 * @property array|null $dimensions The dimensions of the product (for shipping).
 * @property int $views_count The number of times the product page has been viewed.
 * @property int $orders_count The number of times the product has been ordered.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Shop $shop The shop that owns the product.
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $orders The orders for this product.
 * @property-read \Illuminate\Database\Eloquent\Collection|ResellerLink[] $resellerLinks The reseller links for this product.
 * @property-read string $whatsapp_order_link A pre-filled WhatsApp link for ordering the product.
 * @property-read string $url The public URL to the product page.
 */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
     * Get the shop that owns the product.
     *
     * @return BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the orders for the product.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reseller links for the product.
     *
     * @return HasMany
     */
    public function resellerLinks(): HasMany
    {
        return $this->hasMany(ResellerLink::class);
    }

    /**
     * Get the pre-filled WhatsApp order link for the product.
     *
     * @return string
     */
    public function getWhatsappOrderLinkAttribute(): string
    {
        $message = urlencode("I'm interested in {$this->title} - ₦" . number_format($this->price, 2));
        return "https://wa.me/{$this->shop->whatsapp_number}?text={$message}";
    }

    /**
     * Get the public URL for the product page.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return "https://{$this->shop->slug}." . config('app.domain') . "/products/{$this->id}";
    }

    /**
     * Scope a query to only include active products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('products.is_active', true);
    }

    /**
     * Scope a query to only include physical products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('type', 'physical');
    }

    /**
     * Scope a query to only include digital products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDigital(Builder $query): Builder
    {
        return $query->where('type', 'digital');
    }

    /**
     * Scope a query to only include resellable products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeResellable(Builder $query): Builder
    {
        return $query->where('is_resellable', true);
    }

    /**
     * Increment the views count for the product.
     *
     * @return void
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment the orders count for the product.
     *
     * @return void
     */
    public function incrementOrders(): void
    {
        $this->increment('orders_count');
    }
}
