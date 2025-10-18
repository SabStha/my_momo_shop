# Deploy Crash Fix NOW - Step by Step

## 🚨 THE PROBLEM

Your login API is **STILL returning this**:

```json
"user": {
  "roles": [
    { "pivot": { ... } }  // ⚠️ CRASHES APK
  ]
}
```

Instead of this:

```json
"user": {
  "id": "1",
  "name": "Sab",
  "email": "sabstha98@gmail.com",
  "phone": "sabstha98@gmail.com"
}
```

## ✅ VERIFIED

- Local code has the fix ✅
- Changes are in `routes/api.php` ✅
- App name fixed in `strings.xml` ✅

## 🎯 DEPLOY TO PRODUCTION NOW

### Option 1: If You Have SSH Access

Run these commands on your production server:

```bash
# 1. SSH into server
ssh user@amakomomo.com

# 2. Navigate to project
cd /var/www/amako-momo\(p\)/my_momo_shop

# 3. Pull latest code
git pull origin main

# 4. Clear Laravel caches
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear

# 5. CRITICAL: Restart PHP-FPM to clear OPcache
sudo systemctl restart php8.1-fpm

# 6. Restart Nginx
sudo systemctl restart nginx

# 7. Verify fix
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"YOUR_PASSWORD"}' \
  | grep -o '"roles"'

# If "roles" is found, the fix didn't work. If nothing, it's fixed!
```

### Option 2: If Using Control Panel/cPanel

1. Upload `routes/api.php` via FTP/File Manager
2. Go to PHP Selector → Reset OPcache
3. Restart PHP processes
4. Test the API

### Option 3: Quick Local Test

If you're running the server locally:

```bash
# Stop Laravel server
# (Ctrl+C if running)

# Clear caches
php artisan optimize:clear

# Restart server
php artisan serve
```

Then test:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"YOUR_PASSWORD"}'
```

## 🧪 TEST THE API FIX

Run this PowerShell script:

```powershell
cd C:\Users\user\my_momo_shop
.\test-production-api.ps1
```

### Expected Output (GOOD):

```
✅ GOOD: User object does NOT contain 'roles'
User object keys:
  - id
  - name  
  - email
  - phone

✅ Backend fix is working!
```

### Current Output (BAD):

```
❌ PROBLEM: User object contains 'roles' array
   This WILL CAUSE CRASH in production APK!
   
❌ Backend fix NOT applied or cache NOT cleared
```

## 📱 AFTER API IS FIXED

### 1. Rebuild APK

```bash
cd C:\Users\user\my_momo_shop\amako-shop
eas build --platform android --profile preview --non-interactive
```

### 2. Download and Install New APK

### 3. Test Sign Up / Login

Should work without crashing! 🎉

## 🔍 WHY YOUR CURRENT DEPLOY DIDN'T WORK

You said you "updated hosting server" but the logs prove it's not working. Common reasons:

1. **OPcache Not Cleared**
   - PHP compiles code and caches it
   - Even if you upload new files, PHP uses old cached version
   - **Solution:** Restart PHP-FPM

2. **Laravel Route Cache**
   - Routes are cached in `bootstrap/cache/routes-v7.php`
   - **Solution:** `php artisan route:clear`

3. **Config Cache**
   - Configs cached in `bootstrap/cache/config.php`
   - **Solution:** `php artisan config:clear`

4. **Git Pull Didn't Work**
   - File conflicts or permissions
   - **Solution:** `git reset --hard origin/main`

## 🎯 THE SMOKING GUN

Your log shows:

```
LOG  🔐 Login API Response: {
  "user": {
    ...
    "roles": [ ... ]  // ⚠️ THIS SHOULD NOT BE HERE
  }
}
```

This PROVES the production server doesn't have the fix applied.

## ⚡ QUICK FIX (30 seconds)

If you have SSH access, copy-paste this entire command:

```bash
ssh user@amakomomo.com "cd /var/www/amako-momo\(p\)/my_momo_shop && git pull origin main && php artisan optimize:clear && sudo systemctl restart php8.1-fpm && echo '✅ Done!'"
```

Then test with:

```bash
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"YOUR_PASSWORD"}' \
  | jq '.user.roles'
```

If it returns `null`, you're good to rebuild!

## 📊 CURRENT STATUS

| Item | Status | Notes |
|------|--------|-------|
| Local code | ✅ Fixed | `routes/api.php` has simplified response |
| App name | ✅ Fixed | Changed to "AmakoMomo" |
| Production backend | ❌ NOT DEPLOYED | Still returns complex object |
| Cache cleared | ❌ NOT CLEARED | OPcache still has old code |
| APK ready | ❌ NOT YET | Must fix backend first |

## 🎬 WHAT WILL HAPPEN

### Current Scenario (APK will crash):

1. User clicks "Sign Up"
2. App calls API: `POST /auth/register`
3. API returns huge object with roles/pivot
4. App tries to store in SecureStore
5. SecureStore fails to serialize
6. **💥 APP CRASHES**

### After Fix (APK will work):

1. User clicks "Sign Up"  
2. App calls API: `POST /auth/register`
3. API returns simple object: `{id, name, email, phone}`
4. App stores in SecureStore successfully
5. App navigates to home
6. **✅ SUCCESS!**

## NEXT STEPS

1. ⚠️ **Deploy backend** to production (see commands above)
2. ⚠️ **Clear caches** especially PHP-FPM
3. ✅ **Test API** response format
4. ✅ **Rebuild APK** once API is fixed
5. ✅ **Test sign-up** in APK - should work!

Don't rebuild the APK until the API is fixed - it will just crash again!

