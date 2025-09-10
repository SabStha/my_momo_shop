<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'emoji',
        'description',
        'type',
        'package_key',
        'items',
        'total_price',
        'image',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'items' => 'array',
        'total_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
