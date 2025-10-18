# Test if production API has been updated

Write-Host "🔬 Testing Production API Response..." -ForegroundColor Cyan
Write-Host ""

$body = @{
    email = "sabstha98@gmail.com"
    password = "YOUR_PASSWORD_HERE"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "https://amakomomo.com/api/login" `
        -Method Post `
        -ContentType "application/json" `
        -Body $body
    
    Write-Host "✅ API Response received" -ForegroundColor Green
    Write-Host ""
    
    if ($response.user.roles) {
        Write-Host "❌ PROBLEM: User object contains 'roles' array" -ForegroundColor Red
        Write-Host "   This WILL CAUSE CRASH in production APK!" -ForegroundColor Red
        Write-Host ""
        Write-Host "User object keys:" -ForegroundColor Yellow
        $response.user | Get-Member -MemberType NoteProperty | Select-Object Name
        Write-Host ""
        Write-Host "❌ Backend fix NOT applied or cache NOT cleared" -ForegroundColor Red
    } else {
        Write-Host "✅ GOOD: User object does NOT contain 'roles'" -ForegroundColor Green
        Write-Host "   Expected keys: id, name, email, phone" -ForegroundColor Green
        Write-Host ""
        Write-Host "User object keys:" -ForegroundColor Green
        $response.user | Get-Member -MemberType NoteProperty | Select-Object Name
        Write-Host ""
        Write-Host "✅ Backend fix is working!" -ForegroundColor Green
    }
    
} catch {
    Write-Host "❌ API request failed:" -ForegroundColor Red
    Write-Host $_.Exception.Message
}

Write-Host ""
Write-Host "If roles are present, run on production server:" -ForegroundColor Yellow
Write-Host "  php artisan config:clear" -ForegroundColor White
Write-Host "  php artisan cache:clear" -ForegroundColor White
Write-Host "  sudo systemctl restart php8.1-fpm" -ForegroundColor White

