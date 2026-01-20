<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("system_setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        $setting = static::firstOrCreate(['key' => $key]);

        $setting->update([
            'value' => is_array($value) ? json_encode($value) : (string) $value,
            'type' => $type,
        ]);

        Cache::forget("system_setting.{$key}");
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'decimal', 'float' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get all public settings (for API)
     */
    public static function getPublicSettings(): array
    {
        return Cache::remember('system_settings.public', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => static::castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * Clear all setting caches
     */
    public static function clearCache(): void
    {
        Cache::forget('system_settings.public');
        static::all()->each(function ($setting) {
            Cache::forget("system_setting.{$setting->key}");
        });
    }

    /**
     * Boot method to clear cache on update
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("system_setting.{$setting->key}");
            Cache::forget('system_settings.public');
        });

        static::deleted(function ($setting) {
            Cache::forget("system_setting.{$setting->key}");
            Cache::forget('system_settings.public');
        });
    }
}
