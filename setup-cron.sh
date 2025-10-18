#!/bin/bash

# Amako Momo Shop - Cron Job Setup Script
# This script sets up automated tasks for AI notifications and more

echo "🕐 Setting up Laravel Scheduler Cron Job..."
echo ""

# Get the current directory
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "📂 Application Directory: $CURRENT_DIR"
echo ""

# Create the cron job command
CRON_COMMAND="* * * * * cd $CURRENT_DIR && php artisan schedule:run >> /dev/null 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "⚠️  Cron job already exists!"
    echo ""
    echo "Current crontab:"
    crontab -l | grep "schedule:run"
    echo ""
    read -p "Do you want to update it? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Cancelled"
        exit 1
    fi
    
    # Remove old cron job
    crontab -l | grep -v "schedule:run" | crontab -
    echo "🗑️  Removed old cron job"
fi

# Add new cron job
(crontab -l 2>/dev/null; echo "$CRON_COMMAND") | crontab -

echo "✅ Cron job added successfully!"
echo ""

# Verify it was added
echo "📋 Current crontab:"
crontab -l | grep "schedule:run"
echo ""

# Test the scheduler
echo "🧪 Testing scheduler..."
echo ""

# Check if artisan exists
if [ ! -f "$CURRENT_DIR/artisan" ]; then
    echo "❌ Error: artisan file not found in $CURRENT_DIR"
    exit 1
fi

# Show scheduled tasks
echo "📅 Scheduled Tasks:"
php artisan schedule:list
echo ""

# Ask if user wants to test AI offers now
read -p "Do you want to test AI offers now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "🤖 Testing AI Offers..."
    php artisan offers:send-daily-ai
fi

echo ""
echo "✅ Setup Complete!"
echo ""
echo "📝 What happens next:"
echo "   • Every minute, Laravel checks if any tasks need to run"
echo "   • AI offers will be sent daily at 10:00 AM"
echo "   • Campaign triggers will process every 5 minutes"
echo "   • Churn detection runs daily at 9:00 AM"
echo ""
echo "🔍 Monitor logs with:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "🧪 Test manually with:"
echo "   php artisan offers:send-daily-ai"
echo ""

