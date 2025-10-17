@echo off
echo ========================================
echo AmaKo Momo Shop - Beta APK Builder
echo ========================================
echo.

echo Checking if EAS CLI is installed...
where eas >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] EAS CLI not found!
    echo.
    echo Installing EAS CLI...
    call npm install -g eas-cli
    if %errorlevel% neq 0 (
        echo.
        echo [ERROR] Failed to install EAS CLI
        echo Please run: npm install -g eas-cli
        pause
        exit /b 1
    )
)

echo.
echo [OK] EAS CLI found!
echo.

echo Checking Expo login status...
eas whoami
if %errorlevel% neq 0 (
    echo.
    echo You need to login to Expo first.
    echo.
    eas login
    if %errorlevel% neq 0 (
        echo.
        echo [ERROR] Login failed
        pause
        exit /b 1
    )
)

echo.
echo ========================================
echo Starting Beta Build Process
echo ========================================
echo.
echo This will:
echo - Build a signed APK for Android
echo - Take approximately 15-20 minutes
echo - Upload to Expo servers for building
echo.

choice /C YN /M "Do you want to continue"
if errorlevel 2 (
    echo Build cancelled.
    pause
    exit /b 0
)

echo.
echo Building preview APK...
echo.

call eas build --platform android --profile preview

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Build completed successfully!
    echo ========================================
    echo.
    echo Next steps:
    echo 1. Download the APK from the link above
    echo 2. Copy it to: public\downloads\amako-shop-beta.apk
    echo 3. Share the beta page: http://your-domain.com/beta
    echo.
    echo Access codes (edit in beta-testing.blade.php):
    echo - AMAKO2025
    echo - BETA2025
    echo - MOMOTEST
    echo.
) else (
    echo.
    echo ========================================
    echo Build failed!
    echo ========================================
    echo.
    echo Common issues:
    echo - Not logged in to Expo: Run 'eas login'
    echo - Invalid eas.json: Check configuration
    echo - Network issues: Check internet connection
    echo.
    echo Check the error messages above for details.
    echo.
)

pause


