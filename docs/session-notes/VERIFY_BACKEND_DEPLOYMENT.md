# Verify Backend Deployment - Mobile App Crash Fix

## The Problem

Your logs show the login API is **STILL returning the complex user object with roles**:

```json
"user": {
  "roles": [
    {
      "pivot": {  // ⚠️ THIS CAUSES THE CRASH
        "model_type": "App\\Models\\User",
        ...
      }
    }
  ]
}
```

But your local code shows the fix IS there. This means **production server hasn't applied the changes**.

## Steps to Fix

### 1. Verify Git Status on Production

SSH into your server and check:

```bash
ssh user@amakomomo.com
cd /var/www/amako-momo\(p\)/my_momo_shop
git status
git log --oneline -3
```

### 2. Pull Latest Changes

```bash
git pull origin main
```

### 3. Clear ALL Caches (CRITICAL!)

```bash
# Clear application caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Clear OPcache (PHP compiled code cache)
sudo systemctl restart php8.1-fpm

# Or if using php-fpm
sudo service php8.1-fpm restart

# Also restart web server
sudo systemctl restart nginx
```

### 4. Verify the Code Was Actually Deployed

Check if the file has the fix:

```bash
grep -A 5 "Return simplified user object" routes/api.php
```

You should see:

```php
// Return simplified user object to avoid serialization issues
$userResponse = [
    'id' => (string)$user->id,
    'name' => $user->name,
    'email' => $user->email,
    'phone' => $user->phone,
];
```

### 5. Test the API Directly

```bash
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"YOUR_PASSWORD"}' \
  | jq '.user'
```

**Expected Result (GOOD):**
```json
{
  "id": "1",
  "name": "Sab",
  "email": "sabstha98@gmail.com",
  "phone": "sabstha98@gmail.com"
}
```

**Bad Result (STILL BROKEN):**
```json
{
  "id": 1,
  "name": "Sab",
  ...
  "roles": [...]  // ❌ Should NOT have this
}
```

## If Changes Are There But Still Not Working

### Check PHP OPcache

The PHP compiler might be caching the old code:

```bash
# Check if OPcache is enabled
php -i | grep opcache.enable

# Restart PHP to clear it
sudo systemctl restart php8.1-fpm

# Or manually reset OPcache
php -r "opcache_reset();"
```

### Check File Permissions

```bash
ls -la routes/api.php
# Should be readable by www-data or your web server user
```

### Check for Multiple PHP Versions

```bash
which php
php --version

# Make sure artisan uses the same PHP version as your web server
/usr/bin/php8.1 artisan optimize:clear
```

## Common Deployment Issues

### Issue 1: Git Pull Didn't Work
**Symptoms:** `git status` shows "Your branch is behind"

**Fix:**
```bash
git fetch origin
git reset --hard origin/main
```

### Issue 2: File Not Actually Changed
**Symptoms:** `grep` doesn't find the new code

**Fix:**
```bash
# Force update
git fetch --all
git reset --hard origin/main
git pull origin main --force
```

### Issue 3: Cache Not Cleared
**Symptoms:** API still returns old format after git pull

**Fix:**
```bash
# Nuclear option - clear everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
rm -rf bootstrap/cache/*
composer dump-autoload
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

## Quick Test Script

I've created `test-login-api.sh` - run it to test your API:

```bash
chmod +x test-login-api.sh
./test-login-api.sh
```

## Why This Is Critical

The complex user object with nested `roles` array causes:
- ❌ **SecureStore serialization errors** in production APK
- ❌ **App crashes** when storing token after login/signup
- ❌ **Data too large** for secure storage
- ❌ **Circular references** that break JSON stringify

The simplified version:
- ✅ **Simple flat object** - easy to serialize
- ✅ **Small footprint** - fits in SecureStore
- ✅ **No circular refs** - clean JSON
- ✅ **Works in production builds** - no crashes

## After Fixing Production

1. Test the API curl command above
2. Verify it returns ONLY `id`, `name`, `email`, `phone`
3. Rebuild your APK:
   ```bash
   cd amako-shop
   eas build --platform android --profile preview
   ```
4. Install and test sign-up/login

## Expected Timeline

- Git pull: 10 seconds
- Clear caches: 30 seconds
- Restart services: 1 minute
- Total: ~2 minutes

Then your mobile app crash will be fixed! 🎉

