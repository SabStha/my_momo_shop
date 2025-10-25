#!/bin/bash

# Test Notification Script for Mobile Device (Linux)
# Run this from your server

API_URL="https://amakomomo.com/api"

echo "🔔 Amako Momo - Notification Tester"
echo "====================================="
echo ""

function show_menu() {
    echo "Select notification to test:"
    echo ""
    echo "1. 🎁 Offer Notification (20% OFF)"
    echo "2. 🛵 Delivery Notification (Enhanced with ETA!)"
    echo "3. ⚡ Flash Sale Notification"
    echo "4. 📢 System Notification"
    echo "5. 🎯 Test ALL Notifications"
    echo "6. 🛵 Delivery - Different Statuses"
    echo "0. Exit"
    echo ""
}

function send_notification() {
    local endpoint=$1
    echo "Sending notification..."
    
    response=$(curl -s -X POST "$API_URL$endpoint" \
        -H "Content-Type: application/json")
    
    if echo "$response" | grep -q '"success":true'; then
        echo "✅ SUCCESS!"
        echo "$response" | python3 -m json.tool 2>/dev/null || echo "$response"
        echo ""
        echo "📱 Check your device now!"
    else
        echo "❌ FAILED!"
        echo "$response" | python3 -m json.tool 2>/dev/null || echo "$response"
    fi
    
    echo ""
    read -p "Press Enter to continue..."
}

while true; do
    clear
    echo "🔔 Amako Momo - Notification Tester"
    echo "====================================="
    echo ""
    show_menu
    read -p "Enter choice: " choice
    echo ""
    
    case $choice in
        1)
            echo "Testing Offer Notification..."
            send_notification "/test/notification/offer"
            ;;
        2)
            echo "Testing Delivery Notification..."
            send_notification "/test/notification/delivery"
            ;;
        3)
            echo "Testing Flash Sale Notification..."
            send_notification "/test/notification/flash-sale"
            ;;
        4)
            echo "Testing System Notification..."
            send_notification "/test/notification/system"
            ;;
        5)
            echo "Testing ALL Notifications..."
            send_notification "/test/notification/all"
            ;;
        6)
            echo "Select delivery status:"
            echo "1. Confirmed"
            echo "2. Preparing"
            echo "3. Ready"
            echo "4. Out for Delivery (with ETA!)"
            echo "5. Arriving"
            echo "6. Delivered"
            read -p "Enter choice: " status_choice
            
            case $status_choice in
                1) status="confirmed" ;;
                2) status="preparing" ;;
                3) status="ready" ;;
                4) status="out_for_delivery" ;;
                5) status="arriving" ;;
                6) status="delivered" ;;
                *) echo "Invalid choice"; continue ;;
            esac
            
            send_notification "/test/notification/delivery?status=$status"
            ;;
        0)
            echo "Goodbye! 👋"
            exit 0
            ;;
        *)
            echo "Invalid choice. Please try again."
            sleep 2
            ;;
    esac
done

