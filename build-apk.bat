@echo off
REM Build Android APK for Amako Shop
REM This script ensures a clean build to avoid the "build command failed" error

echo ========================================
echo Amako Shop - Android APK Builder
echo ========================================
echo.

cd amako-shop

echo [1/5] Checking EAS CLI...
call npx eas --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: EAS CLI not found. Installing...
    call npm install -g eas-cli
)
echo ✓ EAS CLI ready

echo.
echo [2/5] Cleaning build cache...
if exist "android\.gradle" (
    rmdir /s /q "android\.gradle"
    echo ✓ Cleaned .gradle
)
if exist "android\build" (
    rmdir /s /q "android\build"
    echo ✓ Cleaned build
)
if exist "android\app\build" (
    rmdir /s /q "android\app\build"
    echo ✓ Cleaned app/build
)
if exist "node_modules\.cache" (
    rmdir /s /q "node_modules\.cache"
    echo ✓ Cleaned node_modules cache
)
echo ✓ Build cache cleaned

echo.
echo [3/5] Checking dependencies...
if not exist "node_modules" (
    echo Installing dependencies...
    call npm install
) else (
    echo ✓ Dependencies already installed
)

echo.
echo [4/5] Logging in to Expo...
call eas login
if errorlevel 1 (
    echo ERROR: Failed to login to Expo
    pause
    exit /b 1
)
echo ✓ Logged in

echo.
echo [5/5] Building APK...
echo This will take 10-20 minutes. The build runs on Expo servers.
echo.
echo Build profile: PREVIEW (APK for testing)
echo.
echo You can close this window and check progress at:
echo https://expo.dev/accounts/sabstha98/projects/amako-shop/builds
echo.

call eas build --platform android --profile preview

if errorlevel 1 (
    echo.
    echo ========================================
    echo BUILD FAILED
    echo ========================================
    echo.
    echo Common fixes:
    echo 1. Check build logs at: https://expo.dev
    echo 2. Try with --clear-cache flag
    echo 3. Check android/app/build.gradle for errors
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo BUILD SUCCESSFUL!
echo ========================================
echo.
echo Next steps:
echo 1. Download APK from: https://expo.dev
echo 2. Install on device: adb install -r yourapp.apk
echo 3. Test login flow thoroughly
echo.
pause

