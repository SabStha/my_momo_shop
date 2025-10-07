@echo off
echo ========================================
echo Test Physical Device Detection
echo ========================================
echo.

echo 📱 INSTRUCTIONS:
echo 1. Connect your Android phone via USB cable
echo 2. Enable USB Debugging on your phone
echo 3. Allow USB debugging when prompted
echo 4. Press any key when ready to test
echo.
pause

echo.
echo 🔍 Testing device detection...
echo.

echo Step 1: Checking ADB devices...
adb devices
echo.

echo Step 2: Checking for emulators vs physical devices...
for /f "tokens=1,2" %%a in ('adb devices ^| findstr "device"') do (
    if "%%b"=="device" (
        if "%%a"=="emulator-5554" (
            echo ❌ Emulator detected: %%a
        ) else (
            echo ✅ Physical device detected: %%a
        )
    )
)
echo.

echo Step 3: Checking Windows Device Manager...
echo Opening Device Manager - look for "Android Device" section
start devmgmt.msc

echo.
echo 📊 RESULTS:
echo.
echo If you see a physical device listed above:
echo ✅ Your device is detected - you can use Expo
echo.
echo If you only see emulator or nothing:
echo ❌ Your physical device is not detected
echo    - Check USB cable
echo    - Check USB Debugging is enabled
echo    - Install proper drivers
echo.

echo 🚀 Step 4: Testing Expo connection...
echo.
echo Starting Expo tunnel mode...
npx expo start -c --host tunnel

echo.
echo When Expo starts:
echo - Press 'a' to open Android
echo - It should open on your physical device (if detected)
echo - NOT on the emulator
echo.

pause
