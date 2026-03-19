# 🎯 Loyalty & Rewards System - Implementation Plan

## 📊 Current System Analysis

### ✅ What You Already Have
1. **Wallet/Credits System** - `Wallet` model with `credits_balance`, `WalletTransaction`
2. **Coupon System** - `Coupon`, `CouponService`, `UserCoupon` with validation & redemption
3. **Referral System** - `ReferralService`, `Referral`, `Creator` models with tracking
4. **Badge System** - `BadgeProgressionService`, `UserBadge`, `BadgeProgress` for achievements
5. **Offer System** - `Offer`, `OfferClaim` models with AI-generated offers
6. **Order System** - `OrderController` with payment processing and event firing
7. **Payment Processing** - `WalletPaymentProcessor`, multiple payment methods

### ❌ What Needs to Be Built
1. **NFC/QR Card System** - Physical card linking & scanning
2. **Stamp Tracking** - 10 stamps = 1 free momo logic
3. **Tiered Credit Earning** - 2%-8% based on spend ranges
4. **Credit Expiry** - 6-month expiration logic
5. **Staff POS Tools** - NFC/QR reader, redemption interface
6. **Buy 1 Get 1 Logic** - First-order specific with fraud checks
7. **Taste-to-Trust Campaign** - Stall signup with feedback
8. **Feature Flags** - Enable/disable modules independently
9. **Fraud Detection** - Device tracking, rate limiting
10. **Liability Tracking** - Monitor reward costs

---

## 🏗️ Implementation Phases

### **Phase 1: Foundation & Database** (Days 1-2)
Database migrations, models, and core infrastructure

**Step 1.1: Loyalty Card System**
- [ ] Create `loyalty_cards` table (card_number, user_id, type, qr_code, nfc_uid, status)
- [ ] Create `LoyaltyCard` model with QR generation
- [ ] Add card linking endpoint

**Step 1.2: Stamp System**
- [ ] Create `stamps` table (user_id, order_id, stamp_date, location, staff_id)
- [ ] Create `stamp_redemptions` table (user_id, redeemed_at, stamps_used, reward_type)
- [ ] Create `Stamp` and `StampRedemption` models

**Step 1.3: Feature Flags**
- [ ] Create `feature_flags` table (key, enabled, config)
- [ ] Add settings: `stamps_enabled`, `tiered_credits_enabled`, `b1g1_enabled`

**Step 1.4: Credit Expiry**
- [ ] Add `expires_at` to `wallet_transactions` table
- [ ] Add `expiry_notified_at` for reminder tracking

**Step 1.5: Fraud Prevention**
- [ ] Create `user_devices` table (user_id, device_id, fingerprint, last_used)
- [ ] Create `fraud_logs` table (user_id, type, severity, details)

---

### **Phase 2: Core Services** (Days 3-5)
Business logic services that power the features

**Step 2.1: StampService** 
```php
class StampService {
    - addStamp(user, order, location, staff)
    - getStampCount(user)
    - canRedeem(user)
    - redeemStamps(user, quantity, verificationCode)
    - validateAntiDuplicate(order)
}
```

**Step 2.2: TieredCreditService**
```php
class TieredCreditService {
    - calculateCreditEarning(orderAmount)  // 2%-8% tiered
    - awardCredits(user, order)
    - validateRedemptionLimit(order, creditsUsed)  // max 20%
    - getActiveCredits(user)  // exclude expired
}
```

**Step 2.3: CardService**
```php
class CardService {
    - issueCard(user, type)  // physical or digital
    - linkCard(cardNumber, user)
    - scanCard(cardNumberOrQR)  // unified lookup
    - getCardInfo(card)
}
```

**Step 2.4: RewardService** (extends existing)
```php
class RewardService {
    - applyB1G1(user, order)  // first order only
    - validateFirstOrder(user)
    - checkFraudRisk(user, order)
    - calculateRewardLiability()  // for admin
}
```

**Step 2.5: CreditExpiryService**
```php
class CreditExpiryService {
    - expireOldCredits()  // cron job
    - getExpiringCredits(user, days)
    - notifyExpiring(user)
}
```

---

### **Phase 3: Integration with Existing Systems** (Days 6-8)
Connect new features to your current order & payment flow

**Step 3.1: Update OrderController**
- [ ] After order completion, call `StampService::addStamp()`
- [ ] Use `TieredCreditService::awardCredits()` based on spend
- [ ] Check `RewardService::applyB1G1()` if first order
- [ ] Log stamp & credit activities

**Step 3.2: Update WalletPaymentProcessor**
- [ ] Filter out expired credits with `TieredCreditService::getActiveCredits()`
- [ ] Enforce 20% max redemption limit
- [ ] Track which credits were used (FIFO - oldest first)

