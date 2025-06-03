# 🚀 Production Deployment Checklist

## Pre-Deployment Requirements

### 1. Environment Setup
- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] Node.js 18+ and npm installed
- [ ] Database server (MySQL/PostgreSQL) configured
- [ ] Web server (Apache/Nginx) configured with HTTPS
- [ ] SSL certificate installed and configured

### 2. Security Configuration
- [ ] `.env` file updated with production settings
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secured
- [ ] Mail credentials configured (remove any exposed passwords)
- [ ] Session encryption enabled (`SESSION_ENCRYPT=true`)
- [ ] HTTPS cookies enforced (`SESSION_SECURE_COOKIE=true`)

## Deployment Steps

### Step 1: Install Dependencies
```bash
# Install PHP dependencies (production optimized)
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm ci --production

# Build production assets
npm run production
```

### Step 2: Database Setup
```bash
# Generate application key (if not already set)
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed roles and permissions (REQUIRED for security)
php artisan db:seed --class=RolesAndPermissionsSeeder

# Create wallets for existing users
php artisan wallets:create-for-all-users

# Create admin user
php artisan user:assign-admin admin@yourdomain.com
```

### Step 3: Optimize for Production
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear old caches
php artisan cache:clear
```

### Step 4: Set File Permissions
```bash
# Set appropriate permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache

# Secure configuration files
sudo chmod 644 .env
sudo chown root:www-data .env
```

### Step 5: Verify Web Server Configuration

#### Apache Configuration
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/your/app/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Security Headers (if not handled by Laravel middleware)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Hide server information
    ServerTokens Prod
    ServerSignature Off
    
    <Directory /path/to/your/app/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/your/app/public;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options DENY always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Hide server information
    server_tokens off;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
    
    location ~ /(storage|bootstrap|vendor|config|database|resources|routes|tests)/ {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

## Post-Deployment Testing

### Step 1: Basic Application Tests
```bash
# Test application is accessible
curl -I https://yourdomain.com/
# Should return 200 OK

# Test HTTPS redirect
curl -I http://yourdomain.com/
# Should return 301/302 redirect to HTTPS

# Test security headers
curl -I https://yourdomain.com/ | grep -E "(Strict-Transport|X-Content|X-Frame|X-XSS)"
# Should show security headers
```

### Step 2: Authentication Security Tests
```bash
# Test unauthenticated API access
curl -X GET https://yourdomain.com/api/pos/orders
# Should return 401 Unauthorized

# Test rate limiting on login
for i in {1..6}; do
  curl -X POST https://yourdomain.com/login \
    -d "email=wrong@email.com&password=wrong" \
    -H "Accept: application/json"
done
# 6th request should return 429 Too Many Requests
```

### Step 3: File Protection Tests
```bash
# Test environment file protection
curl https://yourdomain.com/.env
# Should return 403 or 404

# Test vendor directory protection
curl https://yourdomain.com/vendor/
# Should return 403 or 404

# Test storage directory protection
curl https://yourdomain.com/storage/
# Should return 403 or 404
```

### Step 4: Database Integrity Tests
```sql
-- Connect to your database and run:

-- Check users have proper role assignments
SELECT COUNT(*) FROM users u 
JOIN model_has_roles mhr ON u.id = mhr.model_id 
JOIN roles r ON mhr.role_id = r.id;

-- Verify financial data integrity
SELECT COUNT(*) FROM orders 
WHERE grand_total != (total_amount + COALESCE(tax_amount, 0));
-- Should return 0

-- Check for orphaned records
SELECT COUNT(*) FROM orders WHERE user_id IS NOT NULL 
AND user_id NOT IN (SELECT id FROM users);
-- Should return 0
```

### Step 5: Performance Tests
```bash
# Test response times
ab -n 100 -c 10 https://yourdomain.com/
# Monitor average response time

# Test authenticated endpoint performance
ab -n 50 -c 5 -H "Authorization: Bearer {token}" \
  https://yourdomain.com/api/pos/orders
