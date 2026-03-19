# Pre-Build Final Test Checklist

**Purpose:** Ensure app is 100% crash-free before building APK

**Date:** October 20, 2025  
**Status:** ✅ ALL FIXES APPLIED - READY FOR TESTING

---

## 🔧 Fixes Applied

### 1. ✅ Login Crash Protection
- **File:** `amako-shop/src/api/client.ts`
- **Fix:** Added `isLoggingIn` flag to ignore 401 errors during token propagation
- **Result:** Prevents premature logout during login

### 2. ✅ Increased 401 Error Tolerance
- **File:** `amako-shop/src/api/client.ts`
- **Fix:** Changed threshold from 3 → 5 errors, window from 5s → 10s
- **Result:** Allows normal API burst without false positives

### 3. ✅ Token Propagation Delay
- **File:** `amako-shop/src/api/auth-hooks.ts`
- **Fix:** Added 500ms delay after token save
- **Result:** Ensures token is ready before navigation

### 4. ✅ Reset Counter on Init
- **File:** `amako-shop/src/session/SessionProvider.tsx`
- **Fix:** Reset 401 counter when loading valid token
- **Result:** Fresh start for each session

### 5. ✅ Pre-Login 401 Errors Fixed (NEW!)
- **File:** `amako-shop/src/hooks/useNotifications.ts`
- **Fix:** Added authentication checks to notification hooks
- **Result:** No API calls when user is not logged in

---

## 📋 Testing Steps

### Test 1: Cold Start (No Saved Token)

**Expected Logs:**
```
✅ 🔐 SessionProvider: No valid token found
✅ 🛡️ RouteGuard: Redirecting unauthenticated user to login
✅ NO 401 errors before login
```

**What to Check:**
- [ ] App opens to login screen
- [ ] No red ERROR logs
- [ ] No "API Error - GET /notifications"
- [ ] No 401 errors in console

**How to Test:**
1. Logout if logged in
2. Close app completely
3. Restart app
4. Watch console for errors

---

### Test 2: Login Flow

**Expected Logs:**
```
✅ 🔐 Login in progress: true
✅ 🔐 Login Success - Token: [token]
✅ 🔐 SessionProvider: Setting new token
✅ 🔐 401 counter reset
✅ 🔐 Login: Token stored, waiting for propagation...
✅ 🔐 Login: Complete, navigating to home
✅ 🔐 Login in progress: false
✅ ✅ Cart loaded from server successfully
✅ 📱 Notifications: [X] items
```

**What to Check:**
- [ ] Login succeeds without crash
- [ ] "401 counter reset" appears
- [ ] "Login in progress: true" then "false"
- [ ] Cart loads successfully
- [ ] Notifications load successfully
- [ ] No "Multiple 401 errors detected" message
- [ ] Successfully navigates to home

**How to Test:**
1. Enter credentials: `sabstha98@gmail.com`
2. Click Login
3. Wait for home screen
4. Verify data loads

---

### Test 3: App Restart with Saved Token

**Expected Logs:**
```
✅ 🔐 SessionProvider: Found valid token, user: Sab
✅ 🔐 401 counter reset
✅ 🛒 SessionProvider: Initializing cart sync
✅ 📱 Notifications: [X] items
✅ 🛡️ RouteGuard: No redirect needed
```

**What to Check:**
- [ ] App opens directly to home (no login screen)
- [ ] User data loads
- [ ] Cart loads
- [ ] Notifications load
- [ ] No 401 errors
- [ ] No logout occurs

**How to Test:**
1. After logging in, close app
2. Reopen app
3. Should open to home screen immediately

---

### Test 4: Rapid Tab Switching

**Expected Logs:**
```
✅ No crashes
✅ No unexpected logouts
✅ No "Multiple 401 errors"
```

**What to Check:**
- [ ] App remains stable
- [ ] No crashes
- [ ] No logouts
- [ ] Smooth navigation

**How to Test:**
1. Login if not logged in
2. Rapidly switch tabs: Home → Menu → Orders → Profile
3. Do this 10 times
4. Watch for crashes

---

### Test 5: App Background/Foreground

**Expected Logs:**
```
✅ 📱 App became active, checking for new notifications...
✅ 📱 Notifications: [X] items
✅ No 401 errors
```

**What to Check:**
- [ ] App resumes correctly
- [ ] Notifications refresh
- [ ] No logout occurs
- [ ] No 401 errors

**How to Test:**
1. With app open and logged in
2. Press home button (background app)
3. Wait 5 seconds
4. Reopen app
5. Should still be logged in

---

## 🎯 Success Criteria

All tests must show:
- ✅ Zero crashes
- ✅ Zero "Multiple 401 errors detected" messages
- ✅ Zero 401 errors before login
- ✅ Zero unexpected logouts
- ✅ Smooth navigation throughout
- ✅ All data loads correctly

---

## 🔍 What to Watch For

### ✅ Good Signs (Everything Working)
```
🔐 Login in progress: true/false
🔐 401 counter reset
🔐 Login: Token stored, waiting for propagation...
✅ Cart loaded from server successfully
📱 Notifications: [X] items
🛡️ RouteGuard: No redirect needed
```

### 🚨 Bad Signs (Still Broken)
```
❌ Multiple 401 errors detected - token expired, logging out
❌ API Error - GET /notifications (before login)
❌ 🔐 API 401 error #5 on non-sensitive endpoint
❌ SessionProvider: Failed to initialize session
❌ FATAL EXCEPTION
```

---

## 📝 Testing Command

Run development server:
```bash
cd amako-shop
npx expo start --tunnel
```

Then scan QR code or press 'a' for Android emulator.

---

## ✅ Pre-Build Checklist

Before running `build-apk.bat`:

- [ ] Test 1: Cold Start - PASSED
- [ ] Test 2: Login Flow - PASSED
- [ ] Test 3: Saved Token - PASSED
- [ ] Test 4: Tab Switching - PASSED
- [ ] Test 5: Background/Foreground - PASSED
- [ ] No 401 errors before login - CONFIRMED
- [ ] No crashes observed - CONFIRMED
- [ ] All API calls succeed after login - CONFIRMED

**If ALL boxes checked:** ✅ **READY TO BUILD APK**

**If ANY box unchecked:** ⚠️ **DO NOT BUILD YET** - Debug failing test first

---

## 🚀 Next Steps After Testing

Once all tests pass:

1. **Build APK:**
   ```bash
   build-apk.bat
   ```

2. **Monitor Build:**
   - Go to: https://expo.dev/accounts/sabstha98/projects/amako-shop/builds
   - Wait for "FINISHED" status (10-20 minutes)
   - Download APK

3. **Test on Device:**
   ```bash
   test-apk-install.bat
   ```

4. **Verify on Real Device:**
   - Repeat all 5 tests above on the installed APK
   - Monitor with: `adb logcat | grep -i "amako\|expo\|crash"`

---

## 📊 Expected vs Actual Results

Fill this out after testing:

| Test | Expected Result | Actual Result | Status |
|------|----------------|---------------|---------|
| Cold Start | No 401 errors | _____________ | ☐ |
| Login Flow | Success, no crash | _____________ | ☐ |
| Saved Token | Opens to home | _____________ | ☐ |
| Tab Switch | Stable | _____________ | ☐ |
| Background | Resumes OK | _____________ | ☐ |

---

**Tester:** _____________  
**Date:** _____________  
**Time:** _____________  
**Result:** ☐ PASS / ☐ FAIL

---

**Notes:**
_Write any observations or issues here_

---

**Sign-off:** ☐ APPROVED FOR APK BUILD

Once signed off, proceed with `build-apk.bat`


