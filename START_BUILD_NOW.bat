@echo off
echo ========================================
echo  Building Universal APK with EAS
echo ========================================
echo.
echo This will build an APK that works on:
echo   - Real Android phones (ARM)
echo   - Android emulators (x86)
echo.
echo Build time: ~15-20 minutes
echo.
pause

cd amako-shop

echo.
echo Starting EAS build...
echo.

eas build --platform android --profile preview-universal --non-interactive

echo.
echo ========================================
echo  Build Complete!
echo ========================================
echo.
echo Download your APK from the link above
echo or scan the QR code to install.
echo.
pause


