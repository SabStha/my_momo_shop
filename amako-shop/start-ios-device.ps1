# PowerShell script to start Expo for iOS devices
# This script forces the use of the correct WiFi IP address

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Starting Expo for iOS Device" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Set the correct IP address (your WiFi network)
$env:REACT_NATIVE_PACKAGER_HOSTNAME = "192.168.2.145"

Write-Host "Using IP Address: 192.168.2.145" -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANT:" -ForegroundColor Yellow
Write-Host "1. Make sure your iPhone/iPad is connected to the SAME WiFi network" -ForegroundColor Yellow
Write-Host "2. On your iPhone: Settings > WiFi > Tap (i) > Check IP is 192.168.2.xxx" -ForegroundColor Yellow
Write-Host "3. Install 'Expo Go' app from App Store if you haven't already" -ForegroundColor Yellow
Write-Host ""
Write-Host "To connect:" -ForegroundColor Cyan
Write-Host "- Open Camera app (not Expo Go) on iPhone" -ForegroundColor Cyan
Write-Host "- Point at the QR code below" -ForegroundColor Cyan
Write-Host "- Tap the notification that appears" -ForegroundColor Cyan
Write-Host ""
Write-Host "Starting Metro bundler..." -ForegroundColor Cyan
Write-Host ""

# Change to amako-shop directory
Set-Location -Path $PSScriptRoot

# Start Expo with LAN mode and the correct IP
npx expo start --clear --host lan

# Keep window open if there's an error
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "Error occurred. Press any key to exit..." -ForegroundColor Red
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}

