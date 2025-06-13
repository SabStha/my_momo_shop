<?php
namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory, BranchAware, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'wallet_number',
        'balance',
        'total_earned',
        'total_spent',
        'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($wallet) {
            if (empty($wallet->wallet_number)) {
                $wallet->wallet_number = static::generateWalletNumber();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function generateWalletNumber()
    {
        do {
            // Generate a 16-character wallet number with format: XXXX-XXXX-XXXX-XXXX
            $number = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (static::where('wallet_number', $number)->exists());

        return $number;
    }
} 