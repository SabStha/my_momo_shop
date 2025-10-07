# AmaKo Shop - USB Device Connection
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "AmaKo Shop - USB Device Connection" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if ADB is available
try {
    $adbVersion = adb version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ ADB found" -ForegroundColor Green
    } else {
        throw "ADB not found"
    }
} catch {
    Write-Host "❌ ADB not found! Please install Android SDK Platform Tools" -ForegroundColor Red
    Write-Host "Download from: https://developer.android.com/studio/releases/platform-tools" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# Check if device is connected
Write-Host "🔍 Checking for connected devices..." -ForegroundColor Yellow
adb devices
Write-Host ""

# Set up port forwarding
Write-Host "🔧 Setting up port forwarding..." -ForegroundColor Yellow
adb reverse tcp:8081 tcp:8081
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to set up port forwarding" -ForegroundColor Red
    Write-Host "Make sure your device has USB debugging enabled" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

adb reverse tcp:19000 tcp:19000
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to set up port forwarding for port 19000" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ Port forwarding set up successfully" -ForegroundColor Green
Write-Host ""

# Start Expo with LAN mode (will use localhost via USB)
Write-Host "🚀 Starting Expo development server..." -ForegroundColor Green
Write-Host "Your device will connect via USB (localhost)" -ForegroundColor Yellow
Write-Host ""
npx expo start -c --host lan
