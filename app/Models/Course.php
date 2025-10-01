<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Course
 *
 * Represents a course in the learning center.
 *
 * @package App\Models
 * @property int $id
 * @property string $title The title of the course.
 * @property string $description A detailed description of the course content.
 * @property string $slug A URL-friendly version of the course title.
 * @property string $type The type of course content (e.g., 'video', 'pdf').
 * @property string $content_url The URL to the course content (e.g., video link, file path).
 * @property string|null $thumbnail_path The path to the course's thumbnail image.
 * @property int|null $duration_minutes The duration of the course in minutes.
 * @property string|null $category The category the course belongs to.
 * @property int $order The display order of the course.
 * @property bool $is_active Whether the course is currently available to users.
 * @property bool $is_featured Whether the course is featured on the homepage or learning center.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseEnrollment[] $enrollments
 * @property-read \Illuminate\Database\Eloquent\Collection|Certificate[] $certificates
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $enrolledUsers
 * @property-read string $url The public URL to the course page.
 * @property-read string|null $formatted_duration The duration of the course formatted as "Xh Ym" or "Ym".
 */
class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'type',
        'content_url',
        'thumbnail_path',
        'duration_minutes',
        'category',
        'order',
        'is_active',
        'is_featured',
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
            'is_featured' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * Automatically generates a slug from the title when creating or updating a course.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });

        static::updating(function ($course) {
            if ($course->isDirty('title')) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    /**
     * Scope a query to only include active courses.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured courses.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the enrollments for the course.
     *
     * @return HasMany
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get the certificates that have been awarded for this course.
     *
     * @return HasMany
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Get the users who are enrolled in this course.
     *
     * @return BelongsToMany
     */
    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot(['status', 'progress_percentage', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Scope a query to order courses by their specified order.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to filter courses by a specific category.
     *
     * @param Builder $query
     * @param string $category The category to filter by.
     * @return Builder
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Get the public URL for the course.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return route('courses.show', $this->slug);
    }

    /**
     * Get the course duration in a human-readable format (e.g., "1h 30m").
     *
     * @return string|null
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_minutes) {
            return null;
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }
}
