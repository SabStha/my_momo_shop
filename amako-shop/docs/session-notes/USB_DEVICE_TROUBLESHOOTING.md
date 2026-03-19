# USB Device Detection Troubleshooting

## 🚨 Current Issue
Your system is only detecting the Android emulator (`emulator-5554`) but not your physical device. This is why automatic USB connection isn't working.

## 🔍 Diagnosis
```bash
adb devices
# Output: emulator-5554   device
# Missing: Your physical device
```

## ✅ Solutions (Try in Order)

### Solution 1: Enable USB Debugging on Your Phone
1. **Go to Settings** on your Android device
2. **Find "About Phone"** or "About Device"
3. **Tap "Build Number"** 7 times (enables Developer Options)
4. **Go back to Settings** > **Developer Options**
5. **Enable these options:**
   - ✅ USB Debugging
   - ✅ Install via USB (if available)
   - ✅ USB Debugging (Security Settings) (if available)
   - ✅ USB Debugging (Revoke USB debugging authorizations) - then tap "OK"

### Solution 2: Check USB Connection
1. **Try a different USB cable** (some cables are charge-only)
2. **Try a different USB port** on your computer
3. **Use a USB 2.0 port** (not USB 3.0) if available
4. **Unplug and reconnect** the device

### Solution 3: Computer-Side Fixes
1. **Restart ADB server:**
   ```bash
   adb kill-server
   adb start-server
   adb devices
   ```

2. **Check Windows Device Manager:**
   - Press `Win + X` > Device Manager
   - Look for "Android Device" or your phone name
   - If you see a yellow warning, update drivers

3. **Install proper drivers:**
   - Download from your phone manufacturer's website
   - Or use Universal ADB Drivers

### Solution 4: Phone-Side Authorization
1. **When you connect the USB cable:**
   - Your phone should show "Allow USB debugging?"
   - Check "Always allow from this computer"
   - Tap "OK"

2. **If no prompt appears:**
   - Go to Developer Options
   - Tap "Revoke USB debugging authorizations"
   - Disconnect and reconnect USB cable
   - Allow when prompted

### Solution 5: Alternative Connection Methods

#### Option A: Wi-Fi Connection (No USB needed)
```bash
npm run start:lan
```
- Make sure phone and computer are on same Wi-Fi
- Scan QR code with Expo Go

#### Option B: Tunnel Connection (Always works)
```bash
npm run start:tunnel
```
- Works even if devices are on different networks
- Slightly slower but very reliable

## 🔧 Quick Fix Scripts

I've created these scripts to help:

1. **`fix-usb-detection.bat`** - Diagnose USB issues
2. **`start-with-physical-device.bat`** - Start only when physical device detected
3. **`troubleshoot-device.bat`** - Complete system check

## 📱 Device-Specific Tips

### Samsung Devices
- Enable "USB Debugging" and "Install via USB"
- Install Samsung USB drivers

### Google Pixel
- Enable "USB Debugging"
- Use original USB-C cable

### OnePlus
- Enable "USB Debugging" and "OEM Unlocking"
- Install OnePlus USB drivers

### Xiaomi/MIUI
- Enable "USB Debugging" and "Install via USB"
- Enable "USB Debugging (Security Settings)"

## 🎯 Quick Test Commands

```bash
# Check devices
adb devices

# Restart ADB
adb kill-server && adb start-server

# Check ADB version
adb version

# Try Wi-Fi connection
npm run start:lan

# Try tunnel connection
npm run start:tunnel
```

## 🚀 Expected Result

After fixing, `adb devices` should show:
```
List of devices attached
emulator-5554   device
ABC123DEF456   device    <- Your physical device
```

Then you can use:
```bash
npm run start:usb
```

## 📞 Still Not Working?

1. **Try Wi-Fi connection first:** `npm run start:lan`
2. **Use tunnel mode:** `npm run start:tunnel`
3. **Check your phone's Developer Options again**
4. **Try a different computer** to test if it's device-specific
5. **Update your phone's Android version** (older versions have issues)
