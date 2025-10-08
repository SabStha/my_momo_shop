# How to Refresh App to See Latest Changes

## Problem

You're seeing old cached data (like 4.5 rating) even after we fixed the code. This is because:
1. React Query caches API responses
2. Metro bundler caches JavaScript bundles
3. Expo Go caches the app on your phone

---

## Solution: Complete Refresh

### Step 1: Clear Phone Cache

**On Android (Expo Go)**:
1. Close Expo Go app completely (swipe away from recent apps)
2. Go to Settings → Apps → Expo Go
3. Tap "Storage"
4. Tap **"Clear Cache"** (NOT "Clear Data")
5. Tap **"Clear Data"** (This will clear all cached bundles)

**On iOS (Expo Go)**:
1. Close Expo Go app completely (swipe up and dismiss)
2. Uninstall Expo Go
3. Reinstall from App Store
4. Or: Go to Settings → Expo Go → Clear App Data

### Step 2: Start Metro with Clear Cache

**In your terminal** (I've already stopped it):

```powershell
cd amako-shop
npx expo start --clear
```

Or if you want tunnel mode (for iOS):
```powershell
cd amako-shop
npx expo start --tunnel --clear
```

### Step 3: Reload App on Phone

1. Wait for QR code to appear in terminal
2. Scan QR code with Expo Go (Android) or Camera app (iOS)
3. Wait for app to load
4. Once loaded, shake phone and tap "Reload"

---

## Quick Commands

```powershell
# Stop Metro
taskkill /F /IM node.exe

# Start with clear cache
cd amako-shop
npx expo start --clear

# For iOS (tunnel mode)
cd amako-shop
npx expo start --tunnel --clear
```

---

## What You Should See After Refresh

### Customer Reviews Section:
- ✅ Shows "No reviews yet" (not 4.5)
- ✅ Shows "Be the first to review!" subtitle
- ✅ Empty state with comment icon
- ✅ No fake reviews

### Home Stats:
- ✅ 0+ orders (not 1500+)
- ✅ 2+ customers (not 21+)
- ✅ "No reviews yet" (not 4.8⭐)

### Benefits Section:
- ✅ 0+ orders (not 179+)
- ✅ "Just getting started" message

### Profile:
- ✅ 0 NPR credits (not 1250)
- ✅ Bronze tier (not Silver)
- ✅ No badges

---

## If Still Showing Old Data

### Force Refresh in App:

1. **Shake your phone** (or Cmd+D on iOS simulator, Cmd+M on Android emulator)
2. Tap **"Reload"**
3. If that doesn't work, tap **"Clear Metro Cache and Reload"**

### Nuclear Option - Complete Reset:

```powershell
# 1. Stop everything
taskkill /F /IM node.exe

# 2. Clear all caches
cd amako-shop
npx expo start -c

# 3. On phone: Uninstall Expo Go, reinstall, scan QR code
```

---

## Debugging: Check What Data is Being Fetched

In the app console, you should see logs like:

```
🔍 Home Stats API Response:
{
  "orders_delivered": "0+",
  "happy_customers": "2+",
  "customer_rating": "No reviews yet"
}

🔍 Reviews API Response:
{
  "data": []
}
```

If you see old data in logs, the backend hasn't restarted. Restart Laravel:
```powershell
# Stop Laravel
Ctrl+C in Laravel terminal

# Start Laravel
php artisan serve --host=192.168.2.145 --port=8000
```

---

## Summary

The changes are already in the code. You just need to:

1. ✅ **Close Expo Go** on phone
2. ✅ **Clear Expo Go cache** (Settings → Apps → Expo Go → Storage → Clear Data)
3. ✅ **Start Metro with clear cache**: `npx expo start --clear`
4. ✅ **Scan QR code** and reload app

After this, you should see "No reviews yet" instead of 4.5! 🎉