**Step 3.3: Integrate with Badge System**
- [ ] Award badge points for stamp milestones (5, 10, 25 stamps)
- [ ] Badge for "Big Spender" (5000+ order earning 8%)

**Step 3.4: Update CouponService**
- [ ] Add B1G1 coupon generation for first-time users
- [ ] Add stamp redemption coupons (10 stamps = 1 free momo coupon)
- [ ] Link coupons to referral system

---

### **Phase 4: API Endpoints** (Days 9-10)
RESTful APIs for mobile app & POS

**Step 4.1: Card APIs**
```
POST   /api/cards/issue           - Issue new card
POST   /api/cards/link            - Link card to user
GET    /api/cards/scan/{code}     - Scan QR/NFC (unified)
GET    /api/cards/my-card         - Get user's card info
```

**Step 4.2: Stamp APIs**
```
POST   /api/stamps/add            - Staff adds stamp (requires auth)
GET    /api/stamps/my-stamps      - Get user stamp count
POST   /api/stamps/redeem         - Redeem stamps for reward
GET    /api/stamps/history        - Stamp transaction history
```

**Step 4.3: Credits APIs**
```
GET    /api/credits/balance       - Get available credits (excluding expired)
GET    /api/credits/expiring      - Get credits expiring soon
GET    /api/credits/history       - Credit transaction history with expiry
POST   /api/credits/calculate     - Preview credit earning for cart
```

**Step 4.4: Rewards APIs**
```
GET    /api/rewards/eligible      - Check eligible rewards (B1G1, stamps)
POST   /api/rewards/redeem        - Redeem a reward
GET    /api/rewards/history       - Reward redemption history
```

---

### **Phase 5: Staff POS Integration** (Days 11-13)
Tools for staff to manage loyalty at point of sale

**Step 5.1: POS Card Scanner Interface**
- [ ] Add scan card button (QR camera or NFC tap)
- [ ] Display customer info & stamp count after scan
- [ ] Show available rewards

**Step 5.2: POS Stamp Management**
- [ ] "Add Stamp" button after order completion
- [ ] Manual short-code entry for backup
- [ ] Visual stamp progress indicator

**Step 5.3: POS Redemption Interface**
- [ ] "Redeem Reward" button
- [ ] OTP verification or manager PIN
- [ ] Print stamp progress on receipt

**Step 5.4: Staff Authentication**
- [ ] Staff login for POS terminals
- [ ] Device registration for security
- [ ] Activity logging for audits

---

### **Phase 6: Mobile App Features** (Days 14-16)
Customer-facing loyalty features

**Step 6.1: Wallet Screen Enhancements**
- [ ] Show stamp progress (circular progress: X/10)
- [ ] Display credits with expiry warnings
- [ ] Show tiered earning rate for current cart

**Step 6.2: Card Display**
- [ ] Digital loyalty card with QR code
- [ ] NFC tap simulation (if device supports)
- [ ] Card number & barcode

**Step 6.3: Rewards Screen**
- [ ] List available rewards
- [ ] Stamp milestones visualization
- [ ] One-tap redemption

**Step 6.4: Notifications**
- [ ] Stamp earned notification
- [ ] Credits expiring soon (30 days before)
- [ ] Reward unlocked notification

---

### **Phase 7: Taste-to-Trust Campaign** (Days 17-18)
Stall-side signup & tasting campaign

**Step 7.1: Campaign Signup Flow**
- [ ] Create `taste_campaign_signups` table (name, phone, location, rating)
- [ ] Simple signup form (name, phone, location)
- [ ] Auto-issue card + free momo coupon
- [ ] Capture tasting feedback (1-5 stars)

**Step 7.2: Campaign Analytics**
- [ ] Conversion rate per location
- [ ] Signup → First Order funnel
- [ ] Average rating by location

---

### **Phase 8: Referral & Creator Race** (Days 19-20)
Enhance existing referral system

**Step 8.1: Update ReferralService**
- [ ] Award B1G1 coupon to referred user (already partially implemented)
- [ ] Track "qualified orders" (exclude canceled/refunded)
- [ ] Creator leaderboard query optimization

**Step 8.2: Leaderboard UI**
- [ ] Public leaderboard page (top 10 creators)
- [ ] Real-time updates
- [ ] Prize tiers display

**Step 8.3: Admin Reward Distribution**
- [ ] Manual reward payout interface
- [ ] Export leaderboard for prizes
- [ ] Winner announcement tools

---

### **Phase 9: Fraud & Security** (Days 21-22)
Protect against abuse

