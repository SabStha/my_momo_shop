@echo off
REM Momo Shop Production Deployment Script for Windows
REM Run this script on your Windows production server

echo 🚀 Starting Momo Shop Production Deployment...

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo [WARNING] Running as administrator - this is not recommended for security
) else (
    echo [INFO] Running as regular user - good for security
)

REM Check if project directory exists
if not exist "%~dp0" (
    echo [ERROR] Project directory not found
    exit /b 1
)

cd /d "%~dp0"

echo [INFO] Creating backup...
REM Create backup directory if it doesn't exist
if not exist "backups" mkdir backups

REM Backup current deployment
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "BACKUP_NAME=backup-%YYYY%%MM%%DD%-%HH%%Min%%Sec%"

xcopy /E /I /H /Y . "backups\%BACKUP_NAME%"
echo [INFO] Backup created: backups\%BACKUP_NAME%

echo [INFO] Pulling latest code...
git pull origin main

echo [INFO] Installing/Updating Composer dependencies...
composer install --no-dev --optimize-autoloader --no-interaction

echo [INFO] Installing/Updating NPM dependencies...
npm ci --production

echo [INFO] Building frontend assets...
npm run build

echo [INFO] Setting up environment file...
if not exist ".env" (
    if exist "production.env.example" (
        copy "production.env.example" ".env"
        echo [WARNING] Created .env from production.env.example - PLEASE CONFIGURE IT!
    ) else (
        echo [ERROR] No .env file found and no production.env.example to copy from
        exit /b 1
    )
)

echo [INFO] Generating application key...
php artisan key:generate --force

echo [INFO] Running database migrations...
php artisan migrate --force

echo [INFO] Clearing and caching configurations...
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan event:clear
php artisan event:cache

echo [INFO] Optimizing application...
php artisan optimize

echo [INFO] Creating storage link...
php artisan storage:link

echo [INFO] Setting up production...
php artisan production:setup --force

echo [INFO] Cleaning up old backups (keeping last 5)...
cd backups
for /f "skip=5 delims=" %%i in ('dir /b /o-d') do rd /s /q "%%i"
cd ..

echo [INFO] 🎉 Deployment completed successfully!
echo [INFO] Backup location: backups\%BACKUP_NAME%
echo [WARNING] Don't forget to:
echo [WARNING] 1. Configure your .env file with production values
echo [WARNING] 2. Set up SSL certificate
echo [WARNING] 3. Configure your web server
echo [WARNING] 4. Set up monitoring and logging

pause
