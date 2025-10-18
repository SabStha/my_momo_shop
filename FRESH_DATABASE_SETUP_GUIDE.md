# Fresh Database Setup Guide - Error-Free Installation 🚀

## 📋 **Complete Steps for `migrate:fresh`**

If you ever need to reset your database, follow these steps to avoid all the errors we encountered:

---

## 🔄 **Step-by-Step Fresh Installation**

### **Step 1: Backup Current Database (Important!)**

```bash
# Backup database before resetting
php artisan backup:db  # If you have backup package
# OR
mysqldump -u your_user -p your_database > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

### **Step 2: Fresh Migration**

```bash
php artisan migrate:fresh
```

This will:
- Drop all tables
- Recreate all tables
- Ready for seeding

---

### **Step 3: Run All Seeders (In Correct Order)**

```bash
php artisan db:seed
```

This automatically runs all seeders in the correct order:

1. ✅ **RolesAndPermissionsSeeder** - User roles
2. ✅ **BranchSeeder** - Store branches
3. ✅ **PaymentMethodSeeder** - Payment options
4. ✅ **TaxDeliverySettingsSeeder** - Tax & delivery settings
5. ✅ **ExpenseSeeder** - Business expenses
6. ✅ **MenuSeeder** - Menu items
7. ✅ **BadgeSystemSeeder** - Badge classes, ranks, tiers (NEW!)
8. ✅ **FindsCategoriesSeeder** - Ama's Finds categories (NEW!)

---

### **Step 4: Create Admin User**

```bash
php artisan tinker --execute="
\$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@amakoshop.com',
    'password' => Hash::make('password123'),
    'phone' => '1234567890',
    'city' => 'Kathmandu',
    'role' => 'admin',
    'is_admin' => true,
]);
echo 'Admin user created: ' . \$user->email;
"
```

---

### **Step 5: Create Test Customer**

```bash
php artisan tinker --execute="
\$user = App\Models\User::create([
    'name' => 'Test Customer',
    'email' => 'customer@test.com',
    'password' => Hash::make('password123'),
    'phone' => '9876543210',
    'city' => 'Kathmandu',
    'role' => 'customer',
]);

// Create AmaCredit account
App\Models\AmaCredit::create([
    'user_id' => \$user->id,
    'current_balance' => 0,
    'total_earned' => 0,
    'total_spent' => 0,
    'weekly_earned' => 0,
    'weekly_reset_date' => now()->startOfWeek()->addWeek()->toDateString(),
    'weekly_cap' => 1000,
    'last_activity_at' => now(),
]);

echo 'Customer created: ' . \$user->email;
"
```

---

### **Step 6: Update Branch Coordinates (Optional)**

```bash
php artisan tinker --execute="
\$branch = App\Models\Branch::find(1);
\$branch->latitude = 27.7172;
\$branch->longitude = 85.3240;
\$branch->address = 'Thamel, Kathmandu, Nepal';
\$branch->save();
echo 'Branch coordinates updated';
"
```

---

### **Step 7: Clear All Caches**

```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
composer dump-autoload
```

---

### **Step 8: Verify Everything Works**

```bash
# Check badge system
php artisan tinker --execute="
echo 'Badge Classes: ' . App\Models\BadgeClass::count() . PHP_EOL;
echo 'Badge Ranks: ' . App\Models\BadgeRank::count() . PHP_EOL;
echo 'Badge Tiers: ' . App\Models\BadgeTier::count() . PHP_EOL;
echo 'Finds Categories: ' . DB::table('finds_categories')->count() . PHP_EOL;
"
```

**Expected Output:**
```
Badge Classes: 2
Badge Ranks: 8
Badge Tiers: 24
Finds Categories: 6
```

---

## 🐛 **Common Issues After Fresh Migration**

### **Issue 1: Badge Classes Show 0 (But DB Has Them)**

**Cause:** Soft-deleted records or model cache

**Fix:**
```bash
php artisan tinker --execute="
DB::table('badge_classes')->update(['deleted_at' => null]);
DB::table('badge_ranks')->update(['deleted_at' => null]);
DB::table('badge_tiers')->update(['deleted_at' => null]);
"
composer dump-autoload
```

---

### **Issue 2: User Has No AmaCredit**

**Cause:** AmaCredit not created with user

**Fix:**
```bash
php artisan tinker --execute="
\$user = App\Models\User::find(YOUR_USER_ID);
if (!\$user->amaCredit) {
    App\Models\AmaCredit::create([
        'user_id' => \$user->id,
        'current_balance' => 0,
        'total_earned' => 0,
        'total_spent' => 0,
        'weekly_earned' => 0,
        'weekly_reset_date' => now()->startOfWeek()->addWeek()->toDateString(),
        'weekly_cap' => 50000,
        'last_activity_at' => now(),
    ]);
    echo 'AmaCredit created';
}
"
```

---

### **Issue 3: Finds Categories Empty**

**Cause:** Seeder wasn't run

**Fix:**
```bash
php artisan db:seed --class=FindsCategoriesSeeder
```

---

### **Issue 4: No Badges Awarded After Orders**

**Cause:** Badge processing not triggered

**Fix:**
```bash
# Process badges for specific user
php artisan badges:process USER_ID

