# Login Flow Testing Checklist

Use this checklist to verify the crash fix is working correctly.

## Pre-Test Setup

### 1. Start Development Server
```bash
cd amako-shop
npx expo start --tunnel
```

### 2. Connect Device/Emulator
- Scan QR code with Expo Go, OR
- Press `a` to open Android emulator

### 3. Open DevTools Console
Watch for these log messages during testing.

---

## Test Case 1: Fresh Login (No Saved Token)

### Steps:
1. Open app (should show login screen)
2. Enter credentials: `sabstha98@gmail.com`
3. Click "Login"
4. Watch console logs

### Expected Console Output:
```
✅ 🔐 Login in progress: true
✅ 🔐 Login Success - Token: [token]
✅ 🔐 Login: Token stored, waiting for propagation...
✅ 🔐 401 counter reset
✅ 🔐 Login: Complete, navigating to home
✅ 🔐 Login in progress: false
✅ 🛡️ RouteGuard: Redirecting authenticated user from auth to tabs
```

### Expected Result:
- ✅ No crashes
- ✅ Successfully navigates to home screen
- ✅ Cart loads (or shows empty)
- ✅ User stays logged in

### ❌ FAIL Indicators:
- Sees "Multiple 401 errors detected" before home loads
- Immediately logged out after login
- App crashes or freezes
- Navigation loop (login → home → login → home)

---

## Test Case 2: App Restart with Saved Token

### Steps:
1. Close app completely (swipe away from recents)
2. Reopen app
3. Watch console logs

### Expected Console Output:
```
✅ 🔐 SessionProvider: Found valid token, user: Sab
✅ 🔐 401 counter reset
✅ 🛒 SessionProvider: Initializing cart sync
✅ 🛡️ RouteGuard: No redirect needed
```

### Expected Result:
- ✅ App opens directly to home screen (no login required)
- ✅ User data loads correctly
- ✅ No 401 errors causing logout

---

## Test Case 3: Multiple API Calls After Login

### Steps:
1. Logout if logged in
2. Login again
3. Immediately navigate to different tabs
4. Watch for 401 errors in console

### Expected Console Output:
```
✅ 🔐 Login in progress: true
✅ 🔐 API 401 during login, ignoring (token propagating): /cart
✅ 🔐 API 401 during login, ignoring (token propagating): /notifications
✅ [After 500ms delay]
✅ 🔐 Login in progress: false
✅ ✅ Cart loaded from server successfully
✅ 📱 Notifications: [X] items
```

### Expected Result:
- ✅ 401 errors during login are IGNORED (not counted)
- ✅ After 500ms, API calls succeed
- ✅ No logout triggered
- ✅ All data loads correctly

---

## Test Case 4: Stress Test - Rapid Navigation

### Steps:
1. Login
2. Rapidly switch between tabs: Home → Menu → Orders → Profile
3. Do this 5-10 times quickly
4. Watch for crashes or logouts

### Expected Result:
- ✅ App remains stable
- ✅ No unexpected logouts
- ✅ No crashes
- ✅ Data loads on each tab

---

## Test Case 5: Production APK Test

### Steps:
1. Build APK: `build-apk.bat`
2. Install: `test-apk-install.bat`
3. Test all above scenarios on real device

### Expected Result:
- ✅ APK installs successfully
- ✅ All test cases pass on production build
- ✅ No crashes visible in logcat

### Check Logs:
```bash
adb logcat | grep -i "amako\|expo\|react\|crash\|fatal"
```

---

## Known Issues (These are OK)

### ⚠️ Expected Warnings (Safe to Ignore):
```
⚠️ expo-av has been deprecated
⚠️ Layout children must be of type Screen
⚠️ API 401 error #1 on non-sensitive endpoint (during login)
```

### 🔴 Critical Errors (MUST Fix):
```
🔴 Multiple 401 errors detected - token expired, logging out
🔴 FATAL EXCEPTION
🔴 SessionProvider: Failed to initialize session
🔴 Navigation loop detected
```

---

## Debugging Failed Tests

### If Login Still Crashes:

1. **Check Build**
   ```bash
   cd amako-shop
   eas build:list
   ```
   Verify latest build has status: "FINISHED" (not "FAILED")

2. **Enable Verbose Logging**
   Edit `amako-shop/src/api/client.ts`:
   ```typescript
   const API_CONFIG = {
     // ...
     VERBOSE_LOGGING: true,  // ← Change this
   }
   ```

3. **Check Token Storage**
   Add this in console after login:
   ```javascript
   import * as SecureStore from 'expo-secure-store';
   SecureStore.getItemAsync('@auth_token').then(console.log);
   ```

4. **Monitor Network**
   - Verify API server is running: https://amakomomo.com/api
   - Check if token is being sent in requests
   - Look for CORS errors

5. **Check Android Logs**
   ```bash
   adb logcat -s ReactNativeJS:V ReactNative:V Expo:V
   ```

---

## Success Criteria

All tests pass when:
- ✅ Login completes without crashes
- ✅ No immediate logout after login  
- ✅ 401 errors during login are ignored
- ✅ App navigates correctly
- ✅ Token persists across app restarts
- ✅ Production APK works same as development

---

## Report Issues

If crashes persist, collect:
1. Full console logs from failing test
2. ADB logcat output (if on APK)
3. Screenshot of error (if visible)
4. EAS build logs from: https://expo.dev

Share in `DEBUG_APK_CRASH.md`

