<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserLogin
 *
 * Represents a record of a user's login activity, including IP, location, and device information.
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id The ID of the user who logged in.
 * @property string|null $ip_address The IP address used for the login.
 * @property string|null $user_agent The user agent string of the client.
 * @property string|null $device_type The type of device (e.g., 'desktop', 'mobile').
 * @property string|null $browser The browser used for the login.
 * @property string|null $platform The operating system platform.
 * @property string|null $country The country of the login origin.
 * @property string|null $city The city of the login origin.
 * @property string|null $region The region or state of the login origin.
 * @property float|null $latitude The latitude of the login origin.
 * @property float|null $longitude The longitude of the login origin.
 * @property string|null $timezone The timezone of the login origin.
 * @property bool $is_mobile Whether the login was from a mobile device.
 * @property bool $is_suspicious Whether the login has been flagged as suspicious.
 * @property \Illuminate\Support\Carbon $login_at Timestamp when the login occurred.
 * @property \Illuminate\Support\Carbon|null $logout_at Timestamp when the logout occurred.
 * @property int|null $session_duration The duration of the session in minutes.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user The user who owns the login record.
 * @property-read string $location A formatted string of the login location (City, Region, Country).
 * @property-read string $device_info A formatted string of the device information (Browser on Platform).
 */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * The "booted" method of the model.
     *
     * Automatically sets the login_at timestamp when a new record is created.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($login) {
            if (empty($login->login_at)) {
                $login->login_at = now();
            }
        });
    }

    /**
     * Get the user that owns the login record.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include suspicious logins.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSuspicious(Builder $query): Builder
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope a query to only include logins from mobile devices.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeMobile(Builder $query): Builder
    {
        return $query->where('is_mobile', true);
    }

    /**
     * Scope a query to include logins within a recent number of days.
     *
     * @param Builder $query
     * @param int $days The number of days to look back.
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to filter logins by a specific country.
     *
     * @param Builder $query
     * @param string $country The country to filter by.
     * @return Builder
     */
    public function scopeFromCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    /**
     * Mark this login record as suspicious.
     *
     * @param string|null $reason Optional reason for marking as suspicious (not currently stored).
     * @return void
     */
    public function markAsSuspicious(string $reason = null): void
    {
        $this->update([
            'is_suspicious' => true,
        ]);
    }

    /**
     * Record the logout time and calculate the session duration.
     *
     * @return void
     */
    public function recordLogout(): void
    {
        $logoutTime = now();
        $sessionDuration = $this->login_at->diffInMinutes($logoutTime);
        
        $this->update([
            'logout_at' => $logoutTime,
            'session_duration' => $sessionDuration,
        ]);
    }

    /**
     * Get a formatted string of the login location.
     *
     * @return string
     */
    public function getLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return implode(', ', $parts) ?: 'Unknown';
    }

    /**
     * Get a formatted string of the device information.
     *
     * @return string
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([$this->browser, $this->platform]);
        return implode(' on ', $parts) ?: 'Unknown Device';
    }

    /**
     * Check if this login is from a new location for the user.
     *
     * @return bool
     */
    public function isFromNewLocation(): bool
    {
        return !static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('country', $this->country)
            ->exists();
    }

    /**
     * Check if this login is from a new device (based on user agent).
     *
     * @return bool
     */
    public function isFromNewDevice(): bool
    {
        return !static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('user_agent', $this->user_agent)
            ->exists();
    }
}
