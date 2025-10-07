@echo off
echo ========================================
echo Force Physical Device Connection
echo ========================================
echo.

echo 🔧 Step 1: Killing emulator...
adb -s emulator-5554 emu kill
timeout /t 3 /nobreak >nul
echo.

echo 🔍 Step 2: Checking available devices...
adb devices
echo.

echo 🔌 Step 3: Make sure your physical device is connected via USB
echo    - Enable USB Debugging on your phone
echo    - Allow USB debugging when prompted
echo    - Try a different USB cable if needed
echo.

echo 🚀 Step 4: Starting Expo (will prioritize physical device)...
echo.
npx expo start -c --host tunnel

echo.
echo 📱 When prompted:
echo - Press 'a' to open Android
echo - It should now open on your physical device instead of emulator
echo.
pause
