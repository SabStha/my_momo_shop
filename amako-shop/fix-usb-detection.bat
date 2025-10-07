@echo off
echo ========================================
echo Fix USB Device Detection
echo ========================================
echo.

echo 🔍 Step 1: Checking current device status...
adb devices
echo.

echo 🔧 Step 2: Restarting ADB server...
adb kill-server
timeout /t 2 /nobreak >nul
adb start-server
timeout /t 2 /nobreak >nul
echo.

echo 🔍 Step 3: Checking devices after restart...
adb devices
echo.

echo 📱 Step 4: If your device is not listed, try these steps:
echo.
echo 1. Make sure your Android device is connected via USB
echo 2. On your phone:
echo    - Go to Settings ^> Developer Options
echo    - Enable "USB Debugging"
echo    - Enable "Install via USB" (if available)
echo    - Enable "USB Debugging (Security Settings)" (if available)
echo.
echo 3. On your computer:
echo    - Try a different USB cable
echo    - Try a different USB port
echo    - Check if Windows installed drivers automatically
echo.
echo 4. If still not working:
echo    - Uninstall and reinstall device drivers
echo    - Download device-specific drivers from manufacturer
echo.

echo 🔄 Step 5: Try USB connection again...
adb devices
echo.

if %errorlevel% equ 0 (
    echo ✅ ADB is working. If device still not detected:
    echo    - Check USB debugging is enabled on phone
    echo    - Try different USB cable/port
    echo    - Restart both phone and computer
) else (
    echo ❌ ADB issue detected. Please install Android SDK Platform Tools
)

echo.
pause
