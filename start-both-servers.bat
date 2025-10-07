@echo off
echo ========================================
echo Starting BOTH Laravel API + Expo App
echo ========================================
echo.
echo This will open TWO terminal windows:
echo   1. Laravel API Server (Port 8000)
echo   2. Expo Development Server (Tunnel)
echo.
echo Press any key to continue...
pause > nul

REM Start Laravel server in new window
start "Laravel API Server" cmd /k "cd /d %~dp0 && php -S 0.0.0.0:8000 -t public"

REM Wait 2 seconds for Laravel to start
timeout /t 2 /nobreak > nul

REM Start Expo in new window
start "Expo Development Server" cmd /k "cd /d %~dp0\amako-shop && npm run start:tunnel"

echo.
echo ========================================
echo Both servers are starting!
echo ========================================
echo.
echo Check the new terminal windows for:
echo   - Laravel API: http://192.168.2.145:8000
echo   - Expo App: Scan QR code to open
echo.
echo Close this window when done.
pause

