@echo off
echo ========================================
echo Complete Fix for Metro and Auth Issues
echo ========================================
echo.

echo 🔧 Step 1: Stopping all Node processes...
taskkill /f /im node.exe 2>nul
echo.

echo 🧹 Step 2: Clearing all caches...
echo Clearing npm cache...
npm cache clean --force
echo.

echo Clearing Expo cache...
npx expo r -c 2>nul
echo.

echo 🚀 Step 3: Starting fresh development server...
echo.
echo Starting Expo with tunnel mode (most reliable)...
npx expo start --tunnel --clear

echo.
echo 📱 Step 4: Instructions for your phone
echo.
echo 1. On your phone, close Expo Go completely
echo 2. Go to Settings ^> Apps ^> Expo Go
echo 3. Tap "Storage" ^> "Clear Data" (this fixes auth issues)
echo 4. Reopen Expo Go
echo 5. Scan the new QR code
echo 6. Log in again when prompted
echo.
echo ✅ This should fix both Metro errors and authentication issues!
echo.

pause
