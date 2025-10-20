@echo off
REM Install and test APK on connected Android device

echo ========================================
echo Amako Shop - APK Installer and Tester
echo ========================================
echo.

REM Check if ADB is available
adb version >nul 2>&1
if errorlevel 1 (
    echo ERROR: ADB not found in PATH
    echo Please install Android SDK Platform Tools
    pause
    exit /b 1
)

echo [1/4] Checking connected devices...
adb devices
echo.

set /p apk_path="Enter path to APK file (or drag and drop): "
set apk_path=%apk_path:"=%

if not exist "%apk_path%" (
    echo ERROR: APK file not found: %apk_path%
    pause
    exit /b 1
)

echo.
echo [2/4] Uninstalling previous version...
adb uninstall com.amako.shop 2>nul
echo ✓ Previous version removed (if existed)

echo.
echo [3/4] Installing APK...
adb install -r "%apk_path%"
if errorlevel 1 (
    echo ERROR: Failed to install APK
    pause
    exit /b 1
)
echo ✓ APK installed successfully

echo.
echo [4/4] Starting logcat monitoring...
echo.
echo The app should now be installed on your device.
echo Press Ctrl+C to stop monitoring logs.
echo.
echo ACTION REQUIRED: Open the app and try to login
echo.
pause

echo.
echo Monitoring app logs (looking for crashes)...
echo Press Ctrl+C to stop
echo.
adb logcat | findstr /i "amako expo react crash error fatal"

