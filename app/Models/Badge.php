<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
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
     * Get the shops that have this badge
     */
    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * Scope to only active badges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order badges by their order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if a shop qualifies for this badge
     */
    public function shopQualifies(Shop $shop): bool
    {
        return $shop->products()->count() >= $this->min_products
            && $shop->orders()->where('status', 'delivered')->count() >= $this->min_orders;
            // && $shop->reviews()->where('rating', '>=', 4)->count() >= $this->min_reviews; // If reviews are implemented
    }
}
