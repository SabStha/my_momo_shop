@echo off
echo ========================================
echo Test Connection Methods
echo ========================================
echo.

echo 📱 METHOD 1: Wi-Fi Connection (RECOMMENDED)
echo.
echo ✅ This is already working! Your Expo tunnel is running.
echo.
echo Steps:
echo 1. Install Expo Go on your phone
echo 2. Make sure phone and computer are on same Wi-Fi
echo 3. Scan the QR code from your terminal
echo 4. Your app will load on your physical device!
echo.

echo 🔌 METHOD 2: USB Connection (Optional)
echo.
echo To fix USB detection:
echo 1. Download Universal ADB Drivers: https://adb.clockworkmod.com/
echo 2. Install the drivers and restart computer
echo 3. Enable USB Debugging on phone
echo.

echo 🔍 Current Status:
echo.
adb devices
echo.

if %errorlevel% equ 0 (
    echo ✅ ADB is working
) else (
    echo ❌ ADB issue detected
)

echo.
echo 🚀 Starting Expo tunnel mode...
echo.
npx expo start -c --host tunnel

echo.
echo 📱 INSTRUCTIONS:
echo.
echo 1. Use Wi-Fi: Scan QR code with Expo Go (recommended)
echo 2. Use USB: Press 'a' (only works if device is detected)
echo.
echo Your physical device will work with Wi-Fi connection!
echo.

pause
