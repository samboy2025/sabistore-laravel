<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'timezone',
        'is_mobile',
        'is_suspicious',
        'login_at',
        'logout_at',
        'session_duration',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_mobile' => 'boolean',
            'is_suspicious' => 'boolean',
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($login) {
            if (empty($login->login_at)) {
                $login->login_at = now();
            }
        });
    }

    /**
     * Get the user that owns the login record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to suspicious logins
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope to mobile logins
     */
    public function scopeMobile($query)
    {
        return $query->where('is_mobile', true);
    }

    /**
     * Scope to recent logins
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }

    /**
     * Scope by country
     */
    public function scopeFromCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Mark login as suspicious
     */
    public function markAsSuspicious($reason = null)
    {
        $this->update([
            'is_suspicious' => true,
        ]);
    }

    /**
     * Record logout
     */
    public function recordLogout()
    {
        $logoutTime = now();
        $sessionDuration = $this->login_at->diffInMinutes($logoutTime);
        
        $this->update([
            'logout_at' => $logoutTime,
            'session_duration' => $sessionDuration,
        ]);
    }

    /**
     * Get formatted location
     */
    public function getLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return implode(', ', $parts) ?: 'Unknown';
    }

    /**
     * Get device info
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([$this->browser, $this->platform]);
        return implode(' on ', $parts) ?: 'Unknown Device';
    }

    /**
     * Check if login is from new location
     */
    public function isFromNewLocation(): bool
    {
        return !static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('country', $this->country)
            ->exists();
    }

    /**
     * Check if login is from new device
     */
    public function isFromNewDevice(): bool
    {
        return !static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('user_agent', $this->user_agent)
            ->exists();
    }
}
