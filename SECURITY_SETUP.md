# Security Setup and Testing Guide

## 🚨 CRITICAL SECURITY FIXES IMPLEMENTED

This guide details the security improvements made to your Laravel application and provides instructions for testing and deployment.

## Environment Setup

### 1. Production Environment Variables

**CRITICAL**: Update your `.env` file with secure production settings:

```bash
# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Session Security
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Logging Security
LOG_LEVEL=warning

# Remove Exposed Credentials
# NEVER commit actual email passwords to version control
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password_here
```

### 2. Required Deployment Steps

```bash
# 1. Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# 2. Generate application key (if not set)
php artisan key:generate

# 3. Run database migrations
php artisan migrate --force

# 4. Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# 5. Create admin user
php artisan user:assign-admin admin@yourdomain.com

# 6. Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set file permissions
chmod -R 755 storage bootstrap/cache
```

## Testing Instructions

### 1. Run Comprehensive Test Suite

```bash
# Install dependencies first
composer install

# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run security-specific tests
php artisan test tests/Feature/Security/
php artisan test tests/Feature/Api/
```

### 2. Security Validation Checklist

#### API Security Tests
```bash
# Test unauthenticated access (should return 401)
curl -X GET http://your-domain.com/api/pos/orders

# Test insufficient permissions (should return 403)
curl -X GET http://your-domain.com/api/admin/dashboard \
  -H "Authorization: Bearer {customer_token}"

# Test rate limiting (should return 429 after limits)
# Run multiple rapid requests to auth endpoints
```

#### Authentication Tests
```bash
# Test login rate limiting
for i in {1..6}; do
  curl -X POST http://your-domain.com/login \
    -d "email=test@test.com&password=wrong"
done
# 6th request should return 429

# Test employee verification rate limiting
for i in {1..11}; do
  curl -X POST http://your-domain.com/api/employee/verify \
    -d "identifier=test@test.com&password=wrong"
done
# 11th request should return 429
```

#### Data Protection Tests
```bash
# Test mass assignment protection
curl -X POST http://your-domain.com/register \
  -d "name=Test&email=test@test.com&password=password&password_confirmation=password&is_admin=true&points=1000"
# User should be created WITHOUT admin privileges or points

# Test financial field protection
curl -X POST http://your-domain.com/api/pos/orders \
  -H "Authorization: Bearer {token}" \
  -d "type=takeaway&items[0][product_id]=1&items[0][quantity]=1&total_amount=999999"
# Order should be created with CALCULATED total, not the submitted amount
```

### 3. Security Headers Validation

```bash
# Test security headers are present
curl -I http://your-domain.com/

# Should include:
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
# Strict-Transport-Security: max-age=31536000; includeSubDomains
# Content-Security-Policy: [policy string]
```

### 4. File Access Protection

```bash
# Test environment file protection (should return 403/404)
curl http://your-domain.com/.env
curl http://your-domain.com/.env.example

# Test sensitive directory protection (should return 403/404)
curl http://your-domain.com/storage/
curl http://your-domain.com/bootstrap/
curl http://your-domain.com/vendor/
```

## Database Security Verification

### 1. Check Financial Data Integrity

```sql
-- Verify no orders have manually set financial amounts that don't match calculations
SELECT id, order_number, total_amount, tax_amount, grand_total
FROM orders 
WHERE grand_total != (total_amount + tax_amount);

-- Should return no results if financial calculations are correct
```

### 2. Verify User Privileges

```sql
-- Check no users have admin privileges without proper role assignment
SELECT u.id, u.name, u.email, u.is_admin, r.name as role_name
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
WHERE u.is_admin = 1 AND r.name != 'admin';

-- Should return no results
```

## Production Deployment Checklist

### Pre-Deployment Security Checks

- [ ] Environment file contains no exposed credentials
- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production` in production
- [ ] Session encryption enabled (`SESSION_ENCRYPT=true`)
- [ ] HTTPS enforced (`SESSION_SECURE_COOKIE=true`)
- [ ] Security headers configured
- [ ] Rate limiting implemented
- [ ] Database backups configured

### Post-Deployment Verification

- [ ] All tests pass
- [ ] API endpoints require proper authentication
- [ ] Role-based access control works correctly
- [ ] Financial calculations are accurate
- [ ] Security headers are present
- [ ] File access protection works
- [ ] Rate limiting is enforced
- [ ] Audit logging is working

## Monitoring and Alerting

### Log Monitoring

Monitor these log patterns for security issues:

```bash
# Failed authentication attempts
grep "Authentication failed" storage/logs/laravel.log

# Authorization failures
grep "Authorization failed" storage/logs/laravel.log

# Validation failures (potential attack attempts)
grep "Validation failed" storage/logs/laravel.log

# Unexpected errors
grep "Unexpected error" storage/logs/laravel.log
```

### Security Alerts

Set up alerts for:
- Multiple failed login attempts from same IP
- Access attempts to protected API endpoints without authentication
- Mass assignment protection violations
- Rate limit exceeded events
- Unexpected 500 errors in financial operations

## Regular Security Maintenance

### Weekly Tasks
- Review authentication logs for suspicious activity
- Check for failed authorization attempts
- Verify financial data integrity
- Update dependencies with security patches

### Monthly Tasks
- Review and rotate API keys if used
- Audit user roles and permissions
- Test backup and restore procedures
- Review security headers and policies

### Quarterly Tasks
- Security penetration testing
- Code security audit
- Dependency vulnerability scan
- Update security documentation

## Emergency Response

### Security Incident Response

1. **Immediate Actions**
   - Disable affected accounts
   - Check logs for scope of breach
   - Preserve evidence

2. **Investigation**
   - Identify attack vector
   - Assess data exposure
   - Document timeline

3. **Recovery**
   - Patch vulnerabilities
   - Reset affected credentials
   - Notify stakeholders

4. **Prevention**
   - Update security measures
   - Improve monitoring
   - Train team on new procedures

## Support Contacts

- Laravel Security: https://laravel.com/docs/security
- Spatie Permission: https://spatie.be/docs/laravel-permission
- OWASP Top 10: https://owasp.org/Top10/