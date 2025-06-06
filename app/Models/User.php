<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'is_active',
        'last_login_at',
        'profile_picture',
        'referral_code',
    ];

    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'points',
        'is_admin',
        'is_creator',
        'role',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'profile_picture'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(substr(md5(uniqid()), 0, 8));
            }
        });

        static::created(function ($user) {
            // Create a wallet for the new user
            $user->wallet()->create(['balance' => 0]);
        });
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a cashier.
     *
     * @return bool
     */
    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    /**
     * Check if the user is an employee.
     *
     * @return bool
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function creator()
    {
        return $this->hasOne(Creator::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'user_coupons')->withTimestamps()->withPivot('used_at');
    }

    public function wallet()
    {
        return $this->hasOne(\App\Models\Wallet::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSettings::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
