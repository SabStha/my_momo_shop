# 🔍 Debug Loading GIF - ADB Commands

## Quick Start (Use This!)

### Windows:
```bash
debug-loading-gif.bat
```

### Linux/Mac:
```bash
chmod +x debug-loading-gif.sh
./debug-loading-gif.sh
```

---

## Manual Commands (If Script Doesn't Work)

### Step 1: Connect Device and Clear Logs
```bash
# Check device is connected
adb devices

# Clear old logs
adb logcat -c
```

### Step 2: Start Capturing Logs
```bash
# Capture all relevant logs for loading GIF
adb logcat *:E ReactNativeJS:V expo:V Image:V GIF:V Asset:V LoadingSpinner:V
```

### Step 3: In Your App
1. Open the app
2. Trigger loading state (pull to refresh on any page)
3. Look for where loading GIF should appear
4. Wait 5-10 seconds

### Step 4: Stop and Save Logs
1. Press `Ctrl+C` to stop logging
2. Copy all the output
3. Send it to me

---

## Alternative: Save Logs to File

### Windows:
```bash
# Clear logs
adb logcat -c

# Capture to file (run this, then use app, then press Ctrl+C)
adb logcat *:E ReactNativeJS:V expo:V Image:V GIF:V Asset:V LoadingSpinner:V > gif-debug.txt
```

### Linux/Mac:
```bash
# Clear logs
adb logcat -c

# Capture to file
adb logcat *:E ReactNativeJS:V expo:V Image:V GIF:V Asset:V LoadingSpinner:V > gif-debug.txt
```

Then send me the `gif-debug.txt` file contents.

---

## What to Look For

When you send me the logs, I'll look for:

1. ❌ **Image Loading Errors:**
   ```
   E/ReactNativeJS: Failed to load image
   E/Image: Unable to load asset
   ```

2. ❌ **Asset Not Found:**
   ```
   E/expo: Asset not found: assets/animations/loading.gif
   ```

3. ❌ **GIF Decode Errors:**
   ```
   E/GIF: Failed to decode GIF
   E/Fresco: Unable to decode image
   ```

4. ❌ **File Path Issues:**
   ```
   E/ReactNativeJS: Module not found
   ENOENT: no such file or directory
   ```

---

## Even More Detailed Logs (If Needed)

If the above doesn't show enough, try this:

```bash
# Clear logs
adb logcat -c

# Capture EVERYTHING (will be very verbose)
adb logcat | grep -i -E "loading|gif|image|asset|spinner"
```

---

## Quick Test Commands

### Test 1: Check if GIF file exists in APK
```bash
# Windows
adb shell pm path com.amako.shop
adb pull <path-from-above> app.apk
unzip -l app.apk | findstr loading.gif

# Linux/Mac
adb shell pm path com.amako.shop
adb pull <path-from-above> app.apk
unzip -l app.apk | grep loading.gif
```

### Test 2: Check app assets directory
```bash
adb shell run-as com.amako.shop ls -la files/
```

---

## What I Need From You

After running the commands, send me:

1. **The full log output** from when you triggered the loading state
2. **What you did** to trigger it (e.g., "pulled to refresh on home page")
3. **What you saw** (e.g., "blank space where GIF should be" or "placeholder image")

---

## Simplified Single Command (Copy-Paste This)

```bash
adb logcat -c && adb logcat *:E ReactNativeJS:V expo:V Image:V GIF:V Asset:V LoadingSpinner:V
```

**Then:**
1. Trigger loading in your app
2. Wait 10 seconds
3. Press `Ctrl+C`
4. Copy ALL the output
5. Send to me

---

## Expected Good Output (No Errors)

If GIF is working, you should see:
```
I/ReactNativeJS: ⏳ Loading...
I/ReactNativeJS: 🎞️ Loading GIF displayed
V/Image: Successfully loaded: assets/animations/loading.gif
```

## Expected Bad Output (GIF Not Working)

If GIF has issues, you'll see:
```
E/ReactNativeJS: Failed to load image: assets/animations/loading.gif
E/Image: Unable to decode image
E/expo-asset: Asset not found in bundle
```

---

**Ready? Run this command and trigger the loading in your app:**

```bash
adb logcat -c && adb logcat *:E ReactNativeJS:V expo:V Image:V GIF:V Asset:V LoadingSpinner:V
```

Then send me the output! 🔍




