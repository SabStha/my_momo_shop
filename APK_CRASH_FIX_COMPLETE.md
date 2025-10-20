# APK Crash After Login - Complete Fix

## Problem Summary

The Android APK was crashing silently after successful login. This was caused by a **race condition** in the authentication flow that created a crash loop.

## Root Causes Identified

### 1. **Token Propagation Race Condition**
- After successful login, the token was saved to `SecureStore`
- Multiple API calls (cart, notifications, reviews) were made immediately
- These calls happened BEFORE the token was fully saved and loaded into the API client
- Result: Multiple 401 (Unauthorized) errors

### 2. **Aggressive 401 Error Handling**
- The app tracked 401 errors with a counter
- After **3x 401 errors within 5 seconds**, the app automatically logged out the user
- During login, 3+ API calls would fail before token propagated
- This triggered `emitUnauthorized()` → logout → navigate back to login

### 3. **Navigation Loop → Crash**
The sequence that caused crashes:
```
Login Success → Save Token → Navigate to (tabs) 
  → Home loads → Multiple API calls fire
  → Token not ready yet → 401 errors x3
  → Auto logout triggered → Navigate to login
  → User still "logged in" in UI → Navigate back to (tabs)
  → CRASH LOOP
```

### 4. **EAS Build Failure**
Your build logs showed: `Error: build command failed.`
The APK wasn't built successfully, which would definitely cause crashes.

## Fixes Applied

### Fix 1: Login/Registration Protection Flag
**File:** `amako-shop/src/api/client.ts`

Added `isLoggingIn` flag to prevent 401 errors during token propagation:

```typescript
let isLoggingIn = false;

export const setLoggingIn = (value: boolean) => {
  isLoggingIn = value;
  if (__DEV__) {
    console.log('🔐 Login in progress:', value);
  }
};

// In 401 error handler:
if (isLoggingIn) {
  if (__DEV__) {
    console.warn('🔐 API 401 during login, ignoring (token propagating):', error.config?.url);
  }
  return Promise.reject(normalizedError);
}
```

### Fix 2: Increased 401 Error Threshold
**File:** `amako-shop/src/api/client.ts`

- Increased threshold from **3 to 5** errors
- Increased time window from **5 to 10 seconds**
- Added `reset401Counter()` function to clear counter on successful login

```typescript
// Reset counter if it's been more than 10 seconds (was 5s)
if (Date.now() - last401Reset > 10000) {
  recent401Count = 0;
}

// Increased threshold from 3 to 5
if (recent401Count >= 5 || isSensitiveEndpoint) {
  emitUnauthorized();
}
```

### Fix 3: Token Propagation Delay
**File:** `amako-shop/src/api/auth-hooks.ts`

Added 500ms delay after saving token to allow propagation:

```typescript
export function useLogin() {
  return useMutation({
    mutationFn: async (credentials) => {
      setLoggingIn(true);  // ← Mark login in progress
      try {
        return await login(credentials);
      } catch (error) {
        setLoggingIn(false);
        throw error;
      }
    },
    onSuccess: async (data) => {
      // Store token
      await setToken({ token: data.token, user: data.user });
      
      // Reset 401 counter
      reset401Counter();
      
      // Wait for token propagation
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Clear login flag
      setLoggingIn(false);
      
      // Navigate to app
      router.replace('/(tabs)');
    }
  });
}
```

### Fix 4: Reset Counter on App Start
**File:** `amako-shop/src/session/SessionProvider.tsx`

Reset 401 counter when loading valid token from storage:

```typescript
const tokenData = await getToken();
if (tokenData && tokenData.token) {
  setTokenState(tokenData.token);
  setUser(tokenData.user || null);
  
  // Reset 401 counter since we have a valid token
  reset401Counter();
  
  await loadFromServer();
}
```

## Testing the Fix

### 1. **Test in Development First**
```bash
cd amako-shop
npx expo start --tunnel
```

Then:
1. Login with your credentials
2. Watch console logs for:
   - ✅ `🔐 Login in progress: true`
   - ✅ `🔐 401 counter reset`
   - ✅ `🔐 Login: Token stored, waiting for propagation...`
   - ✅ `🔐 Login in progress: false`
