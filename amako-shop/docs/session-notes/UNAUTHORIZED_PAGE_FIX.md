# Unauthorized Page on App Start - Fix Summary

## Problem

When the mobile app was bundled or opened for the first time, it would navigate to an "unauthorized" page instead of the login page. This happened because the RouteGuard component wasn't properly handling the initial app state for unauthenticated users.

## Root Cause

The `RouteGuard.tsx` component had incomplete routing logic that only handled two scenarios:

1. ✅ Authenticated user in `(auth)` screens → redirect to tabs
2. ✅ Unauthenticated user in `(tabs)` → redirect to login
3. ❌ **MISSING**: Unauthenticated user at root/index → no redirect (caused the issue)
4. ❌ **MISSING**: Authenticated user at root/index → no redirect

### What Was Happening:

1. App starts → `app/index.tsx` tries to redirect to `/(auth)/login`
2. During initial load, the route segments are `['index']` or empty
3. RouteGuard checks: `isAuthenticated = false`, `root = 'index'`
4. Since `root` is not `"(auth)"` and not `"(tabs)"`, no redirect happens
5. User sees an unauthorized/error page instead of login

## Solution

Updated `amako-shop/src/session/RouteGuard.tsx` to handle all routing scenarios:

### Added Two New Cases:

1. **Unauthenticated user at root/index** → Redirect to login
   ```typescript
   else if (!isAuthenticated && !inAuth && !inTabs) {
     // Unauthenticated user at root/index → redirect to login
     router.replace("/(auth)/login");
   }
   ```

2. **Authenticated user at root/index** → Redirect to home
   ```typescript
   else if (isAuthenticated && !inAuth && !inTabs) {
     // Authenticated user at root/index → redirect to home
     router.replace("/(tabs)/home");
   }
   ```

### Complete Routing Logic Now:

| Auth State | Current Location | Action |
|------------|------------------|--------|
| ✅ Authenticated | `(auth)` screens | → Redirect to `(tabs)/home` |
| ✅ Authenticated | Root/Index | → Redirect to `(tabs)/home` |
| ✅ Authenticated | `(tabs)` screens | ✓ Stay (correct) |
| ❌ Not Authenticated | `(tabs)` screens | → Redirect to `(auth)/login` |
| ❌ Not Authenticated | Root/Index | → Redirect to `(auth)/login` |
| ❌ Not Authenticated | `(auth)` screens | ✓ Stay (correct) |

## Files Modified

- `amako-shop/src/session/RouteGuard.tsx`
  - Added handling for unauthenticated users at root/index
  - Added handling for authenticated users at root/index
  - Added `router` to useEffect dependency array for consistency

## Testing

To verify the fix:

1. **Fresh Install Test**: 
   - Clear app data completely
   - Open the app
   - Should see login page immediately (not unauthorized page)

2. **After Login Test**:
   - Log in successfully
   - Close and reopen app
   - Should see home page (authenticated state persisted)

3. **After Logout Test**:
   - Log out from the app
   - Close and reopen app
   - Should see login page (not unauthorized page)

## How to Deploy

### For Development Testing:
```bash
cd amako-shop
npx expo start --clear
```

### For Production Build:
```bash
cd amako-shop
eas build --platform android --profile production
```

## Debug Console Logs

When running in development mode (`__DEV__`), you'll see helpful logs:

- `🛡️ RouteGuard: Redirecting unauthenticated user from root to login`
- `🛡️ RouteGuard: Redirecting authenticated user from root to home`
- `🛡️ RouteGuard: Checking redirect - isAuthenticated: false, root: index, segments: ['index']`

## Related Files

- `amako-shop/app/index.tsx` - Initial app entry point (redirects to auth)
- `amako-shop/src/session/SessionProvider.tsx` - Manages authentication state
- `amako-shop/src/session/RouteGuard.tsx` - **FIXED** - Handles route protection
- `amako-shop/app/_layout.tsx` - Root layout with providers

## Impact

✅ **Fixed**: First-time users now see login page instead of unauthorized page
✅ **Fixed**: App properly handles all initial routing scenarios
✅ **Improved**: Better debug logging for route decisions
✅ **Enhanced**: Complete coverage of all auth + route combinations

---

**Date Fixed**: October 8, 2025
**Issue**: Unauthorized page shown on first app launch
**Status**: ✅ Resolved

