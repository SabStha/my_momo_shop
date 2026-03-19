# CLEANUP AUDIT AND PLAN

**Repository:** C:\Users\user\my_momo_shop  
**Backend:** Laravel 11 (root)  
**Frontend:** React Native Expo at `/amako-shop`  
**Audit Date:** 2025-10-07  
**Status:** ⚠️ ANALYSIS ONLY - NO CHANGES MADE

---

## 1) PROJECT TOPOLOGY

### Repository Structure

```
my_momo_shop/                           ← Laravel Backend (ROOT)
├── database/
│   ├── seeders/                        (31 files)
│   │   ├── DatabaseSeeder.php          ← Master seeder
│   │   ├── UserSeeder.php              ← Creates test users (admin@example.com, etc.)
│   │   ├── OrderSeeder.php             ← Creates 20 dummy orders
│   │   ├── SalesDataSeeder.php         ← Creates 60 days of test sales
│   │   ├── StatisticsSeeder.php        ← Creates test stats/ratings
│   │   ├── ProductSeeder.php           ← Seeds products
│   │   ├── MenuDataSeeder.php          ← Seeds menu categories
│   │   ├── BranchSeeder.php            ← Seeds branches
│   │   ├── RolesAndPermissionsSeeder.php ← PROD (keep)
│   │   ├── InvestorDataSeeder.php      ← Test investor data
│   │   ├── CouponSeeder.php
│   │   ├── OfferSeeder.php
│   │   ├── SupplierSeeder.php
│   │   └── ... (18 more)
│   ├── factories/                      (4 files)
│   │   ├── UserFactory.php             ← Generates fake users
│   │   ├── OrderFactory.php            ← Generates fake orders
│   │   ├── ProductFactory.php          ← Generates fake products
│   │   └── TableFactory.php            ← Generates fake tables
│   └── migrations/                     (199 files)
│       ├── 0001_01_01_000000_create_users_table.php
│       ├── 0001_01_01_000001_create_cache_table.php
│       ├── 2024_03_18_000001_create_products_table.php
│       └── ... (196 more)
├── tests/                              (11 files)
│   ├── Feature/
│   │   ├── Api/
│   │   │   └── PosOrderControllerTest.php
│   │   ├── ExampleTest.php
│   │   ├── OrderTest.php
│   │   ├── ProductTest.php
│   │   └── Security/
│   │       └── AuthenticationTest.php
│   └── Unit/
│       ├── ESewaPaymentProcessorTest.php
│       ├── ExampleTest.php
│       ├── Models/
│       │   ├── OrderTest.php
│       │   └── UserTest.php
│       └── Policies/
│           └── OrderPolicyTest.php
├── storage/app/public/                 (Product images, banners, etc.)
│   ├── products/
│   │   ├── foods/                      (30 images - momos)
│   │   ├── drinks/                     (30 images - beverages)
│   │   ├── desserts/                   (21 images - sweets)
│   │   ├── combos/                     (15 images - combo sets)
│   │   └── merchandise/                (merchandise images)
│   ├── banners/                        (9 images)
│   ├── logo/                           (14 images - various logos)
│   ├── profile-pictures/               (1 image - user uploaded)
│   ├── bulk-packages/                  (2 images)
│   └── default.jpg                     ← Placeholder image
└── amako-shop/                         ← React Native Mobile App
    ├── assets/
    │   └── menu.json                   ← ⚠️ MOCK FALLBACK DATA
    ├── src/
    │   ├── api/
    │   │   ├── menu.ts                 (Uses assets/menu.json as fallback)
    │   │   ├── menu-hooks.ts           (Has mock initialData)
    │   │   ├── home-hooks.ts           (Has ~80 lines of mock products)
    │   │   ├── bulk-hooks.ts           (Has mock packages)
    │   │   └── finds-hooks.ts          (Has mock finds)
    │   └── components/
    └── tests/                          ← NONE (React Native has no tests)
```

---

## 2) SEEDERS & FACTORIES INVENTORY

### Database Seeders Analysis

| File | Purpose | Tables/Models | Safe to Delete? | Reason |
|------|---------|---------------|-----------------|--------|
| **DatabaseSeeder.php** | Master orchestrator | All | ❌ **KEEP** | Entry point - modify to call only prod seeders |
| **RolesAndPermissionsSeeder.php** | Create roles & permissions | roles, permissions, role_has_permissions | ❌ **KEEP** | PRODUCTION - Required for RBAC |
| **UserSeeder.php** | Create test users | users | ⚠️ **MODIFY** | Creates admin@example.com, manager@example.com, cashier@example.com, employee@example.com, user1-5@example.com - Keep structure, clear test users in DB |
| **BranchSeeder.php** | Create branches | branches | ⚠️ **MODIFY** | Creates Main/North/South branches - Likely PROD data, review emails (momoshop.com) |
| **ProductSeeder.php** | Seed base products | products | ⚠️ **MODIFY** | Creates real menu items - PROD but may have test data mixed in |
| **MenuDataSeeder.php** | Add menu details | products | ⚠️ **MODIFY** | Updates products with ingredients/allergens - PROD but has wrong tax (5% vs 13%) |
| **OrderSeeder.php** | Create 20 dummy orders | orders, order_items | ✅ **DELETE** | Explicitly creates "dummy orders" - Pure test data |
| **SalesDataSeeder.php** | Generate 60 days of sales | users, products, orders, order_items | ✅ **DELETE** | Generates test sales data - Pure development seeder |
| **StatisticsSeeder.php** | Create sample stats | users, products, orders, product_ratings | ✅ **DELETE** | Creates "sample users", "sample data" for 50+ orders - Pure test data |
| **SupplierSeeder.php** | Create suppliers | suppliers | ⚠️ **KEEP/REVIEW** | Unknown - need to check if real suppliers or test |
| **TableSeeder.php** | Create dining tables | tables | ⚠️ **KEEP/REVIEW** | Likely PROD (restaurant tables) |
| **StockItemSeeder.php** | Create stock items | stock_items | ⚠️ **KEEP/REVIEW** | May be real inventory or test data |
| **MerchandiseSeeder.php** | Create merchandise | merchandises | ⚠️ **KEEP/REVIEW** | Unknown purpose |
| **BulkPackageSeeder.php** | Create bulk packages | bulk_packages | ⚠️ **KEEP/REVIEW** | May be PROD feature |
| **CouponSeeder.php** | Create coupons | coupons | ⚠️ **KEEP/REVIEW** | May have real or test coupons |
| **OfferSeeder.php** | Create offers | offers | ⚠️ **KEEP/REVIEW** | May have real or test offers |
| **PaymentMethodSeeder.php** | Create payment methods | payment_methods | ❌ **KEEP** | Likely PROD (eSewa, Khalti, Cash, etc.) |
| **CustomerSegmentSeeder.php** | Create customer segments | customer_segments | ⚠️ **KEEP/REVIEW** | May be PROD or test |
| **TaxDeliverySettingsSeeder.php** | Create tax/delivery settings | settings | ❌ **KEEP** | PROD configuration |
| **BadgeSystemSeeder.php** | Create badge system | badge_classes, badge_ranks, badge_tiers | ⚠️ **KEEP/REVIEW** | Loyalty system - likely PROD |
| **BranchLocationSeeder.php** | Update branch locations | branches | ⚠️ **KEEP/REVIEW** | May update real branch data |
| **CashDenominationSeeder.php** | Create cash denominations | cash_denominations | ⚠️ **KEEP/REVIEW** | POS feature - likely PROD |
| **CashDrawerAlertSeeder.php** | Create cash drawer alerts | cash_drawer_alerts | ⚠️ **KEEP/REVIEW** | POS feature |
| **FindsCategorySeeder.php** | Create finds categories | finds_categories | ⚠️ **KEEP/REVIEW** | Feature categories |
| **HomePageContentSeeder.php** | Create home page content | site_content | ⚠️ **KEEP/REVIEW** | CMS content |
| **InventoryCategorySeeder.php** | Create inventory categories | inventory_categories | ⚠️ **KEEP/REVIEW** | Likely PROD |
| **InvestorDataSeeder.php** | Create test investor data | investors, investor_investments | ✅ **DELETE** | Has "test" investors |
| **ProductImageFixSeeder.php** | Fix product images | products | ⚠️ **KEEP/REVIEW** | Utility seeder |
| **ProductionSeeder.php** | Production data seeder | multiple | ❌ **KEEP** | Name suggests PROD seeder |
| **SiteSettingsSeeder.php** | Create site settings | site_settings | ❌ **KEEP** | PROD configuration |

### Factories Analysis

| File | Purpose | Safe to Delete? | Reason |
|------|---------|-----------------|--------|
| **UserFactory.php** | Generate fake users with faker | ⚠️ **KEEP** | Used by tests - delete if tests deleted |
| **OrderFactory.php** | Generate fake orders | ⚠️ **KEEP** | Used by tests - delete if tests deleted |
| **ProductFactory.php** | Generate fake products | ⚠️ **KEEP** | Used by tests - delete if tests deleted |
| **TableFactory.php** | Generate fake tables | ⚠️ **KEEP** | Used by tests - delete if tests deleted |

**Note:** Factories are dev-only and not used in production, but needed for tests. Safe to delete if tests are removed.

---

## 3) TESTS & TEST HELPERS

### Test Files Inventory

