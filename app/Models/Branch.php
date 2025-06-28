<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_person',
        'email',
        'phone',
        'latitude',
        'longitude',
        'delivery_fee',
        'delivery_radius_km',
        'is_active',
        'is_main',
        'access_password',
        'requires_password'
    ];

    protected $hidden = [
        'access_password'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
        'requires_password' => 'boolean'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function inventoryCategories()
    {
        return $this->hasMany(InventoryCategory::class);
    }

    public function inventorySuppliers()
    {
        return $this->hasMany(InventorySupplier::class);
    }

    public function inventoryOrders()
    {
        return $this->hasMany(InventoryOrder::class);
    }

    public function inventoryOrderItems()
    {
        return $this->hasMany(InventoryOrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function getTotalSalesAttribute()
    {
        return $this->orders()->where('status', 'completed')->sum('total_amount');
    }

    public function getActiveEmployeesCountAttribute()
    {
        return $this->employees()->where('is_active', true)->count();
    }

    public function getAvailableTablesCountAttribute()
    {
        return $this->tables()->where('is_available', true)->count();
    }

    public function getTotalOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    public function setAccessPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['access_password'] = Hash::make($value);
        }
    }

    public function verifyPassword($password)
    {
        if (!$this->requires_password) {
            return true;
        }

        return Hash::check($password, $this->access_password);
    }

    /**
     * Calculate distance between this branch and given coordinates
     * @param float $lat
     * @param float $lng
     * @return float Distance in kilometers
     */
    public function distanceTo($lat, $lng)
    {
        if (!$this->latitude || !$this->longitude) {
            return null; // Branch has no location data
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latDelta = deg2rad($lat - $this->latitude);
        $lngDelta = deg2rad($lng - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if given coordinates are within delivery radius
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    public function isWithinDeliveryRadius($lat, $lng)
    {
        $distance = $this->distanceTo($lat, $lng);
        if ($distance === null) {
            return false; // No location data
        }

        $radius = $this->delivery_radius_km ?? 5; // Default 5km
        return $distance <= $radius;
    }

    /**
     * Get delivery fee for given coordinates
     * @param float $lat
     * @param float $lng
     * @return float
     */
    public function getDeliveryFee($lat, $lng)
    {
        if (!$this->isWithinDeliveryRadius($lat, $lng)) {
            return null; // Outside delivery area
        }

        return $this->delivery_fee ?? 0;
    }
} 