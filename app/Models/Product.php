<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, BranchAware;

    protected $fillable = [
        'name',
        'code',
        'description',
        'ingredients',
        'allergens',
        'calories',
        'preparation_time',
        'spice_level',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'nutritional_info',
        'serving_size',
        'price',
        'cost_price',
        'stock',
        'image',
        'unit',
        'category',
        'tag',
        'is_featured',
        'is_active',
        'is_menu_highlight',
        'points',
        'tax_rate',
        'discount_rate',
        'attributes',
        'notes',
        'branch_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'points' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_menu_highlight' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_gluten_free' => 'boolean',
        'attributes' => 'array'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
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

    public function getProfitAttribute()
    {
        return $this->price - $this->cost_price;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->price == 0) return 0;
        return ($this->getProfitAttribute() / $this->price) * 100;
    }
} 