```
tests/
├── Feature/                            (5 tests)
│   ├── Api/
│   │   └── PosOrderControllerTest.php
│   ├── ExampleTest.php                 ← Laravel default test
│   ├── OrderTest.php
│   ├── ProductTest.php
│   └── Security/
│       └── AuthenticationTest.php
├── Unit/                               (5 tests)
│   ├── ESewaPaymentProcessorTest.php
│   ├── ExampleTest.php                 ← Laravel default test
│   ├── Models/
│   │   ├── OrderTest.php
│   │   └── UserTest.php
│   └── Policies/
│       └── OrderPolicyTest.php
└── TestCase.php                        ← Base test class
```

**Total:** 11 test files

### Test Configuration Files

- `phpunit.xml` - PHPUnit configuration

### Recommendation

**DEV_ONLY: TRUE** - All test files are development-only.

**Recommendation:**
- ✅ **KEEP** if you want to maintain test coverage (best practice)
- ⚠️ **DELETE** if you never run tests and want minimal codebase
- ⚠️ **KEEP** TestCase.php and phpunit.xml even if deleting tests (for future testing)

**Decision:** Recommend **KEEP** unless explicitly requested to remove.

---

## 4) MOCK/FAKE DATA IN FRONTENDS

### React Native (amako-shop)

#### Primary Mock Data Files

| Path | Lines | Type | Referenced In | Safe to Delete? |
|------|-------|------|---------------|-----------------|
| `assets/menu.json` | 130 | Menu fallback | `src/api/menu.ts:6`, `src/api/menu-hooks.ts:24-26` | ⚠️ **KEEP** as emergency offline fallback |

**Content Preview:**
```json
{
  "categories": [
    { "id": "cat-momo", "name": "Momo" },
    { "id": "cat-drinks", "name": "Drinks" }
  ],
  "items": [
    {
      "id": "itm-classic-momo",
      "name": "Classic Chicken Momo",
      "basePrice": { "currency": "NPR", "amount": 180 }
    }
  ]
}
```

#### Hardcoded Mock Arrays in Source Code

| File | Lines | Type | Snippet | Safe to Delete? |
|------|-------|------|---------|-----------------|
| `src/api/home-hooks.ts` | 104-186 | Mock featured products (13 items) | `const mockProducts = [...]` with Unsplash URLs | ✅ **DELETE** |
| `src/api/home-hooks.ts` | 204-220 | Mock benefits (5 items) | `const benefits = [...]` | ✅ **DELETE** |
| `src/api/home-hooks.ts` | 222-260 | Mock stats | `return { totalOrders: 1234, ... }` | ✅ **DELETE** |
| `src/api/home-hooks.ts` | 275-355 | Mock reviews (10 items) | `const reviews = [...]` | ✅ **DELETE** |
| `src/api/bulk-hooks.ts` | 61-120 | Mock bulk packages (5 items) | Hardcoded package data | ✅ **DELETE** |
| `src/api/finds-hooks.ts` | 78-145 | Mock finds (8 items) | Hardcoded finds with placeholder.com images | ✅ **DELETE** |
| `app/(tabs)/menu.tsx` | 136-158 | Hardcoded featured carousel (3 items) | `featuredItems` with Unsplash URLs | ✅ **DELETE** |

#### Placeholder Image URLs

| File | Line | URL | Type |
|------|------|-----|------|
| `src/components/cart/CartAddedSheet.tsx` | 109 | `https://via.placeholder.com/96` | External placeholder service |
| `app/(tabs)/menu.tsx` | 45-46 | `http://192.168.56.1:8000/storage/default.jpg` | Hardcoded dev IP |
| `app/(tabs)/menu.tsx` | 57-59 | `http://192.168.56.1:8000/storage/products/...` | Hardcoded dev IP (3 images) |
| `app/(tabs)/bulk.tsx` | 300 | `https://via.placeholder.com/400x300/...` | External placeholder service |

### Summary: Mock Data Safe Deletion

**Safe to DELETE (No Production Impact):**
- ✅ All hardcoded mock arrays in `*-hooks.ts` files (~250 lines total)
- ✅ `featuredItems` in `menu.tsx` (23 lines)
- ✅ Replace `via.placeholder.com` URLs with proper fallback logic

**Must KEEP:**
- ⚠️ `assets/menu.json` - Emergency offline fallback (mark as dev-only)

**Must FIX:**
- 🔧 Remove hardcoded IPs (`192.168.56.1`) from image fallback logic

---

## 5) DB TABLE MAP (STATIC INFERENCE)

### Table Classification

#### KEEP LIST (Authentication & Infrastructure)

| Table Name | Origin Migration | Model | Purpose | Keep? |
|------------|------------------|-------|---------|-------|
| `users` | `0001_01_01_000000_create_users_table.php` | User | User authentication | ✅ **KEEP STRUCTURE** (purge test data) |
| `password_reset_tokens` | Implied in users migration | — | Password reset | ✅ **KEEP** (can truncate) |
| `sessions` | `2025_06_27_091749_recreate_sessions_table.php` | — | Session storage | ✅ **KEEP** (can truncate) |
| `personal_access_tokens` | Laravel Sanctum | — | API tokens | ✅ **KEEP** (can truncate old) |
| `migrations` | Laravel default | — | Migration tracking | ✅ **KEEP** |
| `cache`, `cache_locks` | `0001_01_01_000001_create_cache_table.php` | — | Application cache | ✅ **KEEP** (can truncate) |
| `jobs`, `job_batches`, `failed_jobs` | `0001_01_01_000002_create_jobs_table.php` | — | Queue system | ✅ **KEEP** (can truncate) |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | Spatie Permission | — | RBAC system | ✅ **KEEP** |

#### PROD CONFIGURATION TABLES (Keep Structure & Data)

| Table Name | Origin Migration | Purpose | Keep? |
|------------|------------------|---------|-------|
| `branches` | `2024_03_09_000000_create_branches_table.php` | Store locations | ✅ **KEEP** (review branch data) |
| `categories` | `2024_03_19_000000_create_categories_table.php` | Product categories | ✅ **KEEP** |
| `payment_methods` | `2024_03_21_000000_create_payment_methods_table.php` | Payment options | ✅ **KEEP** |
| `settings` | `2024_06_14_000000_create_settings_table.php` | App settings | ✅ **KEEP** |
| `site_settings` | `2025_06_28_113250_create_site_settings_table.php` | Site configuration | ✅ **KEEP** |
| `site_content` | `2025_01_28_000000_create_site_content_table.php` | CMS content | ✅ **KEEP** (review content) |
| `badge_classes`, `badge_ranks`, `badge_tiers` | `2025_01_15_*` | Loyalty badge system | ✅ **KEEP** (system config) |
| `credit_tasks`, `credit_rewards` | `2025_01_15_*` | Gamification system | ✅ **KEEP** (system config) |
| `bulk_settings` | `2025_01_15_000000_create_bulk_settings_table.php` | Bulk order settings | ✅ **KEEP** |
| `rules` | `2025_06_27_082312_create_rules_table.php` | Business rules | ✅ **KEEP** |
| `finds_categories` | `2025_09_27_121807_create_finds_categories_table.php` | Ama's Finds categories | ✅ **KEEP** |
| `suppliers` | `2024_03_25_000001_create_suppliers_table.php` | Supplier management | ✅ **KEEP** (review supplier data) |
| `cash_denominations` | `2025_06_17_190000_create_cash_denominations_table.php` | POS cash tracking | ✅ **KEEP** |
| `devices` | `2025_01_27_000001_create_devices_table.php` | Mobile device tracking | ✅ **KEEP** (can truncate inactive) |

#### PURGE CANDIDATES (Business Data - Clear for Fresh Start)

