@echo off
echo ========================================
echo Starting Expo for iOS Development
echo ========================================
echo.
echo Using IP: 192.168.2.145 (WiFi network)
echo Make sure your iPhone/iPad is on the same WiFi!
echo.

cd /d "%~dp0"
set REACT_NATIVE_PACKAGER_HOSTNAME=192.168.2.145
npx expo start -c --host lan

pause

