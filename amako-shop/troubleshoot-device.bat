@echo off
echo ========================================
echo AmaKo Shop - Device Troubleshooting
echo ========================================
echo.

echo 🔍 Checking your system configuration...
echo.

REM Check network configuration
echo 📡 Network Configuration:
ipconfig | findstr "IPv4"
echo.

REM Check if ADB is available
echo 🔧 ADB Status:
adb version >nul 2>&1
if errorlevel 1 (
    echo ❌ ADB not found - Install Android SDK Platform Tools
) else (
    echo ✅ ADB is available
)
echo.

REM Check connected devices
echo 📱 Connected Devices:
adb devices
echo.

REM Check if Expo is installed
echo 📦 Expo Status:
npx expo --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Expo CLI not found - Run: npm install -g @expo/cli
) else (
    echo ✅ Expo CLI is available
)
echo.

echo 💡 Troubleshooting Tips:
echo.
echo 1. For Wi-Fi connection:
echo    - Make sure your phone and computer are on the same Wi-Fi
echo    - Use: npm run start:lan
echo    - Or run: start-physical-device.bat
echo.
echo 2. For USB connection:
echo    - Enable USB debugging on your Android device
echo    - Connect via USB cable
echo    - Use: npm run start:usb
echo    - Or run: start-usb-device.bat
echo.
echo 3. For tunnel connection (works everywhere):
echo    - Use: npm run start:tunnel
echo    - This works even if devices are on different networks
echo.

pause
