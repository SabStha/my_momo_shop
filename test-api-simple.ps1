# Simple API test - You provide the password

Write-Host "Testing Production Login API..." -ForegroundColor Cyan
Write-Host ""

# Get password from user
$password = Read-Host "Enter your password for sabstha98@gmail.com"

$body = @{
    email = "sabstha98@gmail.com"
    password = $password
} | ConvertTo-Json

Write-Host ""
Write-Host "Calling: POST https://amakomomo.com/api/login" -ForegroundColor Cyan
Write-Host ""

try {
    $response = Invoke-WebRequest -Uri "https://amakomomo.com/api/login" `
        -Method POST `
        -ContentType "application/json" `
        -Body $body
    
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host ""
    
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "Response:" -ForegroundColor Yellow
    $data | ConvertTo-Json -Depth 5
    Write-Host ""
    
    # Check if user has roles
    if ($data.user.roles) {
        Write-Host "❌ CRASH CAUSE FOUND: User has 'roles' array!" -ForegroundColor Red -BackgroundColor DarkRed
        Write-Host ""
        Write-Host "The backend is NOT updated yet!" -ForegroundColor Red
        Write-Host "This is why the APK crashes after login." -ForegroundColor Red
    }
    else {
        Write-Host "✅ User object is clean (no roles)" -ForegroundColor Green
        Write-Host "Backend is updated correctly!" -ForegroundColor Green
    }
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Status Code: $statusCode" -ForegroundColor Red
        
        if ($statusCode -eq 401) {
            Write-Host ""
            Write-Host "Wrong password! Try again or check credentials." -ForegroundColor Yellow
        }
    }
}

Write-Host ""
Read-Host "Press Enter to exit"

