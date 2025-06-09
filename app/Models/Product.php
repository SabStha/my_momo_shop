<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BranchAware;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'is_featured',
        'tag',
        'is_active',
        'cost_price',
        'is_menu_highlight',
        'category_id',
        'branch_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_menu_highlight' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

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