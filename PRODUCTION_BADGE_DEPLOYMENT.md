# Production Badge System Deployment Guide 🚀

## 🔍 Current Status

**Production Server Shows:**
```
php artisan badges:process 1
👤 Processing: Sabs (ID: 1)
   Orders: 10
   Badges Earned: 0  ❌ This should be 12+
```

**Mobile App Shows:**
```
loyalty: {
  "badges": [],           ❌ Empty
  "badge_progress": [],   ❌ Empty
  "total_badges": 0       ❌ Should be 12+
}
```

---

## 🎯 Root Cause

Your **production server** is missing the updated files! The badge system code was fixed locally but hasn't been deployed to `amakomomo.com` yet.

---

## 📋 Complete Deployment Steps

### **Step 1: Deploy Updated Files to Production**

On your **production server** (`amakomomo.com`), run:

```bash
cd /var/www/amako-momo\(p\)/my_momo_shop

# Pull latest code
git pull origin main

# Install any new dependencies (if needed)
composer install --no-dev --optimize-autoloader
```

---

### **Step 2: Verify Files Were Updated**

Check that these files have the latest changes:

```bash
# Check if BadgeProgressionService has the "delivered" status fix
grep -n "delivered" app/Services/BadgeProgressionService.php

# Check if LoyaltyController has the badge_tiers join
grep -n "badge_tiers" app/Http/Controllers/Api/LoyaltyController.php

# Check if OrderController fires the OrderPlaced event
grep -n "OrderPlaced" app/Http/Controllers/Api/OrderController.php
```

You should see matches in all three files!

---

### **Step 3: Seed Badge System Tables**

```bash
php artisan db:seed --class=BadgeSystemSeeder
```

**Expected Output:**
```
🏆 Seeding Badge System...
✅ Badge Classes created: 2
✅ Badge Ranks created: 8
✅ Badge Tiers created: 24
🎉 Badge System seeding completed!
```

---

### **Step 4: Clear All Caches**

```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

### **Step 5: Process Existing User Badges**

Run the enhanced badge processing command:

```bash
php artisan badges:process 1
```

**Expected Output:**
```
🎯 Processing badges for 1 user(s)...

👤 Processing: Sabs (ID: 1)
   Orders: 10
   Badge Classes in DB: 2
   Processing badge progression...
   Badges Earned: 12
   Momo Loyalty: 10032 points
   Momo Engagement: 50 points
   ✅ Complete!

🎉 Badge processing complete!
```

**If you see:**
- ❌ `Badge Classes in DB: 0` → Run Step 3 again
- ❌ `Error: ...` → Copy the full error message
- ❌ `No badge progress created` → Check Laravel logs

---

### **Step 6: Verify Database**

Check that badges were created:

```bash
php artisan tinker
```

Then run:
```php
// Check badge classes
\App\Models\BadgeClass::count();
// Should return: 2

// Check user badges
\App\Models\UserBadge::where('user_id', 1)->count();
// Should return: 12 or more

// Check badge progress
\App\Models\BadgeProgress::where('user_id', 1)->get();
// Should return 2 records (Loyalty + Engagement)
```

Type `exit` to leave tinker.

---

### **Step 7: Test the API**

```bash
# Get your auth token from the app logs or database
TOKEN="11|03RWLgNGiRKaZMMqd2FP993yiZT2jGvwSlSSiwNu281116b1"

# Test the loyalty API
curl -H "Authorization: Bearer $TOKEN" \
  https://amakomomo.com/api/loyalty
