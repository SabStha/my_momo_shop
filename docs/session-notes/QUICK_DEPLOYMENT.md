# 🚀 Quick Production Deployment

Since the deployment script isn't on your server yet, here are the manual commands to run:

## Step 1: Upload Files to Server

First, upload these files to your server:
- `deploy-production.sh`
- `production.env.example`
- `database/seeders/ProductionSeeder.php`
- `config/production.php`
- `routes/health.php`
- `app/Console/Commands/ProductionSetup.php`

## Step 2: Manual Deployment Commands

Run these commands on your server:

```bash
# Navigate to your project directory
cd /var/www/amako-momo(p)/my_momo_shop

# Make the script executable
chmod +x deploy-production.sh

# Create backup
mkdir -p backups
cp -r . backups/backup-$(date +%Y%m%d-%H%M%S)

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies
npm ci --production

# Build frontend assets
npm run build

# Set up environment file
if [ ! -f ".env" ]; then
    cp production.env.example .env
    echo "⚠️  Created .env from production.env.example - PLEASE CONFIGURE IT!"
fi

# Generate application key
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan event:clear
php artisan event:cache

# Optimize application
php artisan optimize

# Create storage link
php artisan storage:link

# Set proper file permissions
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chmod 644 .env

# Restart services
systemctl restart php8.1-fpm
systemctl restart nginx

echo "🎉 Deployment completed!"
```

## Step 3: Configure Environment

Edit your `.env` file:
```bash
nano .env
```

Make sure these are set correctly:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
```

## Step 4: Run Production Setup (Optional)

```bash
# Only if you want to create an admin user
php artisan db:seed --class=ProductionSeeder
```

## Step 5: Test Deployment

```bash
# Test health endpoint
curl http://localhost/health

# Check if application is working
curl -I http://localhost
```

## Alternative: Upload Script and Run

If you want to use the automated script:

1. Upload `deploy-production.sh` to your server
2. Make it executable: `chmod +x deploy-production.sh`
3. Run it: `./deploy-production.sh`

The script will handle everything automatically including backups!
