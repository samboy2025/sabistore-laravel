<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class Setting
 *
 * Represents a key-value setting in the application's database.
 * Provides a convenient way to manage application-wide settings with caching.
 *
 * @package App\Models
 * @property int $id
 * @property string $key The unique key for the setting.
 * @property string|null $value The value of the setting.
 * @property string $type The data type of the setting (e.g., 'text', 'boolean', 'number', 'json').
 * @property string|null $group A group name to categorize the setting.
 * @property string|null $label A human-readable label for the setting.
 * @property string|null $description A description of what the setting does.
 * @property bool $is_public Whether the setting can be exposed to the frontend.
 * @property int $order The display order for the setting within a group.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * Clears the settings cache whenever a setting is saved or deleted.
     *
     * @return void
     */
    protected static function boot(): void
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
     * Scope a query to only include public settings.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to filter settings by a specific group.
     *
     * @param Builder $query
     * @param string $group The name of the group.
     * @return Builder
     */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Get the setting's value, cast to its proper data type.
     *
     * @return mixed
     */
    public function getTypedValue(): mixed
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
     * Set the setting's value, handling type conversion before saving.
     *
     * @param mixed $value The value to set.
     * @return void
     */
    public function setTypedValue(mixed $value): void
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
     * Get all settings as a cached key-value array.
     *
     * @return array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('app_settings', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a specific setting by its key.
     *
     * @param string $key The key of the setting to retrieve.
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Create or update a setting.
     *
     * @param string $key The key of the setting.
     * @param mixed $value The value to set.
     * @param string $type The data type of the setting.
     * @return Setting The created or updated setting model.
     */
    public static function set(string $key, mixed $value, string $type = 'text'): Setting
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->type = $type;
        $setting->setTypedValue($value);
        $setting->save();
        
        return $setting;
    }

    /**
     * Get all settings for a specific group as a key-value collection.
     *
     * @param string $group The name of the group.
     * @return Collection
     */
    public static function getGroup(string $group): Collection
    {
        return static::where('group', $group)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getTypedValue()];
            });
    }
}
