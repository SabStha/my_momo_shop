<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'branch_id',
        'opened_by',
        'closed_by',
        'opening_cash',
        'closing_cash',
        'total_sales',
        'total_payments',
        'total_discounts',
        'total_taxes',
        'total_orders',
        'voided_orders',
        'payment_methods_summary',
        'cash_movements',
        'notes',
        'status',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'total_taxes' => 'decimal:2',
        'payment_methods_summary' => 'array',
        'cash_movements' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime'
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function canBeClosed()
    {
        // Check if there are any pending orders
        $pendingOrders = $this->orders()->where('status', 'pending')->exists();
        return !$pendingOrders;
    }

    public function calculateTotals()
    {
        $this->total_sales = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $this->total_discounts = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('discount_amount');

        $this->total_taxes = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('tax_amount');

        $this->total_orders = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->count();

        $this->voided_orders = $this->orders()
            ->where('status', 'cancelled')
            ->count();

        // Calculate payment methods summary
        $paymentSummary = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->with('payments.paymentMethod')
            ->get()
            ->pluck('payments')
            ->flatten()
            ->groupBy('payment_method.code')
            ->map(function ($payments) {
                return [
                    'count' => $payments->count(),
                    'total' => $payments->sum('amount')
                ];
            });

        $this->payment_methods_summary = $paymentSummary;
        $this->save();
    }
} 