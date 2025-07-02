<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditsTransaction extends Model
{
    use HasFactory;
    
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
        'credits_amount' => 'integer',
        'credits_balance_before' => 'integer',
        'credits_balance_after' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function creditsAccount()
    {
        return $this->belongsTo(CreditsAccount::class);
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

    /**
     * Get the display amount (e.g., "5 Credits")
     */
    public function getDisplayAmountAttribute()
    {
        $prefix = $this->type === 'credit' ? '+' : '-';
        return $prefix . $this->credits_amount . ' Credits';
    }

    /**
     * Get the transaction type display name
     */
    public function getTypeDisplayAttribute()
    {
        return ucfirst($this->type);
    }
}
