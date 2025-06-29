<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_number',
        'recipient_name',
        'course_title',
        'completion_date',
        'issue_date',
        'file_path',
        'is_verified',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'issue_date' => 'date',
            'is_verified' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($certificate) {
            // Generate PDF certificate after creation
            $certificate->generatePDF();
        });
    }

    /**
     * Get the user that owns the certificate
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
     * Generate PDF certificate
     */
    public function generatePDF()
    {
        $data = [
            'certificate' => $this,
            'user' => $this->user,
            'course' => $this->course,
        ];

        $pdf = Pdf::loadView('certificates.template', $data);
        
        $filename = 'certificate_' . $this->certificate_number . '.pdf';
        $path = 'certificates/' . $filename;
        
        Storage::disk('public')->put($path, $pdf->output());
        
        $this->update(['file_path' => $path]);
        
        return $path;
    }

    /**
     * Get certificate download URL
     */
    public function getDownloadUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return route('buyer.certificates.download', $this->id);
    }

    /**
     * Get certificate view URL
     */
    public function getViewUrlAttribute()
    {
        return route('buyer.certificates.show', $this->id);
    }

    /**
     * Get certificate verification URL
     */
    public function getVerificationUrlAttribute()
    {
        return route('certificates.verify', $this->certificate_number);
    }

    /**
     * Check if certificate file exists
     */
    public function fileExists()
    {
        return $this->file_path && Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeAttribute()
    {
        if (!$this->fileExists()) {
            return null;
        }

        $bytes = Storage::disk('public')->size($this->file_path);
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Scope for verified certificates
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for recent certificates
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('issue_date', '>=', now()->subDays($days));
    }
}
