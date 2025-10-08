# How to Start Expo for iOS Device

## The Problem

Your computer has two network interfaces:
- `192.168.56.1` - VirtualBox/VMware (wrong) ❌
- `192.168.2.145` - Your WiFi (correct) ✅

The system keeps picking the wrong one, causing iOS to timeout.

---

## Quick Solution

### Option 1: Use PowerShell Script (RECOMMENDED)

1. **Right-click** on `amako-shop\start-ios-device.ps1`
2. Select **"Run with PowerShell"**
3. Wait for QR code to appear
4. Scan with iPhone Camera app

### Option 2: Command Line

```powershell
cd amako-shop
$env:REACT_NATIVE_PACKAGER_HOSTNAME = "192.168.2.145"
npx expo start --clear --host lan
```

---

## Prerequisites

### 1. Check Your iPhone is on Correct WiFi

**On iPhone:**
- Settings → WiFi
- Make sure connected to same network as computer
- Tap (i) next to network name
- **IP should be like**: `192.168.2.xxx`

If IP is different (like `192.168.1.x`), you're on wrong network!

### 2. Make Sure Laravel Backend is Accessible

```powershell
# Start Laravel with network access (in main project folder)
php artisan serve --host=192.168.2.145 --port=8000
```

**Test it from your iPhone's browser:**
- Open Safari
- Go to: `http://192.168.2.145:8000`
- Should see Laravel welcome page

If this doesn't load, your iPhone can't reach your computer!

---

## Step by Step

### 1. Stop All Running Processes

```powershell
taskkill /F /IM node.exe
```

### 2. Start Expo with Correct IP

**Method A: PowerShell Script**
```powershell
cd C:\Users\user\my_momo_shop\amako-shop
.\start-ios-device.ps1
```

**Method B: Manual**
```powershell
cd amako-shop
$env:REACT_NATIVE_PACKAGER_HOSTNAME = "192.168.2.145"
npx expo start --clear --host lan
```

### 3. Connect iPhone

**On iPhone:**
1. Open native **Camera app** (not Expo Go)
2. Point at QR code in terminal
3. Tap notification
4. Opens in Expo Go ✅

---

## Troubleshooting

### Still Getting `exp://192.168.56.1` ?

The environment variable isn't working. Try this:

**Temporarily disable VirtualBox adapter:**

1. Press `Win + R`, type `ncpa.cpl`, press Enter
2. Right-click **"VirtualBox Host-Only Network"**
3. Select **"Disable"**
4. Start Expo again
5. After testing, re-enable the adapter

### Still Timing Out?

**Check firewall:**

```powershell
# Run as Administrator
New-NetFirewallRule -DisplayName "Expo Metro" -Direction Inbound -Protocol TCP -LocalPort 8081,19000,19001,19002 -Action Allow
```

**Or use Windows Firewall GUI:**
1. Windows Defender Firewall → Allow an app
2. Allow Node.js on both Private and Public networks

### "Cannot Connect to Metro"

**Check both devices are on same network:**

**On Computer:**
```powershell
ipconfig | findstr "IPv4"
# Should show: 192.168.2.145
```

**On iPhone:**
Settings → WiFi → Tap (i) → Should show: `192.168.2.xxx`

**If different networks:**
- Connect iPhone to same WiFi as computer
- Or use mobile hotspot from iPhone and connect computer to it

---

## Alternative: Use Your Phone as Hotspot

If same WiFi doesn't work:

1. **On iPhone**: Settings → Personal Hotspot → Turn On
2. **On Computer**: Connect to iPhone's hotspot
3. **Find new IP**: 
   ```powershell
   ipconfig | findstr "IPv4"
   # Might be like 172.20.10.x
   ```
4. **Update IP in script** or set manually:
   ```powershell
   $env:REACT_NATIVE_PACKAGER_HOSTNAME = "172.20.10.2"  # Your new IP
   npx expo start --clear --host lan
   ```

---

## Verification Checklist

Before scanning QR code, verify:

- [ ] Terminal shows: `exp://192.168.2.145:8081` (NOT .56.1)
- [ ] iPhone on same WiFi (Settings → WiFi)
- [ ] iPhone IP is `192.168.2.xxx`
- [ ] Can open `http://192.168.2.145:8000` in iPhone Safari
- [ ] Expo Go installed on iPhone
- [ ] Laravel backend running with `--host=192.168.2.145`

If all checked ✅, scan QR code and it should work!

---

## What You Should See

**In Terminal:**
```
› Metro waiting on exp://192.168.2.145:8081
› Scan the QR code above with Camera app (iOS)
```

**NOT this:**
```
› Metro waiting on exp://192.168.56.1:8082  ❌ WRONG IP
```

---

## Quick Commands Reference

```powershell
# Stop all Node processes
taskkill /F /IM node.exe

# Set correct IP and start
cd amako-shop
$env:REACT_NATIVE_PACKAGER_HOSTNAME = "192.168.2.145"
npx expo start --clear --host lan

# In another terminal: Start Laravel
php artisan serve --host=192.168.2.145 --port=8000

# Check your IP
ipconfig | findstr "IPv4"

# Test backend from iPhone
# Open Safari: http://192.168.2.145:8000
```

---

**TL;DR**: Use `start-ios-device.ps1` script, make sure iPhone is on same WiFi, verify IP is `192.168.2.145`, not `192.168.56.1`