```

## Monitoring Setup

### 1. Log Monitoring
```bash
# Monitor application logs
tail -f storage/logs/laravel.log

# Watch for security events
grep -E "(Authentication failed|Authorization failed|Validation failed)" \
  storage/logs/laravel.log

# Monitor web server logs
tail -f /var/log/nginx/access.log  # or /var/log/apache2/access.log
tail -f /var/log/nginx/error.log   # or /var/log/apache2/error.log
```

### 2. Database Monitoring
```sql
-- Monitor for suspicious activity
SELECT * FROM orders WHERE created_at > NOW() - INTERVAL 1 HOUR
ORDER BY total_amount DESC LIMIT 10;

-- Check for failed login attempts (if logging to database)
SELECT * FROM failed_jobs WHERE failed_at > NOW() - INTERVAL 1 HOUR;
```

### 3. System Resource Monitoring
```bash
# Monitor system resources
htop
iostat 1
netstat -tulpn
```

## Backup Strategy

### 1. Database Backups
```bash
# Daily database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Automated backup script
#!/bin/bash
BACKUP_DIR="/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

### 2. File Backups
```bash
# Backup application files (excluding vendor and node_modules)
tar --exclude='vendor' --exclude='node_modules' --exclude='storage/logs' \
  -czf app_backup_$(date +%Y%m%d).tar.gz /path/to/your/app/
```

### 3. Configuration Backups
```bash
# Backup server configuration
cp /etc/nginx/sites-available/yourdomain.com /backups/nginx_$(date +%Y%m%d).conf
cp /path/to/your/app/.env /backups/env_$(date +%Y%m%d).backup
```

## Maintenance Tasks

### Daily
- [ ] Check application logs for errors
- [ ] Monitor system resources
- [ ] Verify automated backups completed
- [ ] Check security alerts

### Weekly
- [ ] Review authentication logs
- [ ] Update system packages
- [ ] Test backup restoration
- [ ] Check SSL certificate expiration

### Monthly
- [ ] Update Laravel dependencies
- [ ] Security audit of user permissions
- [ ] Performance optimization review
- [ ] Disaster recovery testing

## Rollback Plan

### If Issues Are Detected
1. **Immediate Actions**
   ```bash
   # Put application in maintenance mode
   php artisan down --message="Maintenance in progress"
   
   # Switch to previous version
   git checkout previous-stable-tag
   composer install --no-dev
   php artisan migrate:rollback --step=X
   
   # Clear caches
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   
   # Bring application back online
   php artisan up
   ```

2. **Database Rollback**
   ```bash
   # Restore from backup
   mysql -u username -p database_name < backup_previous.sql
   ```

3. **File Rollback**
   ```bash
   # Restore from backup
   tar -xzf app_backup_previous.tar.gz -C /
   ```

## Security Incident Response

### 1. Detection
- Monitor logs for unusual patterns
- Set up automated alerts for:
  - Multiple failed login attempts
  - Unusual financial transactions
  - Access to protected endpoints
  - Rate limit violations

### 2. Response
- Immediately disable compromised accounts
- Change all passwords and API keys
- Review logs for scope of breach
- Preserve evidence for investigation

### 3. Recovery
- Patch vulnerabilities
- Restore from clean backups if necessary
- Update security measures
- Notify stakeholders as required

## Support Contacts

- **Emergency Contact**: [Your emergency contact]
- **Hosting Provider**: [Your hosting provider support]
- **SSL Certificate Provider**: [Your SSL provider support]
- **Database Administrator**: [Your DBA contact]

## Final Security Verification

- [ ] All tests pass
- [ ] Security headers present
- [ ] HTTPS enforced
- [ ] Rate limiting active
- [ ] File access protected
- [ ] Database integrity verified
- [ ] Monitoring configured
- [ ] Backups automated
- [ ] Incident response plan documented

**🎉 Your Laravel application is now securely deployed with enterprise-level security!**