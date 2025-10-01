<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CourseEnrollment
 *
 * Represents the enrollment of a user in a course, tracking their progress.
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id The ID of the enrolled user.
 * @property int $course_id The ID of the course.
 * @property string $status The current status of the enrollment (e.g., 'enrolled', 'in_progress', 'completed').
 * @property int $progress_percentage The user's progress in the course, as a percentage.
 * @property \Illuminate\Support\Carbon $enrolled_at The date and time the user enrolled.
 * @property \Illuminate\Support\Carbon|null $started_at The date and time the user started the course.
 * @property \Illuminate\Support\Carbon|null $completed_at The date and time the user completed the course.
 * @property int $time_spent_minutes The total time in minutes the user has spent on the course.
 * @property array|null $progress_data JSON data to store detailed progress (e.g., completed lessons).
 * @property float|null $score The final score or grade the user received.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user The user associated with this enrollment.
 * @property-read Course $course The course associated with this enrollment.
 */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * The "booted" method of the model.
     *
     * Automatically sets the enrollment date when a new record is created.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->enrolled_at)) {
                $enrollment->enrolled_at = now();
            }
        });
    }

    /**
     * Get the user that owns the enrollment.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course for this enrollment.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope a query to only include completed enrollments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include enrollments that are in progress.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Mark the enrollment as started.
     *
     * Sets the status to 'in_progress' and records the start time if not already started.
     *
     * @return void
     */
    public function markAsStarted(): void
    {
        if ($this->status === 'enrolled') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    /**
     * Mark the enrollment as completed.
     *
     * Sets the status to 'completed', records the completion time, and sets progress to 100%.
     *
     * @param float|null $score The final score, if applicable.
     * @return void
     */
    public function markAsCompleted(float $score = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
            'score' => $score,
        ]);
    }

    /**
     * Update the progress of the enrollment.
     *
     * Automatically marks the course as started or completed based on the percentage.
     *
     * @param int $percentage The new progress percentage (0-100).
     * @param array $progressData Additional data to merge into the progress log.
     * @return void
     */
    public function updateProgress(int $percentage, array $progressData = []): void
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
     * Add time spent on the course.
     *
     * @param int $minutes The number of minutes to add to the total time spent.
     * @return void
     */
    public function addTimeSpent(int $minutes): void
    {
        $this->increment('time_spent_minutes', $minutes);
    }

    /**
     * Check if the enrollment is eligible for a certificate.
     *
     * @return bool True if the course is completed with 100% progress.
     */
    public function isEligibleForCertificate(): bool
    {
        return $this->status === 'completed' && $this->progress_percentage >= 100;
    }
}
