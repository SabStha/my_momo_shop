<?php
namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory, BranchAware;
    
    protected $fillable = [
        'wallet_id',
        'user_id',
        'branch_id',
        'amount',
        'type',
        'description',
        'performed_by', // ID of admin who performed the transaction
        'performed_by_branch_id', // Branch ID of the admin who performed the transaction
        'status',
        'reference_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performedByBranch()
    {
        return $this->belongsTo(Branch::class, 'performed_by_branch_id');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
} 