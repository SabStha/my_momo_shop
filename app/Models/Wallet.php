<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'credits_accounts';

    protected $fillable = [
        'user_id',
        'account_number',
        'credits_barcode',
        'credits_balance',
        'total_credits_earned',
        'total_credits_spent',
        'is_active'
    ];

    protected $casts = [
        'credits_balance' => 'decimal:2',
        'total_credits_earned' => 'decimal:2',
        'total_credits_spent' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($wallet) {
            if (empty($wallet->account_number)) {
                $wallet->account_number = static::generateWalletNumber();
            }
            if (empty($wallet->credits_barcode)) {
                $wallet->credits_barcode = static::generateBarcode();
            }
        });

        static::saving(function ($wallet) {
            // Ensure balance is always a decimal with 2 places
            $wallet->credits_balance = round($wallet->credits_balance, 2);
            $wallet->total_credits_earned = round($wallet->total_credits_earned, 2);
            $wallet->total_credits_spent = round($wallet->total_credits_spent, 2);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'credits_account_id');
    }

    public static function generateWalletNumber()
    {
        do {
            // Generate a 16-character wallet number with format: XXXX-XXXX-XXXX-XXXX
            $number = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (static::where('account_number', $number)->exists());

        return $number;
    }

    public static function generateBarcode()
    {
        do {
            // Generate a 12-digit barcode
            $barcode = str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (static::where('credits_barcode', $barcode)->exists());

        return $barcode;
    }

    public function generateQRCode()
    {
        $data = [
            'wallet_id' => $this->id,
            'barcode' => $this->credits_barcode,
            'wallet_number' => $this->account_number,
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
            $this->credits_balance = round($this->credits_balance + $amount, 2);
            $this->total_credits_earned = round($this->total_credits_earned + $amount, 2);
        } else {
            $this->credits_balance = round($this->credits_balance - $amount, 2);
            $this->total_credits_spent = round($this->total_credits_spent + $amount, 2);
        }
        return $this->save();
    }

    public function getBalanceAttribute($value)
    {
        return round($this->credits_balance, 2);
    }

    public function getTotalEarnedAttribute($value)
    {
        return round($this->total_credits_earned, 2);
    }

    public function getTotalSpentAttribute($value)
    {
        return round($this->total_credits_spent, 2);
    }

    // Alias for backward compatibility
    public function getWalletNumberAttribute()
    {
        return $this->account_number;
    }

    public function getBarcodeAttribute()
    {
        return $this->credits_barcode;
    }
} 