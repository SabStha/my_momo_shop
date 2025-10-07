@echo off
echo ========================================
echo Connect to Physical Device Only
echo ========================================
echo.

echo 🔍 Checking for physical devices...
adb devices
echo.

echo 🚀 Starting Expo with tunnel (works with any device)...
echo This will work whether your physical device is connected via USB or Wi-Fi
echo.

npx expo start -c --host tunnel

echo.
echo 📱 Instructions:
echo 1. Connect your physical device via USB
echo 2. Enable USB Debugging on your phone
echo 3. Scan the QR code with Expo Go
echo 4. OR press 'a' to open on Android (should detect your physical device)
echo.
pause
