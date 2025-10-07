@echo off
echo 🚀 Starting Laravel server with automatic network detection...

REM Get the current network IP address
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    set "ip=%%a"
    set "ip=!ip: =!"
    if not "!ip!"=="" (
        echo 📡 Found IP: !ip!
        echo 🌐 Starting server on http://!ip!:8000
        echo.
        echo ✅ Server will be accessible from your mobile device at: http://!ip!:8000
        echo.
        php artisan serve --host=0.0.0.0 --port=8000
        goto :end
    )
)

echo ❌ Could not detect network IP automatically
echo 🔧 Please run: php artisan serve --host=0.0.0.0 --port=8000
pause

:end
