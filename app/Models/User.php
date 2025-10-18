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
        'city',
        'ward_number',
        'area_locality',
        'building_name',
        'detailed_directions',
        'preferred_branch_id',
        'profile_picture',
        'referral_code',
        'referred_by',
        'points',
        'role',
        'is_admin',
    ];

    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
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
        'points' => 'decimal:2',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the login identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the login identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the remember token for the user.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the remember token for the user.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->referral_code = self::generateUniqueReferralCode();
        });
    }

    public static function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('referral_code', $code)->exists());
        
        return $code;
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
        return $this->hasOne(Wallet::class);
    }

    public function creditsAccount()
    {
        return $this->hasOne(CreditsAccount::class);
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

    public function offerClaims()
    {
        return $this->hasMany(OfferClaim::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function investor()
    {
        return $this->hasOne(Investor::class);
    }

    // Badge System Relationships
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function badgeProgress()
    {
        return $this->hasMany(BadgeProgress::class);
    }

    // AmaKo Credits System Relationships
    public function amaCredit()
    {
        return $this->hasOne(AmaCredit::class);
    }

    /**
     * Get all badges earned by this user
     */
    public function badges()
    {
        return $this->belongsToMany(
            \App\Models\BadgeTier::class,
            'user_badge_progress',
            'user_id',
            'badge_tier_id'
        )
        ->withPivot(['awarded_at', 'progress', 'last_calculated_at'])
        ->withTimestamps()
        ->orderBy('awarded_at', 'desc');
    }

    public function amaCreditTransactions()
    {
        return $this->hasMany(AmaCreditTransaction::class);
    }

    // Task and Reward System Relationships
    public function taskCompletions()
    {
        return $this->hasMany(UserTaskCompletion::class);
    }

    public function rewardRedemptions()
    {
        return $this->hasMany(UserRewardRedemption::class);
    }

    // Badge System Helper Methods
    public function hasBadge($badgeClassCode, $rankCode = null, $tierLevel = null)
    {
        $query = $this->userBadges()
            ->whereHas('badgeClass', function ($q) use ($badgeClassCode) {
                $q->where('code', $badgeClassCode);
            })
            ->where('status', 'active');

        if ($rankCode) {
            $query->whereHas('badgeRank', function ($q) use ($rankCode) {
                $q->where('code', $rankCode);
            });
        }

        if ($tierLevel) {
            $query->whereHas('badgeTier', function ($q) use ($tierLevel) {
                $q->where('level', $tierLevel);
            });
        }

        return $query->exists();
    }

    public function getHighestBadge($badgeClassCode)
    {
        return $this->userBadges()
            ->whereHas('badgeClass', function ($q) use ($badgeClassCode) {
                $q->where('code', $badgeClassCode);
            })
            ->where('status', 'active')
            ->with(['badgeTier', 'badgeRank'])
            ->orderBy('badge_rank_id')
            ->orderBy('badge_tier_id')
            ->first();
    }

    public function canApplyForGoldPlus()
    {
        // Check if user has Gold + Tier 3 in both Momo Loyalty and Momo Engagement
        $loyaltyBadge = $this->getHighestBadge('loyalty');
        $engagementBadge = $this->getHighestBadge('engagement');

        if (!$loyaltyBadge || !$engagementBadge) {
            return false;
        }

        return $loyaltyBadge->badgeRank->code === 'gold' && 
               $loyaltyBadge->badgeTier->level === 3 &&
               $engagementBadge->badgeRank->code === 'gold' && 
               $engagementBadge->badgeTier->level === 3;
    }

    // AmaKo Credits Helper Methods
    public function getAmaCreditBalance()
    {
        return $this->amaCredit?->current_balance ?? 0;
    }

    public function canEarnAmaCredits($amount)
    {
        return $this->amaCredit?->canEarnCredits($amount) ?? false;
    }

    public function addAmaCredits($amount, $description = '', $source = null, $metadata = [])
    {
        if (!$this->amaCredit) {
            // Create AmaCredit account if it doesn't exist
            $this->amaCredit()->create([
                'user_id' => $this->id,
                'current_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'weekly_earned' => 0,
                'weekly_reset_date' => now()->addWeek(),
                'weekly_cap' => 1000,
                'last_activity_at' => now(),
            ]);
        }

        return $this->amaCredit->addCredits($amount, $description, $source, $metadata);
    }

    public function spendAmaCredits($amount, $description = '', $source = null, $metadata = [])
    {
        return $this->amaCredit?->spendCredits($amount, $description, $source, $metadata);
    }

    // Task System Helper Methods
    public function getAvailableTasks()
    {
        return CreditTask::active()
            ->where(function ($query) {
                $query->where('requires_badge', false)
                    ->orWhereHas('requiredBadgeClass', function ($q) {
                        $q->whereIn('id', $this->userBadges()
                            ->where('status', 'active')
                            ->pluck('badge_class_id'));
                    });
            })
            ->get()
            ->filter(function ($task) {
                return $task->canBeCompletedByUser($this);
            });
    }

    public function completeTask($taskCode, $completionData = [])
    {
        $task = CreditTask::where('code', $taskCode)->first();
        
        if (!$task || !$task->canBeCompletedByUser($this)) {
            throw new \Exception('Task cannot be completed');
        }

        // Create task completion record
        $completion = $this->taskCompletions()->create([
            'credit_task_id' => $task->id,
            'credits_earned' => $task->credits_reward,
            'completion_data' => $completionData,
            'completed_at' => now(),
        ]);

        // Add credits to user
        $this->addAmaCredits(
            $task->credits_reward,
            "Completed task: {$task->name}",
            'task_completion',
            ['task_code' => $task->code, 'completion_id' => $completion->id]
        );

        return $completion;
    }

    // Reward System Helper Methods
    public function getAvailableRewards()
    {
        return CreditReward::available()
            ->inStock()
            ->where(function ($query) {
                $query->where('requires_badge', false)
                    ->orWhereHas('requiredBadgeClass', function ($q) {
                        $q->whereIn('id', $this->userBadges()
                            ->where('status', 'active')
                            ->pluck('badge_class_id'));
                    });
            })
            ->get()
            ->filter(function ($reward) {
                return $reward->canBeRedeemedByUser($this);
            });
    }

    public function redeemReward($rewardId, $redemptionData = [])
    {
        $reward = CreditReward::find($rewardId);
        
        if (!$reward || !$reward->canBeRedeemedByUser($this)) {
            throw new \Exception('Reward cannot be redeemed');
        }

        // Spend credits
        $this->spendAmaCredits(
            $reward->credits_cost,
            "Redeemed reward: {$reward->name}",
            'reward_redemption',
            ['reward_id' => $reward->id]
        );

        // Create redemption record
        $redemption = $this->rewardRedemptions()->create([
            'credit_reward_id' => $reward->id,
            'credits_spent' => $reward->credits_cost,
            'status' => 'active',
            'redemption_data' => $redemptionData,
            'redeemed_at' => now(),
            'expires_at' => now()->addYear(), // Rewards expire after 1 year
        ]);

        // Update reward redemption count
        $reward->increment('redeemed_count');

        return $redemption;
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Check if the user has access to a specific branch.
     *
     * @param int $branchId
     * @return bool
     */
    public function hasBranchAccess($branchId)
    {
        // Admin users have access to all branches
        if ($this->hasRole('admin')) {
            return true;
        }

        // Check if user is an employee at this branch
        if ($this->employee && $this->employee->branch_id == $branchId) {
            return true;
        }

        // Check if user has any of the required roles for POS access
        if ($this->hasAnyRole(['employee.manager', 'employee.cashier'])) {
            // For now, allow access if user has the role
            // You might want to add additional checks here
            return true;
        }

        return false;
    }

    public function userThemes()
    {
        return $this->hasMany(UserTheme::class);
    }

    public function themes()
    {
        return $this->hasMany(UserTheme::class);
    }

    public function activeTheme()
    {
        return $this->hasOne(UserTheme::class)->where('is_active', true);
    }

    public function unlockedThemes()
    {
        return $this->hasMany(UserTheme::class)->where('is_unlocked', true);
    }

    public function unlockTheme($themeName)
    {
        $theme = $this->themes()->firstOrCreate([
            'theme_name' => $themeName,
            'theme_display_name' => ucfirst($themeName) . ' Theme'
        ]);

        if (!$theme->is_unlocked) {
            $theme->unlock();
        }

        return $theme;
    }

    public function activateTheme($themeName)
    {
        $theme = $this->themes()->where('theme_name', $themeName)->first();
        
        if ($theme && $theme->is_unlocked) {
            $theme->activate();
            return true;
        }

        return false;
    }

    public function getHighestBadgeRank()
    {
        return $this->userBadges()
            ->with(['badgeRank', 'badgeClass'])
            ->active()
            ->get()
            ->map(function ($badge) {
                return [
                    'rank' => $badge->badgeRank,
                    'class' => $badge->badgeClass
                ];
            })
            ->sortByDesc(function ($badge) {
                return $badge['rank']->level;
            })
            ->first();
    }

    public function syncThemesWithBadges()
    {
        $highestRank = $this->getHighestBadgeRank();
        
        if (!$highestRank) {
            // Default to bronze if no badges
            $this->unlockTheme('bronze');
            $this->activateTheme('bronze');
            return;
        }

        $rankCode = strtolower($highestRank['rank']->code);
        
        // Unlock themes based on rank
        switch ($rankCode) {
            case 'elite':
                $this->unlockTheme('elite');
                $this->unlockTheme('gold');
                $this->unlockTheme('silver');
                $this->unlockTheme('bronze');
                break;
            case 'gold':
                $this->unlockTheme('gold');
                $this->unlockTheme('silver');
                $this->unlockTheme('bronze');
                break;
            case 'silver':
                $this->unlockTheme('silver');
                $this->unlockTheme('bronze');
                break;
            case 'bronze':
            default:
                $this->unlockTheme('bronze');
                break;
        }

        // Auto-activate the highest unlocked theme if none is active
        if (!$this->activeTheme) {
            $this->activateTheme($rankCode);
        }
    }

    /**
     * Get the user's cart
     */
    public function cart()
    {
        return $this->hasOne(UserCart::class);
    }

    /**
     * Get or create user cart
     */
    public function getOrCreateCart()
    {
        return $this->cart ?: $this->cart()->create([
            'cart_data' => [],
            'last_updated' => now(),
        ]);
    }
}
