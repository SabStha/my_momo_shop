@echo off
echo ========================================
echo Windows Device Manager Check
echo ========================================
echo.

echo 🔍 Checking Windows Device Manager for Android devices...
echo.

echo Step 1: Opening Device Manager...
echo Look for these categories:
echo - "Android Device" or "Android Phone"
echo - "Portable Devices"
echo - "Universal Serial Bus devices"
echo - "Unknown devices" (with yellow warning)
echo.

start devmgmt.msc

echo.
echo 📱 Step 2: What to look for in Device Manager:
echo.
echo ✅ GOOD SIGNS:
echo - "Android Device" section with your phone name
echo - No yellow warning triangles
echo - Device shows as "Android ADB Interface"
echo.
echo ❌ PROBLEM SIGNS:
echo - "Unknown Device" with yellow triangle
echo - Device under "Other devices" with yellow triangle
echo - No "Android Device" section at all
echo.
echo 🔧 Step 3: If you see problems:
echo.
echo For "Unknown Device" with yellow triangle:
echo 1. Right-click the device
echo 2. Select "Update driver"
echo 3. Choose "Browse my computer for drivers"
echo 4. Select "Let me pick from a list of available drivers"
echo 5. Choose "Android Device" or "ADB Interface"
echo.
echo For no Android device at all:
echo 1. Install Universal ADB Drivers (run install-usb-drivers.bat)
echo 2. Or install your phone manufacturer's drivers
echo 3. Restart computer after installing drivers
echo.

echo 🔄 Step 4: After fixing drivers, check ADB detection...
echo.
echo Press any key when you've checked Device Manager...
pause

echo.
echo 🔍 Checking ADB device detection...
adb devices

echo.
echo ✅ If your physical device appears above, you're ready!
echo ❌ If not, you need to fix the Windows drivers first
echo.

pause
