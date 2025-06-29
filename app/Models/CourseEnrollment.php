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
        'amount_paid',
        'payment_method',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'certificate_issued',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'certificate_issued' => 'boolean',
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
     * Get the user that enrolled
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that was enrolled in
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the learning progress for this enrollment
     */
    public function progress()
    {
        return $this->hasMany(LearningProgress::class, 'user_id', 'user_id')
                    ->where('course_id', $this->course_id);
    }

    /**
     * Get the certificate for this enrollment
     */
    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'user_id', 'user_id')
                    ->where('course_id', $this->course_id);
    }

    /**
     * Check if enrollment is completed
     */
    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    /**
     * Mark enrollment as completed
     */
    public function markCompleted()
    {
        $this->update([
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        // Auto-generate certificate if not already issued
        if (!$this->certificate_issued) {
            $this->generateCertificate();
        }
    }

    /**
     * Generate certificate for this enrollment
     */
    public function generateCertificate()
    {
        $certificate = Certificate::create([
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'certificate_number' => 'CERT-' . strtoupper(uniqid()),
            'recipient_name' => $this->user->name,
            'course_title' => $this->course->title,
            'completion_date' => $this->completed_at ?? now(),
            'issue_date' => now(),
        ]);

        $this->update(['certificate_issued' => true]);

        return $certificate;
    }

    /**
     * Update progress percentage based on completed lessons
     */
    public function updateProgress()
    {
        $totalLessons = $this->course->lessons()->active()->count();
        
        if ($totalLessons === 0) {
            $this->update(['progress_percentage' => 100]);
            return;
        }

        $completedLessons = $this->progress()->where('is_completed', true)->count();
        $progressPercentage = round(($completedLessons / $totalLessons) * 100);

        $this->update(['progress_percentage' => $progressPercentage]);

        // Mark as completed if 100%
        if ($progressPercentage >= 100 && !$this->isCompleted()) {
            $this->markCompleted();
        }
    }

    /**
     * Scope for completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope for in-progress enrollments
     */
    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at');
    }
}
