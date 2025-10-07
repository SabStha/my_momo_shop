# Start Expo for Physical Device
Write-Host "Starting Expo for Physical Device..." -ForegroundColor Green
Write-Host "Using Wi-Fi IP: 192.168.2.145" -ForegroundColor Yellow
Write-Host ""

# Set the correct hostname for physical device
$env:REACT_NATIVE_PACKAGER_HOSTNAME = "192.168.2.145"

# Start Expo with LAN mode
npx expo start -c --host lan
