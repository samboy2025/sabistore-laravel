<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Badge
 *
 * Represents a vendor badge, which is awarded based on certain criteria like the number of products and orders.
 *
 * @package App\Models
 * @property int $id
 * @property string $name The name of the badge (e.g., "Bronze Vendor").
 * @property string $slug A URL-friendly version of the badge name.
 * @property string|null $description A description of the badge and its benefits.
 * @property string $color The color associated with the badge (e.g., a hex code).
 * @property int $min_products The minimum number of products required for this badge.
 * @property int $min_orders The minimum number of completed orders required for this badge.
 * @property int $min_reviews The minimum number of positive reviews required (not currently implemented).
 * @property int $order The display order of the badge.
 * @property bool $is_active Whether the badge is currently active and can be awarded.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'min_products',
        'min_orders',
        'min_reviews',
        'order',
        'is_active',
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
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * Automatically generates a slug from the name when creating or updating a badge.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($badge) {
            if (empty($badge->slug)) {
                $badge->slug = Str::slug($badge->name);
            }
        });

        static::updating(function ($badge) {
            if ($badge->isDirty('name')) {
                $badge->slug = Str::slug($badge->name);
            }
        });
    }

    /**
     * Get the shops that have been awarded this badge.
     *
     * @return HasMany
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * Scope a query to only include active badges.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order badges by their specified order.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Check if a given shop qualifies for this badge based on its stats.
     *
     * @param Shop $shop The shop to check.
     * @return bool True if the shop qualifies, false otherwise.
     */
    public function shopQualifies(Shop $shop): bool
    {
        return $shop->products()->count() >= $this->min_products
            && $shop->orders()->where('status', 'delivered')->count() >= $this->min_orders;
            // && $shop->reviews()->where('rating', '>=', 4)->count() >= $this->min_reviews; // If reviews are implemented
    }
}
