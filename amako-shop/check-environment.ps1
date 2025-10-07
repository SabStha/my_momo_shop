# Environment Check Script for Expo React Native Project

Write-Host "`n=== Environment Check for Amako Shop ===" -ForegroundColor Cyan
Write-Host ""

# Check Node.js version
Write-Host "Checking Node.js version..." -ForegroundColor Yellow
$nodeVersion = node --version
Write-Host "Current Node.js version: $nodeVersion" -ForegroundColor Green

# Parse major version
$nodeMajor = [int]($nodeVersion -replace 'v(\d+)\..*', '$1')

if ($nodeMajor -eq 18 -or $nodeMajor -eq 20) {
    Write-Host "✅ Node.js version is compatible with Expo SDK 54" -ForegroundColor Green
} elseif ($nodeMajor -eq 22) {
    Write-Host "⚠️  WARNING: Node.js v22 may cause compatibility issues!" -ForegroundColor Red
    Write-Host "   Recommended: Switch to Node.js 18 LTS or 20 LTS" -ForegroundColor Yellow
    Write-Host "   Use NVM to switch: nvm install 20 && nvm use 20" -ForegroundColor Yellow
} else {
    Write-Host "⚠️  Node.js version may not be optimal for Expo SDK 54" -ForegroundColor Yellow
}

Write-Host ""

# Check npm version
Write-Host "Checking npm version..." -ForegroundColor Yellow
$npmVersion = npm --version
Write-Host "Current npm version: $npmVersion" -ForegroundColor Green
Write-Host ""

# Check if nvm is installed
Write-Host "Checking for Node Version Manager (nvm)..." -ForegroundColor Yellow
try {
    $nvmVersion = nvm version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ NVM is installed" -ForegroundColor Green
        Write-Host "   To switch Node versions, use:" -ForegroundColor Cyan
        Write-Host "   nvm install 20" -ForegroundColor White
        Write-Host "   nvm use 20" -ForegroundColor White
    }
} catch {
    Write-Host "❌ NVM is not installed" -ForegroundColor Yellow
    Write-Host "   Download from: https://github.com/coreybutler/nvm-windows/releases" -ForegroundColor Cyan
}

Write-Host ""

# Check for Expo CLI
Write-Host "Checking for Expo CLI..." -ForegroundColor Yellow
try {
    $expoVersion = npx expo --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Expo CLI is available" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  Could not verify Expo CLI" -ForegroundColor Yellow
}

Write-Host ""

# Check project dependencies
Write-Host "Checking project setup..." -ForegroundColor Yellow
if (Test-Path "node_modules") {
    Write-Host "✅ node_modules exists" -ForegroundColor Green
} else {
    Write-Host "❌ node_modules not found - run 'npm install'" -ForegroundColor Red
}

if (Test-Path "package.json") {
    Write-Host "✅ package.json exists" -ForegroundColor Green
} else {
    Write-Host "❌ package.json not found" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Cyan

if ($nodeMajor -eq 22) {
    Write-Host ""
    Write-Host "🔧 RECOMMENDED ACTIONS:" -ForegroundColor Yellow
    Write-Host "1. Install NVM for Windows if not already installed" -ForegroundColor White
    Write-Host "2. Run: nvm install 20" -ForegroundColor White
    Write-Host "3. Run: nvm use 20" -ForegroundColor White
    Write-Host "4. Clean and reinstall: npm cache clean --force && npm install" -ForegroundColor White
    Write-Host "5. Try starting Expo: npm run start:tunnel" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "✅ Environment looks good!" -ForegroundColor Green
    Write-Host "If you're still having issues, try:" -ForegroundColor White
    Write-Host "   npm cache clean --force" -ForegroundColor Cyan
    Write-Host "   Remove-Item -Recurse -Force node_modules" -ForegroundColor Cyan
    Write-Host "   npm install" -ForegroundColor Cyan
}

Write-Host ""