| Table Name | Origin Migration | Purpose | Purge? | Notes |
|------------|------------------|---------|--------|-------|
| `products` | `2024_03_18_000001_create_products_table.php` | Menu items | ⚠️ **TRUNCATE** then re-seed | Keep structure, clear test products, re-seed real menu |
| `orders` | `2024_03_19_000000_consolidate_orders_table.php` | Customer orders | ✅ **TRUNCATE** | Clear all test/dummy orders |
| `order_items` | `2024_03_19_000002_create_order_items_table.php` | Order line items | ✅ **TRUNCATE** | Cascades with orders |
| `product_ratings` | `2025_05_21_060347_create_product_ratings_table.php` | Product reviews | ✅ **TRUNCATE** | Clear test ratings |
| `wallets` | `2024_03_18_000000_create_wallets_table.php` | User credit wallets | ⚠️ **TRUNCATE** (except real users) | Clear test user wallets |
| `wallet_transactions` | `2024_03_20_000000_wallet_transactions...` | Wallet transaction history | ✅ **TRUNCATE** | Clear test transactions |
| `ama_credits`, `ama_credit_transactions` | `2025_01_15_*` | AmaCredit system | ✅ **TRUNCATE** | Clear test credit data |
| `user_badges`, `badge_progress` | `2025_01_15_*` | User badges earned | ✅ **TRUNCATE** | Clear test achievements |
| `user_task_completions`, `user_reward_redemptions` | `2025_01_15_*` | Gamification progress | ✅ **TRUNCATE** | Clear test progress |
| `coupons`, `coupon_usages`, `user_coupons` | `2024_06_09_*` | Coupon system | ⚠️ **REVIEW** then truncate | May have real coupons to keep |
| `offers`, `offer_claims` | `2024_03_21_*, 2025_06_27_*` | Promotional offers | ⚠️ **REVIEW** then truncate | May have real offers |
| `referrals` (new_referrals) | `2024_06_03_000000_create_new_referrals_table.php` | Referral program | ✅ **TRUNCATE** | Clear test referrals |
| `combos`, `drinks` | `2025_05_30_*` | Menu item types (legacy?) | ⚠️ **REVIEW** | May be superseded by products table |
| `merchandises` | `2025_06_27_095048_create_merchandises_table.php` | Merch items | ⚠️ **TRUNCATE** | Business data |
| `bulk_packages` | `2025_06_27_100731_create_bulk_packages_table.php` | Bulk order packages | ⚠️ **TRUNCATE** | Business data |
| `customers` | `2024_03_19_000001_create_customers_table.php` | Customer profiles | ⚠️ **REVIEW** | May duplicate users table |
| `customer_segments` | `2024_03_19_000000_create_customer_segments_table.php` | Customer grouping | ⚠️ **TRUNCATE** | Segmentation data |
| `customer_feedback` | `2024_03_19_000003_create_customer_feedback_table.php` | Feedback submissions | ✅ **TRUNCATE** | Clear test feedback |
| `churn_predictions` | `2024_03_21_000000_create_churn_predictions_table.php` | ML predictions | ✅ **TRUNCATE** | Analytics data |
| `campaigns`, `campaign_triggers` | `2024_03_19_*` | Marketing campaigns | ⚠️ **TRUNCATE** | Marketing data |
| `rewards`, `payouts`, `payout_requests` | `2024_03_19_*, 2024_06_09_*` | Creator rewards | ⚠️ **TRUNCATE** | Creator program data |
| `creator_earnings`, `creator_rewards` | `2024_06_10_*, 2024_06_09_*` | Creator earnings | ⚠️ **TRUNCATE** | Creator program |
| `cashouts` | `2025_05_29_101208_create_cashouts_table.php` | Cashout requests | ⚠️ **TRUNCATE** | Financial data |
| `cash_drawers`, `cash_drawer_sessions`, `cash_drawer_logs`, `cash_drawer_adjustments`, `cash_drawer_alerts` | Multiple 2024_03_*, 2025_06_* | POS cash management | ✅ **TRUNCATE** | POS operational data |
| `payments` | `2024_03_21_000001_create_payments_table.php` | Payment records | ✅ **TRUNCATE** | Clear test payments |
| `tables` | `2024_03_10_000001_create_tables_table.php` | Restaurant tables | ❌ **KEEP** | PROD config (table numbers) |
| `stock_items` | `2024_03_10_000000_create_stock_items_table.php` | Kitchen stock | ⚠️ **TRUNCATE** | Inventory data |
| `inventories`, `branch_inventory`, `inventory_transactions` | Multiple | Inventory system | ⚠️ **TRUNCATE** | Inventory records |
| `supply_orders`, `supply_order_items` | `2025_06_07_*` | Supply chain | ⚠️ **TRUNCATE** | Supply orders |
| `weekly_stock_checks`, `monthly_stock_checks`, `daily_stock_checks` | `2025_06_21_*` | Stock audits | ⚠️ **TRUNCATE** | Audit records |
| `forecast_feedback` | `2025_06_21_195036_create_forecast_feedback_table.php` | Forecasting data | ⚠️ **TRUNCATE** | Analytics |
| `employees`, `employee_schedules`, `time_logs`, `time_entries` | Multiple 2024_03_* | Employee management | ⚠️ **TRUNCATE** | HR data |
| `pos_access_logs` | `2024_03_21_create_pos_access_logs_table.php` | POS access tracking | ✅ **TRUNCATE** | Logs |
| `activity_log` | `2025_06_07_164628_create_activity_log_table.php` | System activity log | ✅ **TRUNCATE** | Logs |
| `investors`, `investor_investments`, `investor_payouts`, `investor_reports` | `2025_06_23_*` | Investor management | ⚠️ **TRUNCATE** (check for real investors) | Financial data |
| `investment_page_visits` | `2025_06_28_*` | Investment page analytics | ✅ **TRUNCATE** | Analytics |
| `user_themes` | `2025_07_01_071712_create_user_themes_table.php` | User theme preferences | ⚠️ **TRUNCATE** | User preferences |

### Known Test Users (From Seeders)

**UserSeeder.php creates:**
- admin@example.com (Admin)
- manager@example.com (Manager)
- cashier@example.com (Cashier)
- employee@example.com (Employee)
- user1@example.com through user5@example.com (Regular users)

**SalesDataSeeder.php creates:**
- customer0@example.com through customer4@example.com

**StatisticsSeeder.php creates:**
- customer1@example.com through customer20@example.com

**TOTAL TEST USERS:** ~30 users with @example.com domain

**REAL USER (Keep):**
- sabstha98@gmail.com (ID: 31, Role: admin) ← Confirmed real user

---

## 6) PROPOSED SAFE DELETION/TRUNCATION PLAN (NO EXECUTION)

### STEP A: BACKUPS (MANDATORY FIRST STEP)

#### A1: Database Backup

**For MySQL/MariaDB:**
```bash
# Full database dump with structure + data
mysqldump -u root -p my_momo_shop > backup-$(date +%Y%m%d-%H%M%S)-full.sql

# Structure only (for reference)
mysqldump -u root -p --no-data my_momo_shop > backup-$(date +%Y%m%d-%H%M%S)-structure.sql

# Verify backup file is >0 bytes
ls -lh backup-*.sql
```

**For SQLite (if using):**
```bash
# Copy database file
cp database/database.sqlite database/database.sqlite.backup-$(date +%Y%m%d-%H%M%S)

# Verify backup
ls -lh database/*.backup-*
```

#### A2: Environment Backup

```bash
# Backup .env file
cp .env .env.backup-$(date +%Y%m%d-%H%M%S)

# Verify backup
ls -lh .env.backup-*
```

#### A3: Storage Backup

```bash
# Create tarball of storage/app/public
tar -czf storage-public-backup-$(date +%Y%m%d-%H%M%S).tar.gz storage/app/public

# Verify backup
ls -lh storage-public-backup-*.tar.gz
```

#### A4: Git Commit (Checkpoint)

```bash
# Create checkpoint before cleanup
git add -A
git commit -m "Checkpoint before cleanup ($(date +%Y-%m-%d))"

# Create tag for easy rollback
git tag cleanup-checkpoint-$(date +%Y%m%d)

# Verify
git log -1 --oneline
git tag -l "cleanup-*"
```

### STEP B: DATABASE PURGE

#### B1: DRY-RUN (Preview Only - MySQL)

**Purpose:** Generate TRUNCATE statements for review WITHOUT executing

```sql
-- DRY-RUN: Generate TRUNCATE statements for all tables EXCEPT KEEP LIST
SELECT CONCAT('TRUNCATE TABLE `', table_name, '`;') AS truncate_statement
FROM information_schema.tables
WHERE table_schema = 'my_momo_shop'
  AND table_name NOT IN (
    -- KEEP LIST (Infrastructure)
    'users', 'password_reset_tokens', 'sessions', 'web_sessions',
    'personal_access_tokens', 'migrations',
    'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
    'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions',
    
    -- KEEP LIST (PROD Configuration)
    'branches', 'categories', 'payment_methods', 'settings', 'site_settings', 'site_content',
    'badge_classes', 'badge_ranks', 'badge_tiers', 'credit_tasks', 'credit_rewards',
    'bulk_settings', 'rules', 'finds_categories', 'suppliers', 'tables',
    'cash_denominations', 'cash_denomination_changes', 'devices'
  )
ORDER BY table_name;

-- Review output before executing!
```

#### B2: Whitelist Explicit TRUNCATE (Conservative Approach)

**Purpose:** Explicitly list tables to truncate (safest method)

```sql
-- EXPLICIT PURGE LIST (Review Each Line Before Execution)

SET FOREIGN_KEY_CHECKS = 0;  -- Disable foreign key checks temporarily

-- Business Transaction Data
TRUNCATE TABLE orders;
TRUNCATE TABLE order_items;
TRUNCATE TABLE payments;

-- Product Data (Will Re-Seed)
TRUNCATE TABLE products;
-- TRUNCATE TABLE categories;  -- ❌ Commented - May need to keep if manually configured

-- Inventory & Stock
TRUNCATE TABLE inventories;
TRUNCATE TABLE branch_inventory;
TRUNCATE TABLE inventory_transactions;
TRUNCATE TABLE stock_items;
TRUNCATE TABLE supply_orders;
TRUNCATE TABLE supply_order_items;
TRUNCATE TABLE weekly_stock_checks;
TRUNCATE TABLE monthly_stock_checks;
-- TRUNCATE TABLE daily_stock_checks;  -- Table may not exist

-- Analytics & Logs
TRUNCATE TABLE activity_log;
TRUNCATE TABLE pos_access_logs;
TRUNCATE TABLE customer_feedback;
TRUNCATE TABLE product_ratings;
TRUNCATE TABLE churn_predictions;
TRUNCATE TABLE forecast_feedback;
TRUNCATE TABLE investment_page_visits;

-- Financial & Loyalty
TRUNCATE TABLE wallets;
TRUNCATE TABLE wallet_transactions;
TRUNCATE TABLE ama_credits;
TRUNCATE TABLE ama_credit_transactions;
TRUNCATE TABLE user_badges;
TRUNCATE TABLE badge_progress;
TRUNCATE TABLE user_task_completions;
TRUNCATE TABLE user_reward_redemptions;
TRUNCATE TABLE cashouts;

-- Marketing & Campaigns
TRUNCATE TABLE coupons;
TRUNCATE TABLE coupon_usages;
TRUNCATE TABLE user_coupons;
TRUNCATE TABLE offers;
TRUNCATE TABLE offer_claims;
TRUNCATE TABLE campaigns;
TRUNCATE TABLE campaign_triggers;
TRUNCATE TABLE referrals;  -- or new_referrals
TRUNCATE TABLE customer_segments;

-- Creator/Influencer Program
TRUNCATE TABLE rewards;
TRUNCATE TABLE payouts;
TRUNCATE TABLE payout_requests;
TRUNCATE TABLE creator_earnings;
TRUNCATE TABLE creator_rewards;

-- Investor Management
TRUNCATE TABLE investors;
TRUNCATE TABLE investor_investments;
TRUNCATE TABLE investor_payouts;
TRUNCATE TABLE investor_reports;

-- POS Operations
TRUNCATE TABLE cash_drawers;
TRUNCATE TABLE cash_drawer_sessions;
TRUNCATE TABLE cash_drawer_logs;
TRUNCATE TABLE cash_drawer_adjustments;
TRUNCATE TABLE cash_drawer_alerts;

-- Employee Management
TRUNCATE TABLE employees;
TRUNCATE TABLE employee_schedules;
TRUNCATE TABLE time_logs;
TRUNCATE TABLE time_entries;

-- Legacy/Unused Tables (if confirmed unused)
-- TRUNCATE TABLE combos;           -- May be superseded by products
-- TRUNCATE TABLE drinks;            -- May be superseded by products
-- TRUNCATE TABLE merchandises;      -- Check if in use
-- TRUNCATE TABLE bulk_packages;     -- Check if in use
-- TRUNCATE TABLE customers;         -- May duplicate users table

-- Session cleanup (safe to clear)
TRUNCATE TABLE sessions;
TRUNCATE TABLE web_sessions;

-- Cache cleanup (safe to clear)
TRUNCATE TABLE cache;
TRUNCATE TABLE cache_locks;

-- Queue cleanup (safe to clear old jobs)
TRUNCATE TABLE jobs;
TRUNCATE TABLE failed_jobs;
-- TRUNCATE TABLE job_batches;  -- Table may not exist

SET FOREIGN_KEY_CHECKS = 1;  -- Re-enable foreign key checks

-- Verify cleanup
SELECT table_name, table_rows 
FROM information_schema.tables 
WHERE table_schema = 'my_momo_shop' 
  AND table_rows > 0
ORDER BY table_rows DESC;
```

