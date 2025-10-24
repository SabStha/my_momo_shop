@echo off
echo ================================
echo REBUILDING APP WITH FIXED ICON
echo ================================
echo.
echo Changes applied:
echo  - Removed adaptive icon black background
echo  - Icon will now fill entire space
echo  - Premier look without padding
echo.
echo Starting EAS build...
echo.

cd amako-shop
call eas build --platform android --profile preview --non-interactive

echo.
echo ================================
echo BUILD SUBMITTED!
echo ================================
echo.
echo Once complete:
echo  1. Download the APK from the link provided
echo  2. Install on your device
echo  3. Your icon should now be BIG and fill the full space!
echo.
pause

