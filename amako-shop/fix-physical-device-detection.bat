@echo off
echo ========================================
echo Fix Physical Device Detection
echo ========================================
echo.

echo 🚨 PROBLEM: Expo can't find your physical device
echo ❌ Error: "No Android connected device found"
echo.

echo 📱 STEP 1: Connect Your Physical Device
echo.
echo 1. Connect your Android phone via USB cable
echo 2. Make sure it's a DATA cable (not just charging cable)
echo 3. Try different USB ports on your computer
echo.

echo 🔧 STEP 2: Enable USB Debugging on Your Phone
echo.
echo On your Android phone:
echo 1. Go to Settings ^> About Phone
echo 2. Tap "Build Number" 7 times (enables Developer Options)
echo 3. Go back to Settings ^> Developer Options
echo 4. Enable "USB Debugging"
echo 5. Enable "Install via USB" (if available)
echo 6. When you connect USB, allow debugging when prompted
echo.

echo 💻 STEP 3: Install USB Drivers on Computer
echo.
echo Download Universal ADB Drivers:
echo https://adb.clockworkmod.com/
echo.
echo Installation steps:
echo 1. Download the drivers
echo 2. Extract the zip file
echo 3. Right-click "android_winusb.inf" ^> Install
echo 4. Restart your computer
echo 5. Reconnect your phone
echo.

echo 🔍 STEP 4: Check Device Manager
echo.
echo Opening Device Manager...
echo Look for "Android Device" section
echo If you see "Unknown Device" with yellow triangle, drivers are missing
echo.
start devmgmt.msc

echo.
echo Press any key after checking Device Manager...
pause

echo.
echo 🔄 STEP 5: Restart ADB
echo.
echo Restarting ADB server...
adb kill-server
timeout /t 2 /nobreak >nul
adb start-server
timeout /t 2 /nobreak >nul

echo.
echo 🔍 STEP 6: Check Device Detection
echo.
echo Checking if your device is now detected...
adb devices
echo.

echo ✅ If your device appears above, you're ready!
echo ❌ If still not detected:
echo    - Try different USB cable
echo    - Try different USB port
echo    - Reinstall drivers
echo    - Restart both phone and computer
echo.

echo 🚀 STEP 7: Test Expo Connection
echo.
echo If device is detected, press any key to start Expo...
pause

echo.
echo Starting Expo...
npx expo start -c --host tunnel

echo.
echo Now when you press 'a', it should open on your physical device!
echo.

pause