#### B3: Selective User Cleanup (Preserve Real User)

**Purpose:** Delete only test users, keep real authenticated users

```sql
-- DELETE test users from seeders (all @example.com)
DELETE FROM users 
WHERE email LIKE '%@example.com';

-- DELETE specific test users by email (whitelist approach)
DELETE FROM users WHERE email IN (
  'admin@example.com',
  'manager@example.com',
  'cashier@example.com',
  'employee@example.com',
  'user1@example.com',
  'user2@example.com',
  'user3@example.com',
  'user4@example.com',
  'user5@example.com',
  'customer0@example.com',
  'customer1@example.com',
  'customer2@example.com',
  'customer3@example.com',
  'customer4@example.com',
  'customer5@example.com',
  'customer6@example.com',
  'customer7@example.com',
  'customer8@example.com',
  'customer9@example.com',
  'customer10@example.com',
  'customer11@example.com',
  'customer12@example.com',
  'customer13@example.com',
  'customer14@example.com',
  'customer15@example.com',
  'customer16@example.com',
  'customer17@example.com',
  'customer18@example.com',
  'customer19@example.com',
  'customer20@example.com'
);

-- Verify real users remain
SELECT id, name, email, role, created_at 
FROM users 
ORDER BY created_at DESC;

-- Expected: Only sabstha98@gmail.com and any other real users remain
```

### STEP C: FILE DELETIONS (Git Staging)

#### C1: Delete Dev-Only Seeders

```bash
# Navigate to project root
cd /c/Users/user/my_momo_shop

# Delete test data seeders (STAGE ONLY - don't commit yet)
git rm database/seeders/OrderSeeder.php
git rm database/seeders/SalesDataSeeder.php
git rm database/seeders/StatisticsSeeder.php
git rm database/seeders/InvestorDataSeeder.php

# Verify staging
git status
```

#### C2: Delete Factories (If Deleting Tests)

**Only execute if you decide to remove tests completely:**

```bash
git rm database/factories/OrderFactory.php
git rm database/factories/ProductFactory.php
git rm database/factories/TableFactory.php
# Keep UserFactory.php if keeping tests, or delete if not
# git rm database/factories/UserFactory.php
```

#### C3: Delete Tests (Optional - Not Recommended)

**Only execute if explicitly requested:**

```bash
# Delete all test files
git rm -r tests/Feature
git rm -r tests/Unit
# Keep tests/TestCase.php for future use
# git rm tests/TestCase.php

# Delete test configuration
# git rm phpunit.xml  # Keep for future testing

# Verify
git status
```

#### C4: Clean Mock Data in Mobile App

**DO NOT DELETE FILES - These changes require code modification, not file deletion:**

Files to **MODIFY** (not delete):
- `amako-shop/src/api/menu-hooks.ts` - Remove initialData fallbacks
- `amako-shop/src/api/home-hooks.ts` - Remove mock arrays
- `amako-shop/src/api/bulk-hooks.ts` - Remove mock packages
- `amako-shop/src/api/finds-hooks.ts` - Remove mock finds
- `amako-shop/app/(tabs)/menu.tsx` - Remove featuredItems array

**Keep but mark as dev-only:**
- `amako-shop/assets/menu.json` - Emergency offline fallback

#### C5: Delete Placeholder Images (Optional)

**Only if confirmed unused:**

```bash
# Delete default.jpg if not referenced
git rm storage/app/public/default.jpg

# Verify no code references it first:
grep -r "default\.jpg" app/ resources/ routes/
```

#### C6: Commit Cleanup (After Review)

```bash
# Review all staged deletions
git status
git diff --staged --summary

# Commit if satisfied
git commit -m "Clean up: Remove dev-only seeders, test data, and mock files

- Removed OrderSeeder, SalesDataSeeder, StatisticsSeeder, InvestorDataSeeder
- [Optional] Removed test factories and tests
- Database tables truncated (see CLEANUP_AUDIT_AND_PLAN.md)
- Backed up: backup-YYYYMMDD-HHMMSS-full.sql

Checkpoint tag: cleanup-checkpoint-YYYYMMDD"

# Create new tag
git tag cleanup-complete-$(date +%Y%m%d)
```

---

## 7) RISK CHECKS & SAFEGUARDS

### Pre-Execution Checklist

**MANDATORY CHECKS - Do NOT proceed without:**

- [ ] **1. Verify Environment**
  ```bash
  grep "APP_ENV" .env
  # If APP_ENV=production, require explicit confirmation
  ```
  
- [ ] **2. Backup Exists**
  ```bash
  ls -lh backup-*.sql
  # Verify file exists and is >100KB (not empty)
  ```

- [ ] **3. Laravel Health Check**
  ```bash
  php artisan --version     # Should output Laravel version
  php artisan migrate:status # Should show migrations
  ```

- [ ] **4. Database Connection Test**
  ```bash
  php artisan tinker
  >>> \DB::connection()->getPdo();
  >>> \App\Models\User::count();
  >>> exit
  ```

- [ ] **5. Storage Link Exists**
  ```bash
  ls -la public/storage
  # Should show symlink to storage/app/public
  ```

- [ ] **6. Real User Verified**
  ```bash
  php artisan tinker
  >>> \App\Models\User::where('email', 'sabstha98@gmail.com')->first();
  # Should return user object
  >>> exit
  ```

- [ ] **7. Git Clean State**
  ```bash
  git status
  # Should show clean working directory or only expected changes
  ```

- [ ] **8. Review Permissions**
  ```bash
  # Check current user roles/permissions won't be lost
  php artisan tinker
  >>> \Spatie\Permission\Models\Role::with('permissions')->get();
  >>> exit
  ```

### Environment-Specific Safeguards

```php
// Add to cleanup script header (if creating artisan command):

if (app()->environment('production')) {
    if (!$this->confirm('⚠️  WARNING: Running in PRODUCTION! Continue cleanup?')) {
        $this->error('Cleanup cancelled for safety.');
        return 1;
    }
    
    if (!$this->confirm('🔴 DATABASE WILL BE WIPED! Type YES to continue', false)) {
        $this->error('Cleanup cancelled.');
        return 1;
    }
}

// Require fresh backup
$backupFiles = glob(base_path('backup-*.sql'));
$latestBackup = end($backupFiles);
$backupAge = $latestBackup ? time() - filemtime($latestBackup) : PHP_INT_MAX;

if ($backupAge > 3600) {  // Older than 1 hour
    $this->error('❌ No recent backup found! Create backup first:');
    $this->line('mysqldump -u root -p my_momo_shop > backup-$(date +%Y%m%d-%H%M%S)-full.sql');
    return 1;
}

$this->info('✅ Backup found: ' . basename($latestBackup) . ' (' . human_filesize(filesize($latestBackup)) . ')');
```

### Post-Cleanup Verification

```bash
# After cleanup, verify:

# 1. Real user still exists
php artisan tinker
>>> \App\Models\User::where('email', 'sabstha98@gmail.com')->exists();
# Should return: true

# 2. Roles still exist
>>> \Spatie\Permission\Models\Role::count();
# Should return: 6 (admin, creator, user, employee.manager, employee.cashier, employee.regular)

# 3. Test users gone
>>> \App\Models\User::where('email', 'like', '%@example.com')->count();
# Should return: 0

# 4. Products table ready for fresh data
>>> \App\Models\Product::count();
# Should return: 0 (if truncated) or >0 (if kept some products)

# 5. Storage link intact
>>> exit
ls -la public/storage
# Should still show symlink

# 6. Artisan commands work
php artisan route:list | head -20
php artisan config:clear
php artisan cache:clear

# 7. Re-seed if needed
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=MenuDataSeeder
```

