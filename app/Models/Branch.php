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
} 