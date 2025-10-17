# Android Build Fixes Applied

## Date: October 16, 2025

This document summarizes all the fixes applied to resolve the Android Gradle build failures.

## Issues Found and Fixed

### 1. Missing versionCode (CRITICAL)
**File:** `android/app/build.gradle`
**Problem:** Android builds require both `versionCode` (integer) and `versionName` (string) in the defaultConfig.
**Fix:** Added `versionCode 1` to the defaultConfig section.

```gradle
defaultConfig {
    applicationId 'com.amako.shop'
    minSdkVersion rootProject.ext.minSdkVersion
    targetSdkVersion rootProject.ext.targetSdkVersion
    versionCode 1  // ADDED
    versionName "1.0.0"
}
```

### 2. Invalid Google Maps API Key
**File:** `android/app/src/main/AndroidManifest.xml`
**Problem:** Placeholder API key value can cause build issues.
**Fix:** Commented out the meta-data tag until a real API key is provided.

```xml
<!-- <meta-data android:name="com.google.android.geo.API_KEY" android:value="YOUR_ANDROID_GOOGLE_MAPS_API_KEY"/> -->
```

### 3. Wrong Plugin Configuration
**File:** `app.json`
**Problem:** `expo-video` plugin was listed, but the code uses `expo-av` for Video component.
**Fix:** Replaced `expo-video` with properly configured `expo-av` plugin.

```json
"plugins": [
  "expo-router",
  "expo-secure-store",
  [
    "expo-av",
    {
      "microphonePermission": "Allow $(PRODUCT_NAME) to access your microphone."
    }
  ]
],
```

### 4. New Architecture Required
**File:** `android/gradle.properties`
**Problem:** `react-native-reanimated` requires the new architecture to be enabled.
**Fix:** Enabled new architecture by setting `newArchEnabled=true`.

```properties
newArchEnabled=true
```

### 5. Architecture Build Optimization
**File:** `android/gradle.properties`
**Problem:** Building for all architectures (including x86, x86_64) increases build time and complexity.
**Fix:** Reduced to only ARM architectures (most common for mobile devices).

```properties
reactNativeArchitectures=armeabi-v7a,arm64-v8a
```

### 6. MainActivity Fabric Configuration
**File:** `android/app/src/main/java/com/amako/shop/MainActivity.kt`
**Problem:** Inconsistent use of `fabricEnabled` vs `BuildConfig.IS_NEW_ARCHITECTURE_ENABLED`.
**Fix:** Reverted to use `fabricEnabled` for proper new architecture support.

```kotlin
override fun createReactActivityDelegate(): ReactActivityDelegate {
  return ReactActivityDelegateWrapper(
        this,
        BuildConfig.IS_NEW_ARCHITECTURE_ENABLED,
        object : DefaultReactActivityDelegate(
            this,
            mainComponentName,
            fabricEnabled  // Using fabricEnabled for proper new architecture
        ){})
}
```

## Next Steps

### Immediate Actions:
1. **Commit these changes** to your repository
2. **Run the EAS build again**:
   ```bash
   cd amako-shop
   eas build --platform android
   ```

### If Build Still Fails:
1. **Check the EAS Build Logs**: Visit the build URL and look for specific error messages
2. **Common issues to check**:
   - Node modules: Run `npm install` or `yarn install` to ensure dependencies are up to date
   - Clear build cache: `eas build --platform android --clear-cache`
   - Check for any custom native modules that might need configuration

### To Add Google Maps Later:
1. Get an API key from [Google Cloud Console](https://console.cloud.google.com/)
2. Uncomment the line in `AndroidManifest.xml` and replace the placeholder:
   ```xml
   <meta-data android:name="com.google.android.geo.API_KEY" android:value="YOUR_ACTUAL_KEY_HERE"/>
   ```

### To Re-enable New Architecture (Optional, Advanced):
If all your dependencies support it, you can re-enable the new architecture:
1. Set `newArchEnabled=true` in `android/gradle.properties`
2. Test thoroughly as some packages may not be compatible

## Build Command Reference

```bash
# Build for Android
eas build --platform android

# Build with cache clearing
eas build --platform android --clear-cache

# Build locally (if needed)
eas build --platform android --local

# Preview build (APK instead of AAB)
eas build --platform android --profile preview
```

## Files Modified

- `android/app/build.gradle` - Added versionCode
- `android/app/src/main/AndroidManifest.xml` - Commented out invalid Google Maps key
- `app.json` - Fixed plugin configuration
- `android/gradle.properties` - Disabled new architecture, reduced architectures
- `android/app/src/main/java/com/amako/shop/MainActivity.kt` - Fixed fabric configuration

## Version Information

- Expo SDK: ~54.0.0
- React Native: 0.81.4
- React: 19.1.0
- Gradle: 8.13
- Min SDK: (inherited from Expo)
- Target SDK: (inherited from Expo)
- Hermes: Enabled
- New Architecture: Enabled (required by react-native-reanimated)