---

## 8) OUTPUT APPENDICES

### APPENDIX A: SEEDERS INDEX

| # | File | Tables Touched | Purpose | Test Users? | Safe to Delete? | Justification |
|---|------|----------------|---------|-------------|-----------------|---------------|
| 1 | DatabaseSeeder.php | All | Master orchestrator | No | ❌ KEEP | Modify to call only prod seeders |
| 2 | RolesAndPermissionsSeeder.php | roles, permissions | RBAC setup | No | ❌ KEEP | PRODUCTION - Required for auth |
| 3 | UserSeeder.php | users | Create test users | Yes (10 users) | ⚠️ MODIFY | Creates admin@example.com + test users - Keep structure, delete test users in DB |
| 4 | BranchSeeder.php | branches | Create store branches | No | ⚠️ REVIEW | Creates Main/North/South branches - Verify if real or test |
| 5 | ProductSeeder.php | products | Seed menu products | No | ⚠️ KEEP | PRODUCTION - Real menu items (verify no test items) |
| 6 | MenuDataSeeder.php | products | Add menu details | No | ⚠️ MODIFY | PRODUCTION but has wrong tax (5% → 13%) |
| 7 | OrderSeeder.php | orders, order_items | Create 20 dummy orders | Yes | ✅ DELETE | Line 30: "Create 20 dummy orders" - Explicitly test data |
| 8 | SalesDataSeeder.php | users, products, orders, order_items | 60 days of test sales | Yes (5 users) | ✅ DELETE | Lines 16-28: Creates customer0-4@example.com, generates fake sales |
| 9 | StatisticsSeeder.php | users, products, orders, product_ratings | Sample stats | Yes (20 users) | ✅ DELETE | Lines 16-28: Creates customer1-20@example.com, 50 test orders |
| 10 | SupplierSeeder.php | suppliers | Create suppliers | Unknown | ⚠️ REVIEW | Need to check if test or real suppliers |
| 11 | TableSeeder.php | tables | Create restaurant tables | No | ❌ KEEP | PRODUCTION - Physical table numbers |
| 12 | StockItemSeeder.php | stock_items | Create stock items | Unknown | ⚠️ REVIEW | May be test or real inventory |
| 13 | MerchandiseSeeder.php | merchandises | Create merchandise | Unknown | ⚠️ REVIEW | Need content review |
| 14 | BulkPackageSeeder.php | bulk_packages | Create bulk packages | Unknown | ⚠️ REVIEW | Feature seeder |
| 15 | CouponSeeder.php | coupons | Create coupons | Unknown | ⚠️ REVIEW | May have test or real coupons |
| 16 | OfferSeeder.php | offers | Create offers | Unknown | ⚠️ REVIEW | May have test or real offers |
| 17 | PaymentMethodSeeder.php | payment_methods | Create payment methods | No | ❌ KEEP | PRODUCTION - eSewa, Khalti, Cash, Card |
| 18 | CustomerSegmentSeeder.php | customer_segments | Create segments | Unknown | ⚠️ TRUNCATE DATA | Segmentation rules |
| 19 | TaxDeliverySettingsSeeder.php | settings | Tax/delivery config | No | ❌ KEEP | PRODUCTION configuration |
| 20 | BadgeSystemSeeder.php | badge_classes, badge_ranks, badge_tiers | Badge system | No | ❌ KEEP | PRODUCTION - Loyalty system structure |
| 21 | BranchLocationSeeder.php | branches | Update branch GPS | Unknown | ⚠️ REVIEW | May update real locations |
| 22 | CashDenominationSeeder.php | cash_denominations | Cash denominations | No | ❌ KEEP | PRODUCTION - NPR denominations (Rs 1, 2, 5, 10, etc.) |
| 23 | CashDrawerAlertSeeder.php | cash_drawer_alerts | Alert templates | Unknown | ⚠️ REVIEW | POS alert rules |
| 24 | FindsCategorySeeder.php | finds_categories | Finds categories | Unknown | ⚠️ REVIEW | Feature categories |
| 25 | HomePageContentSeeder.php | site_content | Home page CMS | Unknown | ⚠️ REVIEW | May have test or real content |
| 26 | InventoryCategorySeeder.php | inventory_categories | Inventory categories | Unknown | ⚠️ KEEP | Likely PROD |
| 27 | InvestorDataSeeder.php | investors, investor_investments | Test investor data | Yes | ✅ DELETE | Has example.com emails in comments |
| 28 | ProductImageFixSeeder.php | products | Fix image paths | No | ⚠️ UTILITY | One-time fix seeder - can delete after run |
| 29 | ProductionSeeder.php | multiple | Production data | No | ❌ KEEP | Name indicates PROD seeder |
| 30 | SiteSettingsSeeder.php | site_settings | Site settings | No | ❌ KEEP | PRODUCTION configuration |
| 31 | RoleSeeder.php | roles | Create roles | No | ⚠️ DUPLICATE? | May duplicate RolesAndPermissionsSeeder |

**Summary:**
- **KEEP (10):** RolesAndPermissionsSeeder, PaymentMethodSeeder, TaxDeliverySettingsSeeder, BadgeSystemSeeder, CashDenominationSeeder, TableSeeder, InventoryCategorySeeder, ProductionSeeder, SiteSettingsSeeder, DatabaseSeeder
- **DELETE (4):** OrderSeeder, SalesDataSeeder, StatisticsSeeder, InvestorDataSeeder
- **MODIFY (3):** UserSeeder, ProductSeeder, MenuDataSeeder
- **REVIEW (14):** All others require content inspection

---

### APPENDIX B: FACTORIES INDEX

| # | File | Model | Purpose | Used By | Safe to Delete? |
|---|------|-------|---------|---------|-----------------|
| 1 | UserFactory.php | User | Generate fake users | Tests, seeders via `User::factory()` | ⚠️ KEEP if tests exist, DELETE if tests removed |
| 2 | OrderFactory.php | Order | Generate fake orders | Tests, used in OrderTest.php | ⚠️ KEEP if tests exist, DELETE if tests removed |
| 3 | ProductFactory.php | Product | Generate fake products | Tests, ProductTest.php | ⚠️ KEEP if tests exist, DELETE if tests removed |
| 4 | TableFactory.php | Table | Generate fake restaurant tables | Unknown | ⚠️ KEEP if tests exist, DELETE if tests removed |

**Summary:**
- Factories are **DEV-ONLY** (never used in production)
- Used by PHPUnit/Pest tests for generating fake data
- Safe to delete **IF AND ONLY IF** you delete tests too
- Recommend **KEEP** if keeping tests

**Grep Evidence:** No `factory()` calls found in seeders (seeders use `firstOrCreate` instead).

---

### APPENDIX C: MOCK DATA INDEX (Frontend)

#### Mock Data Files

```json
[
  {
    "path": "amako-shop/assets/menu.json",
    "lines": "1-130 (entire file)",
    "snippet": "{ categories: [...], items: [...] }",
    "type": "Offline fallback menu data",
    "safe_to_delete": "NO - keep as emergency fallback",
    "action": "Keep but add comment marking it as emergency-only"
  }
]
```

#### Mock Data in Source Code

```json
[
  {
    "path": "amako-shop/src/api/menu.ts",
    "line": 6,
    "snippet": "import bundledMenuData from '../../assets/menu.json';",
    "safe_to_delete": "NO - keep import but reduce usage"
  },
  {
    "path": "amako-shop/src/api/menu.ts",
    "line": 49,
    "snippet": "return fallbackData;",
    "safe_to_delete": "NO - keep fallback for offline mode"
  },
  {
    "path": "amako-shop/src/api/menu-hooks.ts",
    "line": 76,
    "snippet": "initialData: fallbackCategories,",
    "safe_to_delete": "YES - remove pre-population with mock"
  },
  {
    "path": "amako-shop/src/api/menu-hooks.ts",
    "line": 99,
    "snippet": "initialData: fallbackItems.find(...)",
    "safe_to_delete": "YES - remove pre-population"
  },
  {
    "path": "amako-shop/src/api/menu-hooks.ts",
    "line": 122,
    "snippet": "initialData: fallbackItems.filter(...)",
    "safe_to_delete": "YES - remove pre-population"
  },
  {
    "path": "amako-shop/src/api/menu-hooks.ts",
    "line": 146,
    "snippet": "initialData: query.length >= 2 ? fallbackItems.filter(...)",
    "safe_to_delete": "YES - remove pre-population"
  },
  {
    "path": "amako-shop/src/api/menu-hooks.ts",
    "line": 211,
    "snippet": "initialData: fallbackItems,",
    "safe_to_delete": "YES - remove pre-population"
  },
  {
    "path": "amako-shop/src/api/home-hooks.ts",
    "line": "104-186",
    "snippet": "const mockProducts = [ ... 13 products with Unsplash images ... ]",
    "safe_to_delete": "YES - replace with API-only fetching"
  },
  {
    "path": "amako-shop/src/api/home-hooks.ts",
    "line": "204-220",
    "snippet": "const benefits = [ ... 5 benefit cards ... ]",
    "safe_to_delete": "YES - replace with API or CMS"
  },
  {
    "path": "amako-shop/src/api/home-hooks.ts",
    "line": "222-260",
    "snippet": "return { totalOrders: 1234, activeUsers: 892, ... }",
    "safe_to_delete": "YES - use real API stats"
  },
  {
    "path": "amako-shop/src/api/home-hooks.ts",
    "line": "275-355",
    "snippet": "const reviews = [ ... 10 fake reviews ... ]",
    "safe_to_delete": "YES - use real product_ratings from DB"
  },
  {
    "path": "amako-shop/src/api/bulk-hooks.ts",
    "line": "61-120",
    "snippet": "return { ... mock bulk packages ... }",
    "safe_to_delete": "YES - use real bulk_packages from DB"
  },
  {
    "path": "amako-shop/src/api/finds-hooks.ts",
    "line": "78-145",
    "snippet": "const mockFinds = [ ... 8 items with placeholder.com ... ]",
    "safe_to_delete": "YES - use real finds_categories from DB"
  },
  {
    "path": "amako-shop/app/(tabs)/menu.tsx",
    "line": "136-158",
    "snippet": "const featuredItems = [ ... 3 carousel items with Unsplash ... ]",
    "safe_to_delete": "YES - already removed in menu flash fix"
  },
  {
    "path": "amako-shop/src/components/cart/CartAddedSheet.tsx",
    "line": 109,
    "snippet": "uri: payload.thumb || 'https://via.placeholder.com/96'",
    "safe_to_delete": "YES - replace with proper default image"
  },
  {
    "path": "amako-shop/app/(tabs)/bulk.tsx",
    "line": 300,
    "snippet": "uri: 'https://via.placeholder.com/400x300/...'",
    "safe_to_delete": "YES - replace with real package image or local default"
  }
]
```