**Step 9.1: FraudDetectionService**
```php
class FraudDetectionService {
    - detectMultiUse(user, order)
    - checkDeviceFingerprint(device)
    - rateLimitCheck(user, action)
    - flagSuspiciousActivity(user, reason)
}
```

**Step 9.2: Security Measures**
- [ ] OTP for high-value redemptions (> Rs 500)
- [ ] Manager PIN for in-store redemptions
- [ ] Rate limiting: max 10 stamps per day per user
- [ ] Idempotency: prevent duplicate stamp for same order

**Step 9.3: Audit Logging**
- [ ] Centralized logging for all redemptions
- [ ] Daily fraud reports
- [ ] Anomaly detection (e.g., 50 stamps in 1 hour)

---

### **Phase 10: Admin Dashboard** (Days 23-24)
Management & reporting tools

**Step 10.1: Loyalty Dashboard**
- [ ] Active cards count
- [ ] Stamps issued today/week/month
- [ ] Redemptions by type
- [ ] Credit liability (outstanding credits value)

**Step 10.2: Reports**
- [ ] Reward liability report (should be < 7% of monthly sales)
- [ ] Fraud flagging report
- [ ] Taste campaign performance
- [ ] Retention curve: first-taste → paid → repeat

**Step 10.3: Configuration**
- [ ] Feature flag toggles
- [ ] Update tiered credit rates
- [ ] Stamp reward settings (stamps required, reward value)
- [ ] B1G1 eligibility rules

---

### **Phase 11: Marketing Automation** (Days 25-26)
Dynamic campaigns and notifications

**Step 11.1: Campaign Scheduler**
- [ ] "Eat Now, Earn Later" launch offers
- [ ] Dynamic promo scheduler (time-based)
- [ ] Segment-based targeting (high spenders, dormant users)

**Step 11.2: Push Notifications**
- [ ] Repeat incentive notifications
- [ ] "You have 3 stamps, 7 more for free momo!"
- [ ] Credits expiring reminder

**Step 11.3: AI Integration Hooks**
- [ ] Connect to existing `AIOfferService`
- [ ] Personalized offers based on loyalty tier
- [ ] Predictive rewards for churn prevention

---

### **Phase 12: Testing & Deployment** (Days 27-28)
Ensure everything works together

**Step 12.1: Unit Tests**
- [ ] Test each service independently
- [ ] Test fraud detection logic
- [ ] Test credit expiry calculation

**Step 12.2: Integration Tests**
- [ ] End-to-end order → stamp → credit flow
- [ ] Redemption with OTP verification
- [ ] B1G1 first-order validation

**Step 12.3: Load Testing**
- [ ] Simulate 1000 concurrent scans
- [ ] Test card lookup performance
- [ ] Database query optimization

**Step 12.4: Deployment**
- [ ] Deploy database migrations
- [ ] Enable feature flags gradually (10% → 50% → 100%)
- [ ] Monitor error rates & performance

---

## 🎯 Priority Matrix

### Must-Have (MVP - Phases 1-4)
1. Card system (QR fallback)
2. Stamp tracking & redemption
3. Tiered credit earning
4. Basic API endpoints

### Should-Have (Phases 5-8)
5. POS integration
6. Mobile app features
7. Taste-to-Trust campaign
8. Enhanced referrals

### Nice-to-Have (Phases 9-12)
9. Advanced fraud detection
10. Admin dashboard
11. Marketing automation
12. Full testing suite

---

## 🔄 Reuse Strategy

To avoid duplication, we'll **extend existing systems**:

1. **Wallet System** → Add tiered earning + expiry logic
2. **Badge System** → Award badges for stamp milestones
3. **Coupon System** → Generate B1G1 & stamp redemption coupons
4. **Referral System** → Add B1G1 coupon distribution
5. **Order Events** → Hook into `OrderPlaced` event for stamps & credits
6. **AI Offer Service** → Integrate loyalty data for personalized offers

**No rewrites needed!** We'll create new services that work alongside existing ones.

---

## 📅 Timeline Summary

- **Weeks 1-2**: Database & Core Services (Phases 1-4) - **MVP**
- **Weeks 3-4**: POS & Mobile App (Phases 5-8) - **Enhanced Features**
- **Week 5**: Security & Admin (Phases 9-10) - **Production Ready**
- **Week 6**: Marketing & Testing (Phases 11-12) - **Launch Ready**

**Total: 6 weeks** for complete implementation

---

## 🚀 Next Steps

**Ready to start?** Let me know and we'll begin with:
1. **Phase 1, Step 1.1**: Create the loyalty cards database table
2. Set up the `LoyaltyCard` model with QR code generation
3. Build the card issuance endpoint

I'll build each step incrementally, testing as we go! 🎉

