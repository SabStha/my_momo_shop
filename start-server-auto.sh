#!/bin/bash

echo "🚀 Starting Laravel server with automatic network detection..."

# Get the current network IP address
IP=$(ip route get 1.1.1.1 | awk '{print $7; exit}' 2>/dev/null)

if [ -z "$IP" ]; then
    # Fallback method for macOS
    IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | head -1)
fi

if [ -z "$IP" ]; then
    # Another fallback method
    IP=$(hostname -I | awk '{print $1}')
fi

if [ ! -z "$IP" ]; then
    echo "📡 Found IP: $IP"
    echo "🌐 Starting server on http://$IP:8000"
    echo ""
    echo "✅ Server will be accessible from your mobile device at: http://$IP:8000"
    echo ""
    php artisan serve --host=0.0.0.0 --port=8000
else
    echo "❌ Could not detect network IP automatically"
    echo "🔧 Please run: php artisan serve --host=0.0.0.0 --port=8000"
    exit 1
fi
