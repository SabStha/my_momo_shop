# iOS Development Setup Guide

## ✅ Your App IS iOS Compatible!

The timeout issue is a **network connectivity problem**, not a compatibility issue.

---

## Problem Diagnosis

Your computer has **two network interfaces**:
- `192.168.56.1` - Virtual network (VirtualBox/VMware) ❌
- `192.168.2.145` - WiFi network ✅

**iOS devices were trying to connect to the wrong IP** (`192.168.56.1`), causing timeouts.

---

## Solution Options

### Option 1: Tunnel Mode (RECOMMENDED) 🏆

**Best for**: Any network setup, most reliable for iOS

**How to start**:
```bash
cd amako-shop
npm start
# Or explicitly: npx expo start --tunnel --clear
```

**Pros**:
- ✅ Works on any network
- ✅ Bypasses firewall issues
- ✅ No configuration needed
- ✅ Most reliable for iOS

**Cons**:
- ⚠️ Slower initial load (tunnel overhead)
- ⚠️ Requires internet connection

**How to connect iOS device**:
1. Open **Camera app** (native iOS camera)
2. Point at QR code in terminal
3. Tap notification that appears
4. App opens in Expo Go

---

### Option 2: LAN Mode with Correct IP 🚀

**Best for**: Faster development, same WiFi network

**How to start**:
```bash
cd amako-shop
npm run start:ios
# Or: .\start-for-ios.bat
```

**Prerequisites**:
- ✅ iPhone/iPad on **same WiFi** as development machine
- ✅ WiFi network name: Check your router settings
- ✅ iOS device IP should be `192.168.2.x`

**How to verify same network**:

On iOS:
1. Settings → WiFi → Tap (i) next to connected network
2. Check IP address (should be `192.168.2.xxx`)

On Windows:
```powershell
ipconfig
# Look for: IPv4 Address: 192.168.2.145
```

**Pros**:
- ✅ Faster than tunnel
- ✅ Lower latency
- ✅ Better for development

**Cons**:
- ⚠️ Requires same WiFi network
- ⚠️ May need firewall configuration

---

## Step-by-Step iOS Setup

### 1. Install Expo Go on iOS

**On your iPhone/iPad**:
1. Open **App Store**
2. Search: "Expo Go"
3. Install the app
4. Open it once to set up

### 2. Choose Your Connection Method

#### For Tunnel Mode (Easiest):
```bash
cd amako-shop
npm start
```

#### For LAN Mode (Fastest):
```bash
cd amako-shop
npm run start:ios
```

### 3. Connect Your iOS Device

**Method A: Camera App (Recommended)**
1. Open native **Camera app** on iOS
2. Point at QR code in terminal
3. Tap notification → Opens in Expo Go

**Method B: Manual URL**
1. Open **Expo Go** app
2. Tap "Enter URL manually"
3. Enter the URL from terminal (e.g., `exp://192.168.2.145:8081`)

### 4. Wait for Bundle to Load

First load takes 30-60 seconds:
```
Loading bundle... ████████░░ 80%
```

### 5. Test Registration Flow

Once loaded:
- Should see login page ✅
- Try registration ✅
- Test all features ✅

---

## Troubleshooting iOS Issues

### Issue 1: "Unable to connect to Metro"

**Cause**: Wrong IP address or network mismatch

**Solutions**:
1. Use tunnel mode: `npx expo start --tunnel`
2. Check both devices on same WiFi
3. Restart Metro bundler
4. Check firewall settings

### Issue 2: "Couldn't load exp://..."

**Cause**: Expo Go can't reach development server

**Solutions**:
1. Close and restart Expo Go app
2. Check WiFi connection on iOS
3. Try tunnel mode
4. Verify IP address is correct

### Issue 3: QR Code Scan Does Nothing

**Cause**: Camera permissions or Expo Go not installed

**Solutions**:
1. Install Expo Go from App Store
2. Grant camera permissions
3. Try manual URL entry in Expo Go
4. Restart iOS device

