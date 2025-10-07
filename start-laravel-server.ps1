# Laravel API Server Startup Script

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Starting Laravel API Server" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "Server will be available at:" -ForegroundColor Yellow
Write-Host "- Local: http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "- Network: http://192.168.2.145:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host "========================================`n" -ForegroundColor Cyan

# Change to script directory
Set-Location -Path $PSScriptRoot

# Start PHP development server
php -S 0.0.0.0:8000 -t public

