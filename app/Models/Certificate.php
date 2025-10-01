<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Certificate
 *
 * Represents a certificate awarded to a user for completing a course.
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id The ID of the user who received the certificate.
 * @property int $course_id The ID of the course for which the certificate was awarded.
 * @property string $certificate_number A unique number identifying the certificate.
 * @property string|null $certificate_path The storage path to the generated certificate file (e.g., PDF).
 * @property \Illuminate\Support\Carbon $issued_at The date and time the certificate was issued.
 * @property \Illuminate\Support\Carbon|null $expires_at The date and time the certificate expires.
 * @property bool $is_active Whether the certificate is currently valid (not revoked).
 * @property array|null $template_data Additional data for the certificate template (e.g., custom text).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user The user who owns the certificate.
 * @property-read Course $course The course associated with the certificate.
 * @property-read string $download_url The public URL to download the certificate file.
 */
class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_number',
        'certificate_path',
        'issued_at',
        'expires_at',
        'is_active',
        'template_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'template_data' => 'array',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * Automatically generates a certificate number and sets the issue date when creating a certificate.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = 'CERT-' . strtoupper(Str::random(8)) . '-' . now()->format('Y');
            }
            if (empty($certificate->issued_at)) {
                $certificate->issued_at = now();
            }
        });
    }

    /**
     * Get the user that owns the certificate.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course for this certificate.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope a query to only include active certificates.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include non-expired certificates.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if the certificate is expired.
     *
     * @return bool True if the certificate has an expiration date and it is in the past.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the public URL for downloading the certificate file.
     *
     * @return string The full URL to the certificate file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return $this->certificate_path ? asset('storage/' . $this->certificate_path) : '';
    }
}
