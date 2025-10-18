@echo off
echo Fixing Android App Icons...
echo.

REM Install sharp for image processing if not already installed
call npm install --save-dev sharp

echo.
echo Generating Android launcher icons from appicon.png...
call npx @expo/cli prebuild --clean --platform android

echo.
echo ✅ Icons generated!
echo Now rebuild your APK with: eas build --platform android --profile preview
pause

