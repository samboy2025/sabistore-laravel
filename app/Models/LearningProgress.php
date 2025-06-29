<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'is_completed',
        'watch_time_seconds',
        'completion_percentage',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($progress) {
            if (empty($progress->started_at)) {
                $progress->started_at = now();
            }
        });

        static::updated(function ($progress) {
            // Update enrollment progress when lesson progress changes
            if ($progress->isDirty('is_completed')) {
                $enrollment = CourseEnrollment::where('user_id', $progress->user_id)
                    ->where('course_id', $progress->course_id)
                    ->first();
                
                if ($enrollment) {
                    $enrollment->updateProgress();
                }
            }
        });
    }

    /**
     * Get the user that owns the progress
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lesson
     */
    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    /**
     * Mark lesson as completed
     */
    public function markCompleted()
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completion_percentage' => 100,
        ]);
    }

    /**
     * Update watch time for video lessons
     */
    public function updateWatchTime($seconds)
    {
        $this->update([
            'watch_time_seconds' => $seconds,
        ]);

        // Auto-complete if watched enough (e.g., 90% of video)
        if ($this->lesson->duration_minutes) {
            $totalSeconds = $this->lesson->duration_minutes * 60;
            $watchPercentage = ($seconds / $totalSeconds) * 100;
            
            if ($watchPercentage >= 90 && !$this->is_completed) {
                $this->markCompleted();
            }
        }
    }

    /**
     * Get formatted watch time
     */
    public function getFormattedWatchTimeAttribute()
    {
        if (!$this->watch_time_seconds) {
            return '0m';
        }

        $hours = floor($this->watch_time_seconds / 3600);
        $minutes = floor(($this->watch_time_seconds % 3600) / 60);
        $seconds = $this->watch_time_seconds % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        } elseif ($minutes > 0) {
            return $minutes . 'm ' . $seconds . 's';
        } else {
            return $seconds . 's';
        }
    }

    /**
     * Scope for completed progress
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for in-progress
     */
    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false);
    }
}
