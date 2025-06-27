<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchandise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'model',
        'purchasable',
        'status',
        'stock',
        'badge',
        'badge_color',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchasable' => 'boolean',
        'is_active' => 'boolean',
        'stock' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByModel($query, $model)
    {
        if ($model === 'all') {
            return $query;
        }
        return $query->where('model', $model);
    }

    public function scopePurchasable($query)
    {
        return $query->where('purchasable', true);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/products/merchandise/' . $this->image);
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rs.' . number_format($this->price, 2);
    }
}
