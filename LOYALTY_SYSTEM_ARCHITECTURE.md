# 🏗️ Loyalty System Architecture

## 📐 System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    LOYALTY & REWARDS ECOSYSTEM                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │   Physical   │  │   Digital    │  │   Mobile     │          │
│  │   NFC Card   │  │   QR Card    │  │   App        │          │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │                  │                  │                   │
│         └──────────────────┴──────────────────┘                   │
│                            │                                      │
│                            ▼                                      │
│                  ┌───────────────────┐                           │
│                  │   CardService     │                           │
│                  │  (Unified Scan)   │                           │
│                  └─────────┬─────────┘                           │
│                            │                                      │
│         ┌──────────────────┼──────────────────┐                 │
│         │                  │                  │                  │
│         ▼                  ▼                  ▼                  │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────┐       │
│  │StampService │  │TieredCredit  │  │  RewardService  │       │
│  │             │  │   Service    │  │                 │       │
│  │ • Add Stamp │  │ • Calculate  │  │ • B1G1 Offer   │       │
│  │ • Redeem    │  │ • Award      │  │ • Validation   │       │
│  │ • Validate  │  │ • Expire     │  │ • Fraud Check  │       │
│  └─────────────┘  └──────────────┘  └─────────────────┘       │
│         │                  │                  │                  │
│         └──────────────────┴──────────────────┘                  │
│                            │                                      │
│                            ▼                                      │
│                  ┌───────────────────┐                           │
│                  │  Order System     │                           │
│                  │  (Existing)       │                           │
│                  └─────────┬─────────┘                           │
│                            │                                      │
│                            ▼                                      │
│         ┌──────────────────┴──────────────────┐                 │
│         │                                      │                  │
│         ▼                                      ▼                  │
│  ┌─────────────┐                      ┌──────────────┐          │
│  │   Wallet    │◄─────────────────────┤Badge System  │          │
│  │  (Existing) │                      │  (Existing)  │          │
│  └─────────────┘                      └──────────────┘          │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema

### New Tables

```sql
-- Loyalty Cards (NFC + QR)
loyalty_cards
├── id
├── user_id (FK → users)
├── card_number (unique, e.g., "AMAKO-1234-5678")
├── qr_code (generated image path)
├── nfc_uid (optional, for NFC chips)
├── card_type (physical | digital)
├── status (active | inactive | lost | expired)
├── issued_at
├── issued_by (staff_id)
└── timestamps

-- Stamp Tracking
stamps
├── id
├── user_id (FK → users)
├── order_id (FK → orders, unique to prevent duplicates)
├── loyalty_card_id (optional FK)
├── stamp_date
├── location (branch/stall name)
├── staff_id (who issued the stamp)
├── verified (boolean, for manual review)
└── timestamps

-- Stamp Redemptions
stamp_redemptions
├── id
├── user_id (FK → users)
├── stamps_used (usually 10)
├── reward_type (free_momo | discount)
├── reward_value (Rs amount or item)
├── verification_code (OTP or PIN)
├── verified_by (staff_id)
├── redeemed_at
├── order_id (FK → orders, if used in order)
└── timestamps

-- Feature Flags
feature_flags
├── id
├── key (stamps_enabled, tiered_credits_enabled, etc.)
├── enabled (boolean)
├── config (JSON, e.g., {"stamps_required": 10})
└── timestamps

-- User Devices (Fraud Prevention)
user_devices
├── id
├── user_id (FK → users)
├── device_id (from mobile app)
├── device_fingerprint (hash)
├── platform (ios | android | web)
├── first_seen_at
├── last_used_at
└── timestamps

-- Fraud Logs
fraud_logs
├── id
├── user_id (FK → users, nullable)
├── type (duplicate_stamp | device_fraud | rate_limit)
├── severity (low | medium | high)
├── details (JSON)
├── flagged_at
├── resolved_at
└── timestamps

-- Taste Campaign
taste_campaign_signups
├── id
├── name
├── phone
├── email (optional)
├── location (stall location)
├── tasting_rating (1-5)
├── feedback (text)
├── card_issued (boolean)
├── first_order_placed (boolean)
├── signup_date
└── timestamps
```

