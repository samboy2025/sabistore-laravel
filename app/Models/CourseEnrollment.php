<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'progress_percentage',
        'enrolled_at',
        'started_at',
        'completed_at',
        'time_spent_minutes',
        'progress_data',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_data' => 'array',
            'score' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->enrolled_at)) {
                $enrollment->enrolled_at = now();
            }
        });
    }

    /**
     * Get the user that owns the enrollment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course for this enrollment
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope to completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to in-progress enrollments
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Mark enrollment as started
     */
    public function markAsStarted()
    {
        if ($this->status === 'enrolled') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    /**
     * Mark enrollment as completed
     */
    public function markAsCompleted($score = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
            'score' => $score,
        ]);
    }

    /**
     * Update progress
     */
    public function updateProgress(int $percentage, array $progressData = [])
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
            'progress_data' => array_merge($this->progress_data ?? [], $progressData),
        ]);

        if ($percentage >= 100) {
            $this->markAsCompleted();
        } elseif ($this->status === 'enrolled') {
            $this->markAsStarted();
        }
    }

    /**
     * Add time spent
     */
    public function addTimeSpent(int $minutes)
    {
        $this->increment('time_spent_minutes', $minutes);
    }

    /**
     * Check if enrollment is eligible for certificate
     */
    public function isEligibleForCertificate(): bool
    {
        return $this->status === 'completed' && $this->progress_percentage >= 100;
    }
}
