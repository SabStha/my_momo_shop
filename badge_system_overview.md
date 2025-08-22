# 🏆 AmaKo Badge System - Complete Overview

## 📋 Table of Contents
1. [Badge Classes](#badge-classes)
2. [Badge Ranks](#badge-ranks)
3. [Badge Tiers](#badge-tiers)
4. [Point System](#point-system)
5. [Credit System](#credit-system)
6. [Task System](#task-system)
7. [Reward System](#reward-system)
8. [Progression Logic](#progression-logic)
9. [Seasonal Badges](#seasonal-badges)
10. [Technical Implementation](#technical-implementation)

---

## 🏅 Badge Classes

### 1. 🥟 Momo Loyalty (Public)
- **Status**: Public, available to all users
- **Description**: Earn badges through consistent ordering and loyalty
- **Requirements**:
  - Place orders regularly
  - Maintain consistent ordering patterns
  - Build order volume over time
- **Benefits**:
  - Loyalty discounts
  - Priority customer service
  - Special loyalty rewards

### 2. 🎯 Momo Engagement (Public)
- **Status**: Public, available to all users
- **Description**: Earn badges through active community engagement
- **Requirements**:
  - Try different menu items
  - Refer new customers
  - Participate in community events
  - Donate to dog rescue campaigns
- **Benefits**:
  - Exclusive engagement rewards
  - Community recognition
  - Special event invitations

---

## 🥉 Badge Ranks

### Bronze (Level 1)
- **Description**: Beginner level - starting your journey
- **Color**: #CD7F32
- **Requirements**: Complete previous rank requirements

### Silver (Level 2)
- **Description**: Intermediate level - growing stronger
- **Color**: #C0C0C0
- **Requirements**: Complete previous rank requirements

### Gold (Level 3)
- **Description**: Advanced level - reaching excellence
- **Color**: #FFD700
- **Requirements**: Complete previous rank requirements

### Prestige (Level 4) - NEW!
- **Description**: Legendary status - ultimate achievement
- **Color**: #9370DB
- **Requirements**: Complete Gold Tier 3 + additional points
- **Special**: Long-term loyalty and engagement

---

## 📊 Badge Tiers

### Tier 1 (Foundation)
- **Bronze Tier 1**: 100 points required
- **Silver Tier 1**: 300 points required
- **Gold Tier 1**: 600 points required
- **Prestige Tier 1**: 1200 points required
- **Description**: First tier - building foundation

### Tier 2 (Advancement)
- **Bronze Tier 2**: 250 points required
- **Silver Tier 2**: 750 points required
- **Gold Tier 2**: 1500 points required
- **Prestige Tier 2**: 3000 points required
- **Description**: Second tier - advancing skills

### Tier 3 (Mastery)
- **Bronze Tier 3**: 500 points required
- **Silver Tier 3**: 1500 points required
- **Gold Tier 3**: 3000 points required
- **Prestige Tier 3**: 6000 points required
- **Description**: Third tier - mastering the rank

---

## 🎯 Point System

### Loyalty Points Calculation
```
Loyalty Points = Spending Points + Order Bonus + Consistency Bonus

Where:
- Spending Points = Total Spent ÷ 10 (1 point per Rs. 10)
- Order Bonus = Number of Orders × 10
- Consistency Bonus = Consistency Score × 100
```

### Engagement Points Calculation
```
Engagement Points = Unique Items + Referrals + Social Shares + Donations + Community

Where:
- Unique Items = Items Tried × 50 points
- Referrals = Referral Count × 200 points
- Social Shares = Share Count × 100 points
- Donations = Donation Count × 500 points
- Community = Community Participation × 50 points
```

### Consistency Score
```
Consistency Score = min(1, Order Frequency × 7)

Where:
- Order Frequency = Total Orders ÷ Total Days
- Normalized to 0-1 scale
- Weekly frequency baseline
```

---

## 💰 Credit System (AmaKo Credits)

### Credit Award Formula
```
Credits Awarded = Base Credits × Rank Multiplier × Tier Multiplier

Where:
- Base Credits = 100
- Rank Multiplier = Rank Level (1, 2, 3, 4)
- Tier Multiplier = Tier Level (1, 2, 3)
```

### Examples:
- **Bronze Tier 1**: 100 × 1 × 1 = **100 credits**
- **Silver Tier 2**: 100 × 2 × 2 = **400 credits**
- **Gold Tier 3**: 100 × 3 × 3 = **900 credits**
- **Prestige Tier 1**: 100 × 4 × 1 = **400 credits**

### Credit Cap System
- **Weekly Cap**: 1000 credits
- **Weekly Reset**: Every 7 days
- **Balance Tracking**: Current, total earned, total spent

---

## ✅ Task System

### Daily Tasks
1. **Daily Order** (50 credits)
   - Place an order today
   
2. **Try New Item** (75 credits)
   - Order a menu item you haven't tried before

### Weekly Tasks
1. **Weekly Order Streak** (150 credits)
   - Place orders on 3 consecutive days
   
2. **Social Share** (100 credits)
   - Share AmaKo content on social media

### One-Time Tasks
1. **First Order** (100 credits)
   - Complete your first order
   
2. **Refer a Friend** (300 credits)
   - Successfully refer a new customer
   
3. **Dog Rescue Donation** (500 credits)
   - Make a donation to dog rescue campaign

---

## 🎁 Reward System

### Free Items
1. **Free Momo (Any Variety)** - 200 credits
   - Max value: Rs. 150
   - Validity: 30 days

2. **Free Drink** - 150 credits
   - Max value: Rs. 100
   - Validity: 30 days

### Privileges
1. **Skip the Line** - 100 credits
   - Priority service
   - Validity: 7 days

2. **Community Event Pass** - 500 credits
   - Free entry to community events
   - Requires engagement badge
   - Validity: 90 days

### Physical Rewards
1. **Tasting Kit** - 400 credits
   - Sample pack of different momo varieties
   - 5 different varieties
   - Validity: 60 days

### Discounts
1. **Loyalty Discount (10%)** - 300 credits
   - 10% discount on next order
   - Max discount: Rs. 200
   - Requires loyalty badge
   - Validity: 14 days

---

## 🔄 Progression Logic

### Automatic Badge Awarding
1. **Order Placement** → `OrderPlaced` event fires
2. **Badge Listener** → `HandleBadgeProgression` processes
3. **Point Calculation** → Loyalty and engagement points calculated
4. **Progress Update** → Badge progress updated
5. **Badge Check** → New badges awarded if thresholds met
6. **Credit Award** → AmaKo credits awarded for new badges

### Progress Tracking
- **Current Points**: Real-time point calculation
- **Progress Percentage**: Visual progress to next tier
- **Points to Next Tier**: Exact points needed
- **Total Points Earned**: Lifetime point accumulation

### Badge Status
- **Active**: Currently earned and valid
- **Inactive**: Temporarily disabled
- **Expired**: Past expiration date

---

## 🗓️ Seasonal Badges - NEW!

### Monthly Champions
- **January Champion** ❄️ - 500 points
- **February Champion** 💝 - 400 points
- **March Champion** 🌸 - 450 points
- **April Champion** 🌧️ - 500 points
- **May Champion** 🌺 - 550 points
- **June Champion** ☀️ - 600 points
- **July Champion** 🏖️ - 650 points
- **August Champion** 🌻 - 700 points
- **September Champion** 🍂 - 600 points
- **October Champion** 🎃 - 550 points
- **November Champion** 🦃 - 500 points
- **December Champion** 🎄 - 800 points

### Seasonal Features
- **Time-Limited**: Each badge expires at month end
- **Unique Icons**: Month-specific emojis and colors
- **Special Rewards**: Exclusive seasonal privileges
- **Community Events**: Monthly challenges and competitions

### Seasonal Commands
```bash
# Create current month badge
php artisan badges:seasonal create

# Award badges to eligible users
php artisan badges:seasonal award

# Clean up expired badges
php artisan badges:seasonal cleanup
```

---

## ⚙️ Technical Implementation

### Database Structure
```
badge_classes
├── id, name, code, description, icon
├── is_public, is_active, is_seasonal
├── expires_at (for seasonal badges)
└── requirements, benefits (JSON)

badge_ranks
├── id, badge_class_id, name, code, level
├── description, color, is_active
└── requirements, benefits (JSON)

badge_tiers
├── id, badge_rank_id, name, level
├── description, points_required, is_active
└── requirements, benefits (JSON)

user_badges
├── id, user_id, badge_tier_id, badge_rank_id, badge_class_id
├── status, earned_at, expires_at
└── earned_data (JSON)

badge_progress
├── id, user_id, badge_class_id
├── current_points, total_points_earned
├── progress_data (JSON)
└── last_activity_at
```

### Key Services
1. **BadgeProgressionService**: Main logic for point calculation and badge awarding
2. **HandleBadgeProgression**: Event listener for order completion
3. **SeasonalBadgeService**: Manages time-limited seasonal badges
4. **DynamicRewardService**: Handles spin-the-wheel and mystery rewards
5. **ImpactTrackingService**: Tracks dog rescue impact
6. **AmaCredit System**: Credit management and tracking

### Event Flow
```
Order Created → OrderPlaced Event → HandleBadgeProgression → 
BadgeProgressionService → Point Calculation → Progress Update → 
Badge Awarding → Credit Awarding → User Notification
```

### Models and Relationships
- **User** → hasMany → **UserBadge**
- **User** → hasMany → **BadgeProgress**
- **BadgeClass** → hasMany → **BadgeRank**
- **BadgeRank** → hasMany → **BadgeTier**
- **BadgeTier** → hasMany → **UserBadge**

---

## 🎯 Current Status (Your Account)

### Earned Badges
- ✅ **🥟 Momo Loyalty Bronze Tier 1**

### Current Progress
- **Loyalty Points**: 662 points
- **Engagement Points**: 250 points
- **Next Tier**: Bronze Tier 2 (250 points required)
- **Progress**: 264% (exceeded Tier 1, working towards Tier 2)

### AmaKo Credits
- **Earned**: 100 credits for Bronze Tier 1 badge
- **Available**: For spending on rewards

### Recent Activity
- **Orders Placed**: Multiple orders contributing to loyalty points
- **Unique Items**: Contributing to engagement points
- **Badge System**: Fully functional and processing orders

---

## 🚀 How to Progress

### For More Loyalty Points:
1. **Place more orders** (10 points per order)
2. **Spend more money** (1 point per Rs. 10)
3. **Order consistently** (consistency bonus)

### For More Engagement Points:
1. **Try new menu items** (50 points per unique item)
2. **Refer friends** (200 points per referral)
3. **Share on social media** (100 points per share)
4. **Donate to dog rescue** (500 points per donation)

### Next Milestones:
- **Bronze Tier 2**: 250 points (you have 662 - already exceeded!)
- **Silver Tier 1**: 300 points (you have 662 - already exceeded!)
- **Gold Tier 1**: 600 points (you have 662 - already exceeded!)
- **Prestige Tier 1**: 1200 points (need 538 more points)

---

## 🎉 System Features

### Real-time Processing
- ✅ Orders trigger immediate badge progression
- ✅ Points calculated instantly
- ✅ Badges awarded automatically
- ✅ Credits awarded immediately

### Visual Feedback
- ✅ Progress bars and percentages
- ✅ Badge display in profile
- ✅ Credit balance tracking
- ✅ Achievement notifications

### Community Features
- ✅ Leaderboards and rankings
- ✅ Community events access
- ✅ Social sharing integration
- ✅ Dog rescue campaign support

### Seasonal Features - NEW!
- ✅ Monthly rotating badges
- ✅ Time-limited challenges
- ✅ Seasonal rewards and privileges
- ✅ Community competitions

---

## 🏆 AmaKo Gold Plus - Premium Membership

**Note**: AmaKo Gold Plus has been repositioned as a **premium membership** rather than a gamified badge.

### Premium Features
- **Monthly Subscription**: Rs. 15,000/month
- **Invite-Only**: Application and review required
- **Lifestyle Benefits**: Not tied to gamification
- **Exclusive Access**: VIP events, early access, special menus
- **Social Impact**: 25% of revenue donated to dog rescue

### Membership vs Badge System
| Feature | Badge System | Gold Plus Membership |
|---------|-------------|---------------------|
| **Access** | Public gamification | Invite-only lifestyle |
| **Progression** | Points-based tiers | Subscription-based |
| **Rewards** | Credits and privileges | Exclusive access |
| **Duration** | Permanent achievements | Monthly subscription |
| **Focus** | Behavioral engagement | Status and prestige |

---

*This badge system is designed to reward loyalty, encourage engagement, and build a strong community around AmaKo Momo! 🥟🐕* 