### Modified Tables

```sql
-- wallet_transactions (add expiry)
ALTER TABLE wallet_transactions ADD COLUMN expires_at TIMESTAMP NULL;
ALTER TABLE wallet_transactions ADD COLUMN expiry_notified_at TIMESTAMP NULL;
ALTER TABLE wallet_transactions ADD COLUMN transaction_source VARCHAR(50); -- 'tiered_earning', 'badge', 'referral'

-- coupons (add campaign tracking)
ALTER TABLE coupons ADD COLUMN campaign_type VARCHAR(50); -- 'b1g1', 'stamp_redemption', 'taste_campaign'
ALTER TABLE coupons ADD COLUMN device_restricted BOOLEAN DEFAULT FALSE;
ALTER TABLE coupons ADD COLUMN phone_restricted VARCHAR(20) NULL;
```

---

## 🔄 Service Layer Architecture

### Core Services (New)

#### 1. **CardService**
```php
namespace App\Services\Loyalty;

class CardService
{
    // Card Management
    public function issueCard(User $user, string $type = 'digital'): LoyaltyCard
    public function linkCard(string $cardNumber, User $user): bool
    public function deactivateCard(LoyaltyCard $card): void
    public function reportLost(LoyaltyCard $card): void
    
    // Scanning
    public function scanCard(string $input): ?LoyaltyCard  // Unified: QR, NFC, or short code
    public function getCardInfo(LoyaltyCard $card): array
    
    // QR Generation
    protected function generateQRCode(string $cardNumber): string
    protected function generateCardNumber(): string
}
```

#### 2. **StampService**
```php
namespace App\Services\Loyalty;

class StampService
{
    // Stamp Operations
    public function addStamp(User $user, Order $order, ?LoyaltyCard $card = null): Stamp
    public function getStampCount(User $user): int
    public function getStampHistory(User $user): Collection
    
    // Redemption
    public function canRedeem(User $user, int $stampsRequired = 10): bool
    public function redeemStamps(User $user, int $quantity, string $verificationCode): StampRedemption
    
    // Validation
    protected function validateAntiDuplicate(Order $order): bool
    protected function checkRateLimit(User $user): bool
    
    // Generate reward coupon
    protected function generateRewardCoupon(User $user, StampRedemption $redemption): Coupon
}
```

#### 3. **TieredCreditService**
```php
namespace App\Services\Loyalty;

class TieredCreditService
{
    // Tiered earning rates
    const TIERS = [
        ['min' => 0,    'max' => 199,  'rate' => 0.02],  // 2%
        ['min' => 200,  'max' => 499,  'rate' => 0.03],  // 3%
        ['min' => 500,  'max' => 999,  'rate' => 0.04],  // 4%
        ['min' => 1000, 'max' => 4999, 'rate' => 0.05],  // 5%
        ['min' => 5000, 'max' => null, 'rate' => 0.08],  // 8%
    ];
    
    // Credit Operations
    public function calculateCreditEarning(float $orderAmount): float
    public function awardCredits(User $user, Order $order): WalletTransaction
    public function getActiveCredits(User $user): float  // Exclude expired
    
    // Redemption
    public function validateRedemptionLimit(float $orderTotal, float $creditsUsed): bool  // Max 20%
    public function deductCredits(User $user, float $amount, Order $order): void
    
    // Expiry
    public function getExpiringCredits(User $user, int $days = 30): Collection
    public function markExpired(WalletTransaction $transaction): void
}
```

#### 4. **RewardService**
```php
namespace App\Services\Loyalty;

class RewardService
{
    // Buy 1 Get 1
    public function applyB1G1(User $user, Order $order): ?Coupon
    public function validateFirstOrder(User $user): bool
    public function generateB1G1Coupon(User $user): Coupon
    
    // Fraud Prevention
    public function checkFraudRisk(User $user, Order $order): array
    protected function checkDeviceFingerprint(User $user): bool
    
    // Admin Reports
    public function calculateRewardLiability(): float  // Total outstanding rewards value
    public function getLiabilityPercentage(): float  // Should be < 7%
}
```

