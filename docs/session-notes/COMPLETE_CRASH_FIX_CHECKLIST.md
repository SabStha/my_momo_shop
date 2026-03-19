# Complete APK Crash Fix Checklist

## 🚨 CRITICAL ISSUE IDENTIFIED

Your logs show the API is **STILL returning complex user object with roles**:

```json
"roles": [
  { 
    "pivot": {  // ⚠️ THIS CAUSES APK CRASH
      "model_type": "App\\Models\\User",
      "model_id": 1,
      "role_id": 1
    }
  }
]
```

## Root Cause

The backend fix is in your **local** `routes/api.php` but NOT on **production server** (or cache not cleared).

## Step-by-Step Fix

### ✅ Step 1: Verify Local Code Has Fix

```powershell
Select-String -Path "routes/api.php" -Pattern "userResponse" -Context 2,5
```

Expected output:
```php
$userResponse = [
    'id' => (string)$user->id,
    'name' => $user->name,
    'email' => $user->email,
    'phone' => $user->phone,
];
```

### ✅ Step 2: Commit and Push Changes

```bash
git status
git add routes/api.php
git add amako-shop/android/app/src/main/res/values/strings.xml
git commit -m "Fix mobile app crash: simplify user response and update app name"
git push origin main
```

### ✅ Step 3: Deploy to Production Server

SSH into production:

```bash
ssh user@amakomomo.com
cd /var/www/amako-momo\(p\)/my_momo_shop

# Pull latest code
git pull origin main

# CRITICAL: Clear ALL caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# CRITICAL: Clear PHP OPcache (this is what you're missing!)
sudo systemctl restart php8.1-fpm

# Also restart web server
sudo systemctl restart nginx

# Verify the fix is in the file
grep -A 5 "Return simplified user object" routes/api.php
```

### ✅ Step 4: Test API Returns Simplified Response

```bash
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"YOUR_PASSWORD"}' \
  | jq '.user | keys'
```

**Expected (GOOD):**
```json
[
  "email",
  "id",
  "name",
  "phone"
]
```

**Current (BAD):**
```json
[
  ...many fields...
  "roles",  // ❌ Should NOT be here
  ...
]
```

### ✅ Step 5: Once API is Fixed, Rebuild APK

```bash
cd C:\Users\user\my_momo_shop\amako-shop
eas build --platform android --profile preview --non-interactive
```

## Why It Will Crash in APK But Not Expo

| Environment | Result | Why |
|-------------|--------|-----|
| **Expo Go (Development)** | ✅ Works | More tolerant, better error handling |
| **Production APK** | ❌ Crashes | SecureStore can't serialize complex objects with circular refs |

The complex user object with nested `roles` and `pivot` data:
- Has circular references
- Exceeds SecureStore size limits
- Can't be stringified properly
- Causes JSON.stringify to fail in production

## Verification Checklist

Before rebuilding APK, verify:

- [ ] Local `routes/api.php` has `$userResponse` with only 4 fields
- [ ] Changes are committed and pushed to git
- [ ] Production server pulled latest code (`git pull`)
- [ ] Production caches cleared (`php artisan optimize:clear`)
- [ ] **PHP-FPM restarted** (`sudo systemctl restart php8.1-fpm`)
- [ ] **Web server restarted** (`sudo systemctl restart nginx`)
- [ ] API test returns simplified user object (no "roles" field)

## Test Commands

### On Production Server:

```bash
# Check git status
git log --oneline -3

# Check if file has the fix
grep "userResponse" routes/api.php

# Clear caches
php artisan optimize:clear
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# Test API
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password"}' \
  | jq '.user.roles'
```

If the last command returns `null` instead of an array, the fix is working!

## Current Situation

Based on your logs:

❌ **Production API NOT updated** - Still returning complex object  
✅ **Local code has fix** - routes/api.php is correct  
✅ **App name fixed** - strings.xml updated  
⏳ **Waiting for** - Production cache clear and restart  

## What Happens After Fix

### Development (Expo Go):
```
Login → Store token → Navigate to home → ✅ Works
```

### Production APK (Before Fix):
```
Login → Try to store token → SecureStore fails → 💥 CRASH
```

### Production APK (After Fix):
```
Login → Store simple token → Navigate to home → ✅ Works
```

## Immediate Action Required

You MUST do this on your production server NOW:

```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan optimize:clear
sudo systemctl restart php8.1-fpm
```

Then test the API with curl. Once it returns the simplified user object (without roles), rebuild your APK and it will work!

## Summary

The crash fix is complete in your local code but **NOT deployed** to production yet. The logs prove the API is still returning the problematic complex object. Deploy the fix to production, clear caches, restart PHP-FPM, then rebuild the APK.