**Total Mock Data Lines to Remove:** ~400 lines of hardcoded mock data across 7 files

---

### APPENDIX D: TABLE MAP (Inferred from Migrations)

#### Infrastructure Tables (ALWAYS KEEP)

| Table | Migration | Model | Purpose | Action |
|-------|-----------|-------|---------|--------|
| users | 0001_01_01_000000 | User | User accounts | KEEP structure, DELETE test users |
| password_reset_tokens | users migration | — | Password resets | KEEP, can TRUNCATE |
| sessions | 2025_06_27_091749 | — | User sessions | KEEP, TRUNCATE ok |
| web_sessions | 2025_06_26_130330 | — | Web sessions | KEEP, TRUNCATE ok |
| personal_access_tokens | Sanctum | — | API tokens | KEEP, TRUNCATE old ok |
| migrations | Laravel default | — | Migration tracking | KEEP |
| cache | 0001_01_01_000001 | — | Cache storage | KEEP, TRUNCATE ok |
| cache_locks | cache migration | — | Cache locks | KEEP, TRUNCATE ok |
| jobs | 0001_01_01_000002 | — | Queue jobs | KEEP, TRUNCATE ok |
| job_batches | jobs migration | — | Batch jobs | KEEP, TRUNCATE ok |
| failed_jobs | jobs migration | — | Failed queue jobs | KEEP, TRUNCATE ok |
| roles | Spatie | Role | User roles | KEEP |
| permissions | Spatie | Permission | Permissions | KEEP |
| model_has_roles | Spatie | — | Role assignments | KEEP structure, clear test user roles |
| model_has_permissions | Spatie | — | Permission assignments | KEEP |
| role_has_permissions | Spatie | — | Role permissions | KEEP |

#### Configuration Tables (KEEP Structure & Data)

| Table | Migration | Model | Purpose | Action |
|-------|-----------|-------|---------|--------|
| branches | 2024_03_09_000000 | Branch | Store locations | KEEP (review branch data validity) |
| categories | 2024_03_19_000000 | Category | Product categories | KEEP |
| payment_methods | 2024_03_21_000000 | PaymentMethod | Payment options | KEEP |
| settings | 2024_06_14_000000 | Setting | App settings | KEEP |
| site_settings | 2025_06_28_113250 | SiteSetting | Site config | KEEP |
| site_content | 2025_01_28_000000 | SiteContent | CMS content | KEEP (review content) |
| badge_classes | 2025_01_15_000001 | BadgeClass | Badge definitions | KEEP |
| badge_ranks | 2025_01_15_000002 | BadgeRank | Badge ranks | KEEP |
| badge_tiers | 2025_01_15_000003 | BadgeTier | Badge tiers | KEEP |
| credit_tasks | 2025_01_15_000008 | CreditTask | Gamification tasks | KEEP |
| credit_rewards | 2025_01_15_000010 | CreditReward | Gamification rewards | KEEP |
| bulk_settings | 2025_01_15_000000 | BulkSetting | Bulk order settings | KEEP |
| rules | 2025_06_27_082312 | Rule | Business rules | KEEP |
| finds_categories | 2025_09_27_121807 | FindsCategory | Ama's Finds categories | KEEP |
| suppliers | 2024_03_25_000001 | Supplier | Supplier management | KEEP (review data) |
| tables | 2024_03_10_000001 | Table | Restaurant tables | KEEP |
| cash_denominations | 2025_06_17_190000 | CashDenomination | NPR denominations | KEEP |
| cash_denomination_changes | 2025_06_17_190000 | — | Denomination change log | KEEP, TRUNCATE ok |
| devices | 2025_01_27_000001 | Device | Mobile device tracking | KEEP, TRUNCATE inactive ok |

#### Business Data Tables (TRUNCATE for Fresh Start)

| Table | Migration | Model | Purpose | Action |
|-------|-----------|-------|---------|--------|
| products | 2024_03_18_000001 | Product | Menu items | TRUNCATE, then re-seed with MenuDataSeeder |
| orders | 2024_03_19_000000 | Order | Customer orders | TRUNCATE (all test orders) |
| order_items | 2024_03_19_000002 | OrderItem | Order line items | TRUNCATE (cascades with orders) |
| product_ratings | 2025_05_21_060347 | ProductRating | Product reviews | TRUNCATE (all test reviews) |
| wallets | 2024_03_18_000000 | Wallet | User credit wallets | TRUNCATE (recreate for real users) |
| wallet_transactions | 2024_03_20_000000 | WalletTransaction | Wallet history | TRUNCATE |
| payments | 2024_03_21_000001 | Payment | Payment records | TRUNCATE |
| ama_credits | 2025_01_15_000006 | AmaCredit | AmaCredit balances | TRUNCATE |
| ama_credit_transactions | 2025_01_15_000007 | AmaCreditTransaction | Credit history | TRUNCATE |
| user_badges | 2025_01_15_000004 | UserBadge | Earned badges | TRUNCATE |
| badge_progress | 2025_01_15_000005 | BadgeProgress | Badge progress | TRUNCATE |
| user_task_completions | 2025_01_15_000009 | UserTaskCompletion | Task completion | TRUNCATE |
| user_reward_redemptions | 2025_01_15_000011 | UserRewardRedemption | Reward redemptions | TRUNCATE |
| coupons | 2024_06_09_000001 | Coupon | Coupons | TRUNCATE (or keep real coupons) |
| coupon_usages | 2025_05_28_091448 | CouponUsage | Coupon usage history | TRUNCATE |
| user_coupons | 2024_06_09_000002 | UserCoupon | User-specific coupons | TRUNCATE |
| offers | 2024_03_21_000002 | Offer | Promotional offers | TRUNCATE (or keep real offers) |
| offer_claims | 2025_06_27_152454 | OfferClaim | Claimed offers | TRUNCATE |
| referrals | 2024_06_03_000000 | Referral | Referral program | TRUNCATE |
| cashouts | 2025_05_29_101208 | Cashout | Creator cashouts | TRUNCATE |
| rewards | 2024_03_19_000002 | Reward | Rewards | TRUNCATE |
| payouts | 2024_03_19_000001 | Payout | Payout records | TRUNCATE |
| payout_requests | 2024_06_09_000006 | PayoutRequest | Payout requests | TRUNCATE |
| creator_earnings | 2024_06_10_000000 | CreatorEarning | Creator earnings | TRUNCATE |
| creator_rewards | 2024_06_09_000005 | CreatorReward | Creator rewards | TRUNCATE |
| merchandises | 2025_06_27_095048 | Merchandise | Merchandise items | TRUNCATE |
| bulk_packages | 2025_06_27_100731 | BulkPackage | Bulk packages | TRUNCATE (or keep real packages) |
| customer_segments | 2024_03_19_000000 | CustomerSegment | Customer segments | TRUNCATE |
| customer_feedback | 2024_03_19_000003 | CustomerFeedback | Feedback | TRUNCATE |
| campaigns | 2024_03_19_000003 | Campaign | Marketing campaigns | TRUNCATE |
| campaign_triggers | 2024_03_19_000001 | CampaignTrigger | Campaign automation | TRUNCATE |

#### Operational Data Tables (TRUNCATE - Logs/History)

