# What to Do While Your APK is Building

EAS builds typically take 15-20 minutes. Here's how to use that time productively!

## 1. Test Backend Fixes on Production (5 mins)

### Update Production Server

```bash
# SSH into your server
ssh root@amakomomo.com

# Navigate to project
cd /var/www/amako-momo(p)/my_momo_shop

# Check current commit
git log -1 --oneline

# If not latest, pull
git pull origin main

# Clear all caches
php artisan optimize:clear
systemctl restart php8.3-fpm
systemctl restart nginx
```

### Test the API

Run the test script we created:
```bash
# On Windows
.\test-api-simple.ps1
```

Expected result: User object should have ONLY `id`, `name`, `email`, `phone` (no `roles`!)

## 2. Test in Expo Go (10 mins) - RECOMMENDED!

This is faster than waiting for APK and tests the backend fix:

```bash
cd C:\Users\user\my_momo_shop\amako-shop

# Start Expo
npx expo start
```

Then:
1. Open Expo Go app on your phone
2. Scan QR code
3. Try to **login** with your credentials
4. If it works → backend is fixed! ✅
5. If it crashes → backend still has issues ❌

## 3. Understand What We Fixed (5 mins)

### Read the Fix Documentation

Open these files to understand what was fixed:
- `MOBILE_APP_SIGNUP_CRASH_FIX.md` - Main crash fix
- `ADMIN_DASHBOARD_ZERO_DATA_FIX.md` - Dashboard fix
- `DEBUG_APK_CRASH.md` - How to debug future crashes

### Key Concepts You Learned

1. **SecureStore Limitation**: Can only store simple data, not complex objects with nested arrays
2. **API Response Design**: Always return simple, flat objects to mobile apps
3. **Architecture Mismatch**: APK must support both ARM (phones) and x86 (emulators)
4. **ADB Debugging**: How to view real crash logs from Android devices
5. **Cache Issues**: PHP-FPM, Nginx, and OpCache can cache old code

## 4. Learn React Native Debugging Tools (10 mins)

### Install Android Platform Tools (for future debugging)

1. Download: https://developer.android.com/tools/releases/platform-tools
2. Extract to `C:\platform-tools\`
3. Add to PATH:
   ```powershell
   # Run as Administrator
   $env:Path += ";C:\platform-tools"
   [Environment]::SetEnvironmentVariable("Path", $env:Path, [System.EnvironmentVariableTarget]::Machine)
   ```

### Learn ADB Commands

```bash
# Check connected devices
adb devices

# View logs
adb logcat

# Filter for errors only
adb logcat *:E

# Clear logs
adb logcat -c

# Save logs to file
adb logcat > crash-log.txt

# Install APK
adb install path/to/app.apk

# Uninstall app
adb uninstall com.amako.shop
```

## 5. Review Your Project Structure (5 mins)

### Backend Structure
```
my_momo_shop/
├── app/
│   ├── Http/Controllers/Admin/
│   │   ├── DashboardController.php (Fixed: counts users correctly)
│   │   ├── AdminDashboardController.php (Fixed: uses users table)
│   │   └── CustomerAnalyticsController.php (Fixed: journey analysis)
│   └── Services/
│       └── CustomerAnalyticsService.php (Fixed: order filtering)
├── routes/
│   ├── api.php (Fixed: simplified user response)
│   └── web.php (Fixed: journey-analysis route)
```

### Mobile App Structure
```
amako-shop/
├── app/
│   ├── (auth)/
│   │   ├── login.tsx
│   │   └── register.tsx
│   └── (tabs)/
│       ├── home.tsx
│       ├── profile.tsx
│       └── finds.tsx
├── src/
│   ├── api/
│   │   ├── auth-hooks.ts (Fixed: error handling)
│   │   └── client.ts
│   └── services/
│       └── token.ts (Uses SecureStore)
└── eas.json (Fixed: added universal APK build)
```

## 6. Plan Your Next Features (5 mins)

### Features to Add
- [ ] Push notifications for order updates
- [ ] Offline mode support
- [ ] Order history pagination
- [ ] Customer reviews and ratings
- [ ] Loyalty points display
- [ ] Dark mode theme

### Backend Improvements
- [ ] Add API rate limiting
- [ ] Implement Redis caching
- [ ] Add database indexes for performance
- [ ] Set up automated backups
- [ ] Add monitoring (Sentry, New Relic)

## 7. Prepare for APK Testing (2 mins)

### APK Testing Checklist

Once build completes:
1. Download APK from EAS
2. Install on emulator OR real device
3. Test these scenarios:
   - [ ] App opens without crash
   - [ ] Login with correct credentials
   - [ ] Signup new user
   - [ ] Browse menu items
   - [ ] Add items to cart
   - [ ] Place order
   - [ ] View order history
   - [ ] Update profile
   - [ ] Logout and login again

### What to Check
- App name shows "AmakoMomo" (not "AmaKo Shop")
- App icon is correct (if you fixed it)
- No crashes on login/signup
- All data loads correctly

## 8. Learn Git Workflow (3 mins)

### What We Did Today

```bash
# View all commits we made
git log --oneline -10

