@echo off
echo ========================================
echo Install USB Drivers for Physical Device
echo ========================================
echo.

echo 🔍 Step 1: Checking current device detection...
echo.
adb devices
echo.

echo 📱 Step 2: Physical Device Setup Instructions
echo.
echo PLEASE DO THESE STEPS ON YOUR ANDROID PHONE:
echo.
echo 1. Connect your Android device via USB cable
echo 2. On your phone, go to Settings ^> About Phone
echo 3. Tap "Build Number" 7 times (enables Developer Options)
echo 4. Go back to Settings ^> Developer Options
echo 5. Enable "USB Debugging"
echo 6. Enable "Install via USB" (if available)
echo 7. Enable "USB Debugging (Security Settings)" (if available)
echo.

echo 💻 Step 3: Computer-side Setup
echo.
echo 1. When you connect USB, Windows should prompt for driver installation
echo 2. If prompted, choose "Install drivers automatically"
echo 3. If no prompt, check Device Manager for unknown devices
echo.

echo 🔧 Step 4: Installing Universal ADB Drivers...
echo.
echo Downloading Universal ADB Drivers...
echo This will help Windows recognize your Android device
echo.

REM Check if we can download Universal ADB Drivers
echo 📥 Universal ADB Drivers Download:
echo https://adb.clockworkmod.com/
echo.
echo Manual installation steps:
echo 1. Download Universal ADB Drivers from above link
echo 2. Extract the downloaded file
echo 3. Right-click "android_winusb.inf" ^> Install
echo 4. Restart your computer
echo 5. Reconnect your Android device
echo.

echo 🔄 Step 5: Restarting ADB after driver installation...
echo.
echo After installing drivers, press any key to continue...
pause

adb kill-server
timeout /t 2 /nobreak >nul
adb start-server
timeout /t 2 /nobreak >nul

echo.
echo 🔍 Step 6: Checking device detection after driver installation...
adb devices
echo.

echo ✅ If your physical device now appears above, you're ready!
echo ❌ If still not detected, try:
echo    - Different USB cable
echo    - Different USB port
echo    - Restart both phone and computer
echo.

pause
