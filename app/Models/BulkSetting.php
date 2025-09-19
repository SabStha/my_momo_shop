<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue($key, $value, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Get bulk discount percentage
     */
    public static function getBulkDiscountPercentage()
    {
        return (float) static::getValue('bulk_discount_percentage', 15);
    }

    /**
     * Set bulk discount percentage
     */
    public static function setBulkDiscountPercentage($percentage)
    {
        return static::setValue('bulk_discount_percentage', $percentage, 'Default discount percentage for bulk orders');
    }
}
