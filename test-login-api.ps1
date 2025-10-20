# Test Login API to check if backend is fixed

Write-Host "Testing Production Login API..." -ForegroundColor Cyan
Write-Host ""

$body = @{
    email = "sabstha98@gmail.com"
    password = "Admin@123"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "https://amakomomo.com/api/login" `
        -Method POST `
        -ContentType "application/json" `
        -Body $body
    
    Write-Host "✅ API Response received!" -ForegroundColor Green
    Write-Host ""
    Write-Host "User Object:" -ForegroundColor Yellow
    $response.user | ConvertTo-Json -Depth 3
    Write-Host ""
    
    # Check if user has roles (BAD - causes crash)
    if ($response.user.roles) {
        Write-Host "❌ PROBLEM FOUND: User object contains 'roles' array!" -ForegroundColor Red
        Write-Host "This WILL cause the app to crash!" -ForegroundColor Red
        Write-Host ""
        Write-Host "Backend is NOT updated. Please run on production server:" -ForegroundColor Yellow
        Write-Host "  cd /var/www/amako-momo(p)/my_momo_shop" -ForegroundColor White
        Write-Host "  git pull origin main" -ForegroundColor White
        Write-Host "  php artisan optimize:clear" -ForegroundColor White
        Write-Host "  systemctl restart php8.3-fpm" -ForegroundColor White
        Write-Host "  systemctl restart nginx" -ForegroundColor White
    }
    # Check if user has only simple fields (GOOD)
    elseif ($response.user.id -and $response.user.name -and $response.user.email -and $response.user.phone) {
        Write-Host "✅ User object is SIMPLIFIED - This should work!" -ForegroundColor Green
        Write-Host "Fields: id, name, email, phone only" -ForegroundColor Green
        Write-Host ""
        Write-Host "Backend is correctly updated! You can rebuild the APK now." -ForegroundColor Cyan
    }
    else {
        Write-Host "⚠️ User object structure is unexpected" -ForegroundColor Yellow
    }
    
} catch {
    Write-Host "❌ Error testing API:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

