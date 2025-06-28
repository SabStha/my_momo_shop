<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return static::where('group', $group)->where('is_active', true)->get();
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllAsArray()
    {
        return static::where('is_active', true)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Set a setting value
     */
    public static function setValue($key, $value, $type = 'text', $group = 'general', $label = null, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'label' => $label,
                'description' => $description,
                'is_active' => true
            ]
        );
    }
}
