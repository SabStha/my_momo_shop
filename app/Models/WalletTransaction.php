<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;
    
    protected $table = 'credits_transactions';
    
    protected $fillable = [
        'credits_account_id',
        'user_id',
        'branch_id',
        'credits_amount',
        'type',
        'description',
        'status',
        'performed_by',
        'performed_by_branch_id',
        'order_id',
        'reference_number',
        'credits_balance_before',
        'credits_balance_after'
    ];

    protected $casts = [
        'credits_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'credits_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Alias for backward compatibility
    public function getAmountAttribute()
    {
        return $this->credits_amount;
    }

    public function getBalanceBeforeAttribute()
    {
        return $this->credits_balance_before;
    }

    public function getBalanceAfterAttribute()
    {
        return $this->credits_balance_after;
    }
} 