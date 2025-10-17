<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'transaction_type',
        'category',
        'description',
        'payment_method',
        'amount',
        'paid_by',
        'received_from',
        'reference_number',
        'notes',
        'status', // 'pending', 'approved', 'rejected'
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime'
    ];

    // Constants for transaction types
    const TRANSACTION_TYPE_EXPENSE = 'Expense';
    const TRANSACTION_TYPE_INCOME = 'Income';

    // Constants for categories
    const CATEGORY_POS_TECH = 'POS / tech';
    const CATEGORY_MARKETING = 'Marketing';
    const CATEGORY_EQUIPMENT = 'Equipment';
    const CATEGORY_STAFF = 'Staff';
    const CATEGORY_RENT = 'Rent';
    const CATEGORY_UTILITIES = 'Utilities';
    const CATEGORY_LEGAL = 'Legal';
    const CATEGORY_OPERATIONS = 'Operations';
    const CATEGORY_OTHER = 'Other';

    // Constants for payment methods
    const PAYMENT_METHOD_CREDIT_CARD = 'Credit Card';
    const PAYMENT_METHOD_E_BANKING = 'e-Banking';
    const PAYMENT_METHOD_CASH = 'Cash';
    const PAYMENT_METHOD_CHECK = 'Check';
    const PAYMENT_METHOD_TRANSFER = 'Transfer';

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPaymentMethod($query, $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByPaidBy($query, $paidBy)
    {
        return $query->where('paid_by', $paidBy);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Static methods for getting options
    public static function getCategories()
    {
        return [
            self::CATEGORY_POS_TECH,
            self::CATEGORY_MARKETING,
            self::CATEGORY_EQUIPMENT,
            self::CATEGORY_STAFF,
            self::CATEGORY_RENT,
            self::CATEGORY_UTILITIES,
            self::CATEGORY_LEGAL,
            self::CATEGORY_OPERATIONS,
            self::CATEGORY_OTHER
        ];
    }

    public static function getPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_CREDIT_CARD,
            self::PAYMENT_METHOD_E_BANKING,
            self::PAYMENT_METHOD_CASH,
            self::PAYMENT_METHOD_CHECK,
            self::PAYMENT_METHOD_TRANSFER
        ];
    }

    public static function getTransactionTypes()
    {
        return [
            self::TRANSACTION_TYPE_EXPENSE,
            self::TRANSACTION_TYPE_INCOME
        ];
    }
}