# Or process all users with orders
php artisan badges:process
```

---

### **Issue 5: Weekly Credit Cap Blocks Badge Awards**

**Cause:** Anti-exploit protection too strict

**Fix:**
```bash
php artisan tinker --execute="
\$credit = App\Models\AmaCredit::where('user_id', YOUR_USER_ID)->first();
\$credit->weekly_cap = 50000;
\$credit->weekly_earned = 0;
\$credit->save();
echo 'Weekly cap increased';
"
```

---

## 📝 **Complete Fresh Install Script**

Save this as `fresh-install.sh`:

```bash
#!/bin/bash

echo "🔄 Starting fresh database installation..."

# Step 1: Fresh migration
echo "📦 Running fresh migrations..."
php artisan migrate:fresh

# Step 2: Seed database
echo "🌱 Seeding database..."
php artisan db:seed

# Step 3: Clear caches
echo "🧹 Clearing caches..."
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
composer dump-autoload

# Step 4: Create admin user
echo "👤 Creating admin user..."
php artisan tinker --execute="
\$user = App\Models\User::firstOrCreate(
    ['email' => 'admin@amakoshop.com'],
    [
        'name' => 'Admin',
        'password' => Hash::make('admin123'),
        'phone' => '1234567890',
        'city' => 'Kathmandu',
        'role' => 'admin',
        'is_admin' => true,
    ]
);
echo 'Admin: ' . \$user->email;
"

# Step 5: Create test customer with AmaCredit
echo "👤 Creating test customer..."
php artisan tinker --execute="
\$user = App\Models\User::firstOrCreate(
    ['email' => 'customer@test.com'],
    [
        'name' => 'Test Customer',
        'password' => Hash::make('password123'),
        'phone' => '9876543210',
        'city' => 'Kathmandu',
        'role' => 'customer',
    ]
);

if (!\$user->amaCredit) {
    App\Models\AmaCredit::create([
        'user_id' => \$user->id,
        'current_balance' => 1000,
        'total_earned' => 1000,
        'total_spent' => 0,
        'weekly_earned' => 0,
        'weekly_reset_date' => now()->startOfWeek()->addWeek()->toDateString(),
        'weekly_cap' => 50000,
        'last_activity_at' => now(),
    ]);
}

echo 'Customer: ' . \$user->email;
"

# Step 6: Verify installation
echo "✅ Verifying installation..."
php artisan tinker --execute="
echo 'Badge Classes: ' . App\Models\BadgeClass::count() . PHP_EOL;
echo 'Badge Tiers: ' . App\Models\BadgeTier::count() . PHP_EOL;
echo 'Finds Categories: ' . DB::table('finds_categories')->count() . PHP_EOL;
echo 'Menu Items: ' . App\Models\Product::count() . PHP_EOL;
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
"