#### 5. **CreditExpiryService**
```php
namespace App\Services\Loyalty;

class CreditExpiryService
{
    // Cron Jobs
    public function expireOldCredits(): int  // Run daily
    public function notifyExpiring(): int  // Run daily
    
    // Query
    public function getExpiringCredits(User $user, int $daysAhead = 30): Collection
    public function getExpiredCreditsSummary(User $user): array
    
    // Notifications
    protected function sendExpiryWarning(User $user, Collection $credits): void
}
```

---

## 🔌 Integration Points

### 1. Order Flow Integration

```php
// In OrderController::store() - After order creation

// 1. Award Stamp
if (FeatureFlag::isEnabled('stamps_enabled')) {
    $stampService->addStamp($user, $order);
}

// 2. Award Tiered Credits
if (FeatureFlag::isEnabled('tiered_credits_enabled')) {
    $tieredCreditService->awardCredits($user, $order);
}

// 3. Check B1G1 Eligibility
if (FeatureFlag::isEnabled('b1g1_enabled') && $rewardService->validateFirstOrder($user)) {
    $rewardService->applyB1G1($user, $order);
}

// 4. Fire existing event for badges
event(new OrderPlaced($order));
```

### 2. Payment Flow Integration

```php
// In WalletPaymentProcessor::process()

// 1. Get active (non-expired) credits
$availableCredits = $tieredCreditService->getActiveCredits($user);

// 2. Validate 20% redemption limit
if (!$tieredCreditService->validateRedemptionLimit($orderTotal, $creditsUsed)) {
    throw new Exception("Cannot use more than 20% of order value");
}

// 3. Deduct credits (FIFO - oldest first)
$tieredCreditService->deductCredits($user, $creditsUsed, $order);
```

### 3. Badge System Integration

```php
// In BadgeProgressionService::processUserProgression()

// Award badge for stamp milestones
$stampCount = $stampService->getStampCount($user);
if ($stampCount >= 10) {
    $this->awardBadge($user, 'stamp_collector_bronze');
}
if ($stampCount >= 50) {
    $this->awardBadge($user, 'stamp_collector_silver');
}

// Award badge for high-tier spender (8% earning rate)
$totalSpent = $user->orders()->sum('total');
if ($totalSpent >= 5000) {
    $this->awardBadge($user, 'vip_spender_gold');
}
```

---

## 🔐 Security Architecture

### 1. Fraud Prevention Flow

```
Order Attempt
     │
     ▼
┌─────────────────────┐
│ FraudDetectionService│
├─────────────────────┤
│ • Check device ID   │
│ • Rate limit check  │
│ • Order duplicate   │
│ • Pattern analysis  │
└──────┬──────────────┘
       │
       ├─── Low Risk ──────► Proceed
       │
       ├─── Medium Risk ───► Flag + Allow
       │
       └─── High Risk ─────► Block + Log
```

### 2. Redemption Verification

```
Redemption Request
     │
     ▼
┌─────────────────────┐
│ Value > Rs 500?     │
└──────┬──────────────┘
       │
       ├─── Yes ─────► OTP Verification
       │
       └─── No ──────► Manager PIN (POS) or Instant (App)
```

### 3. Anti-Duplicate Logic

```sql
-- Prevent same order from getting multiple stamps
CREATE UNIQUE INDEX idx_stamps_order 
ON stamps(order_id) WHERE order_id IS NOT NULL;

-- Rate limiting (application layer)
Max 10 stamps per user per day
Max 1 B1G1 coupon per device per lifetime
```

---

## 📱 Mobile App Screens

