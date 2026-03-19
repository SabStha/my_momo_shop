# 🚀 Build Fresh APK - Step by Step Guide

## ⚠️ IMPORTANT: Read This First

Your **current codebase has all the login crash fixes**, but the logs you provided are from an **OLD BUILD** that doesn't have these fixes. This guide will help you build and test a **FRESH APK** correctly.

## 🎯 What's in Your Current Code (Already Fixed)

✅ Login crash fixes (race condition prevention)
✅ Token propagation delays (1000ms)
✅ 401 error handling improvements (threshold 3→5)
✅ Video codec error handling
✅ Navigation error handling with fallbacks

## 📋 Step-by-Step Build Process

### Step 1: Uninstall Old APK
```bash
# Option A: On device
Settings > Apps > Amako Shop > Uninstall

# Option B: Via ADB
adb uninstall com.amako.shop
```

### Step 2: Clear Old Logs
```bash
adb logcat -c
```

### Step 3: Clean Build Artifacts
```bash
cd amako-shop
rm -rf android/build
rm -rf android/app/build
rm -rf android/.gradle
rm -rf node_modules/.cache
```

### Step 4: Build New APK
```bash
# Build with EAS (recommended)
eas build --platform android --profile preview --clear-cache

# This will:
# - Clean build cache
# - Use latest code
# - Build fresh APK
# - Upload to expo.dev
```

**Expected Output:**
```
✔ Build successfully started
Build ID: [some-id]
Build URL: https://expo.dev/accounts/[your-account]/projects/amako-shop/builds/[build-id]
```

### Step 5: Monitor Build Progress
1. Open the Build URL from Step 4
2. Watch for any build errors
3. Wait for "Build finished" status
4. Download APK when ready

**Build Time:** Usually 10-20 minutes

### Step 6: Download Fresh APK
```bash
# From expo.dev dashboard, click "Download" button
# Save to a known location, e.g.:
# C:\Users\user\Downloads\amako-shop-build-xxx.apk
```

### Step 7: Install Fresh APK
```bash
# Replace the path with your actual APK path
adb install -r "C:\Users\user\Downloads\amako-shop-build-xxx.apk"
```

**Expected Output:**
```
Performing Streamed Install
Success
```

### Step 8: Start Fresh Log Capture
```bash
# Open a NEW terminal window
adb logcat > fresh_build_test.log
```

**Keep this terminal running** while you test!

### Step 9: Test the App (Fresh Build)

1. **Launch App**
   - Open Amako Shop on device
   - Watch for splash screen / video

2. **Test Login** (Most Important)
   ```
   - Enter valid credentials
   - Click login
   - Watch console logs for:
     ✅ "🔐 Login in progress: true"
     ✅ "🔐 401 counter reset"
     ✅ "Token propagation delay complete"
     ✅ "🔐 Login in progress: false"
     ✅ "Navigation successful"
   ```

3. **Test After Login**
   - Verify home screen loads
   - Check cart icon works
   - Check notifications
   - Navigate between tabs

4. **Test Checkout**
   - Add item to cart
   - Go to checkout
   - Fill phone number
   - **Watch for**: `Cannot read property 'phone' of null`

5. **Test Map Features**
   - Go to "Visit Us" or any map screen
   - **Watch for**: `API key not found`

### Step 10: Stop Log Capture
```bash
# In the terminal running logcat, press Ctrl+C
```

### Step 11: Analyze Fresh Logs

**Search for critical errors:**
```bash
# Search for crashes
grep -i "fatal\|crash\|exception" fresh_build_test.log

# Search for login issues
grep -i "login\|401\|unauthorized" fresh_build_test.log

# Search for API errors
grep -i "api key\|cannot read property" fresh_build_test.log
```

## 🔍 What to Look For in Fresh Logs

### ✅ **GOOD Signs (No Issues)**
```
🔐 Login in progress: true
🔐 401 counter reset
Token propagation delay complete
🔐 Login in progress: false
Navigation successful
🎬 Welcome GIF loaded
🎬 Close GIF loaded
```

### ❌ **BAD Signs (Still Has Issues)**
```
Fatal signal 6 (SIGABRT)
Cannot read property 'phone' of null
API key not found
Multiple 401 errors detected - logging out
java.lang.OutOfMemoryError
```

## 🐛 Troubleshooting

### Issue: Build Fails
**Error:** `build command failed`

**Solution:**
```bash
cd amako-shop
# Clean more aggressively
rm -rf android node_modules
npm install
eas build --platform android --profile preview --clear-cache
```

### Issue: APK Install Fails
**Error:** `INSTALL_FAILED_UPDATE_INCOMPATIBLE`

**Solution:**
```bash
# Completely uninstall old version first
adb uninstall com.amako.shop
# Then install fresh APK
adb install "path/to/new.apk"
```

### Issue: Still Getting Old Logs
**Problem:** Logs show old timestamps or old errors

**Solution:**
1. Verify you uninstalled old APK
2. Clear logcat: `adb logcat -c`
3. Restart device
4. Install ONLY the fresh APK
5. Start fresh logcat capture

### Issue: "Cannot read property 'phone' of null"
**This means:** Old build is still installed

**Solution:**
1. Check APK build date in Settings > Apps > Amako Shop
2. Verify it matches your fresh build
3. If not, reinstall fresh APK

## 📊 Verification Checklist

After testing fresh build, confirm:

- [ ] Build completed successfully (no "build command failed")
- [ ] APK installed without errors
- [ ] App launches without crash
- [ ] Splash screen shows (video or GIF)
- [ ] Login works without crash
- [ ] No immediate logout after login
- [ ] Home screen loads properly
- [ ] Cart loads after login
- [ ] Notifications load without error
- [ ] Tabs navigation works
- [ ] Checkout screen doesn't crash
- [ ] Map screens don't show "API key not found"
- [ ] Fresh logs show no crashes

## 🎯 Expected Results

After building with your **current codebase**, you should see:

✅ **Login works smoothly** - No crashes
✅ **Token propagates correctly** - No 401 loops
✅ **Navigation works** - No routing crashes
✅ **Video errors handled** - Falls back to GIF
✅ **Checkout works** - No null pointer errors
✅ **Maps work** - API key is configured

## 🚨 Red Flags to Report

If after testing the **FRESH BUILD** you still see:

1. **Login crash** - Report immediately with fresh logs
2. **401 loop** - Report with evidence from fresh logs
3. **Cannot read property 'phone'** - Verify it's fresh build
4. **API key not found** - Verify `app.json` has key
5. **Out of memory** - Report device specs

## 📝 Notes

- **Old logs are NOT useful** for verifying fixes
- **Fresh build is required** to test current code
- **Timestamp mismatches** indicate wrong logs
- **Device clock** should be set correctly
- **Single device** - test on same device each time

---

## ✅ Summary

Your code has all the fixes. Now:

1. ✅ Build fresh APK with current code
2. ✅ Uninstall old APK completely
3. ✅ Install fresh APK
4. ✅ Capture fresh logs
5. ✅ Test login flow
6. ✅ Analyze FRESH logs only

**Current Status:** READY TO BUILD ✨

