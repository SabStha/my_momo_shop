# Check if production server has latest code

Write-Host "=" * 60 -ForegroundColor Cyan
Write-Host "Checking Production Server Status" -ForegroundColor Cyan
Write-Host "=" * 60 -ForegroundColor Cyan
Write-Host ""

$serverHost = "amakomomo.com"
$serverUser = "root"
$projectPath = "/var/www/amako-momo(p)/my_momo_shop"

Write-Host "This script will check if production has the latest code." -ForegroundColor Yellow
Write-Host ""
Write-Host "Commands to run on production server:" -ForegroundColor Green
Write-Host "=" * 60 -ForegroundColor Green
Write-Host ""
Write-Host "ssh $serverUser@$serverHost" -ForegroundColor White
Write-Host ""
Write-Host "cd $projectPath" -ForegroundColor White
Write-Host ""
Write-Host "# Check current git commit" -ForegroundColor Gray
Write-Host "git log -1 --oneline" -ForegroundColor White
Write-Host ""
Write-Host "# Should show: 'Fix legacy login route - remove roles from user response'" -ForegroundColor Gray
Write-Host ""
Write-Host "# If NOT showing this, pull latest code:" -ForegroundColor Gray
Write-Host "git pull origin main" -ForegroundColor White
Write-Host ""
Write-Host "# Clear all caches (VERY IMPORTANT!)" -ForegroundColor Gray
Write-Host "php artisan optimize:clear" -ForegroundColor White
Write-Host "systemctl restart php8.3-fpm" -ForegroundColor White
Write-Host "systemctl restart nginx" -ForegroundColor White
Write-Host ""
Write-Host "=" * 60 -ForegroundColor Green
Write-Host ""
Write-Host "After running these commands, test the API by running:" -ForegroundColor Yellow
Write-Host "  .\test-api-simple.ps1" -ForegroundColor White
Write-Host ""
Write-Host "Or test in Expo Go (development mode) before rebuilding APK" -ForegroundColor Yellow
Write-Host ""

Read-Host "Press Enter to exit"

