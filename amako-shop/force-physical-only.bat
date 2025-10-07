@echo off
echo ========================================
echo Force Physical Device Only Mode
echo ========================================
echo.

echo 🛑 Step 1: Stopping all emulators...
echo.
adb devices
echo.

REM Kill all emulators
for /f "tokens=1" %%i in ('adb devices ^| findstr "emulator"') do (
    echo Killing emulator: %%i
    adb -s %%i emu kill
)

echo.
echo 🔍 Step 2: Checking for physical devices only...
echo.
adb devices
echo.

echo 📱 Step 3: Make sure your physical device is connected
echo    - USB cable connected
echo    - USB Debugging enabled on phone
echo    - Allow USB debugging when prompted on phone
echo.

echo 🚀 Step 4: Starting Expo (Physical Device Priority Mode)...
echo.
echo This will prioritize physical devices over emulators
echo.

REM Set environment variable to prefer physical devices
set EXPO_DEVICE_PRIORITY=physical

npx expo start -c --host tunnel

echo.
echo 📱 When you press 'a':
echo - It should now open on your physical device
echo - NOT on any emulator
echo.
pause
