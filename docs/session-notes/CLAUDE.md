# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is "My Momo Shop" - a comprehensive restaurant management system built with Laravel 10. It combines e-commerce ordering, Point of Sale (POS), employee management, inventory tracking, and a creator referral program with Progressive Web App capabilities.

## Common Development Commands

### Asset Compilation
```bash
npm run dev         # Development build
npm run watch       # Watch for changes  
npm run production  # Production build (minified, optimized)
npm run hot         # Hot module replacement
```

### Laravel Development
```bash
php artisan serve                          # Start development server
php artisan migrate:fresh --seed           # Reset database with sample data
php artisan user:assign-admin {email}      # Assign admin role to user
php artisan wallets:create-for-all-users   # Create wallets for existing users
```

### Testing
```bash
vendor/bin/phpunit                 # Run all tests
vendor/bin/phpunit --filter {test} # Run specific test
```

### Custom Composer Scripts
```bash
composer start  # php artisan serve
composer dev    # npm run dev && php artisan serve  
composer watch  # npm run watch
```

## Code Architecture

### Multi-Interface Design
The application serves different user types through distinct interfaces:
- **Web routes** (`routes/web.php`) - Customer-facing e-commerce
- **Desktop routes** (`routes/desktop.php`) - POS system interface at `/desktop/*`
- **Admin routes** (`routes/admin.php`) - Administrative functions
- **API routes** (`routes/api.php`) - Analytics and POS operations

### Role-Based Access Control
Uses Spatie Laravel Permission package with roles: `admin`, `cashier`, `employee`, `creator`
- User model has convenience methods: `isAdmin()`, `isCashier()`, `isEmployee()`
- Role assignments handled via `CheckRole` middleware and `user:assign-admin` command

### Key Business Models and Relationships
- **Users** have wallets, roles, and can be creators/employees
- **Orders** support multiple payment methods (cash, card, wallet) with POS integration
- **Products** have ratings, inventory tracking, and can be combos/drinks
- **Employees** have time tracking, schedules, and salary management
- **Creators** earn from referrals with payout system
- **Inventory** tracks stock levels with supplier orders and automated reordering

### Configuration Files
- `/config/momo.php` - Business settings (currency ¥, order statuses, product categories)
- `/config/cash_drawer.php` - POS cash management thresholds
- `/config/permission.php` - Role/permission configuration

## Database & Setup

### Database Configuration
- **SQLite** by default (portable for development)
- Extensive migration history (80+ migrations) - use `migrate:fresh` for clean starts
- Auto-wallet creation for new users via database triggers

### Initial Setup
```bash
cp .env.example .env
composer install && npm install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan user:assign-admin admin@example.com
npm run dev
```

## Special Features

### Progressive Web App (PWA)
- Service worker configuration in `resources/js/`
- Offline capabilities for POS functionality
- App manifest for mobile installation

### Cash Drawer Management
- Custom helper functions in `app/Helpers/CspHelper.php` for cash alerts
- Configurable low change/excess cash thresholds
- POS-specific cash tracking and reporting

### Creator/Referral System
- Complex earning calculations in `CreatorEarning` model
- Monthly reward distribution via `AssignMonthlyCreatorRewards` command
- Payout request system with admin approval workflow

### Content Security Policy
- Custom CSP implementation with nonce generation
- Security headers middleware for XSS protection

## Frontend Architecture

### Vue.js Integration
- Vue 3 components in `resources/js/components/`
- Key components: `PosApp.vue`, `PaymentManager.vue`, `ReportManager.vue`
- Laravel Mix handles Vue compilation and hot reloading

### CSS/Styling
- Bootstrap 5 with custom Sass variables in `resources/sass/`
- TailwindCSS with PurgeCSS optimization in production
- Component-specific styles in Blade templates

## Testing Strategy

- PHPUnit with in-memory SQLite
- Feature tests for order processing, product management
- Unit tests for business logic
- Test database automatically created/destroyed per test run

## Security Implementations

### Authentication & Authorization
- **API Authentication**: All API endpoints protected with Laravel Sanctum
- **Role-Based Access Control**: Uses Spatie Laravel Permission with strict policies
- **Rate Limiting**: Applied to authentication (5/min), API (60/min), and public endpoints (30/min)
- **Authorization Policies**: OrderPolicy enforces proper access control

### Data Protection
- **Mass Assignment Protection**: Critical fields (financial, admin) are guarded
- **Input Validation**: Custom FormRequest classes with comprehensive validation
- **API Resources**: Control exactly what data is exposed to different user roles
- **Information Leakage Prevention**: Sensitive data hidden from unauthorized users

### Financial Security
- **Database Transactions**: All financial operations wrapped in DB transactions
- **Calculated Fields**: Financial amounts calculated, not mass-assigned
- **Audit Logging**: All order operations logged with user context
- **Business Logic Validation**: Status transitions and role permissions enforced

### Infrastructure Security
- **Session Security**: Encryption enabled, HTTPS-only cookies, strict SameSite
- **Security Headers**: CSP, HSTS, XSS protection, and content type validation
- **Environment Protection**: .htaccess blocks access to sensitive files
- **Error Handling**: Comprehensive error handling with sanitized logging

### Code Quality
- **Form Requests**: Dedicated validation classes with authorization
- **API Resources**: Structured, role-aware data serialization  
- **Policies**: Centralized authorization logic
- **Comprehensive Tests**: Unit, Feature, and Security tests included

## Development Workflow

When working with this codebase:
1. Always run `npm run dev` after pulling changes (asset compilation)
2. Use `migrate:fresh --seed` for clean database state during development
3. Test role-based features by assigning appropriate roles via artisan commands
4. POS functionality requires desktop interface routes (`/desktop/*`)
5. Creator features need users with `creator` role and associated Creator model records
6. **Run tests before committing**: `vendor/bin/phpunit`
7. **Security-first approach**: All new endpoints must include authentication, authorization, and validation