| Table | Migration | Purpose | Action |
|-------|-----------|---------|--------|
| inventories | 2024_03_18_000002 | Inventory records | TRUNCATE |
| branch_inventory | 2025_06_10_000001 | Branch inventory | TRUNCATE |
| inventory_transactions | 2025_06_20_185631 | Inventory movements | TRUNCATE |
| stock_items | 2024_03_10_000000 | Kitchen stock | TRUNCATE |
| supply_orders | 2025_06_07_132603 | Supply orders | TRUNCATE |
| supply_order_items | 2025_06_07_132604 | Supply order details | TRUNCATE |
| weekly_stock_checks | 2025_06_21_202959 | Weekly audits | TRUNCATE |
| monthly_stock_checks | 2025_06_21_203159 | Monthly audits | TRUNCATE |
| forecast_feedback | 2025_06_21_195036 | Demand forecasting | TRUNCATE |
| cash_drawers | Multiple | POS cash drawers | TRUNCATE |
| cash_drawer_sessions | 2025_06_13_110128 | Cash sessions | TRUNCATE |
| cash_drawer_logs | 2024_03_21_create | Cash logs | TRUNCATE |
| cash_drawer_adjustments | 2024_03_21_000002 | Cash adjustments | TRUNCATE |
| cash_drawer_alerts | 2025_06_19_051807 | Cash alerts (instances) | TRUNCATE |
| employees | 2024_03_21_000001 | Employee records | TRUNCATE (or keep real employees) |
| employee_schedules | 2024_03_22_000001 | Employee schedules | TRUNCATE |
| time_logs | 2024_03_22_create | Employee time tracking | TRUNCATE |
| time_entries | 2024_03_14_000002 | Time entries | TRUNCATE |
| pos_access_logs | 2024_03_21_create | POS access logs | TRUNCATE |
| activity_log | 2025_06_07_164628 | System activity log | TRUNCATE |
| churn_predictions | 2024_03_21_000000 | ML predictions | TRUNCATE |
| investors | 2025_06_23_105039 | Investor accounts | TRUNCATE (check for real investors first) |
| investor_investments | 2025_06_23_105043 | Investment records | TRUNCATE |
| investor_payouts | 2025_06_23_105046 | Investor payouts | TRUNCATE |
| investor_reports | 2025_06_23_105050 | Investor reports | TRUNCATE |
| investment_page_visits | 2025_06_28_175048 | Analytics | TRUNCATE |
| user_themes | 2025_07_01_071712 | User theme prefs | TRUNCATE |
| customers | 2024_03_19_000001 | Customer profiles | REVIEW (may duplicate users) |
| combos | 2025_05_30_092953 | Combo items (legacy?) | REVIEW (may be superseded by products) |
| drinks | 2025_05_30_090504 | Drink items (legacy?) | REVIEW (may be superseded by products) |

**Total Tables:** ~95 tables inferred from migrations

**KEEP LIST Count:** 29 tables (infrastructure + configuration)  
**PURGE CANDIDATES Count:** ~66 tables (business/operational data)

---

## CLEANUP EXECUTION PLAN (WHEN APPROVED)

### PRE-FLIGHT CHECKLIST

**Before running ANY cleanup commands:**

```bash
# 1. CHECK ENVIRONMENT
grep "APP_ENV" .env
# ⚠️ If production, proceed with EXTREME caution

# 2. VERIFY BACKUPS EXIST
ls -lh backup-*.sql storage-public-backup-*.tar.gz .env.backup-*
# Must see all 3 backup types with >0 bytes

# 3. TEST DATABASE CONNECTION
php artisan tinker
>>> \DB::connection()->getPdo();
>>> \App\Models\User::count();
>>> \App\Models\User::where('email', 'sabstha98@gmail.com')->first()->name;
# Should return: "sabin"
>>> exit

# 4. VERIFY MIGRATIONS
php artisan migrate:status
# Should show all migrations run

# 5. CREATE CHECKPOINT
git add -A
git commit -m "Checkpoint before cleanup"
git tag cleanup-checkpoint-$(date +%Y%m%d)

# 6. VERIFY STORAGE LINK
ls -la public/storage
# Should point to: ../storage/app/public

# READY TO PROCEED ✅
```

### PHASE 1: DATABASE CLEANUP

#### Option A: Conservative (Recommended)

**Execute ONLY these specific TRUNCATE commands:**

```sql
-- Connect to database
-- mysql -u root -p my_momo_shop

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

-- Clear test transaction data
TRUNCATE TABLE orders;
TRUNCATE TABLE order_items;
TRUNCATE TABLE payments;
TRUNCATE TABLE product_ratings;

-- Clear test financial data
TRUNCATE TABLE wallet_transactions;
TRUNCATE TABLE ama_credit_transactions;
TRUNCATE TABLE cashouts;
TRUNCATE TABLE payouts;
TRUNCATE TABLE payout_requests;

-- Clear test user progress
TRUNCATE TABLE user_badges;
TRUNCATE TABLE badge_progress;
TRUNCATE TABLE user_task_completions;
TRUNCATE TABLE user_reward_redemptions;

-- Clear test marketing data
TRUNCATE TABLE campaigns;
TRUNCATE TABLE campaign_triggers;
TRUNCATE TABLE coupon_usages;
TRUNCATE TABLE user_coupons;
TRUNCATE TABLE offer_claims;
TRUNCATE TABLE referrals;

-- Clear operational logs
TRUNCATE TABLE activity_log;
TRUNCATE TABLE pos_access_logs;
TRUNCATE TABLE cash_drawer_logs;
TRUNCATE TABLE cash_drawer_sessions;
TRUNCATE TABLE cash_drawer_adjustments;

-- Clear analytics
TRUNCATE TABLE churn_predictions;
TRUNCATE TABLE forecast_feedback;
TRUNCATE TABLE customer_feedback;
TRUNCATE TABLE investment_page_visits;

-- Clear inventory history (keep if you have real data!)
TRUNCATE TABLE inventory_transactions;
TRUNCATE TABLE supply_orders;
TRUNCATE TABLE supply_order_items;

-- Clear cache & sessions (always safe)
TRUNCATE TABLE cache;
TRUNCATE TABLE cache_locks;
TRUNCATE TABLE sessions;
TRUNCATE TABLE web_sessions;
TRUNCATE TABLE jobs;
TRUNCATE TABLE failed_jobs;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify before committing
SELECT table_name, table_rows FROM information_schema.tables 
WHERE table_schema = 'my_momo_shop' AND table_rows > 0 
ORDER BY table_rows DESC LIMIT 20;

-- If satisfied:
COMMIT;

-- If not satisfied:
-- ROLLBACK;
```

#### Option B: Aggressive (Delete Test Users)

**After Option A, delete test users:**

```sql
START TRANSACTION;

-- Delete all @example.com users
DELETE FROM model_has_roles WHERE model_type = 'App\\Models\\User' AND model_id IN (
  SELECT id FROM users WHERE email LIKE '%@example.com'
);

DELETE FROM model_has_permissions WHERE model_type = 'App\\Models\\User' AND model_id IN (
  SELECT id FROM users WHERE email LIKE '%@example.com'
);

DELETE FROM wallets WHERE user_id IN (
  SELECT id FROM users WHERE email LIKE '%@example.com'
);

DELETE FROM users WHERE email LIKE '%@example.com';

-- Verify sabstha98@gmail.com still exists
SELECT id, name, email, role FROM users;

-- Expected: Only sabstha98@gmail.com (and any other real users)

-- If satisfied:
COMMIT;

-- If not:
-- ROLLBACK;
```

### PHASE 2: FILE CLEANUP

#### Delete Dev-Only Seeders

```bash
# Stage deletions
git rm database/seeders/OrderSeeder.php
git rm database/seeders/SalesDataSeeder.php
git rm database/seeders/StatisticsSeeder.php
git rm database/seeders/InvestorDataSeeder.php

# Review
git status

# Don't commit yet - review first
```

#### Modify DatabaseSeeder.php

Edit `database/seeders/DatabaseSeeder.php` to remove calls to deleted seeders:

```php
public function run(): void
{
    // PROD seeders only
    $this->call(RolesAndPermissionsSeeder::class);
    $this->call(UserSeeder::class);  // ⚠️ Modify to create ONLY real admin
    $this->call(BranchSeeder::class);
    
    $this->call([
        ProductSeeder::class,
        MenuDataSeeder::class,  // ⚠️ Fix tax rate first
        SupplierSeeder::class,
        TableSeeder::class,
        PaymentMethodSeeder::class,
        TaxDeliverySettingsSeeder::class,
        // ... other PROD seeders
    ]);
    
    // REMOVED:
    // SalesDataSeeder::class,      ← DELETED
    // StatisticsSeeder::class,      ← DELETED  
    // OrderSeeder::class,           ← DELETED (not in current version but mentioned)
}
```

### PHASE 3: FRONTEND MOCK DATA REMOVAL

**Code modifications (not file deletions):**

1. **Remove initialData from menu-hooks.ts:**
   - Lines 76, 99, 122, 146-150, 211
   - Replace with: proper loading states

2. **Remove mock arrays from home-hooks.ts:**
   - Lines 104-186 (mockProducts)
   - Lines 204-220 (benefits)
   - Lines 222-260 (stats)
   - Lines 275-355 (reviews)
   - Replace with: API-only fetching

3. **Remove mock data from bulk-hooks.ts:**
   - Lines 61-120
   - Replace with: API fetching from bulk_packages table

4. **Remove mock data from finds-hooks.ts:**
   - Lines 78-145
   - Replace with: API fetching from finds_categories table

5. **Fix placeholder URLs:**
   - `CartAddedSheet.tsx:109` - Replace `via.placeholder.com` with local default
   - `bulk.tsx:300` - Replace `via.placeholder.com` with real image

6. **Fix hardcoded IPs in menu.tsx:**
   - Lines 45-46, 57-59
   - Replace `192.168.56.1:8000` with dynamic base URL from environment

### PHASE 4: RE-SEED PRODUCTION DATA

```bash
# After cleanup, seed fresh production data

# 1. Seed roles & permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# 2. Create REAL admin user (manually in tinker or update UserSeeder)
php artisan tinker
>>> $admin = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@your-real-domain.com',  // ⚠️ Use real email
    'password' => \Hash::make('secure-password-here'),
    'role' => 'admin'
]);
>>> $admin->assignRole('admin');
>>> exit

# 3. Seed branches (verify BranchSeeder has real branches)
php artisan db:seed --class=BranchSeeder

# 4. Seed products with FIXED TAX RATE (after fixing MenuDataSeeder)
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=MenuDataSeeder

# 5. Seed other PROD config
php artisan db:seed --class=PaymentMethodSeeder
php artisan db:seed --class=TaxDeliverySettingsSeeder
php artisan db:seed --class=BadgeSystemSeeder
# ... other prod seeders

# 6. Verify products
php artisan tinker
>>> \App\Models\Product::count();
>>> \App\Models\Product::first();
>>> exit
```

