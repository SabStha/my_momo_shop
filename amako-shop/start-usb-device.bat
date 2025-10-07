@echo off
echo ========================================
echo AmaKo Shop - USB Device Connection
echo ========================================
echo.

REM Check if ADB is available
adb version >nul 2>&1
if errorlevel 1 (
    echo ❌ ADB not found! Please install Android SDK Platform Tools
    echo Download from: https://developer.android.com/studio/releases/platform-tools
    pause
    exit /b 1
)

echo ✅ ADB found
echo.

REM Check if device is connected
echo 🔍 Checking for connected devices...
adb devices
echo.

REM Set up port forwarding
echo 🔧 Setting up port forwarding...
adb reverse tcp:8081 tcp:8081
if errorlevel 1 (
    echo ❌ Failed to set up port forwarding
    echo Make sure your device has USB debugging enabled
    pause
    exit /b 1
)

adb reverse tcp:19000 tcp:19000
if errorlevel 1 (
    echo ❌ Failed to set up port forwarding for port 19000
    pause
    exit /b 1
)

echo ✅ Port forwarding set up successfully
echo.

REM Start Expo with LAN mode (will use localhost via USB)
echo 🚀 Starting Expo development server...
echo Your device will connect via USB (localhost)
echo.
npx expo start -c --host lan

pause
