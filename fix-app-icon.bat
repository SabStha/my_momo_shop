@echo off
echo Fixing app icon (black background)...
cd amako-shop

echo Removing old Android folder...
if exist android rmdir /s /q android

echo Regenerating Android with correct icon (black background)...
call npx expo prebuild --platform android

echo.
echo Done! Icon background changed to BLACK (#000000)
echo Now build the APK with:
echo eas build --profile preview --platform android --clear-cache
echo.

pause