# See what files changed
git diff HEAD~5..HEAD

# View specific file history
git log --oneline -- routes/api.php
```

### Best Practices Learned
1. **Commit Often**: Small, focused commits are better
2. **Clear Messages**: "Fix X" not "updates"
3. **Test Before Push**: Always test locally first
4. **Cache Clearing**: Always clear cache after pulling code
5. **Restart Services**: PHP-FPM and Nginx need restart

## 9. Understand EAS Build Process (5 mins)

### What Happens During Build

1. **Setup** (2 min): EAS downloads dependencies
2. **Configure** (1 min): Sets up Android environment
3. **Build** (10 min): Compiles native code
4. **Package** (2 min): Creates APK
5. **Upload** (1 min): Uploads to EAS servers

### Build Profiles

- `development`: For debugging with Expo Dev Client
- `preview`: For testing (what you're building now)
- `production`: For Play Store release

### Monitor Your Build

Watch the build progress:
```bash
# View build status
eas build:list

# View specific build
eas build:view <build-id>
```

## 10. Read React Native Documentation (10 mins)

### Key Topics to Learn

1. **SecureStore**: https://docs.expo.dev/versions/latest/sdk/securestore/
   - What data types it supports
   - Size limitations
   - Security best practices

2. **Expo Router**: https://docs.expo.dev/router/introduction/
   - File-based routing
   - Navigation patterns
   - Route parameters

3. **EAS Build**: https://docs.expo.dev/build/introduction/
   - Build profiles
   - Environment variables
   - Native configuration

4. **React Native Performance**: https://reactnative.dev/docs/performance
   - Optimization techniques
   - Profiling tools
   - Common pitfalls

## Quick Win Tasks (Do Any of These!)

### Option A: Test Backend API
```bash
.\test-api-simple.ps1
```

### Option B: Test in Expo Go
```bash
cd amako-shop
npx expo start
# Scan QR with Expo Go app
```

### Option C: Install ADB for Future Debugging
1. Download Platform Tools
2. Extract to C:\platform-tools
3. Add to PATH

### Option D: Review Documentation
Open these files:
- `MOBILE_APP_SIGNUP_CRASH_FIX.md`
- `ADMIN_DASHBOARD_ZERO_DATA_FIX.md`
- `DEBUG_APK_CRASH.md`

## Summary of Today's Fixes

### Issues Fixed
1. ✅ Mobile app crash after login (simplified user response)
2. ✅ Admin Dashboard showing 0 customers (using users table)
3. ✅ Customer Analytics showing 0 data (order status filtering)
4. ✅ Journey Analysis API error (route and method fixes)
5. ✅ TypeScript errors in mobile app
6. ✅ APK architecture mismatch (added universal build)

### Files Modified
- Backend: 4 controllers, 1 service, 1 route file
- Mobile: 12 TypeScript files, 1 config file
- Total commits: ~8 commits

### Skills Learned
- React Native debugging with adb
- Laravel caching strategies
- API design for mobile apps
- EAS build configuration
- Git workflow best practices

---

## While Build is Running - Do This Now!

**PRIORITY 1 (5 min)**: Test backend in Expo Go
```bash
cd amako-shop
npx expo start
```

**PRIORITY 2 (5 min)**: Update production server
```bash
ssh root@amakomomo.com
cd /var/www/amako-momo(p)/my_momo_shop
git pull origin main
php artisan optimize:clear
systemctl restart php8.3-fpm
```

**PRIORITY 3 (10 min)**: Read documentation to understand the fixes

---

Good luck with your build! 🚀


