<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'slug',
        'type',
        'content_url',
        'content',
        'duration_minutes',
        'order',
        'is_active',
        'is_preview',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_preview' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = Str::slug($lesson->title);
            }
        });

        static::updating(function ($lesson) {
            if ($lesson->isDirty('title')) {
                $lesson->slug = Str::slug($lesson->title);
            }
        });
    }

    /**
     * Get the course that owns the lesson
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the learning progress for this lesson
     */
    public function progress()
    {
        return $this->hasMany(LearningProgress::class, 'lesson_id');
    }

    /**
     * Get progress for a specific user
     */
    public function progressForUser($userId)
    {
        return $this->progress()->where('user_id', $userId)->first();
    }

    /**
     * Check if lesson is completed by user
     */
    public function isCompletedByUser($userId)
    {
        $progress = $this->progressForUser($userId);
        return $progress && $progress->is_completed;
    }

    /**
     * Scope to only active lessons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only preview lessons
     */
    public function scopePreview($query)
    {
        return $query->where('is_preview', true);
    }

    /**
     * Scope to order lessons by their order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
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

    /**
     * Get lesson URL
     */
    public function getUrlAttribute()
    {
        return route('buyer.courses.lesson', [
            'course' => $this->course->slug,
            'lesson' => $this->slug
        ]);
    }
}
