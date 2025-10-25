# Test Notification Script for Mobile Device
# Run this from your computer while your device is running the app

$API_URL = "https://amakomomo.com/api"

Write-Host "🔔 Amako Momo - Notification Tester" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Make sure:" -ForegroundColor Yellow
Write-Host "1. Your mobile app is running" -ForegroundColor Yellow
Write-Host "2. You are logged in" -ForegroundColor Yellow
Write-Host "3. Notifications are enabled" -ForegroundColor Yellow
Write-Host ""

# Menu
function Show-Menu {
    Write-Host "Select notification to test:" -ForegroundColor Green
    Write-Host ""
    Write-Host "1. 🎁 Offer Notification (20% OFF)"
    Write-Host "2. 🛵 Delivery Notification (Enhanced with ETA!)"
    Write-Host "3. ⚡ Flash Sale Notification"
    Write-Host "4. 📢 System Notification"
    Write-Host "5. 🎯 Test ALL Notifications"
    Write-Host "6. 🛵 Delivery - Different Statuses"
    Write-Host "0. Exit"
    Write-Host ""
}

function Send-TestNotification {
    param(
        [string]$Endpoint
    )
    
    Write-Host "Sending notification..." -ForegroundColor Yellow
    
    try {
        $response = Invoke-RestMethod -Uri "$API_URL$Endpoint" `
                                      -Method Post `
                                      -ContentType "application/json" `
                                      -TimeoutSec 30
        
        if ($response.success) {
            Write-Host "✅ SUCCESS!" -ForegroundColor Green
            Write-Host "Message: $($response.message)" -ForegroundColor Green
            Write-Host ""
            Write-Host "📱 Check your device now!" -ForegroundColor Cyan
            
            if ($response.notification) {
                Write-Host ""
                Write-Host "Notification Details:" -ForegroundColor White
                $response.notification | Format-List
            }
        } else {
            Write-Host "❌ FAILED!" -ForegroundColor Red
            Write-Host "Error: $($response.message)" -ForegroundColor Red
        }
    }
    catch {
        Write-Host "❌ ERROR!" -ForegroundColor Red
        Write-Host "Error: $_" -ForegroundColor Red
        Write-Host ""
        Write-Host "Make sure your Laravel server is running:" -ForegroundColor Yellow
        Write-Host "php artisan serve" -ForegroundColor Yellow
    }
    
    Write-Host ""
    Write-Host "Press any key to continue..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}

# Main loop
do {
    Clear-Host
    Write-Host "🔔 Amako Momo - Notification Tester" -ForegroundColor Cyan
    Write-Host "=====================================" -ForegroundColor Cyan
    Write-Host ""
    
    Show-Menu
    $choice = Read-Host "Enter choice"
    Write-Host ""
    
    switch ($choice) {
        "1" {
            Write-Host "Testing Offer Notification..." -ForegroundColor Cyan
            Send-TestNotification "/test/notification/offer"
        }
        "2" {
            Write-Host "Testing Delivery Notification..." -ForegroundColor Cyan
            Send-TestNotification "/test/notification/delivery"
        }
        "3" {
            Write-Host "Testing Flash Sale Notification..." -ForegroundColor Cyan
            Send-TestNotification "/test/notification/flash-sale"
        }
        "4" {
            Write-Host "Testing System Notification..." -ForegroundColor Cyan
            Send-TestNotification "/test/notification/system"
        }
        "5" {
            Write-Host "Testing ALL Notifications..." -ForegroundColor Cyan
            Send-TestNotification "/test/notification/all"
            Write-Host "Note: This sends multiple notifications. Check your device!" -ForegroundColor Yellow
        }
        "6" {
            Write-Host "Select delivery status:" -ForegroundColor Cyan
            Write-Host "1. Confirmed"
            Write-Host "2. Preparing"
            Write-Host "3. Ready"
            Write-Host "4. Out for Delivery (with ETA!)"
            Write-Host "5. Arriving"
            Write-Host "6. Delivered"
            $status = Read-Host "Enter choice"
            
            $statusMap = @{
                "1" = "confirmed"
                "2" = "preparing"
                "3" = "ready"
                "4" = "out_for_delivery"
                "5" = "arriving"
                "6" = "delivered"
            }
            
            if ($statusMap.ContainsKey($status)) {
                Send-TestNotification "/test/notification/delivery?status=$($statusMap[$status])"
            }
        }
        "0" {
            Write-Host "Goodbye! 👋" -ForegroundColor Cyan
            break
        }
        default {
            Write-Host "Invalid choice. Please try again." -ForegroundColor Red
            Start-Sleep -Seconds 2
        }
    }
} while ($choice -ne "0")

