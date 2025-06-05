<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
        'is_featured',
        'tag',
        'is_active',
        'cost_price',
        'is_menu_highlight'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_menu_highlight' => 'boolean'
    ];

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating');
    }

    public function canBeRatedBy($user)
    {
        if (!$user) return false;
        // Has completed order for this product and hasn't rated yet
        $hasCompletedOrder = $user->orders()
            ->where('status', 'completed')
            ->whereHas('items', function($q) {
                $q->where('product_id', $this->id);
            })->exists();

        $alreadyRated = $this->ratings()->where('user_id', $user->id)->exists();

        return $hasCompletedOrder && !$alreadyRated;
    }
} 