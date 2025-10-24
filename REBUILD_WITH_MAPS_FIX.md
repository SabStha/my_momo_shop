# 🚀 Rebuild APK with Google Maps Fix

## 🔴 CRITICAL FIX APPLIED

The **#1 production crash** (Google Maps API key missing) has been fixed!

**What was fixed:**
1. ✅ Uncommented API key in `AndroidManifest.xml`
2. ✅ Added `react-native-maps` config plugin to `app.json`

---

## 📋 Rebuild Instructions

Follow these steps **in order** to deploy the fix:

### Step 1: Clean Build Artifacts ⚙️

```bash
cd amako-shop
rm -rf android/build
rm -rf android/app/build
rm -rf android/.gradle
```

**Why?** Ensures no cached files interfere with the build.

---

### Step 2: Build New APK 🏗️

```bash
eas build --platform android --profile preview --clear-cache
```

**Important flags:**
- `--clear-cache` ensures EAS uses the updated AndroidManifest.xml
- `--profile preview` builds an APK (not AAB)

**Expected output:**
```
✔ Build successfully started
Build ID: [some-id]
Build URL: https://expo.dev/accounts/[your-account]/projects/amako-shop/builds/[build-id]
```

**Build time:** ~10-20 minutes

---

### Step 3: Monitor Build Progress 👀

1. Open the Build URL from Step 2
2. Watch for build status:
   - ⏳ "In queue" → Waiting for build server
   - 🔄 "In progress" → Building APK
   - ✅ "Finished" → Ready to download
   - ❌ "Errored" → Build failed (report error)

---

### Step 4: Download New APK 📥

Once build is finished:
1. Click **"Download"** button on build page
2. Save to a known location
3. Note the file path

Example: `C:\Users\user\Downloads\build-1234567890.apk`

---

### Step 5: Uninstall Old APK 🗑️

**Option A: On Device**
```
Settings > Apps > Amako Shop > Uninstall
```

**Option B: Via ADB**
```bash
adb uninstall com.amako.shop
```

**Verify uninstall:**
```bash
adb shell pm list packages | grep amako
# Should return nothing
```

---

### Step 6: Install New APK 📲

```bash
adb install -r "C:\Users\user\Downloads\build-1234567890.apk"
```

**Replace with your actual APK path!**

**Expected output:**
```
Performing Streamed Install
Success
```

---

### Step 7: Start Log Capture 📊

```bash
adb logcat > maps_fix_test.log
```

**Keep this terminal running** during testing!

---

### Step 8: Test Map Features 🗺️ **CRITICAL**

These were crashing before the fix. Test each one:

#### Test 1: Order Tracking Map
1. Place a test order (or view existing order)
2. Navigate to order tracking
3. **Verify:** Map loads without crash
4. **Verify:** No "API key not found" error

#### Test 2: Delivery Map
1. Navigate to delivery screen
2. View delivery location on map
3. **Verify:** Map displays correctly
4. **Verify:** No crashes

#### Test 3: Store Location Map
1. Navigate to "Visit Us" or store location
2. View store on map
3. **Verify:** Map shows store marker
4. **Verify:** No errors

---

### Step 9: Test Other Features (Regression Check) ✅

Make sure nothing broke:

#### Test Login
1. Logout if logged in
2. Login with valid credentials
3. **Verify:** No crash after login
4. **Verify:** Home screen loads

#### Test Checkout
1. Add item to cart
2. Go to checkout
3. Fill phone number field
4. **Verify:** No "Cannot read property 'phone' of null"
5. **Verify:** Checkout completes

---

### Step 10: Stop Log Capture & Analyze 🔍

```bash
# In logcat terminal, press Ctrl+C
```

**Search for errors:**
```bash
# Search for API key errors (should be ZERO)
grep -i "API key not found" maps_fix_test.log

# Search for crashes (should be ZERO for maps)
grep -i "FATAL EXCEPTION" maps_fix_test.log

# Search for successful map loads (should be MULTIPLE)
grep -i "Google Maps" maps_fix_test.log
```

---

## ✅ Success Criteria

The fix is successful if:

- [ ] Build completes without errors
- [ ] APK installs successfully
- [ ] Order tracking map loads (**no crash**)
- [ ] Delivery map loads (**no crash**)
- [ ] Store location map loads (**no crash**)
- [ ] **ZERO** "API key not found" errors in logs
- [ ] Login still works (no regression)
- [ ] Checkout still works (no regression)

---

## 🚨 If You See Errors

### Error: "API key not found" (Still happening)

**Possible causes:**
1. Old APK still installed
2. Build cache not cleared
3. EAS didn't use updated files

**Solution:**
```bash
# Verify you have the latest code
git status

# Force complete rebuild
cd amako-shop
rm -rf android
eas build --platform android --profile preview --clear-cache
```

### Error: Build fails

**Get build logs:**
1. Go to expo.dev build page
2. Click "View logs"
3. Share error with me

### Error: Maps show but are blank

**This is different from crash - possible causes:**
1. Network issues
2. Billing not enabled on Google Cloud
3. API key restrictions

**Check logs for:**
```
grep -i "Google Maps\|Maps API\|billing" maps_fix_test.log
```

---

## 📊 Expected Log Output

### ✅ Before Fix (Crashed)
```
E/AndroidRuntime: FATAL EXCEPTION: main
E/AndroidRuntime: java.lang.IllegalStateException: API key not found
E/AndroidRuntime: at com.google.maps.api.android.lib6.common.g.b
```

### ✅ After Fix (Works)
```
I/Google Maps Android API: Successfully loaded map
I/Google Maps Android API: Google Play services client version: 253425407
D/MapView: Map initialized successfully
I/chromium: [INFO:CONSOLE(1)] "Google Maps JavaScript API loaded"
```

---

## 🎯 What This Fix Solves

| Before | After |
|--------|-------|
| 🔴 20+ crashes in 30 minutes | 🟢 0 crashes expected |
| 🔴 100% crash rate on maps | 🟢 0% crash rate expected |
| 🔴 Can't track orders | 🟢 Order tracking works |
| 🔴 Can't view store location | 🟢 Store location visible |
| 🔴 Users frustrated | 🟢 Users happy |

---

## 📝 Quick Command Reference

```bash
# Clean build
cd amako-shop && rm -rf android/build android/app/build android/.gradle

# Build APK
eas build --platform android --profile preview --clear-cache

# Uninstall old
adb uninstall com.amako.shop

# Install new
adb install -r path/to/your.apk

# Start logging
adb logcat > maps_fix_test.log

# Check for errors
grep -i "API key not found" maps_fix_test.log
```

---

## ⏱️ Estimated Time

| Step | Time |
|------|------|
| Clean build artifacts | 1 minute |
| Start EAS build | 1 minute |
| Wait for build | 10-20 minutes |
| Download APK | 1-2 minutes |
| Uninstall old APK | 1 minute |
| Install new APK | 1 minute |
| Test all features | 5-10 minutes |
| **Total** | **20-35 minutes** |

---

## 🎉 After Successful Testing

Once all tests pass:

1. ✅ Mark as ready for production
2. ✅ Build production AAB: `eas build --platform android --profile production`
3. ✅ Deploy to Google Play Store
4. ✅ Monitor production logs for map-related crashes (should be 0)

---

**Status:** 🟢 Ready to build and test!

**Next Action:** Run Step 1 (Clean build artifacts)