3. Verify no logout occurs
4. Verify home screen loads properly

### 2. **Build APK for Testing**

**Important:** Your previous build failed. Here's how to build properly:

```bash
# Clean any previous build artifacts
cd amako-shop
rm -rf android/.gradle android/build android/app/build node_modules/.cache

# Reinstall dependencies (if needed)
npm install

# Build with EAS
eas build --platform android --profile preview
```

**Build Profiles (from eas.json):**
- `preview` - APK for testing (recommended)
- `production` - AAB for Play Store

### 3. **Monitor Build Progress**
- Watch the EAS build dashboard: https://expo.dev
- Check for build errors in real-time
- Download APK when complete

### 4. **Test APK on Device**
```bash
# Install APK
adb install -r path/to/your.apk

# Monitor logs while testing
adb logcat | grep -i "amako\|expo\|react"
```

## Common Build Errors & Solutions

### Error: "build command failed"
**Solution:** Clean build cache and rebuild:
```bash
cd amako-shop/android
./gradlew clean
cd ..
eas build --platform android --profile preview --clear-cache
```

### Error: "Out of memory"
**Solution:** Increase memory in `android/gradle.properties`:
```properties
org.gradle.jvmargs=-Xmx4096m -XX:MaxPermSize=512m
```

### Error: "Package name mismatch"
**Solution:** Your `app.json` specifies `"package": "com.amako.shop"` but Android directory might have different package. Check:
```bash
grep -r "package=" amako-shop/android/app/src/main/AndroidManifest.xml
```

## Verification Checklist

After deploying the fix:

- [ ] App builds successfully (no "build command failed")
- [ ] Login works without crash
- [ ] No immediate logout after login
- [ ] Cart loads properly after login
- [ ] Notifications load without triggering logout
- [ ] Home screen displays correctly
- [ ] Navigation between tabs works
- [ ] App doesn't crash after returning from background

## Additional Recommendations

### 1. **Add Error Boundary**
Consider adding an error boundary to catch crashes gracefully:
```typescript
// In app/_layout.tsx
<ErrorBoundary fallback={<ErrorScreen />}>
  {children}
</ErrorBoundary>
```

### 2. **Add Crash Reporting**
Install Sentry for production crash reporting:
```bash
npm install @sentry/react-native
npx @sentry/wizard@latest -i reactNative
```

### 3. **Optimize API Calls**
Consider lazy-loading non-critical data:
- Don't load cart/notifications immediately after login
- Use `staleTime` in React Query to prevent duplicate requests
- Implement request deduplication

### 4. **Test on Multiple Devices**
- Test on both Android emulator and real device
- Test on different Android versions (API 23+)
- Test with slow network conditions

## Technical Details

### Why 500ms Delay Works
- `SecureStore.setItemAsync()` is async but doesn't guarantee immediate availability
- API interceptor reads token with `SecureStore.getItemAsync()` on each request
- 500ms ensures token is written and flushed to storage before API calls

### Why Higher Threshold Works
- Production apps often have 3-5 simultaneous requests on page load
- Cart, notifications, user profile, analytics can all fire together
- Threshold of 5 allows for normal "burst" of requests during initialization

### Why Reset Counter Works
- Prevents false positives from lingering counts
- Ensures fresh start after successful authentication
- Prevents cascading failures from previous sessions

## Files Modified

1. ✅ `amako-shop/src/api/client.ts` - Added login flag and increased thresholds
2. ✅ `amako-shop/src/api/auth-hooks.ts` - Added token propagation delay
3. ✅ `amako-shop/src/session/SessionProvider.tsx` - Reset counter on init

## Next Steps

1. **Test in development** with the tunnel
2. **Build preview APK** with EAS
3. **Test on real device** - install and login multiple times
4. **Monitor for crashes** using ADB logcat
5. **Deploy to production** once verified

## Support

If the crash persists:
1. Check ADB logcat for actual crash logs
2. Look for JavaScript errors in Metro bundler
3. Verify API server is responding correctly
4. Check if CORS/network policies are blocking requests
5. Enable verbose logging: Set `VERBOSE_LOGGING: true` in `client.ts`

---

**Status:** ✅ FIXES APPLIED - READY FOR TESTING

**Last Updated:** October 20, 2025

