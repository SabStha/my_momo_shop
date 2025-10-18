#!/bin/bash
# Deploy Backend Fix for Mobile App Crash

echo "🚀 Deploying backend fixes to production..."
echo ""

# SSH into production and run commands
ssh user@amakomomo.com << 'ENDSSH'
cd /var/www/amako-momo\(p\)/my_momo_shop

echo "📥 Pulling latest code from git..."
git pull origin main

echo ""
echo "🧹 Clearing all caches..."
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "💾 Rebuilding production caches..."
php artisan config:cache
php artisan route:cache

echo ""
echo "🔄 Restarting PHP-FPM to clear opcache..."
sudo systemctl restart php8.1-fpm

echo ""
echo "✅ Deployment complete!"
echo ""
echo "Test the API:"
echo "curl -X POST https://amakomomo.com/api/login \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -d '{\"email\":\"test@test.com\",\"password\":\"password\"}'"
ENDSSH

echo ""
echo "✅ Backend deployed and caches cleared!"
echo "Now rebuild your APK to test the fix."

