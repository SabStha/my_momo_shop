@echo off
echo ========================================
echo  Quick Firewall Fix for Mobile App
echo ========================================
echo.
echo This will allow port 8000 through Windows Firewall
echo So your mobile app can connect to Laravel
echo.
echo Right-click this file and select:
echo "Run as administrator"
echo.
pause

echo Removing old rules...
netsh advfirewall firewall delete rule name="Laravel Development Server" protocol=TCP localport=8000 >nul 2>&1

echo Adding new firewall rule...
netsh advfirewall firewall add rule name="Laravel Development Server" dir=in action=allow protocol=TCP localport=8000

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo ✅ SUCCESS! Port 8000 is now open
    echo ========================================
    echo.
    echo Your mobile app should now connect to:
    echo http://192.168.0.19:8000/api
    echo.
    echo Reload your mobile app to test!
    echo.
) else (
    echo.
    echo ========================================
    echo ❌ FAILED!
    echo ========================================
    echo.
    echo Please right-click this file and
    echo select "Run as administrator"
    echo.
)

pause




