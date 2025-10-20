# How to Debug Android APK Crashes

## Method 1: Using ADB (Android Debug Bridge)

### Step 1: Enable USB Debugging on Your Phone
1. Go to **Settings** → **About Phone**
2. Tap **Build Number** 7 times to enable Developer Mode
3. Go to **Settings** → **Developer Options**
4. Enable **USB Debugging**

### Step 2: Connect Phone and View Logs

```bash
# Connect your phone via USB

# Check if device is connected
adb devices

# Clear existing logs
adb logcat -c

# Start viewing logs (filter for React Native)
adb logcat *:E | grep -i "ReactNative"

# OR view all error logs
adb logcat *:E

# OR view specific app logs
adb logcat | grep -i "expo"
```

### Step 3: Reproduce the Crash
1. Open the APK on your phone
2. Try to login/signup
3. Watch the terminal for error logs
4. The crash log will appear in red

### Common Log Filters

```bash
# Show only errors and fatal
adb logcat *:E *:F

# Show React Native specific
adb logcat ReactNative:V ReactNativeJS:V *:S

# Show all with timestamp
adb logcat -v time

# Save logs to file
adb logcat > crash-logs.txt
```

## Method 2: Check Production API Response

The crash might be due to the API still returning old data. Let's test:

```bash
# Test the login endpoint
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "sabstha98@gmail.com",
    "password": "your-password"
  }'
```

**Expected Response (GOOD):**
```json
{
  "success": true,
  "token": "...",
  "user": {
    "id": "1",
    "name": "Sab",
    "email": "sabstha98@gmail.com",
    "phone": "sabstha98@gmail.com"
  }
}
```

**Bad Response (CAUSES CRASH):**
```json
{
  "success": true,
  "token": "...",
  "user": {
    "id": 1,
    "name": "Sab",
    "email": "sabstha98@gmail.com",
    "phone": "sabstha98@gmail.com",
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "pivot": { ... }  // ← This causes crash!
      }
    ]
  }
}
```

## Method 3: Expo Crash Logs (if configured)

If you have Sentry or expo-updates configured:

```bash
# View Expo build logs
eas build:list

# View specific build
eas build:view <build-id>
```

## Most Likely Issue

Based on your previous crashes, the issue is likely:

1. **Backend not updated** - The production API might not have the latest code
2. **PHP-FPM cache** - Old code cached in PHP-FPM
3. **Nginx cache** - Response cached by Nginx
4. **OpCache** - PHP OpCache has old bytecode

## Quick Fix Checklist

On your production server:

```bash
cd /var/www/amako-momo(p)/my_momo_shop

# 1. Check if latest code is pulled
git log -1
# Should show: "Fix legacy login route - remove roles from user response"

# 2. If not, pull latest
git pull origin main

# 3. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Clear OpCache (very important!)
php artisan optimize:clear

# 5. Restart PHP-FPM
systemctl restart php8.3-fpm

# 6. Restart Nginx
systemctl restart nginx
```

## Test the API Directly

Before testing the APK, test the API first:

```bash
# From Windows PowerShell:
Invoke-RestMethod -Uri "https://amakomomo.com/api/login" `
  -Method POST `
  -ContentType "application/json" `
  -Body (@{
    email = "sabstha98@gmail.com"
    password = "your-password"
  } | ConvertTo-Json)
```

If the response includes `roles` or `pivot`, the backend is **NOT** updated yet!

## After Fixing Backend

If you've updated the backend:

1. **Don't rebuild the APK yet** - Test with Expo Go first
2. Open the app in Expo Go (development mode)
3. Try login/signup
4. If it works in Expo, then rebuild APK

```bash
cd amako-shop
eas build --platform android --profile preview --non-interactive
```

## Common Crash Causes

1. **Serialization Error** - Trying to store complex objects in SecureStore
   - **Symptom**: Crash immediately after login
   - **Fix**: Simplify user object (remove roles, pivot)

2. **Network Error** - API returning HTML instead of JSON
   - **Symptom**: Crash with JSON parse error
   - **Fix**: Check API endpoint exists and returns JSON

3. **Null Reference** - Accessing property of undefined
   - **Symptom**: Crash when accessing user data
   - **Fix**: Add null checks

4. **Permission Issue** - Missing Android permissions
   - **Symptom**: Crash when accessing location/camera
   - **Fix**: Check AndroidManifest.xml permissions

## Next Steps

1. **Get the crash logs using adb** (most important!)
2. **Test the production API** to see if it's returning simplified user
3. **Share the crash logs** so I can see the exact error
4. If backend is not updated, pull latest code and restart services

## Quick Command to Get Logs

```bash
# One-liner to capture crash:
adb logcat -c && echo "Ready! Now open the app and reproduce the crash..." && adb logcat *:E
```

This will:
1. Clear old logs
2. Wait for you to open the app
3. Show only errors in real-time

