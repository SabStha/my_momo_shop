<?php
namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Wallet extends Model
{
    use HasFactory, BranchAware, SoftDeletes;

    protected $fillable = [
        'user_id',
        'wallet_number',
        'barcode',
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
            if (empty($wallet->barcode)) {
                $wallet->barcode = static::generateBarcode();
            }
        });

        static::saving(function ($wallet) {
            // Ensure balance is always a decimal with 2 places
            $wallet->balance = round($wallet->balance, 2);
            $wallet->total_earned = round($wallet->total_earned, 2);
            $wallet->total_spent = round($wallet->total_spent, 2);
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

    public static function generateWalletNumber()
    {
        do {
            // Generate a 16-character wallet number with format: XXXX-XXXX-XXXX-XXXX
            $number = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (static::where('wallet_number', $number)->exists());

        return $number;
    }

    public static function generateBarcode()
    {
        do {
            // Generate a 12-digit barcode
            $barcode = str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (static::where('barcode', $barcode)->exists());

        return $barcode;
    }

    public function generateQRCode()
    {
        $data = [
            'wallet_id' => $this->id,
            'barcode' => $this->barcode,
            'wallet_number' => $this->wallet_number,
            'user_name' => $this->user->name,
            'timestamp' => now()->timestamp
        ];

        return QrCode::format('png')
                    ->size(300)
                    ->margin(10)
                    ->generate(json_encode($data));
    }

    public function addBalance($amount, $type = 'credit')
    {
        if ($type === 'credit') {
            $this->balance = round($this->balance + $amount, 2);
            $this->total_earned = round($this->total_earned + $amount, 2);
        } else {
            $this->balance = round($this->balance - $amount, 2);
            $this->total_spent = round($this->total_spent + $amount, 2);
        }
        return $this->save();
    }

    public function getBalanceAttribute($value)
    {
        return round($value, 2);
    }

    public function getTotalEarnedAttribute($value)
    {
        return round($value, 2);
    }

    public function getTotalSpentAttribute($value)
    {
        return round($value, 2);
    }
} 