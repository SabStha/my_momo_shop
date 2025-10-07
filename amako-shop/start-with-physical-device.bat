@echo off
echo ========================================
echo AmaKo Shop - Physical Device Connection
echo ========================================
echo.

echo 🔍 Step 1: Checking for connected devices...
adb devices
echo.

REM Check if any physical devices are connected (not emulator)
for /f "tokens=1" %%i in ('adb devices ^| findstr "device" ^| findstr /v "emulator"') do (
    set PHYSICAL_DEVICE=%%i
    goto :device_found
)

echo ❌ No physical device detected!
echo.
echo 📱 Please connect your Android device and:
echo    1. Enable USB Debugging in Developer Options
echo    2. Allow USB debugging when prompted
echo    3. Try a different USB cable if needed
echo.
echo 🔧 Run fix-usb-detection.bat to troubleshoot
echo.
pause
exit /b 1

:device_found
echo ✅ Physical device detected: %PHYSICAL_DEVICE%
echo.

echo 🔧 Step 2: Setting up port forwarding...
adb reverse tcp:8081 tcp:8081
adb reverse tcp:19000 tcp:19000
echo ✅ Port forwarding configured
echo.

echo 🚀 Step 3: Starting Expo development server...
echo Your physical device will connect automatically via USB
echo.
npx expo start -c --host lan

pause
