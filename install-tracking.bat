@echo off
echo ========================================
echo  Installing Live Delivery Tracking
echo ========================================
echo.

cd amako-shop

echo [1/3] Installing dependencies...
call npm install
echo.

echo [2/3] Dependencies installed!
echo.

echo [3/3] Next steps:
echo.
echo 1. Get Google Maps API keys from:
echo    https://console.cloud.google.com/
echo.
echo 2. Edit amako-shop/app.json and add your API keys
echo.
echo 3. Rebuild the app:
echo    cd amako-shop
echo    npx expo prebuild --clean
echo    npx expo run:android
echo.
echo 4. Read MOBILE_LIVE_TRACKING_SETUP.md for full instructions
echo.

pause



