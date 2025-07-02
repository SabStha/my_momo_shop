<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreditsAccount extends Model
{
    use HasFactory, BranchAware, SoftDeletes;

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
        'credits_balance' => 'integer',
        'total_credits_earned' => 'integer',
        'total_credits_spent' => 'integer',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($creditsAccount) {
            if (empty($creditsAccount->account_number)) {
                $creditsAccount->account_number = static::generateAccountNumber();
            }
            if (empty($creditsAccount->credits_barcode)) {
                $creditsAccount->credits_barcode = static::generateCreditsBarcode();
            }
        });

        static::saving(function ($creditsAccount) {
            // Ensure credits are always integers (whole numbers)
            $creditsAccount->credits_balance = (int) $creditsAccount->credits_balance;
            $creditsAccount->total_credits_earned = (int) $creditsAccount->total_credits_earned;
            $creditsAccount->total_credits_spent = (int) $creditsAccount->total_credits_spent;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(CreditsTransaction::class);
    }

    public static function generateAccountNumber()
    {
        do {
            // Generate a 16-character account number with format: XXXX-XXXX-XXXX-XXXX
            $number = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (static::where('account_number', $number)->exists());

        return $number;
    }

    public static function generateCreditsBarcode()
    {
        do {
            // Generate a 12-digit barcode for credits
            $barcode = str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (static::where('credits_barcode', $barcode)->exists());

        return $barcode;
    }

    public function generateQRCode()
    {
        $data = [
            'credits_account_id' => $this->id,
            'credits_barcode' => $this->credits_barcode,
            'account_number' => $this->account_number,
            'user_name' => $this->user->name,
            'timestamp' => now()->timestamp
        ];

        // Try to use the QR code package if available
        try {
            if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                return \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(300)
                    ->margin(10)
                    ->generate(json_encode($data));
            }
        } catch (\Exception $e) {
            // Fallback to online QR code service
        }

        // Fallback: Generate QR code using online service
        $qrData = urlencode(json_encode($data));
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $qrData;
        
        // Return the URL instead of image data
        return $qrUrl;
    }

    public function addCredits($credits, $type = 'credit')
    {
        if ($type === 'credit') {
            $this->credits_balance = (int) ($this->credits_balance + $credits);
            $this->total_credits_earned = (int) ($this->total_credits_earned + $credits);
        } else {
            $this->credits_balance = (int) ($this->credits_balance - $credits);
            $this->total_credits_spent = (int) ($this->total_credits_spent + $credits);
        }
        return $this->save();
    }

    public function getCreditsBalanceAttribute($value)
    {
        return (int) $value;
    }

    public function getTotalCreditsEarnedAttribute($value)
    {
        return (int) $value;
    }

    public function getTotalCreditsSpentAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Convert credits to display format (1 credit = 1 point)
     */
    public function getDisplayCreditsAttribute()
    {
        return $this->credits_balance . ' Credits';
    }

    /**
     * Check if account has sufficient credits
     */
    public function hasSufficientCredits($requiredCredits)
    {
        return $this->credits_balance >= $requiredCredits;
    }
}
