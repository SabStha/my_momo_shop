@echo off
echo Starting Expo for Physical Device...
echo Using Wi-Fi IP: 192.168.2.145
echo.

REM Set the correct hostname for physical device
set REACT_NATIVE_PACKAGER_HOSTNAME=192.168.2.145

REM Start Expo with LAN mode
npx expo start -c --host lan

pause
