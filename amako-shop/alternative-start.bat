@echo off
echo ========================================
echo Alternative Development Server Start
echo ========================================
echo.

echo 🔧 Trying alternative methods to start development server...
echo.

echo Method 1: Using Expo CLI directly...
call npx @expo/cli start --tunnel --clear
if %errorlevel% equ 0 goto success

echo.
echo Method 2: Using React Native CLI...
call npx react-native start --reset-cache
if %errorlevel% equ 0 goto success

echo.
echo Method 3: Using npm start...
call npm start
if %errorlevel% equ 0 goto success

echo.
echo Method 4: Manual Metro start...
call npx metro start --reset-cache
if %errorlevel% equ 0 goto success

echo.
echo ❌ All methods failed. The Metro bundler is severely corrupted.
echo.
echo 🔧 Manual Fix Required:
echo 1. Close this terminal
echo 2. Open a new terminal as Administrator
echo 3. Run: npm cache clean --force
echo 4. Delete node_modules folder
echo 5. Run: npm install
echo 6. Try again
echo.
goto end

:success
echo.
echo ✅ Development server started successfully!
echo 📱 Scan the QR code with Expo Go on your phone
echo.

:end
pause