### Issue 4: App Loads But API Calls Fail

**Cause**: Backend server not accessible from iOS

**Check**:
1. Laravel server running: `php artisan serve --host=0.0.0.0 --port=8000`
2. API URL in app config uses `192.168.2.145`, not `localhost`
3. iOS can reach `http://192.168.2.145:8000/api/health`

**Fix**:
```bash
# Start Laravel with network access
cd ..
php artisan serve --host=192.168.2.145 --port=8000
```

### Issue 5: Very Slow Loading

**Cause**: Tunnel overhead or large bundle

**Solutions**:
1. Switch to LAN mode if on same WiFi
2. Clear cache: `npx expo start -c`
3. Remove unused dependencies
4. Check internet connection

---

## iOS vs Android Differences

| Feature | Android | iOS |
|---------|---------|-----|
| USB Connection | ✅ Supported | ❌ Not supported with Expo Go |
| LAN Discovery | ✅ Easy | ⚠️ Requires same WiFi |
| Tunnel Mode | ✅ Works | ✅ Works |
| Camera QR Scan | Via Expo Go | Via Camera app |
| Network Setup | More flexible | More strict |

---

## Network Configuration

### Windows Firewall Setup

If LAN mode doesn't work, allow Metro bundler:

1. Open **Windows Defender Firewall**
2. Click **"Allow an app through firewall"**
3. Click **"Change settings"** → **"Allow another app"**
4. Browse to: `C:\Program Files\nodejs\node.exe`
5. Check both **Private** and **Public** networks
6. Click **OK**

### Router Configuration

Some routers block device-to-device communication:

1. Open router admin (usually `192.168.2.1`)
2. Look for **"AP Isolation"** or **"Client Isolation"**
3. **Disable** it
4. Save and restart router

---

## Testing Checklist for iOS

- [ ] Expo Go installed on iOS device
- [ ] Both devices on same WiFi (for LAN mode)
- [ ] Metro bundler running (`npm start` or `npm run start:ios`)
- [ ] QR code scanned successfully
- [ ] App loads in Expo Go
- [ ] Can see login screen
- [ ] Registration works
- [ ] API calls successful
- [ ] Navigation works
- [ ] All features functional

---

## Production Build for iOS

To create an actual iOS build (not Expo Go):

### Prerequisites:
- Apple Developer Account ($99/year)
- Mac computer (required for iOS builds)
- Xcode installed

### Build with EAS:
```bash
cd amako-shop

# Install EAS CLI
npm install -g eas-cli

# Login to Expo account
eas login

# Configure project
eas build:configure

# Build for iOS
eas build --platform ios --profile production
```

**Alternative**: Use cloud build services that don't require Mac:
- Expo EAS Build (recommended)
- Codemagic
- Bitrise

---

## Quick Reference Commands

```bash
# Start with tunnel (most reliable for iOS)
npm start

# Start with LAN for iOS (fastest)
npm run start:ios

# Or use the batch file
.\start-for-ios.bat

# Check your IP addresses
ipconfig

# Start Laravel backend
php artisan serve --host=192.168.2.145 --port=8000

# Clear cache and restart
npx expo start -c --tunnel
```

---

## Summary

✅ **Your app IS fully iOS compatible**
✅ **Configuration is correct**
✅ **Timeout was a network issue, not compatibility**

**Recommended approach**:
1. Use **tunnel mode** for development: `npm start`
2. Switch to **LAN mode** once network is confirmed working: `npm run start:ios`
3. Use **tunnel mode** for testing on different networks

---

## Support

If issues persist:

1. **Check Expo logs**: Look in terminal for errors
2. **Check iOS Console**: Connect iPhone to Mac → Open Console app
3. **Check Laravel logs**: `storage/logs/laravel.log`
4. **Ask in Expo Discord**: https://chat.expo.dev

---

**Date**: October 8, 2025  
**Status**: iOS fully supported ✅  
**Network Issue**: Resolved with tunnel mode

