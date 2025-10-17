<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cart_data',
        'last_updated',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update the cart data and timestamp
     */
    public function updateCart(array $cartData): void
    {
        $this->update([
            'cart_data' => $cartData,
            'last_updated' => now(),
        ]);
    }

    /**
     * Get cart items count
     */
    public function getItemCount(): int
    {
        if (empty($this->cart_data)) {
            return 0;
        }

        return array_sum(array_column($this->cart_data, 'quantity'));
    }

    /**
     * Get cart subtotal
     */
    public function getSubtotal(): float
    {
        if (empty($this->cart_data)) {
            return 0.0;
        }

        return array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $this->cart_data));
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->cart_data) || count($this->cart_data) === 0;
    }
}