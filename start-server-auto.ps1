# PowerShell script to start Laravel server with automatic network detection

Write-Host "🚀 Starting Laravel server with automatic network detection..." -ForegroundColor Green

# Get the current network IP address
$networkAdapters = Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.*" }

if ($networkAdapters) {
    $ip = $networkAdapters[0].IPAddress
    Write-Host "📡 Found IP: $ip" -ForegroundColor Cyan
    Write-Host "🌐 Starting server on http://$ip:8000" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "✅ Server will be accessible from your mobile device at: http://$ip:8000" -ForegroundColor Green
    Write-Host ""
    
    # Start the Laravel server
    php artisan serve --host=0.0.0.0 --port=8000
} else {
    Write-Host "❌ Could not detect network IP automatically" -ForegroundColor Red
    Write-Host "🔧 Please run: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
    Read-Host "Press Enter to continue"
}
