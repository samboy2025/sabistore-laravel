<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are modified
        static::saved(function () {
            Cache::forget('app_settings');
        });

        static::deleted(function () {
            Cache::forget('app_settings');
        });
    }

    /**
     * Scope to public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope by group
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get setting value with proper type casting
     */
    public function getTypedValue()
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : 0;
            case 'json':
                return json_decode($this->value, true) ?? [];
            default:
                return $this->value;
        }
    }

    /**
     * Set setting value with proper type handling
     */
    public function setTypedValue($value)
    {
        switch ($this->type) {
            case 'boolean':
                $this->value = $value ? '1' : '0';
                break;
            case 'json':
                $this->value = is_array($value) ? json_encode($value) : $value;
                break;
            default:
                $this->value = (string) $value;
                break;
        }
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllSettings()
    {
        return Cache::remember('app_settings', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get setting by key
     */
    public static function get($key, $default = null)
    {
        $settings = static::getAllSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Set setting by key
     */
    public static function set($key, $value, $type = 'text')
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->type = $type;
        $setting->setTypedValue($value);
        $setting->save();
        
        return $setting;
    }

    /**
     * Get settings by group
     */
    public static function getGroup($group)
    {
        return static::where('group', $group)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getTypedValue()];
            });
    }
}
