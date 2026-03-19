# Fix: Notifications Showing "Expo Go" Instead of "Amako Momo"

## Problem
Notifications show "from Expo Go" instead of "from Amako Momo" even in development builds.

## Solution Applied

### 1. Updated Notification Channel Names

Changed all notification channel names from generic names to "Amako Momo" branded names:

**Before:**
- `name: 'Orders (important)'`
- `name: 'Orders (silent)'`
- `name: 'default'`

**After:**
- `name: 'Amako Momo - Delivery'`
- `name: 'Amako Momo - Orders'`
- `name: 'Amako Momo'`

### 2. Files Modified

✅ `amako-shop/src/notifications/delivery-notifications.ts`
✅ `amako-shop/src/notifications/NotificationsProvider.tsx`

## How to Apply Changes

### For Development Build:

1. **Rebuild your app** (notification channels are set on app install):
   ```bash
   cd amako-shop
   
   # Clear cache
   npx expo start --clear
   
   # Rebuild
   eas build --profile development --platform android
   ```

2. **Or uninstall and reinstall** current build:
   - Uninstall Amako Momo app from device
   - Reinstall the development build
   - Login again
   - Test notifications

### For Quick Test (Without Rebuild):

Android notification channels are **created once** when app installs. To update them without rebuilding:

1. **Uninstall the app** from your device
2. **Reinstall** it
3. **Login** again
4. **Test notifications**

The app name should now show as "Amako Momo" instead of "Expo Go"!

## Verify It Works

Run your test again:
```bash
./test-notifications.sh
# Choose option 2 (Delivery notification)
```

You should now see:
```
From: Amako Momo        ✅ (not "Expo Go")
🛵 Delivery started — ETA 18-22 min
Rider Suman picked up • ORD-68F69EC
```

## Why This Happens

- **Notification channels** are created on app installation
- The channel name is what shows as "from X app"
- Once created, channels persist until app uninstall
- Therefore: Need to uninstall/reinstall or rebuild to see changes

## For Production Build

When you build for production, these channel names will automatically be set correctly:

```bash
eas build --profile production --platform android
```

Production builds will always show "Amako Momo" (not Expo Go) because they use your app's identity.

## Note

This is **only an issue in development builds**. Production builds from EAS/Google Play will always show your app name correctly.

---

**Quick Fix:** Uninstall app → Reinstall → Test notifications → Should show "Amako Momo" ✅




