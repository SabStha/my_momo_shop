@echo off
echo ========================================
echo Starting Laravel API Server
echo ========================================
echo.
echo Server will be available at:
echo - Local: http://127.0.0.1:8000
echo - Network: http://192.168.2.142:8000
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

cd /d "%~dp0"
php artisan serve --host=0.0.0.0 --port=8000

