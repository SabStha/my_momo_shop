@echo off
echo ========================================
echo Adding Windows Firewall Rule for Laravel
echo ========================================
echo.
echo This script must be run as Administrator
echo.
pause

netsh advfirewall firewall delete rule name="Laravel Development Server" protocol=TCP localport=8000 >nul 2>&1

netsh advfirewall firewall add rule name="Laravel Development Server" dir=in action=allow protocol=TCP localport=8000

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo ✅ SUCCESS! Firewall rule added
    echo ========================================
    echo.
    echo Port 8000 is now accessible from your network
    echo You can now use the mobile app with: http://192.168.2.142:8000
    echo.
) else (
    echo.
    echo ========================================
    echo ❌ FAILED! Please run as Administrator
    echo ========================================
    echo.
    echo Right-click this file and select "Run as administrator"
    echo.
)

pause






