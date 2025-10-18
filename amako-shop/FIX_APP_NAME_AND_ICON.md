# Fix App Name and Icon in Built APK

## Problem

- ❌ Built APK shows "AmaKo Shop" instead of "AmakoMomo"
- ❌ Built APK shows placeholder icon instead of assets/appicon.png
- ✅ Expo development shows correct name and icon

## Root Cause

You have a native `android/` directory that **overrides** the Expo `app.json` configuration:

1. **App Name:** Defined in `android/app/src/main/res/values/strings.xml`
2. **App Icon:** Uses `.webp` placeholder files in `android/app/src/main/res/mipmap-*/` folders

When EAS Build detects a native android directory, it uses those files instead of generating from `app.json`.

## Fixes Applied

### ✅ Fix 1: App Name Updated

Changed `android/app/src/main/res/values/strings.xml`:

```xml
<!-- BEFORE -->
<string name="app_name">AmaKo Shop</string>

<!-- AFTER -->
<string name="app_name">AmakoMomo</string>
```

### ⚙️ Fix 2: Regenerate App Icons

You have 2 options:

#### Option A: Delete Android Directory (Recommended)

Let EAS Build regenerate everything fresh from `app.json`:

```bash
cd amako-shop
rm -rf android
eas build --platform android --profile preview
```

EAS will automatically recreate the android directory with:
- Correct app name from app.json
- Correct icons from assets/appicon.png
- All proper configurations

#### Option B: Regenerate Icons Only

Keep your android directory but regenerate just the icons:

```bash
cd amako-shop

# Install icon generator
npm install -g @expo/prebuild

# Regenerate native folders (will update icons)
npx expo prebuild --clean --platform android

# Then build
eas build --platform android --profile preview
```

#### Option C: Manual Icon Generation

Use online tools or scripts to generate all icon sizes:

1. Use https://romannurik.github.io/AndroidAssetStudio/icons-launcher.html
2. Upload your `assets/appicon.png`
3. Download the generated icons
4. Replace all files in `android/app/src/main/res/mipmap-*/` folders

## Recommended Approach

**Use Option A (Delete android directory)** because:
- ✅ Cleanest approach
- ✅ No manual work needed
- ✅ Icons generated in all required sizes
- ✅ Adaptive icon support
- ✅ All configurations from app.json applied correctly

## Step-by-Step Guide

### 1. Backup (Optional)

If you have custom Android code:

```bash
cd amako-shop
cp -r android android_backup
```

### 2. Delete Android Directory

```bash
cd amako-shop
rm -rf android
```

Or in Windows PowerShell:
```powershell
cd amako-shop
Remove-Item -Recurse -Force android
```

### 3. Build with EAS

```bash
eas build --platform android --profile preview --non-interactive
```

EAS will automatically:
- Recreate android directory
- Generate icons from assets/appicon.png in all sizes
- Use "AmakoMomo" as app name from app.json
- Apply all other android config from app.json

### 4. Verify the Build

After build completes:
- Download and install APK
- Check app name in launcher: Should say **"AmakoMomo"**
- Check app icon: Should show your custom icon

## What Gets Generated

EAS creates proper Android launcher icons in all required densities:

```
mipmap-mdpi/ic_launcher.png (48x48)
mipmap-hdpi/ic_launcher.png (72x72)
mipmap-xhdpi/ic_launcher.png (96x96)
mipmap-xxhdpi/ic_launcher.png (144x144)
mipmap-xxxhdpi/ic_launcher.png (192x192)
```

Plus adaptive icon components for Android 8.0+.

## Alternative: Keep Android Directory

If you want to keep the android directory and just fix the icons:

### Step 1: Install Icon Generator

```bash
npm install -g app-icon
```

### Step 2: Generate Icons

```bash
cd amako-shop
app-icon generate -i ./assets/appicon.png --android
```

### Step 3: Move Icons to Mipmap Folders

The generated icons need to be moved to:
```
android/app/src/main/res/mipmap-*/ic_launcher.png
android/app/src/main/res/mipmap-*/ic_launcher_round.png
android/app/src/main/res/mipmap-*/ic_launcher_foreground.png
```

## Testing

After rebuild:

1. **Install APK** on device
2. **Check launcher** - should show "AmakoMomo" with your icon
3. **Check app drawer** - same
4. **Check settings** - app info should show correct name and icon

## If Icons Still Don't Update After Install

Android launcher caches icons aggressively:

1. **Uninstall completely**
2. **Restart device**
3. **Reinstall APK**

Or clear launcher cache:
- Settings → Apps → [Your Launcher] → Storage → Clear Cache

## Current Status

✅ **App Name Fixed** - strings.xml updated to "AmakoMomo"
⚠️ **App Icons Need Regeneration** - Choose Option A, B, or C above

## Quick Fix Command

```bash
cd amako-shop
rm -rf android
eas build --platform android --profile preview --non-interactive
```

This will rebuild everything correctly with your proper app name and icon! 🎉

