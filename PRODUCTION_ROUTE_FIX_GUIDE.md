# Production Route Fix Guide

## Error

```
RouteNotFoundException: Route [admin.analytics.explain-trend] not defined.
HTTP 500 Internal Server Error
```

## Root Cause

The route **IS defined** in `routes/web.php` (line 723), but Laravel's **cached routes** on the production server are outdated and don't include it.

## Quick Fix (SSH to Production)

SSH into your production server and run:

```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan optimize:clear
php artisan route:cache
```

## Detailed Fix Steps

### Option 1: Using the Fix Script

1. **Upload the script to production:**
   ```bash
   scp fix-production-routes.sh user@amakomomo.com:/var/www/amako-momo\(p\)/my_momo_shop/
   ```

2. **SSH into production:**
   ```bash
   ssh user@amakomomo.com
   ```

3. **Run the script:**
   ```bash
   cd /var/www/amako-momo\(p\)/my_momo_shop
   chmod +x fix-production-routes.sh
   ./fix-production-routes.sh
   ```

### Option 2: Manual Commands

SSH into production and run each command:

```bash
# Navigate to project
cd /var/www/amako-momo\(p\)/my_momo_shop

# Clear all caches
php artisan optimize:clear

# Specifically clear and rebuild route cache
php artisan route:clear
php artisan route:cache

# Clear and rebuild config cache
php artisan config:clear
php artisan config:cache

# Clear view cache
php artisan view:clear
php artisan view:cache
```

### Option 3: Quick One-Liner

```bash
cd /var/www/amako-momo\(p\)/my_momo_shop && php artisan optimize:clear && php artisan route:cache && php artisan config:cache
```

## Verify the Fix

1. Check that routes are loaded:
   ```bash
   php artisan route:list | grep explain-trend
   ```

   You should see:
   ```
   POST   admin/analytics/explain-trend ... admin.analytics.explain-trend
   ```

2. Test in browser:
   - Visit: https://amakomomo.com/admin/analytics
   - Click the "Why?" button on Revenue Trend or Orders Trend
   - Check browser console - should no longer show 500 error

## Why This Happens

Laravel caches routes in production for performance. When you:
1. Add new routes in development
2. Push code to production
3. But don't clear the cache

The new routes aren't recognized because Laravel is still using the old cached route list.

## Prevention

### Deploy Script Should Always Include:

```bash
# After pulling new code
git pull origin main

# Clear and rebuild caches
php artisan optimize:clear
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Update dependencies if needed
composer install --no-dev --optimize-autoloader
```

### Add to Your Deployment Process

Update your `deploy-production.sh` or `deploy-production.bat` to include cache clearing:

```bash
echo "Clearing production caches..."
ssh user@amakomomo.com "cd /var/www/amako-momo\(p\)/my_momo_shop && php artisan optimize:clear && php artisan route:cache && php artisan config:cache"
```

## Common Artisan Cache Commands

| Command | Purpose |
|---------|---------|
| `php artisan optimize:clear` | Clears ALL caches at once |
| `php artisan route:clear` | Clears route cache |
| `php artisan route:cache` | Rebuilds route cache |
| `php artisan config:clear` | Clears config cache |
| `php artisan config:cache` | Rebuilds config cache |
| `php artisan view:clear` | Clears compiled views |
| `php artisan view:cache` | Compiles all views |
| `php artisan cache:clear` | Clears application cache |

## Troubleshooting

### If route:list shows the route but browser still errors:

1. **Check web server cache:**
   ```bash
   sudo systemctl restart nginx  # or apache2
   ```

2. **Check PHP-FPM cache:**
   ```bash
   sudo systemctl restart php8.1-fpm  # or your PHP version
   ```

3. **Check OPcache:**
   ```bash
   php artisan cache:clear
   sudo systemctl restart php8.1-fpm
   ```

### If route:list doesn't show the route:

1. Verify the route exists in `routes/web.php`
2. Check for syntax errors:
   ```bash
   php artisan route:list
   ```
3. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Production Server Info

- **Path:** `/var/www/amako-momo(p)/my_momo_shop`
- **Domain:** https://amakomomo.com
- **Route:** `POST /admin/analytics/explain-trend`
- **Named Route:** `admin.analytics.explain-trend`

## Files Involved

- `routes/web.php` (line 723) - Route definition
- `app/Http/Controllers/Admin/CustomerAnalyticsController.php` - Controller
- `resources/views/admin/customer-analytics/index.blade.php` - View using the route

## Status

⚠️ **ACTION REQUIRED** - Run the fix commands on production server

Once fixed:
- Route cache will be rebuilt with new routes
- The analytics page will work correctly
- The "Why?" buttons will function properly