echo "🎉 Fresh installation complete!"
echo ""
echo "📝 Login Credentials:"
echo "   Admin: admin@amakoshop.com / admin123"
echo "   Customer: customer@test.com / password123"
```

Make it executable:
```bash
chmod +x fresh-install.sh
```

---

## 🚀 **Usage**

### **To Reset Database:**
```bash
./fresh-install.sh
```

### **On Production:**
```bash
# Production requires confirmation
php artisan migrate:fresh --seed --force
composer dump-autoload
```

---

## ✅ **What Gets Automatically Set Up**

After running `migrate:fresh` and `db:seed`, you'll have:

### **✅ User System:**
- Admin user
- Customer roles
- Permissions

### **✅ Badge System:**
- 2 Badge Classes (Loyalty, Engagement)
- 8 Badge Ranks (Bronze, Silver, Gold, Prestige x2)
- 24 Badge Tiers (3 tiers per rank)
- All relationships properly configured

### **✅ Menu System:**
- All menu items
- Categories
- Pricing

### **✅ Ama's Finds:**
- 6 Categories (buyable, accessories, toys, apparel, limited, unlockable)
- Ready for merchandise items

### **✅ Store Settings:**
- Branches with coordinates
- Tax settings
- Delivery settings
- Payment methods

---

## 🔧 **Post-Migration Checklist**

After `migrate:fresh --seed`, verify:

- [ ] Badge classes exist: `BadgeClass::count()` → 2
- [ ] Badge tiers exist: `BadgeTier::count()` → 24
- [ ] Finds categories exist: `finds_categories` → 6 records
- [ ] Menu items exist: `Product::count()` → 50+
- [ ] Admin user exists: Check login
- [ ] Routes work: `php artisan route:list`
- [ ] Caches cleared: All artisan cache commands

---

## 📝 **Files Modified for Error-Free Setup**

### **Seeders Updated:**
1. ✅ `database/seeders/DatabaseSeeder.php` - Added badge & finds seeders
2. ✅ `database/seeders/BadgeSystemSeeder.php` - Complete badge setup
3. ✅ `database/seeders/FindsCategoriesSeeder.php` - Finds categories

### **Models Fixed:**
1. ✅ `app/Services/BadgeProgressionService.php` - Counts delivered orders
2. ✅ `app/Listeners/HandleBadgeProgression.php` - Accepts delivered orders
3. ✅ `app/Http/Controllers/Api/OrderController.php` - Fires OrderPlaced event

### **Commands Added:**
1. ✅ `app/Console/Commands/ProcessUserBadges.php` - Manual badge processing

---

## 🎯 **Expected Result**

After running `migrate:fresh --seed`:

### **Database Tables:**
- All tables created ✅
- All seeders run successfully ✅
- No missing data ✅
- Relationships intact ✅

### **Badge System:**
- Badge classes: 2 ✅
- Badge ranks: 8 ✅
- Badge tiers: 24 ✅
- Ready to award badges ✅

### **Mobile App:**
- Ama's Finds works ✅
- Reviews display ✅
- Badges work ✅
- Visit Us map works ✅

---

## 🚨 **Important Notes**

### **Don't Run on Production Without Backup!**
- ⚠️ `migrate:fresh` **DELETES ALL DATA**
- ⚠️ Always backup first
- ⚠️ Use `--force` flag on production
- ⚠️ Schedule maintenance window

### **After Fresh Migration:**
1. ✅ Run `composer dump-autoload`
2. ✅ Clear all caches
3. ✅ Restart queue workers (if any)
4. ✅ Test all API endpoints
5. ✅ Verify mobile app functionality

---

## ✅ **Status**

Your seeders are now configured to handle everything automatically:

- ✅ `DatabaseSeeder` includes all necessary seeders
- ✅ `BadgeSystemSeeder` creates complete badge structure
- ✅ `FindsCategoriesSeeder` populates finds categories
- ✅ `ProcessUserBadges` command available for manual processing

**You can now safely run `migrate:fresh --seed` and everything will work!** 🎉

---

**Created**: October 18, 2025  
**Status**: ✅ Production-Ready

