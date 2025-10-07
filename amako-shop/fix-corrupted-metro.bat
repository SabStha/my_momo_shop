@echo off
echo ========================================
echo Fix Corrupted Metro Bundler
echo ========================================
echo.

echo 🔧 Step 1: Stopping all Node processes...
taskkill /f /im node.exe 2>nul
echo.

echo 🧹 Step 2: Clearing all caches...
echo Clearing npm cache...
call npm cache clean --force
echo.

echo 🗑️ Step 3: Removing corrupted node_modules...
if exist node_modules rmdir /s /q node_modules
echo.

echo 📦 Step 4: Reinstalling dependencies...
echo This may take a few minutes...
call npm install
echo.

echo 🔄 Step 5: Installing Expo CLI globally (if needed)...
call npm install -g @expo/cli
echo.

echo 🚀 Step 6: Starting development server...
echo.
echo Starting Expo with tunnel mode...
call npx expo start --tunnel --clear

echo.
echo ✅ If the server starts successfully, scan the QR code with your phone!
echo.

pause
