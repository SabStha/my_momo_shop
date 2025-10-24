# App Icon Fix Guide

## Current Issue
The app icon appears small with white background because `appicon.png` has too much black padding/space around the actual logo.

## Quick Fix - Try This First

### Step 1: Regenerate Android folder
```powershell
cd C:\Users\user\my_momo_shop\amako-shop
Remove-Item -Recurse -Force android
npx expo prebuild --platform android
```

### Step 2: Build APK
```powershell
eas build --profile preview --platform android --clear-cache
```

---

## If Icon Still Appears Too Small

You need to create a cropped version of your icon. Here's how:

### Option A: Use Online Tool
1. Go to https://www.remove.bg/ or any image editor
2. Upload `assets/appicon.png`
3. Crop it to show just the logo portion (blue arch with momos) without excessive black space
4. Export as `assets/appicon-cropped.png` (1024x1024 pixels recommended)

### Option B: Use Icon Generator
1. Go to https://icon.kitchen/
2. Upload your logo
3. Choose "Foreground" style
4. Set padding to minimum
5. Download and replace `assets/appicon.png`

### After Creating Better Icon:
```powershell
# Update app.json to use the new icon
# Then regenerate:
cd C:\Users\user\my_momo_shop\amako-shop
Remove-Item -Recurse -Force android
npx expo prebuild --platform android
eas build --profile preview --platform android --clear-cache
```

---

## Current Configuration

Your `app.json` is set to:
- **Icon**: `./assets/appicon.png`
- **Adaptive Icon Foreground**: `./assets/appicon.png`
- **Adaptive Icon Background**: `#000000` (black)

The adaptive icon will crop and scale your foreground image, but if the image itself has too much padding, the logo will still appear small.

---

## Recommended Icon Specs

For best results, your icon should be:
- **Size**: 1024x1024 pixels (or at minimum 512x512)
- **Padding**: Maximum 10-15% padding around the logo
- **Format**: PNG with transparency OR PNG with solid background
- **Content**: Logo should fill 70-80% of the canvas



