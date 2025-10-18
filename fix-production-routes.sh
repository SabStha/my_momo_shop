#!/bin/bash

# Fix Production Routes - Clear Cache and Reload
# Run this on the production server to fix the RouteNotFoundException

echo "🔧 Fixing Production Routes..."
echo ""

# Navigate to project directory
cd /var/www/amako-momo\(p\)/my_momo_shop

# Clear all caches
echo "📦 Clearing all caches..."
php artisan optimize:clear

# Clear route cache specifically
echo "🛣️  Clearing route cache..."
php artisan route:clear

# Clear config cache
echo "⚙️  Clearing config cache..."
php artisan config:clear

# Clear view cache
echo "👁️  Clearing view cache..."
php artisan view:clear

# Cache routes for production (optional but recommended)
echo "💾 Caching routes for production..."
php artisan route:cache

# Cache config for production
echo "💾 Caching config for production..."
php artisan config:cache

# Cache views for production
echo "💾 Caching views for production..."
php artisan view:cache

echo ""
echo "✅ All caches cleared and rebuilt!"
echo ""
echo "Test the fix:"
echo "1. Visit: https://amakomomo.com/admin/analytics"
echo "2. Click the 'Why?' button on Revenue or Orders trend"
echo "3. Check the response in browser console"
echo ""

