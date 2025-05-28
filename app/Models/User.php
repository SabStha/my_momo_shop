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
        'is_admin',
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
        'is_admin' => 'boolean',
    ];

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        Log::info('Checking admin status', [
            'user_id' => $this->id,
            'email' => $this->email,
            'roles' => $this->getRoleNames()
        ]);
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
}