### 1. **Wallet Screen (Enhanced)**
```
┌──────────────────────────────┐
│ 💳 My Wallet                 │
├──────────────────────────────┤
│                              │
│  ┌────────────────────────┐ │
│  │  Available Credits     │ │
│  │  Rs 450.00             │ │
│  │  Expires in 45 days    │ │
│  └────────────────────────┘ │
│                              │
│  ┌────────────────────────┐ │
│  │  Stamp Progress        │ │
│  │  ●●●●●●●○○○ (7/10)     │ │
│  │  3 more for FREE momo! │ │
│  └────────────────────────┘ │
│                              │
│  Next Order Earning: 4%      │
│  (Rs 500-999 tier)           │
│                              │
└──────────────────────────────┘
```

### 2. **Card Screen (New)**
```
┌──────────────────────────────┐
│ 🎴 Loyalty Card              │
├──────────────────────────────┤
│                              │
│  ┌────────────────────────┐ │
│  │                        │ │
│  │    [QR CODE]           │ │
│  │                        │ │
│  │  AMAKO-1234-5678       │ │
│  └────────────────────────┘ │
│                              │
│  Show to staff to earn stamps│
│  Tap NFC if supported        │
│                              │
│  [View History]              │
│                              │
└──────────────────────────────┘
```

### 3. **Rewards Screen (New)**
```
┌──────────────────────────────┐
│ 🎁 My Rewards                │
├──────────────────────────────┤
│                              │
│  Available                   │
│  ┌────────────────────────┐ │
│  │ 🎉 Free Momo            │ │
│  │ 10 stamps collected     │ │
│  │ [Redeem Now]            │ │
│  └────────────────────────┘ │
│                              │
│  Coming Soon                 │
│  ┌────────────────────────┐ │
│  │ 🎁 Mystery Reward       │ │
│  │ Need 3 more stamps      │ │
│  │ ●●●●●●●○○○             │ │
│  └────────────────────────┘ │
│                              │
└──────────────────────────────┘
```

---

## 🖥️ POS Interface

### Staff View After Card Scan

```
┌─────────────────────────────────────┐
│ Customer: Ramesh Sharma             │
│ Phone: +977-9841234567              │
├─────────────────────────────────────┤
│                                     │
│ Stamps: ●●●●●●●○○○ (7/10)           │
│                                     │
│ Available Credits: Rs 320           │
│ (Expiring Rs 50 in 10 days)         │
│                                     │
│ Available Rewards:                  │
│  ✓ Buy 1 Get 1 (First Order)        │
│                                     │
│ [Add Stamp to Order]                │
│ [Apply Reward]                      │
│ [View Full History]                 │
│                                     │
└─────────────────────────────────────┘
```

---

## 🚀 Deployment Strategy

### Feature Rollout Plan

1. **Phase 1: Internal Testing (Week 1-2)**
   - Enable for staff accounts only
   - Test all features in staging
   - Fix bugs, optimize queries

2. **Phase 2: Soft Launch (Week 3)**
   - Enable for 10% of users (feature flag)
   - Monitor performance & errors
   - Gather initial feedback

3. **Phase 3: Gradual Rollout (Week 4)**
   - 25% → 50% → 75% → 100%
   - Monitor reward liability percentage
   - Adjust rates if needed

4. **Phase 4: Full Launch (Week 5)**
   - 100% enabled
   - Marketing campaign
   - Taste-to-Trust stalls activated

---

## 📊 Success Metrics

Track these KPIs:

1. **Adoption**
   - % of users with loyalty cards
   - Average stamps per user
   - % of orders using credits

2. **Engagement**
   - Repeat purchase rate
   - Average days between orders
   - Redemption rate

3. **Financial**
   - Reward liability % (target: < 7%)
   - Average order value (AOV) increase
   - Customer lifetime value (CLV) increase

4. **Fraud**
   - Fraud detection rate
   - False positive rate
   - Blocked attempts

---

## 🎯 Next: Let's Build!

This architecture integrates seamlessly with your existing system. Ready to start building?

**Choose your starting point:**
1. **MVP Path**: Card + Stamps + APIs (2 weeks)
2. **Full Path**: All features (6 weeks)
3. **Custom Path**: Pick specific features

Let me know and I'll start coding! 🚀