### PHASE 5: VERIFICATION

```bash
# 1. Check users
php artisan tinker
>>> \App\Models\User::all(['id', 'name', 'email', 'role']);
# Should see only real users (no @example.com)

# 2. Check products
>>> \App\Models\Product::count();
>>> \App\Models\Product::where('tax_rate', 13)->count();
# Both should match (all products have 13% tax)

# 3. Check orders
>>> \App\Models\Order::count();
# Should return: 0

# 4. Test app
>>> exit

# Start servers
php -S 0.0.0.0:8000 -t public
# In another terminal: cd amako-shop && npm run start:tunnel

# 5. Test mobile app:
# - Login with sabstha98@gmail.com
# - Check menu loads from API
# - Verify products show
# - Test ordering (should work with empty orders table)
```

---

## CRITICAL WARNINGS & CONSIDERATIONS

### ⚠️ BEFORE EXECUTING ANY TRUNCATE/DELETE:

1. **VERIFY ENVIRONMENT**
   ```bash
   cat .env | grep APP_ENV
   # If production, triple-check everything!
   ```

2. **BACKUP EXISTS & VALID**
   ```bash
   # Verify backup
   ls -lh backup-*.sql
   # Should show file with reasonable size (>1MB for real data)
   
   # Test backup can be loaded
   mysql -u root -p test_restore < backup-*.sql
   # Should complete without errors
   ```

3. **REAL USER PROTECTED**
   ```bash
   # Confirm sabstha98@gmail.com exists and is admin
   php artisan tinker
   >>> \App\Models\User::where('email', 'sabstha98@gmail.com')->first();
   # Should return user with admin role
   ```

4. **CURRENT USERS IDENTIFIED**
   ```bash
   # List ALL current users before cleanup
   php artisan tinker
   >>> \App\Models\User::all(['id', 'email', 'role', 'created_at'])->toArray();
   # Review and identify which are REAL (not test)
   ```

5. **NO ACTIVE SESSIONS**
   ```bash
   # Ensure no users are logged in
   # Truncating sessions will log everyone out
   ```

6. **GIT IS CLEAN**
   ```bash
   git status
   # Should show clean or only expected changes
   ```

7. **ARTISAN WORKS**
   ```bash
   php artisan list
   # Should show all commands
   ```

### ⚠️ PRODUCTION SAFETY GUARDS

If `APP_ENV=production`:

```bash
# Require explicit confirmation
echo "This is a PRODUCTION environment!"
echo "Type 'DELETE ALL TEST DATA' to continue:"
read confirmation
if [ "$confirmation" != "DELETE ALL TEST DATA" ]; then
    echo "Cleanup cancelled for safety."
    exit 1
fi
```

### 🚨 IRREVERSIBLE ACTIONS

The following actions CANNOT be undone without restoring from backup:

- ❌ `TRUNCATE TABLE` - Deletes all rows, resets auto-increment
- ❌ `DELETE FROM users` - Permanently removes user accounts
- ❌ `git rm` + commit - Removes files from repository history
- ❌ Deleting backup files

**Always have a recent backup before proceeding!**

---

## RECOMMENDED CLEANUP SEQUENCE

### Minimal Cleanup (Safest - Start Here)

**Time:** 30 minutes  
**Risk:** LOW

```bash
# 1. Backup
mysqldump -u root -p my_momo_shop > backup-$(date +%Y%m%d-%H%M%S)-full.sql

# 2. Clear only logs & caches
mysql -u root -p my_momo_shop <<EOF
TRUNCATE TABLE activity_log;
TRUNCATE TABLE pos_access_logs;
TRUNCATE TABLE cache;
TRUNCATE TABLE sessions;
EOF

# 3. Delete obvious test seeders
git rm database/seeders/OrderSeeder.php
git rm database/seeders/SalesDataSeeder.php
git rm database/seeders/StatisticsSeeder.php

# 4. Commit
git commit -m "Cleanup: Remove test seeders and clear logs"
```

### Moderate Cleanup (Recommended)

**Time:** 1-2 hours  
**Risk:** MEDIUM

1. Execute Minimal Cleanup ↑
2. Execute PHASE 1 - Option A (Conservative DB Truncate)
3. Delete test users with @example.com
4. Verify app still works
5. Remove mock arrays from frontend code
6. Test thoroughly

### Full Cleanup (Maximum)

**Time:** 3-4 hours  
**Risk:** MEDIUM-HIGH

1. Execute Moderate Cleanup ↑
2. Review and truncate ALL business data tables
3. Delete all factories (if removing tests)
4. Remove test files (if not needed)
5. Re-seed from scratch
6. Full QA testing

---

## FINAL RECOMMENDATIONS

### Do This (High Priority)

1. ✅ **Backup everything** (DB + .env + storage)
2. ✅ **Delete test seeders** (OrderSeeder, SalesDataSeeder, StatisticsSeeder, InvestorDataSeeder)
3. ✅ **Truncate test orders/transactions** (orders, order_items, payments)
4. ✅ **Delete test users** (all @example.com emails EXCEPT keep one admin if needed)
5. ✅ **Fix tax rate** (5% → 13% in MenuDataSeeder before re-seeding)
6. ✅ **Clear logs** (activity_log, pos_access_logs, sessions, cache)
7. ✅ **Remove frontend mock arrays** (~400 lines of hardcoded data)

### Review First (Medium Priority)

8. ⚠️ **Review branches** - Are Main/North/South real or test locations?
9. ⚠️ **Review suppliers** - Real suppliers or test data?
10. ⚠️ **Review employees** - Any real employee records to keep?
11. ⚠️ **Review investors** - Any real investors vs test data?
12. ⚠️ **Review coupons/offers** - Any active promotions to keep?
13. ⚠️ **Review bulk packages** - Real packages or test?

### Don't Do This (Keep)

14. ❌ **Don't delete assets/menu.json** - Emergency offline fallback
15. ❌ **Don't delete test infrastructure** - Keep TestCase.php, phpunit.xml
16. ❌ **Don't delete factories** - Keep for future testing
17. ❌ **Don't truncate infrastructure tables** - users (structure), roles, permissions, migrations, etc.

### Create After Cleanup

18. 🆕 **Create REAL admin user** (not @example.com)
19. 🆕 **Create real branches** (if current ones are test)
20. 🆕 **Seed production menu** (with 13% tax)
21. 🆕 **Update .env** with real SMTP, payment gateway credentials

---

## ROLLBACK PLAN (If Something Goes Wrong)

### If Database Corruption

```bash
# 1. Drop and recreate database
mysql -u root -p <<EOF
DROP DATABASE my_momo_shop;
CREATE DATABASE my_momo_shop;
EOF

# 2. Restore from backup
mysql -u root -p my_momo_shop < backup-YYYYMMDD-HHMMSS-full.sql

# 3. Verify
php artisan migrate:status
```

### If File Deletions Were Wrong

```bash
# 1. Find the checkpoint commit/tag
git log --oneline | grep -i checkpoint
git tag -l "cleanup-*"

# 2. Reset to checkpoint
git reset --hard cleanup-checkpoint-YYYYMMDD

# 3. Verify
git status
ls database/seeders/
```

### If Storage Deleted

```bash
# Restore from tarball
tar -xzf storage-public-backup-YYYYMMDD-HHMMSS.tar.gz

# Recreate symlink
php artisan storage:link
```

---

## ESTIMATED IMPACT

### Files Affected

| Category | Delete | Modify | Keep | Total |
|----------|--------|--------|------|-------|
| Seeders | 4 | 3 | 24 | 31 |
| Factories | 0-4 | 0 | 0-4 | 4 |
| Tests | 0-11 | 0 | 0-11 | 11 |
| Migrations | 0 | 0 | 199 | 199 |
| Frontend Files | 0 | 7 | Rest | ~250 |

### Database Records Affected

| Table Category | Tables | Est. Rows Deleted | Action |
|----------------|--------|-------------------|--------|
| Test Users | 1 | ~30 users | DELETE (@example.com) |
| Test Orders | 2 | ~70 orders + ~200 items | TRUNCATE |
| Test Transactions | 5 | ~500 transactions | TRUNCATE |
| Logs | 3 | ~1000+ log entries | TRUNCATE |
| Cache | 2 | Variable | TRUNCATE |
| Business Data | 40+ | Depends on usage | TRUNCATE/REVIEW |

### Disk Space Impact

| Location | Current | After Cleanup | Savings |
|----------|---------|---------------|---------|
| Database | ~50-100 MB | ~5-10 MB | ~40-90 MB |
| storage/app/public | ~200 MB | ~200 MB | 0 MB (keep images) |
| Code | ~500 MB | ~499 MB | ~1 MB (seeder files) |

**Note:** Minimal disk savings - cleanup is for **data hygiene**, not space.

---

✅ **Cleanup audit complete (no changes made). Ready for approval.**

**Next Steps:**
1. Review this document thoroughly
2. Identify any additional real data to preserve
3. Execute backups (Step A)
4. Get approval for specific cleanup phases
5. Execute cleanup in phases (can stop at any point)
6. Test after each phase
7. Commit when satisfied


