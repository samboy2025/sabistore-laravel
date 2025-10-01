<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Shop
 *
 * Represents a vendor's shop on the platform.
 *
 * @package App\Models
 * @property int $id
 * @property int $vendor_id The ID of the user who owns the shop.
 * @property int|null $badge_id The ID of the badge currently awarded to the shop.
 * @property string $name The name of the shop.
 * @property string $slug A unique, URL-friendly version of the shop name.
 * @property string|null $description A description of the shop and what it sells.
 * @property string|null $whatsapp_number The shop's WhatsApp contact number.
 * @property string|null $logo_path The storage path to the shop's logo.
 * @property string|null $banner_path The storage path to the shop's banner image.
 * @property string|null $video_path The storage path or URL to the shop's promotional video.
 * @property string|null $business_category The category of the business.
 * @property bool $is_active Whether the shop is currently active and visible to the public.
 * @property bool $setup_completed Whether the vendor has completed the shop setup process.
 * @property array|null $social_links An array of social media links.
 * @property string|null $facebook_handle The shop's Facebook handle.
 * @property string|null $instagram_handle The shop's Instagram handle.
 * @property string|null $twitter_handle The shop's Twitter handle.
 * @property string|null $tiktok_handle The shop's TikTok handle.
 * @property string|null $business_address The physical address of the business.
 * @property string|null $business_location The general location (e.g., city, state).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $vendor The vendor who owns the shop.
 * @property-read Badge|null $badge The badge associated with the shop.
 * @property-read \Illuminate\Database\Eloquent\Collection|Product[] $products The products in this shop.
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $orders The orders for this shop's products.
 * @property-read \Illuminate\Database\Eloquent\Collection|ResellerLink[] $resellerLinks The reseller links for this shop's products.
 * @property-read string $subdomain_url The full subdomain URL for the shop.
 * @property-read string $whatsapp_link The full "wa.me" link for the shop's WhatsApp number.
 * @property-read string|null $facebook_url The full URL to the shop's Facebook page.
 * @property-read string|null $instagram_url The full URL to the shop's Instagram profile.
 * @property-read string|null $twitter_url The full URL to the shop's Twitter profile.
 * @property-read string|null $tiktok_url The full URL to the shop's TikTok profile.
 */
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
        'facebook_handle',
        'instagram_handle',
        'twitter_handle',
        'tiktok_handle',
        'business_address',
        'business_location',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'setup_completed' => 'boolean',
            'social_links' => 'array',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * Automatically generates a slug from the shop name when creating or updating.
     *
     * @return void
     */
    protected static function boot(): void
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
     * Get the vendor (user) who owns the shop.
     *
     * @return BelongsTo
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the badge associated with the shop.
     *
     * @return BelongsTo
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get the products listed in this shop.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all orders for products in this shop.
     *
     * @return HasManyThrough
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    /**
     * Get all reseller links for products in this shop.
     *
     * @return HasManyThrough
     */
    public function resellerLinks(): HasManyThrough
    {
        return $this->hasManyThrough(ResellerLink::class, Product::class);
    }

    /**
     * Get the full subdomain URL for the shop.
     *
     * @return string
     */
    public function getSubdomainUrlAttribute(): string
    {
        return "https://{$this->slug}." . config('app.domain');
    }

    /**
     * Get the full "wa.me" link for the shop's WhatsApp number.
     *
     * @return string
     */
    public function getWhatsappLinkAttribute(): string
    {
        return "https://wa.me/{$this->whatsapp_number}";
    }

    /**
     * Scope a query to only include active shops.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include shops that have completed setup.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('setup_completed', true);
    }

    /**
     * Get the full URL to the shop's Facebook page.
     *
     * @return string|null
     */
    public function getFacebookUrlAttribute(): ?string
    {
        return $this->facebook_handle ? "https://facebook.com/{$this->facebook_handle}" : null;
    }

    /**
     * Get the full URL to the shop's Instagram profile.
     *
     * @return string|null
     */
    public function getInstagramUrlAttribute(): ?string
    {
        return $this->instagram_handle ? "https://instagram.com/{$this->instagram_handle}" : null;
    }

    /**
     * Get the full URL to the shop's Twitter profile.
     *
     * @return string|null
     */
    public function getTwitterUrlAttribute(): ?string
    {
        return $this->twitter_handle ? "https://twitter.com/{$this->twitter_handle}" : null;
    }

    /**
     * Get the full URL to the shop's TikTok profile.
     *
     * @return string|null
     */
    public function getTiktokUrlAttribute(): ?string
    {
        return $this->tiktok_handle ? "https://tiktok.com/@{$this->tiktok_handle}" : null;
    }
}