```

**Expected Response:**
```json
{
  "credits": 699,
  "tier": "Bronze",
  "badges": [
    {
      "id": 1,
      "name": "Momo Loyalty - Bronze",
      "tier": "Bronze",
      "tier_level": 1,
      "icon": "🥟",
      "color": "#CD7F32",
      "earned_at": "..."
    }
    // ... 11 more badges
  ],
  "badge_progress": [
    {
      "class_name": "Momo Loyalty",
      "current_points": 10032,
      "total_points_earned": 10032
    },
    {
      "class_name": "Momo Engagement",
      "current_points": 50,
      "total_points_earned": 50
    }
  ],
  "total_badges": 12
}
```

**If you see:**
- ✅ `"total_badges": 12` → SUCCESS!
- ❌ `"total_badges": 0` → Badge processing didn't work
- ❌ 401 error → Token issue
- ❌ 500 error → Server error, check logs

---

### **Step 8: Check Laravel Logs**

If badges still aren't created, check the logs:

```bash
tail -f storage/logs/laravel.log
```

Look for errors related to:
- Badge progression
- AmaCredit
- Database queries

---

## 🐛 Common Issues & Solutions

### **Issue 1: "No badge classes found"**

**Solution:**
```bash
php artisan db:seed --class=BadgeSystemSeeder
```

---

### **Issue 2: "Call to a member function addCredits() on null"**

**Solution:**
```bash
php artisan tinker
\App\Models\AmaCredit::create(['user_id' => 1, 'balance' => 0]);
exit
```

Then run badges:process again.

---

### **Issue 3: Git pull shows conflicts**

**Solution:**
```bash
git stash
git pull origin main
git stash pop
# Resolve any conflicts
```

---

### **Issue 4: Badge progression runs but creates 0 badges**

**Possible causes:**
1. `BadgeProgressionService.php` not updated (missing "delivered" fix)
2. User has no AmaCredit record
3. Badge classes not seeded
4. Database connection issue

**Debug:**
```bash
# Check if BadgeProgressionService has the fix
grep -A 5 "whereIn.*status" app/Services/BadgeProgressionService.php
# Should show: ['completed', 'delivered', 'pending']

# Check Laravel logs
tail -100 storage/logs/laravel.log
```

---

## ✅ Success Indicators

### **On Production Server:**
```bash
php artisan badges:process 1

# Should show:
✅ Badge Classes in DB: 2
✅ Badges Earned: 12 (or more)
✅ Momo Loyalty: 10000+ points
✅ Momo Engagement: 50+ points
```

### **In Mobile App:**
After restarting the app, you should see:
```
🏆 renderBadgesTab - loyalty data: {
  "badges": [...12 badges...],
  "badgesLength": 12,
  "loyalty": {
    "badges": [...],
    "total_badges": 12,
    "badge_progress": [...]
  }
}
```

---

## 📝 Files That MUST Be Deployed

These files contain the badge fixes:

1. ✅ `app/Http/Controllers/Api/LoyaltyController.php` - Fixed badge query
2. ✅ `app/Http/Controllers/Api/OrderController.php` - Fires OrderPlaced event
3. ✅ `app/Listeners/HandleBadgeProgression.php` - Accepts delivered orders
4. ✅ `app/Services/BadgeProgressionService.php` - Counts delivered orders
5. ✅ `database/seeders/BadgeSystemSeeder.php` - Seeds badge tables
6. ✅ `app/Console/Commands/ProcessUserBadges.php` - Command to process badges

**Verify with:**
```bash
git status
git log --oneline -5
```

---

## 🚀 Quick Deploy Script

Run this complete script on production:

```bash
#!/bin/bash
cd /var/www/amako-momo\(p\)/my_momo_shop

echo "📥 Pulling latest code..."
git pull origin main

echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "🌱 Seeding badge system..."
php artisan db:seed --class=BadgeSystemSeeder

echo "🧹 Clearing caches..."
php artisan route:clear
php artisan cache:clear
php artisan config:clear

echo "🎯 Processing user badges..."
php artisan badges:process 1

echo "✅ Deployment complete!"
echo "📱 Restart your mobile app to see badges!"
```

---

## 🎊 Expected Final Result

After deployment, the mobile app should show:

**Badges Tab:**
- 🏆 **13 badges earned**
- 👑 **Platinum tier** (you have 699+ credits)
- 💰 **10,032 Loyalty points**
- 🎯 **50 Engagement points**

**Badge Cards Displayed:**
- 🥉 Bronze Tier 1, 2, 3
- 🥈 Silver Tier 1, 2, 3
- 🥇 Gold Tier 1, 2, 3
- 👑 Prestige Tier 1, 2, 3, 4 (if earned)

---

**Status**: ⏳ **Waiting for Production Deployment**  
**Next Step**: Deploy files to production server and run the commands above!

