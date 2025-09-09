# 🚀 Momo Shop Production Deployment Guide

This guide will help you deploy your Momo Shop Laravel application to a production server safely and securely.

## 📋 Pre-Deployment Checklist

### 1. Server Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Redis**: For caching and sessions
- **Nginx/Apache**: Web server
- **SSL Certificate**: For HTTPS
- **Composer**: For PHP dependencies
- **Node.js**: 18+ for frontend builds

### 2. Security Considerations
- [ ] Change all default passwords
- [ ] Set up firewall rules
- [ ] Configure SSL certificate
- [ ] Set proper file permissions
- [ ] Enable security headers
- [ ] Set up monitoring and logging

## 🛠️ Deployment Steps

### Step 1: Prepare Your Server

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.1-fpm php8.1-mysql php8.1-redis php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd composer nodejs npm

# Start services
sudo systemctl start nginx mysql redis php8.1-fpm
sudo systemctl enable nginx mysql redis php8.1-fpm
```

### Step 2: Clone and Setup Project

```bash
# Clone your repository
cd /var/www
sudo git clone https://github.com/yourusername/momo-shop.git
sudo chown -R www-data:www-data momo-shop
cd momo-shop

# Make deployment script executable
chmod +x deploy-production.sh
```

### Step 3: Configure Environment

```bash
# Copy production environment template
cp production.env.example .env

# Edit the .env file with your production values
nano .env
```

**Important .env settings to configure:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_DATABASE=momo_shop_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
```

### Step 4: Run Deployment Script

```bash
# Run the automated deployment script
./deploy-production.sh
```

### Step 5: Configure Web Server

#### Nginx Configuration
Create `/etc/nginx/sites-available/momo-shop`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/momo-shop/public;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/momo-shop /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 6: Database Setup

```bash
# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE momo_shop_production;
CREATE USER 'momo_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON momo_shop_production.* TO 'momo_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 7: Run Production Seeder (Optional)

```bash
# Only run this if you want to create initial admin user
php artisan db:seed --class=ProductionSeeder
```

**⚠️ IMPORTANT**: Do NOT run `php artisan db:seed` without specifying the class, as it will run all seeders including development data!

## 🔒 Security Hardening

### 1. File Permissions
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/momo-shop
sudo chmod -R 755 /var/www/momo-shop
sudo chmod -R 775 /var/www/momo-shop/storage
sudo chmod -R 775 /var/www/momo-shop/bootstrap/cache
sudo chmod 644 /var/www/momo-shop/.env
```

### 2. Firewall Configuration
```bash
# Configure UFW firewall
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 3. SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## 📊 Monitoring and Maintenance

### 1. Set up Log Rotation
Create `/etc/logrotate.d/momo-shop`:
```
/var/www/momo-shop/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

### 2. Set up Cron Jobs
```bash
# Add to crontab
crontab -e
```

Add these lines:
```bash
* * * * * cd /var/www/momo-shop && php artisan schedule:run >> /dev/null 2>&1
0 2 * * * cd /var/www/momo-shop && php artisan backup:run >> /dev/null 2>&1
```

### 3. Health Monitoring
Create a simple health check endpoint in your routes:
```php
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});
```

## 🚨 Troubleshooting

### Common Issues:

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/momo-shop
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Database Connection Issues**
   - Check .env database credentials
   - Verify MySQL service is running
   - Check firewall rules

3. **Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Asset Loading Issues**
   ```bash
   npm run build
   php artisan storage:link
   ```

## 📝 Post-Deployment Tasks

- [ ] Test all major functionality
- [ ] Set up automated backups
- [ ] Configure monitoring alerts
- [ ] Set up error tracking (Sentry, Bugsnag)
- [ ] Update DNS records
- [ ] Test SSL certificate
- [ ] Verify security headers
- [ ] Set up performance monitoring

## 🔄 Updates and Maintenance

To update your production application:

```bash
cd /var/www/momo-shop
./deploy-production.sh
```

This script will:
- Create automatic backups
- Pull latest code
- Update dependencies
- Run migrations
- Clear and rebuild caches
- Restart services

## 📞 Support

If you encounter issues during deployment:
1. Check the logs: `tail -f storage/logs/laravel.log`
2. Verify all services are running: `sudo systemctl status nginx mysql redis php8.1-fpm`
3. Check file permissions
4. Verify environment configuration

---

**Remember**: Always test your deployment in a staging environment first!
