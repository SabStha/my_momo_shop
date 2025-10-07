# Comprehensive Expo Metro Bundler Fix Script
# This script fixes the "SyntaxError: Unexpected token 'this'" error

Write-Host "`n=== Expo Metro Bundler Fix Script ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check Node version
Write-Host "Step 1: Checking Node.js version..." -ForegroundColor Yellow
$nodeVersion = node --version
Write-Host "Current Node.js version: $nodeVersion" -ForegroundColor Green
$nodeMajor = [int]($nodeVersion -replace 'v(\d+)\..*', '$1')

if ($nodeMajor -eq 22) {
    Write-Host "⚠️  WARNING: Node.js v22 detected - this may cause issues!" -ForegroundColor Red
    Write-Host "   Recommended: Switch to Node.js 18 or 20 LTS" -ForegroundColor Yellow
    Write-Host ""
    $response = Read-Host "Continue anyway? (y/n)"
    if ($response -ne 'y' -and $response -ne 'Y') {
        Write-Host "Exiting. Please install Node.js 18 or 20 and run this script again." -ForegroundColor Yellow
        exit
    }
}

Write-Host ""

# Step 2: Stop any running Metro bundler
Write-Host "Step 2: Stopping any running Metro bundler processes..." -ForegroundColor Yellow
Get-Process -Name "node" -ErrorAction SilentlyContinue | Where-Object { $_.Path -like "*node.exe*" } | Stop-Process -Force -ErrorAction SilentlyContinue
Write-Host "✅ Processes stopped" -ForegroundColor Green
Write-Host ""

# Step 3: Clean npm cache
Write-Host "Step 3: Cleaning npm cache..." -ForegroundColor Yellow
npm cache clean --force
Write-Host "✅ npm cache cleaned" -ForegroundColor Green
Write-Host ""

# Step 4: Remove node_modules
Write-Host "Step 4: Removing node_modules..." -ForegroundColor Yellow
if (Test-Path "node_modules") {
    Remove-Item -Recurse -Force node_modules
    Write-Host "✅ node_modules removed" -ForegroundColor Green
} else {
    Write-Host "⚠️  node_modules not found (already removed)" -ForegroundColor Yellow
}
Write-Host ""

# Step 5: Remove package-lock.json
Write-Host "Step 5: Removing package-lock.json..." -ForegroundColor Yellow
if (Test-Path "package-lock.json") {
    Remove-Item -Force package-lock.json
    Write-Host "✅ package-lock.json removed" -ForegroundColor Green
} else {
    Write-Host "⚠️  package-lock.json not found" -ForegroundColor Yellow
}
Write-Host ""

# Step 6: Remove .expo cache
Write-Host "Step 6: Removing .expo cache..." -ForegroundColor Yellow
if (Test-Path ".expo") {
    Remove-Item -Recurse -Force .expo
    Write-Host "✅ .expo cache removed" -ForegroundColor Green
} else {
    Write-Host "⚠️  .expo cache not found" -ForegroundColor Yellow
}
Write-Host ""

# Step 7: Remove Metro cache (in temp directories)
Write-Host "Step 7: Removing Metro cache from temp..." -ForegroundColor Yellow
$tempMetro = "$env:TEMP\metro-*"
Remove-Item -Recurse -Force $tempMetro -ErrorAction SilentlyContinue
$tempReact = "$env:TEMP\react-*"
Remove-Item -Recurse -Force $tempReact -ErrorAction SilentlyContinue
Write-Host "✅ Metro cache removed" -ForegroundColor Green
Write-Host ""

# Step 8: Install dependencies
Write-Host "Step 8: Installing dependencies (this may take a few minutes)..." -ForegroundColor Yellow
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ npm install failed!" -ForegroundColor Red
    Write-Host "Try running manually: npm install" -ForegroundColor Yellow
    exit 1
}
Write-Host "✅ Dependencies installed successfully" -ForegroundColor Green
Write-Host ""

# Step 9: Verify installation
Write-Host "Step 9: Verifying installation..." -ForegroundColor Yellow
if (Test-Path "node_modules\expo") {
    Write-Host "✅ Expo installed correctly" -ForegroundColor Green
} else {
    Write-Host "❌ Expo not found in node_modules!" -ForegroundColor Red
}

if (Test-Path "node_modules\@expo\metro") {
    Write-Host "✅ @expo/metro installed correctly" -ForegroundColor Green
} else {
    Write-Host "❌ @expo/metro not found!" -ForegroundColor Red
}
Write-Host ""

# Final summary
Write-Host "=== Fix Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ All cleanup and reinstall steps completed!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Try starting Expo with: npm run start:tunnel" -ForegroundColor White
Write-Host ""
Write-Host "If you still encounter issues:" -ForegroundColor Yellow
Write-Host "1. Consider switching to Node.js 18 or 20 LTS" -ForegroundColor White
Write-Host "2. Check the METRO_FIX_GUIDE.md for more solutions" -ForegroundColor White
Write-Host "3. Run check-environment.ps1 to verify your setup" -ForegroundColor White
Write-Host ""

# Ask if user wants to start Expo now
$startNow = Read-Host "Would you like to start Expo now? (y/n)"
if ($startNow -eq 'y' -or $startNow -eq 'Y') {
    Write-Host ""
    Write-Host "Starting Expo..." -ForegroundColor Cyan
    npm run start:tunnel
}

