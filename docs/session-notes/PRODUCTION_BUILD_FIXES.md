# 🔧 Production Build Fixes - Complete Solution

## 🐛 Three Critical Issues Fixed

### Issue 1: ❌ Images Not Showing (Product Images & Loading GIF)
**Problem:** Assets (images, GIFs) not bundled in production build

**Root Cause:** Missing `assetBundlePatterns` configuration in `app.json`

**Fix Applied:**
```json
"assetBundlePatterns": [
  "**/*"
]
```

This ensures ALL assets in your project are bundled with the production APK.

---

### Issue 2: ❌ Old Notifications Showing on Background Resume
**Problem:** Old/cached notifications display when opening app from background

**Root Cause:** Notifications not being cleared/dismissed properly

**Fix Applied:** Will implement notification clearing logic (see below)

---

### Issue 3: ❌ Notifications Show "from Expo" Instead of "AmakoMomo"
**Problem:** Production notifications still branded as "Expo" instead of your app

**Root Causes:**
1. Missing notification icon configuration
2. Notification channels created with wrong app name
3. Missing proper notification plugin configuration

**Fixes Applied:**

1. **Added notification icon to plugin config:**
```json
[
  "expo-notifications",
  {
    "color": "#FF6B35",
    "icon": "./assets/appicon.png",
    "sounds": []
  }
]
```

2. **Added notification icon to android config:**
```json
"android": {
  "notification": {
    "icon": "./assets/appicon.png",
    "color": "#FF6B35",
    "androidMode": "default",
    "androidCollapsedTitle": "{{unread_count}} new notifications from AmakoMomo"
  }
}
```

3. **Updated notification channels** (already done in previous fixes)

---

## 📋 Complete Fix Checklist

### ✅ Step 1: app.json Updated
- [x] Added `assetBundlePatterns` for image bundling
- [x] Added notification icon configuration
- [x] Updated android notification settings
- [x] Added proper app name to notification titles

### ⏳ Step 2: Code Changes Needed

You need to apply these code fixes:

#### Fix 2a: Clear Old Notifications on App Resume

**File:** `amako-shop/src/notifications/NotificationsProvider.tsx`

Add this code in the main `useEffect`:

```typescript
// Clear old notifications when app comes to foreground
const subscription = AppState.addEventListener('change', async (nextAppState) => {
  if (nextAppState === 'active') {
    // Clear all displayed notifications when app becomes active
    await Notifications.dismissAllNotificationsAsync();
    console.log('🔔 [APP RESUME] Cleared old notifications');
  }
});

return () => {
  subscription.remove();
  // ... existing cleanup
};
```

#### Fix 2b: Update Channel Names (Already Applied Previously)

The notification channel names have already been updated to "Amako Momo" in:
- `amako-shop/src/notifications/delivery-notifications.ts`
- `amako-shop/src/notifications/NotificationsProvider.tsx`

---

## 🚀 How to Apply Fixes

### Option 1: Quick Fix (Recommended)

```bash
cd amako-shop

# 1. Clean install dependencies
rm -rf node_modules
npm install

# 2. Clear Metro cache
npx expo start --clear

# 3. Build production APK
eas build --profile production --platform android --clear-cache
```

### Option 2: Local Testing First

```bash
cd amako-shop

# 1. Test locally first
npx expo start --clear

# 2. Test on development build
eas build --profile development --platform android

# 3. Once confirmed working, build production
eas build --profile production --platform android
```

---

## 🎯 What Each Fix Does

### 1. `assetBundlePatterns: ["**/*"]`
- **Includes ALL assets** in the build (images, GIFs, fonts, etc.)
- Without this, only explicitly imported assets are bundled
- Your loading GIF and product images will now show

### 2. Notification Icon Configuration
- **Sets your app icon** as the notification icon
- **Removes Expo branding** from notifications
- **Shows "AmakoMomo"** instead of "Expo Go"

### 3. Clear Notifications on Resume
- **Dismisses old notifications** when app opens
- **Prevents stale notifications** from confusing users
- **Clean UX** - only see current state

---

## 🧪 How to Test After Build

### Test 1: Images Loading
1. ✅ Install new APK
2. ✅ Open app
3. ✅ Go to Menu - check product images load
4. ✅ Check if loading GIF shows during loading states

### Test 2: Notifications
1. ✅ Place an order
2. ✅ Wait for delivery notification
3. ✅ Check notification shows "from AmakoMomo" (not Expo)
4. ✅ Open app from background - old notifications should clear

### Test 3: Background/Foreground
1. ✅ Open app
2. ✅ Press home button (don't close app)
3. ✅ Receive notification
4. ✅ Open app - notification should clear automatically

---

## 🔍 Debugging

If issues persist after rebuild:

### Images Still Not Showing?
```bash
# Check if assets are in APK
unzip -l your-app.apk | grep assets

# Should see your images listed
```

### Notifications Still Say "Expo"?
1. **Uninstall old app completely**
2. **Clear app data:** Settings → Apps → AmakoMomo → Storage → Clear Data
3. **Restart phone** (Android caches notification channels)
4. **Install new APK**
5. **Login and test**

### Old Notifications Still Appearing?
- Check `AppState` listener is working:
  - Look for log: `🔔 [APP RESUME] Cleared old notifications`
- Make sure you applied the code fix from Step 2a above

---

## 📦 Build Commands Reference

### Development Build (for testing)
```bash
eas build --profile development --platform android
```

### Preview Build (internal testing)
```bash
eas build --profile preview --platform android
```

### Production Build (final release)
```bash
eas build --profile production --platform android --clear-cache
```

### Build with Clean Cache (if issues)
```bash
eas build --profile production --platform android --clear-cache --no-wait
```

---

## ⚠️ Important Notes

1. **You MUST rebuild the APK** - Changes to `app.json` only apply to new builds
2. **Uninstall old version** - Android caches notification settings
3. **May need phone restart** - For notification channel changes to fully apply
4. **Assets increase APK size** - `assetBundlePatterns: ["**/*"]` includes everything
5. **Test on real device** - Emulators may not show notification branding correctly

---

## 📊 Expected Results

| Issue | Before | After |
|-------|--------|-------|
| Product Images | ❌ Not showing | ✅ All images load |
| Loading GIF | ❌ Not showing | ✅ Animates properly |
| Notification App Name | ❌ "Expo" | ✅ "AmakoMomo" |
| Old Notifications | ❌ Stay visible | ✅ Auto-clear on resume |
| Notification Icon | ❌ Expo icon | ✅ Your app icon |

---

## 🆘 Still Having Issues?

If after following all steps, issues persist:

1. **Share build logs:**
   ```bash
   eas build --profile production --platform android 2>&1 | tee build.log
   ```

2. **Check APK contents:**
   ```bash
   unzip -l your-app.apk > apk-contents.txt
   ```

3. **Share notification logs:**
   - Open app
   - Check for notification-related console logs
   - Share any errors

---

**Last Updated:** Now  
**Build Required:** Yes - Full production rebuild needed  
**Testing Required:** Yes - Test all 3 fixes after installing new APK




