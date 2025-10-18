# 🗺️ Google Maps API Setup Guide

## ✅ What I Added

Your app now has **live driver tracking** like Uber Eats! 

Features:
- 🚴 **Live driver marker** (pink with bicycle icon)
- 🏠 **Delivery location marker** (red home icon)
- 📍 **Auto-zoom** to show both locations
- 🔄 **Updates every 10 seconds**
- ✨ **Smooth marker animation**

## 🔑 Get Your Google Maps API Key

You need a **FREE** Google Maps API key for this to work:

### Step 1: Go to Google Cloud Console
1. Visit: https://console.cloud.google.com/
2. Sign in with your Google account

### Step 2: Create a Project
1. Click **"Select a project"** at the top
2. Click **"New Project"**
3. Name it: `Amako Momo App`
4. Click **"Create"**

### Step 3: Enable APIs
1. Go to **"APIs & Services"** → **"Library"**
2. Search and enable these APIs:
   - **Maps SDK for Android**
   - **Maps SDK for iOS** (if you want iOS later)
   - **Geocoding API** (for address-to-coordinates)

### Step 4: Create API Key
1. Go to **"APIs & Services"** → **"Credentials"**
2. Click **"Create Credentials"** → **"API Key"**
3. Copy the API key (looks like: `AIzaSyABC123...`)

### Step 5: Secure Your API Key (Important!)
1. Click on the API key you just created
2. Under **"Application restrictions"**:
   - Select **"Android apps"**
3. Click **"Add an item"**
4. Enter:
   - **Package name**: `com.amako.shop`
   - **SHA-1**: (Get from your build - see below)
5. Save

### Step 6: Add API Key to Your App

Update `amako-shop/app.json`:

```json
"android": {
  "config": {
    "googleMaps": {
      "apiKey": "AIzaSyA_YOUR_ACTUAL_KEY_HERE"
    }
  }
},
"ios": {
  "config": {
    "googleMapsApiKey": "AIzaSyA_YOUR_ACTUAL_KEY_HERE"
  }
}
```

## 🔍 Get SHA-1 Certificate Fingerprint

To secure your API key, you need your app's SHA-1:

### From EAS Build:
```bash
# After a successful build
eas credentials
# Select Android → Keystore → View SHA-1
```

### Or use eas-cli:
```bash
cd amako-shop
npx eas-cli credentials
```

## 💰 Pricing (Don't Worry!)

Google Maps has **generous free tier**:
- ✅ First **$200/month FREE**
- ✅ ~28,000 map loads per month FREE
- ✅ You won't be charged unless you exceed this

For a small momo delivery app, you'll stay **well within the free tier**!

## 🧪 Testing

### Before Building:
1. Add API key to `app.json`
2. Run: `npm install` (installs react-native-maps)
3. Test in Expo with `npx expo prebuild`

### After Building:
The map will show automatically when:
- Order status is `out_for_delivery`
- Driver has shared GPS location
- Tracking data is available

## 🎨 Map Features

### Driver Marker (Pink):
- 🚴 Bicycle icon
- Pulsing circle animation
- Shows driver's real-time location

### Delivery Marker (Red):
- 🏠 Home icon  
- Shows your delivery address
- Static (doesn't move)

### Auto-Updates:
- Polls backend every 10 seconds
- Smoothly animates driver movement
- Auto-zooms to fit both markers

## ⚠️ Important Notes

1. **Without API key**: Map won't load (shows blank)
2. **With API key**: Beautiful live tracking like Uber!
3. **Free tier**: More than enough for your app

## 🚀 Next Steps

1. Get Google Maps API key (5 minutes)
2. Add to `app.json`
3. Rebuild APK
4. Test with out_for_delivery order!

---

**Map is ready - just needs the API key!** 🗺️

