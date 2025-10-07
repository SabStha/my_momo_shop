@echo off
echo ========================================
echo Quick USB Fix for Physical Device
echo ========================================
echo.

echo 🔧 Quick fixes to try:
echo.

echo 1. Try different USB cable (some are charge-only)
echo 2. Try different USB port (prefer USB 2.0)
echo 3. Enable USB Debugging on phone
echo 4. Allow USB debugging when prompted
echo.

echo 🔍 Current device status:
adb devices
echo.

echo 📱 If no device detected, try this sequence:
echo.
echo Step 1: Unplug USB cable
echo Step 2: Wait 5 seconds
echo Step 3: Plug USB cable back in
echo Step 4: Allow USB debugging on phone
echo Step 5: Check detection again
echo.

echo Press any key to try the sequence...
pause

echo.
echo 🔄 Trying USB reconnection sequence...
echo.

echo Step 1: Restarting ADB...
adb kill-server
timeout /t 2 /nobreak >nul
adb start-server
timeout /t 2 /nobreak >nul

echo.
echo Step 2: Checking device detection...
adb devices

echo.
echo ✅ If device appears above, you're ready!
echo ❌ If still not detected, you need to install drivers
echo.

echo 🚀 Alternative: Use Wi-Fi connection
echo.
echo If USB doesn't work, you can use Wi-Fi:
echo 1. Make sure phone and computer are on same Wi-Fi
echo 2. Install Expo Go on your phone
echo 3. Scan QR code from Expo
echo.

echo Starting Expo tunnel mode (works with Wi-Fi)...
npx expo start -c --host tunnel

echo.
echo When Expo starts:
echo - Use Wi-Fi: Scan QR code with Expo Go
echo - Use USB: Press 'a' (if device is detected)
echo.

pause
