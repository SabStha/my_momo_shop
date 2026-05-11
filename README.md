# Amako Momo Shop — Team Onboarding Guide

> Read this before touching any code. It answers the questions you will have on day one.

---

## What Is This?

A full food-ordering platform for a **physical momo restaurant with multiple branches**. It is not a simple menu site — it has grown into a multi-role system covering everything from customer ordering to investor reporting to AI-driven marketing.

There are two apps in this repo:

| App | Folder | Who uses it |
|-----|--------|-------------|
| Web (Laravel) | `Web applicaiton/` ← note typo, **do not rename** | Customers (web), admin, POS staff, employees, investors, creators |
| Mobile (React Native) | `amako-shop/` | Customers only |

Both apps share the **same Laravel backend and the same database**.

---

## The Six User Types

This is the most important thing to understand before reading any code. Every feature belongs to one of these six roles. When you find a controller or route you don't recognise, ask: *which user type is this for?*

### 1. Customer
Browses the menu, adds to cart, pays, tracks delivery, earns credits, claims offers.
- Web: `routes/public.php` (guest) + `routes/user.php` (logged in)
- Mobile: the entire `amako-shop/` app
- Login: `/login` (web) or `POST /api/auth/register` + token (mobile)

### 2. Admin
Full control of everything. Products, orders, branches, employees, analytics, campaigns, inventory.
- Web only: `routes/admin.php` (42KB — the largest file in the project)
- Controllers: `app/Http/Controllers/Admin/` (73 controllers)
- Login: same `/login` as customer — the system checks role after login
- Middleware gate: `['auth', 'admin']` — the `admin` middleware calls `user->isAdmin()` which checks `hasRole('admin')` via Spatie Permission

### 3. Employee / POS Staff
Takes dine-in and takeaway orders on the POS terminal. Clocks in/out. Scans customer QR codes to top up credits.
- Web: `routes/pos.php`
- Login: `/pos/login` (separate login form, separate session)
- Middleware gate: `pos.access`

### 4. Delivery Driver
Accepts delivery orders assigned to them, updates GPS location, marks as delivered.
- API only (uses the mobile/web API): `routes/api.php` driver endpoints
- Auth: Sanctum token (same as mobile customer)

### 5. Creator
A referral/social program. Creators get a unique referral code and earn points when they bring in customers.
- Web portal: `routes/creator.php`
- Registration: `/creator/register` (separate from customer register)
- Middleware gate: `creator` or `is_creator`

### 6. Investor
Views their investment history, payouts, and financial reports via a self-service portal.
- Web portal: `routes/investor.php`
- Login: same `/login` — role checked after
- Admin also manages investors from `routes/admin.php`

---

## Running Locally — Step by Step

### What you need
- PHP 8.3 + Composer
- XAMPP (for MySQL) — database name is `database4`
- Node.js + npm
- Expo Go on your phone (for mobile testing) or an Android/iOS emulator

### Backend setup
```bash
cd "Web applicaiton"

# 1. Install PHP dependencies
composer install

# 2. Copy env and generate app key
cp .env.example .env
php artisan key:generate

# 3. Set your local IP in .env (find it with: ipconfig on Windows)
# APP_URL=http://YOUR_IP:8000
# Also add it to SANCTUM_STATEFUL_DOMAINS

# 4. Create the database in XAMPP phpMyAdmin — name it: database4

# 5. Run migrations
php artisan migrate

# 6. Seed the POS tables (needed for dine-in to work)
php artisan db:seed --class=TableSeeder

# 7. Compile frontend assets
npm install
npm run build

# 8. Start the server
php -S 0.0.0.0:8000 -t public
```

### Create your first admin account
There is no admin seeder. Create one via tinker:
```bash
php artisan tinker

# Inside tinker:
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password123'),
]);
$user->assignRole('admin');
```
Then log in at `http://localhost:8000/login` → you will be redirected to the admin dashboard.

### Mobile app setup
```bash
cd amako-shop
npm install

# Set the API URL to your machine's local IP
# Edit: amako-shop/src/config/api.ts
# Change API_URL to: http://YOUR_IP:8000/api

npx expo start --clear
# Scan QR with Expo Go on your phone
```

---

## Environment Variables — What Each One Does

Copy `.env.example` to `.env`. Here is what actually matters for local dev:

| Variable | Required locally? | What it does |
|----------|------------------|-------------|
| `APP_KEY` | Yes — run `php artisan key:generate` | Encrypts sessions and cookies |
| `APP_URL` | Yes | Must be your machine's local IP (e.g. `http://192.168.0.10:8000`) so mobile can reach the backend |
| `DB_DATABASE` | Yes | Must be `database4` |
| `DB_PASSWORD` | No (XAMPP default is blank) | Leave blank |
| `SANCTUM_STATEFUL_DOMAINS` | Yes | Add your local IP:8000 here or mobile auth will 401 |
| `PUSHER_APP_*` | Optional | Real-time POS updates and notification bell. Get free credentials at pusher.com. Without it, polling fallback still works. |
| `ESEWA_*` | Optional | Ask team lead for sandbox credentials. Without it, eSewa checkout will fail. |
| `OPENAI_API_KEY` | No | AI offer generation and weekly digest. App degrades gracefully without it. |
| `TWILIO_*` | No | SMS notifications. Silent failure if missing. |
| `MAILCHIMP_*` | No | Newsletter sync. Not needed for local dev. |

**Driver settings for local dev (already set in .env.example — don't change):**
```
BROADCAST_DRIVER=log   # No Pusher needed locally
CACHE_DRIVER=file      # No Redis needed
QUEUE_CONNECTION=sync  # Jobs run inline, no worker process needed
SESSION_DRIVER=file
```

---

## How the Code Is Organised

### Web Application (`Web applicaiton/`)

> Note: the folder name has a typo ("applicaiton"). Do not rename it — git history and scripts depend on it.

```
Web applicaiton/
│
├── routes/                          ← All route definitions. One file per user type.
│   ├── web.php                      ← Orchestrator only — just requires the files below. Add routes to sub-files.
│   ├── auth.php                     ← /login  /logout  /register  /password/*  /pos/login  /payment/login  /creator/register
│   ├── public.php                   ← /  /menu  /menu/food|drinks|desserts|combos|featured  /cart  /checkout  /payment  /offers  /search  /products/*
│   ├── user.php                     ← /dashboard  /orders  /profile  /credits  /notifications  /themes  (auth required)
│   ├── admin.php                    ← /admin/*  (42KB — every admin panel page)
│   ├── pos.php                      ← /pos/*  (POS terminal + employee clock-in/out)
│   ├── creator.php                  ← /creator/*  (creator portal + admin creator management)
│   ├── investor.php                 ← /investor/*  (investor self-service + admin investor management)
│   ├── api.php                      ← /api/*  (REST API — mobile app + POS app + webhooks)
│   ├── debug.php                    ← Dev shortcuts. Only loaded when APP_ENV=local. Never expose in production.
│   ├── health.php                   ← GET /health  (only loaded in production)
│   ├── channels.php                 ← Pusher broadcasting channel authorisation
│   └── console.php                  ← Artisan command schedules
│
├── app/
│   │
│   ├── Console/
│   │   ├── Kernel.php               ← Artisan schedule definitions (what runs and when)
│   │   └── Commands/                ← 31 custom Artisan commands
│   │       ├── MakeUserAdmin.php            ← php artisan make:user-admin {email}
│   │       ├── AssignAdminRole.php
│   │       ├── ProcessUserBadges.php        ← Recalculates badge progress for all users
│   │       ├── ProcessCampaignTriggers.php  ← Runs campaign automation
│   │       ├── ProcessAutomatedOfferTriggers.php
│   │       ├── GenerateAIOffers.php         ← Triggers OpenAI offer generation
│   │       ├── SendDailyAIOffers.php
│   │       ├── CheckChurnRisks.php          ← Updates churn predictions
│   │       ├── CheckBadgeExpiry.php
│   │       ├── ManageSeasonalBadges.php
│   │       ├── AwardInitialBadges.php
│   │       ├── AssignMonthlyCreatorRewards.php
│   │       ├── CalculateImpactStats.php
│   │       ├── GenerateBranchUpdates.php
│   │       ├── CleanupDeclinedOrders.php
│   │       ├── ClearStatisticsCache.php
│   │       ├── CreateWalletsForAllUsers.php ← One-time migration helper
│   │       ├── UpdateWalletsToCredits.php   ← One-time migration helper
│   │       ├── SetupMainBranch.php
│   │       ├── ProductionSetup.php
│   │       └── [debug/test commands]        ← CheckDatabaseData, TestBranchSystem, etc.
│   │
│   ├── Events/                      ← 4 events fired by the application
│   │   ├── OrderPlaced.php          ← Fired after order creation → triggers badge + referral listeners
│   │   ├── PaymentCompleted.php
│   │   ├── PaymentMethodSelected.php← Broadcast via Pusher to customer display screen
│   │   └── CartCleared.php
│   │
│   ├── Listeners/                   ← React to events
│   │   ├── HandleBadgeProgression.php  ← Listens to OrderPlaced, awards badges
│   │   └── HandleReferralOrder.php     ← Listens to OrderPlaced, credits referrer
│   │
│   ├── Mail/                        ← 8 Mailable classes (Laravel Mail)
│   │   ├── CampaignEmail.php           ← Marketing campaign emails
│   │   ├── SupplierOrderMail.php        ← Sent to supplier when order is placed
│   │   ├── SupplierOrderNotification.php
│   │   ├── SupplierOrderConfirmationToAdmin.php
│   │   ├── SupplierReceiptConfirmation.php
│   │   ├── NewBranchOrderNotification.php  ← Sent to main branch when sub-branch orders
│   │   ├── BranchOrderFulfilledNotification.php
│   │   └── CashDrawerSessionNotification.php
│   │
│   ├── Observers/
│   │   └── UserObserver.php         ← Watches User model events (create, update)
│   │
│   ├── Policies/
│   │   └── OrderPolicy.php          ← Laravel policy: who can view/update an order
│   │
│   ├── Providers/                   ← 7 service providers
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php  ← Registers OrderPolicy
│   │   ├── EventServiceProvider.php ← Wires Events → Listeners
│   │   ├── BroadcastServiceProvider.php ← Pusher setup
│   │   ├── RouteServiceProvider.php
│   │   ├── OpenAIServiceProvider.php   ← Binds OpenAI client into container
│   │   ├── PaymentServiceProvider.php  ← Binds payment processors into container
│   │   └── FileStorageServiceProvider.php
│   │
│   ├── Traits/
│   │   └── BranchAware.php          ← Trait for models/controllers that need current branch context
│   │
│   ├── Helpers/
│   │   ├── helpers.php              ← Global helper functions (auto-loaded)
│   │   ├── CspHelper.php            ← Content Security Policy nonce helpers
│   │   └── SettingsHelper.php       ← Reads from settings/site_settings tables
│   │
│   ├── Http/
│   │   ├── Kernel.php               ← Middleware stack definitions (see Middleware section)
│   │   │
│   │   ├── Requests/                ← Form request validation classes
│   │   │   ├── CreateOrderRequest.php
│   │   │   └── UpdateOrderStatusRequest.php
│   │   │
│   │   ├── Resources/               ← API resource transformers (JSON response shaping)
│   │   │   ├── OrderResource.php
│   │   │   ├── OrderItemResource.php
│   │   │   ├── PaymentResource.php
│   │   │   ├── TableResource.php
│   │   │   └── MerchandiseResource.php
│   │   │
│   │   ├── Middleware/              ← 26 middleware files
│   │   │   ├── AdminMiddleware.php          ← alias: 'admin'       — checks hasRole('admin')
│   │   │   ├── RequirePosAccess.php         ← alias: 'pos.access'  — checks POS session
│   │   │   ├── RequirePaymentAccess.php     ← alias: 'payment.access'
│   │   │   ├── VerifyPaymentManagerSession.php ← alias: 'payment.manager'
│   │   │   ├── Creator.php                  ← alias: 'creator'
│   │   │   ├── IsCreator.php                ← alias: 'is_creator'
│   │   │   ├── IsAdmin.php                  ← alias: 'is_admin'
│   │   │   ├── WalletAuthMiddleware.php      ← alias: 'wallet.auth'
│   │   │   ├── BranchContext.php            ← runs on every web request — sets branch in session
│   │   │   ├── SyncCartFromDatabase.php     ← runs on every web request — loads cart from DB
│   │   │   ├── RefreshApiToken.php          ← runs on every web request — refreshes Sanctum token
│   │   │   ├── RunScheduledTasks.php        ← runs on every web request — fires artisan schedule:run
│   │   │   ├── ApiErrorHandler.php          ← wraps all API responses in consistent error format
│   │   │   ├── AddSecurityHeaders.php       ← X-Frame-Options, X-XSS-Protection, etc.
│   │   │   ├── NoCacheForHtml.php           ← Cache-Control: no-store for HTML pages
│   │   │   ├── AddCacheHeaders.php
│   │   │   └── [standard Laravel middleware] ← Authenticate, VerifyCsrfToken, etc.
│   │   │
│   │   ├── Livewire/                ← 7 Livewire 2.12 components
│   │   │   ├── CartManager.php              ← Navbar cart badge. Listens: 'cartUpdated'
│   │   │   ├── CartView.php                 ← Full cart page (wire:click for all mutations)
│   │   │   ├── NotificationBell.php         ← Navbar notification count
│   │   │   ├── PosTableGrid.php             ← POS dine-in table grid (48KB — complex)
│   │   │   ├── Admin/PaymentManager.php     ← Admin payment processing panel
│   │   │   ├── Customer/CustomerPayment.php ← Customer payment flow
│   │   │   └── Customer/PaymentProcessor.php
│   │   │
│   │   └── Controllers/
│   │       ├── Controller.php               ← Base controller
│   │       │
│   │       ├── Auth/                        ← Standard Laravel auth (7 files)
│   │       │   ├── LoginController.php
│   │       │   ├── RegisterController.php
│   │       │   ├── ForgotPasswordController.php
│   │       │   ├── ResetPasswordController.php
│   │       │   ├── AuthenticatesUsers.php
│   │       │   ├── ConfirmPasswordController.php
│   │       │   └── VerificationController.php
│   │       │
│   │       ├── User/                        ← Authenticated customer pages (4 files)
│   │       │   ├── DashboardController.php
│   │       │   ├── ProfileController.php
│   │       │   ├── AccountController.php
│   │       │   └── OrderController.php
│   │       │
│   │       ├── Employee/                    ← POS staff pages (5 files)
│   │       │   ├── DashboardController.php
│   │       │   ├── TimeLogController.php
│   │       │   ├── SalaryController.php
│   │       │   ├── WalletTopUpController.php  ← Scans customer QR to top up wallet
│   │       │   └── CreditsTopUpController.php ← Scans customer QR to top up credits
│   │       │
│   │       ├── Customer/
│   │       │   └── CustomerPaymentController.php
│   │       │
│   │       ├── Investor/
│   │       │   └── DashboardController.php
│   │       │
│   │       ├── Admin/                       ← 75 admin controllers (one per feature area)
│   │       │   │
│   │       │   ├── [Dashboard]
│   │       │   │   ├── AdminDashboardController.php   ← /admin/dashboard
│   │       │   │   └── DashboardController.php        ← Branch-level dashboard
│   │       │   │
│   │       │   ├── [Products & Categories]
│   │       │   │   ├── AdminProductController.php
│   │       │   │   ├── ProductController.php
│   │       │   │   └── CategoryController.php
│   │       │   │
│   │       │   ├── [Orders]
│   │       │   │   ├── AdminOrderController.php
│   │       │   │   └── OrderController.php
│   │       │   │
│   │       │   ├── [Payments & Cash]
│   │       │   │   ├── AdminPaymentController.php
│   │       │   │   ├── PaymentController.php
│   │       │   │   ├── CashDrawerController.php
│   │       │   │   ├── CashDrawerAlertController.php
│   │       │   │   └── CashDenominationController.php
│   │       │   │
│   │       │   ├── [Branches]
│   │       │   │   ├── BranchController.php
│   │       │   │   └── BranchPasswordController.php
│   │       │   │
│   │       │   ├── [Employees & Time]
│   │       │   │   ├── EmployeeController.php
│   │       │   │   ├── EmployeeScheduleController.php
│   │       │   │   ├── EmployeeTimeLogController.php
│   │       │   │   ├── ClockController.php
│   │       │   │   ├── AdminClockController.php
│   │       │   │   └── SessionController.php
│   │       │   │
│   │       │   ├── [Inventory & Supply Chain]
│   │       │   │   ├── InventoryController.php
│   │       │   │   ├── InventoryCRUDController.php
│   │       │   │   ├── InventoryCategoryController.php
│   │       │   │   ├── InventoryCheckController.php
│   │       │   │   ├── InventoryStockCheckController.php
│   │       │   │   ├── InventoryOperationsController.php
│   │       │   │   ├── InventoryOrderController.php
│   │       │   │   ├── KitchenInventoryOrderController.php
│   │       │   │   ├── MonthlyStockCheckController.php
│   │       │   │   ├── WeeklyStockCheckController.php
│   │       │   │   ├── AuditReportController.php
│   │       │   │   ├── SupplierController.php
│   │       │   │   └── SupplyOrderController.php
│   │       │   │
│   │       │   ├── [Marketing & Offers]
│   │       │   │   ├── CampaignController.php
│   │       │   │   ├── CampaignTriggerController.php
│   │       │   │   ├── CampaignPerformanceController.php
│   │       │   │   ├── OfferController.php
│   │       │   │   ├── AutomatedOfferController.php
│   │       │   │   ├── AIOfferController.php
│   │       │   │   └── ReferralSettingsController.php
│   │       │   │
│   │       │   ├── [Analytics & AI]
│   │       │   │   ├── AdminAnalyticsController.php
│   │       │   │   ├── CustomerAnalyticsController.php
│   │       │   │   ├── CustomerSegmentController.php
│   │       │   │   ├── ChurnPredictionController.php
│   │       │   │   ├── ChurnExportController.php
│   │       │   │   ├── AIAssistantController.php
│   │       │   │   └── WeeklyDigestController.php
│   │       │   │
│   │       │   ├── [Wallet & Credits]
│   │       │   │   ├── WalletController.php
│   │       │   │   └── WalletTopUpController.php
│   │       │   │
│   │       │   ├── [Creators & Investors]
│   │       │   │   ├── AdminCreatorController.php
│   │       │   │   ├── CreatorController.php
│   │       │   │   ├── InvestorController.php
│   │       │   │   ├── InvestorDashboardController.php
│   │       │   │   └── InvestmentAnalyticsController.php
│   │       │   │
│   │       │   ├── [POS & Display]
│   │       │   │   ├── PosAccessLogController.php
│   │       │   │   ├── TableController.php
│   │       │   │   └── DisplayAdController.php
│   │       │   │
│   │       │   ├── [Badges & Roles]
│   │       │   │   ├── UserBadgeController.php
│   │       │   │   ├── RoleController.php
│   │       │   │   ├── AdminRoleController.php
│   │       │   │   └── RuleController.php / RulesController.php
│   │       │   │
│   │       │   ├── [Settings & CMS]
│   │       │   │   ├── AdminSettingsController.php
│   │       │   │   ├── SiteSettingsController.php
│   │       │   │   ├── IntegrationController.php  ← Mailchimp + Twilio sync
│   │       │   │   ├── BulkPackageController.php
│   │       │   │   └── AdminReportController.php
│   │       │   │
│   │       │   └── [Users]
│   │       │       ├── AdminUserController.php
│   │       │       └── MobileNotificationController.php
│   │       │
│   │       └── Api/                         ← 26 API controllers (called by mobile + POS)
│   │           ├── CartController.php           ← GET/POST /api/cart
│   │           ├── CartSyncController.php        ← POST /api/cart/sync  (main sync endpoint)
│   │           ├── ProductController.php         ← GET /api/menu  GET /api/products
│   │           ├── ProductImageController.php
│   │           ├── OrderController.php           ← POST /api/orders  GET /api/orders/{id}
│   │           ├── PaymentController.php         ← POST /api/payments
│   │           ├── OfferController.php           ← GET/POST /api/offers
│   │           ├── LoyaltyController.php         ← Credits, badges, tasks, rewards
│   │           ├── UserController.php            ← GET/PATCH /api/user
│   │           ├── BranchController.php          ← GET /api/branches
│   │           ├── ContentController.php         ← GET /api/content (home page CMS data)
│   │           ├── AnalyticsController.php
│   │           ├── CustomerAnalyticsController.php
│   │           ├── DeviceController.php          ← POST /api/devices (register push token)
│   │           ├── WebhookController.php         ← eSewa + Khalti payment webhooks
│   │           ├── TestNotificationController.php ← Dev-only test endpoints
│   │           ├── ReportController.php
│   │           ├── SalesAnalyticsController.php
│   │           ├── EmployeeAuthController.php    ← POST /api/employee/login
│   │           ├── EmployeeController.php
│   │           └── [POS API] (5 controllers)
│   │               ├── PosAuthController.php     ← POST /api/pos/login
│   │               ├── PosController.php
│   │               ├── PosOrderController.php
│   │               ├── PosPaymentController.php
│   │               ├── PosProductController.php
│   │               └── PosTableController.php
│   │
│   ├── Models/                          ← 87 Eloquent models
│   │   │
│   │   ├── [Core]
│   │   │   ├── User.php                 ← Central model. Has roles, credits, cart, orders, badges, employee, creator, investor
│   │   │   ├── Branch.php
│   │   │   ├── Product.php
│   │   │   ├── Category.php
│   │   │   └── UserCart.php             ← Persistent cart. user_carts table. Single source of truth.
│   │   │
│   │   ├── [Orders & Payments]
│   │   │   ├── Order.php
│   │   │   ├── OrderItem.php
│   │   │   ├── OrderAmendment.php
│   │   │   ├── Payment.php
│   │   │   ├── PaymentMethod.php
│   │   │   └── DeliveryTracking.php
│   │   │
│   │   ├── [Credits & Wallet]
│   │   │   ├── AmaCredit.php            ← ACTIVE credits system
│   │   │   ├── AmaCreditTransaction.php
│   │   │   ├── CreditsAccount.php       ← Legacy (read-only history)
│   │   │   ├── CreditsTransaction.php   ← Legacy (read-only history)
│   │   │   ├── Wallet.php               ← Legacy
│   │   │   └── WalletTransaction.php    ← Legacy
│   │   │
│   │   ├── [Badges & Gamification]
│   │   │   ├── BadgeClass.php  /  BadgeRank.php  /  BadgeTier.php
│   │   │   ├── UserBadge.php
│   │   │   ├── BadgeProgress.php
│   │   │   ├── CreditTask.php
│   │   │   ├── UserTaskCompletion.php
│   │   │   ├── CreditReward.php
│   │   │   ├── UserRewardRedemption.php
│   │   │   └── UserTheme.php            ← Theme unlocked by badge rank
│   │   │
│   │   ├── [Employees & POS]
│   │   │   ├── Employee.php
│   │   │   ├── EmployeeSchedule.php
│   │   │   ├── TimeEntry.php  /  TimeLog.php
│   │   │   ├── Session.php              ← POS work session
│   │   │   ├── Table.php                ← Dine-in tables
│   │   │   ├── PosAccessLog.php
│   │   │   ├── CashDrawer.php  /  CashDrawerSession.php
│   │   │   ├── CashDrawerAdjustment.php  /  CashDrawerAlert.php  /  CashDrawerLog.php
│   │   │   ├── CashDenomination.php  /  CashDenominationChange.php
│   │   │   └── Cashout.php
│   │   │
│   │   ├── [Inventory & Supply]
│   │   │   ├── Inventory.php  /  InventoryItem.php  /  InventoryCategory.php
│   │   │   ├── InventoryOrder.php  /  InventoryOrderItem.php
│   │   │   ├── InventoryTransaction.php  /  InventoryCount.php
│   │   │   ├── InventorySupplier.php  /  BranchInventory.php
│   │   │   ├── Supplier.php
│   │   │   ├── SupplyOrder.php  /  SupplyOrderItem.php
│   │   │   ├── KitchenInventoryOrder.php  /  KitchenInventoryOrderItem.php
│   │   │   ├── WeeklyStockCheck.php  /  MonthlyStockCheck.php  /  DailyStockCheck.php
│   │   │   ├── StockItem.php
│   │   │   └── ForecastFeedback.php
│   │   │
│   │   ├── [Marketing]
│   │   │   ├── Campaign.php  /  CampaignTrigger.php
│   │   │   ├── Offer.php  /  OfferClaim.php  /  OfferAnalytics.php
│   │   │   ├── Coupon.php  /  CouponUsage.php  /  UserCoupon.php
│   │   │   ├── Rule.php
│   │   │   └── AutomatedOfferTrigger.php
│   │   │
│   │   ├── [Analytics]
│   │   │   ├── CustomerSegment.php
│   │   │   ├── ChurnPrediction.php
│   │   │   ├── CustomerFeedback.php
│   │   │   ├── ActivityLog.php
│   │   │   ├── ImpactStat.php
│   │   │   └── RiskAlert.php
│   │   │
│   │   ├── [Creator & Referral]
│   │   │   ├── Creator.php
│   │   │   ├── CreatorEarning.php
│   │   │   ├── CreatorReward.php
│   │   │   ├── Referral.php
│   │   │   ├── FindsCategory.php
│   │   │   └── Payout.php  /  PayoutRequest.php
│   │   │
│   │   ├── [Investor]
│   │   │   ├── Investor.php
│   │   │   ├── InvestorInvestment.php
│   │   │   ├── InvestorPayout.php
│   │   │   ├── InvestorReport.php
│   │   │   ├── InvestorReferral.php
│   │   │   └── InvestmentPageVisit.php
│   │   │
│   │   ├── [CMS & Config]
│   │   │   ├── Setting.php  /  SiteSetting.php  /  SiteContent.php
│   │   │   ├── DisplayAd.php
│   │   │   ├── BulkPackage.php  /  BulkSetting.php
│   │   │   ├── Expense.php
│   │   │   └── Device.php               ← Stores Expo push token per user device
│   │   │
│   │   └── [Other]
│   │       ├── Customer.php             ← Guest customer (no User account)
│   │       ├── Merchandise.php
│   │       ├── ProductRating.php
│   │       ├── UserSettings.php
│   │       ├── UserOfferPreference.php
│   │       ├── Reward.php
│   │       └── Combo.php  /  Drink.php
│   │
│   └── Services/                        ← Business logic layer. Controllers stay thin.
│       │
│       ├── OrderService.php             ← All order creation. Start here for any order bug.
│       ├── CartCalculationService.php   ← Totals, discounts, tax
│       ├── CouponService.php            ← Coupon validation and application
│       ├── TaxDeliveryService.php       ← Delivery fee + tax calculation
│       │
│       ├── ExpoPushService.php          ← Sends push notifications to mobile via Expo
│       ├── MobileNotificationService.php← Creates in-app notification records
│       ├── OrderNotificationService.php ← Orchestrates all order-related notifications
│       │
│       ├── InventoryService.php         ← Stock adjustments, deductions
│       ├── StockCheckService.php        ← Daily/weekly/monthly stock checks
│       │
│       ├── CampaignService.php          ← Campaign execution
│       ├── CampaignTriggerService.php   ← Automated campaign triggers
│       ├── AutomatedOfferTriggerService.php
│       ├── OfferAnalyticsService.php
│       │
│       ├── ChurnPredictionService.php   ← Calculates churn risk scores
│       ├── ChurnRiskNotificationService.php
│       ├── CustomerAnalyticsService.php ← Customer segmentation
│       ├── CustomerBehaviorService.php
│       ├── CustomerJourneyService.php
│       ├── CustomerLifetimeValueService.php
│       ├── SalesAnalyticsService.php
│       ├── StatisticsService.php
│       │
│       ├── AIOfferService.php           ← OpenAI offer generation (degrades without key)
│       ├── AIPopupService.php           ← OpenAI popup decisions (degrades without key)
│       ├── AIForecastService.php
│       ├── AIGiftService.php
│       ├── OpenAIService.php            ← Base OpenAI client wrapper
│       ├── WeeklyDigestService.php      ← OpenAI weekly report (degrades without key)
│       │
│       ├── BadgeProgressionService.php  ← Awards badges after orders
│       ├── SeasonalBadgeService.php
│       ├── DynamicRewardService.php
│       ├── UserBehaviorAnalyzer.php
│       ├── SmartTimingEngine.php
│       ├── ABTestingService.php
│       │
│       ├── ReferralService.php          ← Creator referral point tracking
│       ├── CreatorPointsService.php
│       │
│       ├── CashDrawerService.php
│       ├── CashDrawerAlertService.php
│       ├── PrinterService.php           ← ESC/POS kitchen printer
│       ├── QRCodeService.php
│       ├── ActivityLogService.php
│       ├── ImpactTrackingService.php
│       │
│       └── Payment/                     ← Payment processing pipeline
│           ├── PaymentService.php       ← Entry point. Picks the right processor.
│           ├── AbstractPaymentProcessor.php
│           ├── Contracts/PaymentProcessorInterface.php
│           ├── PaymentValidator.php
│           ├── PaymentResponse.php
│           ├── PaymentReceiptGenerator.php
│           ├── ESewaPaymentProcessor.php     ← Active
│           ├── CashPaymentProcessor.php      ← Active
│           ├── WalletPaymentProcessor.php    ← Active (also in Processors/ subfolder)
│           ├── CardPaymentProcessor.php      ← Active (also in Processors/ subfolder)
│           ├── KhaltiPaymentProcessor.php    ← Code exists, gateway disabled
│           └── Processors/
│               ├── WalletPaymentProcessor.php
│               ├── CardPaymentProcessor.php
│               └── KhaltiQRPaymentProcessor.php
│
├── resources/
│   ├── js/
│   │   └── cart.js                      ← Compiled by Vite. Handles web cart. Calls /api/cart/sync.
│   │
│   └── views/                           ← All Blade templates
│       │
│       ├── layouts/                     ← Master layout files (every page extends one of these)
│       │   ├── app.blade.php            ← Main customer layout (topnav + bottomnav)
│       │   ├── admin.blade.php          ← Admin panel layout (sidebar)
│       │   ├── admin-sidebar.blade.php  ← Admin sidebar partial
│       │   ├── auth.blade.php           ← Login/register layout (no nav)
│       │   ├── pos.blade.php            ← POS terminal layout
│       │   ├── payment.blade.php        ← Payment manager layout
│       │   ├── investor.blade.php       ← Investor portal layout
│       │   └── user.blade.php           ← Authenticated customer layout
│       │
│       ├── components/                  ← Reusable Blade components (@component / <x-...>)
│       │   ├── branch-switcher.blade.php
│       │   ├── cart-modal.blade.php
│       │   ├── product-modal.blade.php
│       │   ├── notification-card.blade.php
│       │   ├── momo-card.blade.php
│       │   ├── optimized-image.blade.php
│       │   ├── dropdown.blade.php
│       │   ├── modal/confirm.blade.php
│       │   ├── modal/detail.blade.php
│       │   ├── badge/status.blade.php
│       │   ├── payment/method-selector.blade.php
│       │   ├── payment/amount-input.blade.php
│       │   ├── payment/card-form.blade.php
│       │   ├── form/toggle.blade.php
│       │   ├── card/ai-fallback.blade.php  ← Shows when AI content unavailable
│       │   └── toast/success.blade.php
│       │
│       ├── livewire/                    ← Blade templates for Livewire components
│       │   ├── cart-manager.blade.php
│       │   ├── cart-view.blade.php
│       │   ├── notification-bell.blade.php
│       │   └── pos-table-grid.blade.php
│       │
│       ├── partials/                    ← Included with @include
│       │   ├── topnav.blade.php
│       │   ├── bottomnav.blade.php
│       │   ├── menu-card.blade.php
│       │   ├── payment-modals.blade.php
│       │   └── payment-modals-fixed.blade.php
│       │
│       ├── auth/                        ← Login, register, password reset pages
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── pos-login.blade.php      ← Separate POS login form
│       │   ├── payment-login.blade.php  ← Separate payment manager login
│       │   └── passwords/              (email, reset, confirm)
│       │
│       ├── home/                        ← Home page (composed of sections)
│       │   ├── sections/
│       │   │   ├── hero.blade.php
│       │   │   ├── featured-products.blade.php
│       │   │   ├── quick-categories.blade.php
│       │   │   ├── special-combos.blade.php
│       │   │   ├── limited-offers.blade.php
│       │   │   ├── customer-reviews.blade.php
│       │   │   ├── how-it-works.blade.php
│       │   │   ├── shop-info.blade.php
│       │   │   ├── trust-stats.blade.php
│       │   │   └── why-choose-us.blade.php
│       │   └── components/
│       │       ├── cart-toast.blade.php
│       │       └── quick-order-modal.blade.php
│       │
│       ├── menu/                        ← Menu category pages
│       │   ├── food.blade.php  /  drinks.blade.php  /  desserts.blade.php
│       │   ├── combos.blade.php  /  featured.blade.php  /  momo.blade.php
│       │
│       ├── cart/
│       │   └── index.blade.php          ← Cart page (uses CartView Livewire component)
│       │
│       ├── checkout.blade.php
│       │
│       ├── payment/                     ← Payment confirmation pages
│       │   ├── success.blade.php
│       │   ├── esewa/success.blade.php  /  esewa/failure.blade.php
│       │   └── confirmations/           (card, esewa, fonepay, khalti)
│       │
│       ├── orders/
│       │   ├── index.blade.php  /  history.blade.php  /  show.blade.php
│       │   ├── receipt.blade.php  /  success.blade.php
│       │   └── pos.blade.php
│       │
│       ├── user/                        ← Customer account pages
│       │   ├── profile.blade.php        ← Profile hub (links to sub-sections)
│       │   ├── profile/
│       │   │   ├── edit.blade.php
│       │   │   ├── badges.blade.php
│       │   │   └── partials/            (account, address-book, credits, order-history, referrals, security, themes, wallet)
│       │   ├── credits/index.blade.php  /  credits/transactions.blade.php
│       │   ├── wallet/transactions.blade.php
│       │   └── themes/index.blade.php
│       │
│       ├── admin/                       ← All admin panel pages (75+ blade files)
│       │   ├── dashboard.blade.php
│       │   ├── products/               (index, create, edit)
│       │   ├── orders/                 (index, show, kitchen-print)
│       │   ├── branches/               (index, show, passwords, switch-modal)
│       │   ├── employees/              (index, show, create, edit, schedule_index)
│       │   ├── payments/               (index, show, sessions + partials/)
│       │   ├── inventory/              (dashboard, index, create, edit, show, daily-check, stock-check, bulk-order + audit-reports/ + categories/ + checks/ + orders/ + weekly-checks/ + monthly-checks/)
│       │   ├── suppliers/              (index, show, create, edit)
│       │   ├── supply/orders/          (index, list, show, create)
│       │   ├── campaigns/              (index, create, edit, performance + triggers/)
│       │   ├── offers/                 (index, create, edit)
│       │   ├── churn/                  (index, show)
│       │   ├── customer-analytics/
│       │   ├── customer-segments/
│       │   ├── cash-denominations/     (index, history)
│       │   ├── creators/               (index, show, create, edit)
│       │   ├── investors/              (index, show, create, edit)
│       │   ├── badges/                 (index, show)
│       │   ├── bulk-packages/          (index, show, create, edit)
│       │   ├── display-ads/            (index, create, edit)
│       │   ├── tables/                 (index, create, edit)
│       │   ├── wallet/                 (index, qr-generator, scan, topup, transactions)
│       │   ├── rules/                  (index, create, edit)
│       │   ├── roles/
│       │   ├── clock/                  (index, report)
│       │   ├── sessions/               (index, show)
│       │   ├── activity-logs/          (index, show)
│       │   ├── ai-offers/
│       │   ├── ai-popup/
│       │   ├── weekly-digest/
│       │   ├── sales/overview.blade.php
│       │   ├── settings/
│       │   ├── site-settings/
│       │   └── referral-settings/
│       │
│       ├── accounting/                  ← Investor accounting pages
│       │   ├── dashboard.blade.php
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── spreadsheet.blade.php
│       │
│       ├── investor/                    ← Investor self-service portal
│       │   ├── dashboard.blade.php
│       │   ├── investments.blade.php
│       │   ├── payouts.blade.php
│       │   ├── reports.blade.php
│       │   ├── statement.blade.php
│       │   └── profile.blade.php
│       │
│       ├── creator/
│       │   ├── dashboard.blade.php
│       │   └── register.blade.php
│       │
│       ├── employee/                    ← POS staff utility pages
│       │   ├── wallet/scanner.blade.php     ← QR scanner to top up wallet
│       │   └── credits/scanner.blade.php    ← QR scanner to top up credits
│       │
│       ├── delivery/
│       │   └── dashboard.blade.php
│       │
│       ├── customer/
│       │   ├── payment/index.blade.php
│       │   └── payment-viewer.blade.php   ← Fullscreen customer display at POS
│       │
│       ├── finds/                         ← Creator content feed pages
│       │   ├── index.blade.php
│       │   ├── accessories.blade.php
│       │   ├── limited.blade.php
│       │   ├── toys.blade.php
│       │   └── tshirts.blade.php
│       │
│       ├── bulk/
│       │   ├── index.blade.php
│       │   └── custom-builder.blade.php
│       │
│       ├── pages/                         ← Static informational pages
│       │   ├── about.blade.php  /  contact.blade.php  /  help.blade.php
│       │   ├── terms.blade.php  /  privacy.blade.php
│       │   ├── roadmap.blade.php  /  new-user-guide.blade.php
│       │   └── amasfinds.blade.php
│       │
│       ├── emails/                        ← Email templates (rendered by Laravel Mail)
│       │   ├── campaign.blade.php
│       │   ├── supplier/order.blade.php  /  order-notification.blade.php  /  receipt-confirmation.blade.php
│       │   ├── admin/supplier-confirmation.blade.php
│       │   ├── branch/order-fulfilled.blade.php
│       │   ├── main_branch/new-branch-order.blade.php
│       │   └── cash_drawer/session_notification.blade.php
│       │
│       ├── pdf/                           ← PDF templates (rendered by dompdf)
│       │   ├── payment-receipt.blade.php
│       │   └── supply_order.blade.php
│       │
│       ├── receipts/
│       │   └── kitchen.blade.php          ← ESC/POS kitchen printer receipt
│       │
│       └── supplier/                      ← Pages for supplier portal (tokenised links in emails)
│           ├── order-details.blade.php
│           ├── order-confirmed.blade.php
│           ├── error.blade.php
│           └── invalid-token.blade.php
│
├── public/js/                             ← Compiled/static JS assets (loaded via asset())
│   ├── pos.js                             ← POS terminal (79KB). Do not edit without reading everything.
│   ├── home.js                            ← Home page interactions
│   ├── notification-sync.js               ← Real-time notification polling
│   ├── interactive-tour.js                ← New-user onboarding tour
│   ├── menu-animations.js
│   ├── payment-manager.js                 ← Legacy monolith (kept for reference — now split below)
│   ├── elephant.js                        ← Utility helpers
│   ├── js.js                              ← General utilities
│   └── payments/                          ← 6 payment panel modules (loaded by payment manager page)
│       ├── order-list.js                  ← Order polling and list rendering
│       ├── payment-panel.js               ← Payment method selection and processing
│       ├── cash-drawer.js                 ← Cash drawer hardware + balance tracking
│       ├── order-actions.js               ← Accept / ready / complete workflow buttons
│       ├── sounds.js                      ← POS notification sounds (Web Audio API)
│       └── notifications.js              ← In-page notification handling
│
└── database/
    ├── migrations/                        ← 192 migration files (chronological DB schema history)
    └── seeders/
        └── TableSeeder.php               ← Seeds dine-in tables for all branches (run this after migrate)
```

---

### Mobile App (`Web applicaiton/amako-shop/`)

> The mobile app lives inside the Laravel folder, not at the repo root.

```
amako-shop/
│
├── app/                                   ← Expo Router screens (file path = URL route)
│   ├── _layout.tsx                        ← Root layout: wraps app in SessionProvider + NotificationsProvider
│   ├── index.tsx                          ← Entry: redirects to (tabs)/home or (auth)/login
│   ├── branch-selection.tsx               ← Branch picker shown before home if no branch selected
│   ├── cart.tsx                           ← Cart screen
│   ├── checkout.tsx                       ← Checkout flow (branch + address + payment method)
│   ├── payment.tsx                        ← Payment processing screen
│   ├── payment-success.tsx                ← Order placed confirmation
│   ├── orders.tsx                         ← Order history list
│   ├── notifications.tsx                  ← Notification centre
│   ├── offers.tsx                         ← Offers list
│   ├── debug.tsx                          ← Dev-only debug screen
│   │
│   ├── (auth)/                            ← Auth flow (no bottom tab bar)
│   │   ├── _layout.tsx
│   │   ├── login.tsx
│   │   └── register.tsx
│   │
│   ├── (tabs)/                            ← Bottom tab bar screens
│   │   ├── _layout.tsx                    ← Defines the 6 tabs and their icons
│   │   ├── index.tsx                      ← Redirects to home tab
│   │   ├── home.tsx                       ← Home tab: hero carousel, featured products, stats, reviews
│   │   ├── menu.tsx                       ← Menu tab: browse all products with category filter
│   │   ├── bulk.tsx                       ← Bulk orders tab
│   │   ├── finds.tsx                      ← Finds tab: creator content feed + leaderboard
│   │   ├── help.tsx                       ← Help tab: FAQ + contact
│   │   ├── profile.tsx                    ← Profile tab: user info, credits, QR, settings
│   │   └── notifications.tsx
│   │
│   ├── item/[id].tsx                      ← Product detail screen
│   ├── order/[id].tsx                     ← Order detail screen
│   ├── order-tracking/[id].tsx            ← Live delivery tracking with map
│   └── _dev/DesignParity.tsx             ← Design system reference screen (dev only)
│
└── src/
    │
    ├── api/                               ← All HTTP calls to the backend
    │   ├── client.ts                      ← Axios instance with auth headers + base URL
    │   ├── auth.ts                        ← login(), register(), logout()
    │   ├── auth-hooks.ts                  ← React Query hooks wrapping auth.ts
    │   ├── menu.ts                        ← getMenu(), getProduct(), searchProducts()
    │   ├── menu-hooks.ts                  ← useMenu(), useProduct() hooks
    │   ├── orders.ts                      ← createOrder(), getOrders(), getOrder()
    │   ├── offers.ts                      ← getOffers(), claimOffer(), applyOffer()
    │   ├── notifications.ts               ← getNotifications(), markRead()
    │   ├── loyalty.ts                     ← getCredits(), getTasks(), redeemReward()
    │   ├── devices.ts                     ← registerPushToken()
    │   ├── health.ts                      ← GET /api/health (connectivity check)
    │   ├── reviews.ts  /  reviews-hooks.ts
    │   ├── finds-hooks.ts
    │   ├── home-hooks.ts                  ← useFeaturedProducts(), useHomeContent()
    │   ├── bulk-hooks.ts
    │   ├── branch-hooks.ts
    │   ├── user-hooks.ts
    │   ├── product-images.ts
    │   ├── services.ts                    ← Misc API calls
    │   ├── hooks.ts                       ← Shared query hooks
    │   ├── errors.ts                      ← Error type definitions + handlers
    │   ├── types.ts                       ← API response type definitions
    │   └── index.ts
    │
    ├── components/                        ← Shared UI components
    │   ├── home/
    │   │   ├── HeroCarousel.tsx           ← Auto-scrolling featured product carousel
    │   │   ├── ProductGrid.tsx
    │   │   ├── ProductCard.tsx
    │   │   ├── ProductInfoModal.tsx
    │   │   ├── ReviewsSection.tsx
    │   │   ├── DetailedStats.tsx
    │   │   ├── KpiRow.tsx  /  KpiCard.tsx
    │   │   ├── SectionHeader.tsx
    │   │   ├── BenefitsGrid.tsx
    │   │   └── VisitUs.tsx
    │   ├── cart/CartAddedSheet.tsx        ← Bottom sheet shown after adding to cart
    │   ├── bulk/CustomBuilderModal.tsx
    │   ├── modals/
    │   │   ├── OrderDeliveredModal.tsx
    │   │   └── OrderSuccessModal.tsx
    │   ├── navigation/
    │   │   ├── TopBar.tsx
    │   │   ├── BottomBar.tsx
    │   │   └── StandaloneBottomBar.tsx
    │   ├── notifications/
    │   │   ├── NotificationCard.tsx
    │   │   └── NotificationDebug.tsx
    │   ├── product/FoodInfoSheet.tsx      ← Product detail bottom sheet
    │   ├── reviews/WriteReviewModal.tsx
    │   ├── tracking/
    │   │   ├── LiveTrackingMap.tsx        ← Map showing driver location
    │   │   ├── DriverApp.tsx
    │   │   └── DriverLocationTracker.tsx
    │   ├── CartItem.tsx
    │   ├── CategoryFilter.tsx
    │   ├── ItemCard.tsx
    │   ├── FeaturedCarousel.tsx
    │   ├── SearchInput.tsx
    │   ├── SkeletonCard.tsx               ← Loading placeholder
    │   ├── ErrorBoundary.tsx
    │   ├── ErrorState.tsx
    │   ├── NetworkDetector.tsx            ← Shows offline banner
    │   ├── LoadingSpinner.tsx
    │   ├── OptimizedImage.tsx
    │   ├── PreloadedImage.tsx
    │   ├── ImagePreloadIndicator.tsx
    │   ├── OffersBanner.tsx
    │   ├── OfferSuccessModal.tsx
    │   ├── OrderDeliveredHandler.tsx
    │   ├── SplashScreen.tsx
    │   ├── Screen.tsx                     ← Base screen wrapper with safe area
    │   ├── ScreenWithBottomNav.tsx
    │   ├── StatsRow.tsx
    │   └── QueryProvider.tsx              ← React Query provider (wraps entire app)
    │
    ├── config/
    │   ├── api.ts                         ← ★ CHANGE THIS to switch local ↔ production API URL
    │   ├── constants.ts
    │   ├── environment.ts
    │   ├── network.ts                     ← Network timeout and retry config
    │   └── network-fixed.ts
    │
    ├── hooks/                             ← Custom React hooks
    │   ├── useCartSheet.ts                ← Controls CartAddedSheet visibility
    │   ├── useCheckoutCalculations.ts     ← Totals, delivery fee, discount in checkout
    │   ├── useCheckoutForm.ts             ← Checkout form state management
    │   ├── useCheckoutRedirect.ts         ← Handles redirect after payment
    │   ├── useDeliveryLocation.ts         ← GPS location for delivery orders
    │   ├── useImagePreloader.ts
    │   ├── useNotifications.ts
    │   ├── useOrderDeliveredNotification.ts
    │   ├── useOrders.ts
    │   ├── useProductImages.ts
    │   └── useSiteContent.ts              ← Loads home page CMS content
    │
    ├── notifications/
    │   ├── NotificationsProvider.tsx      ← Registers Expo push token on mount
    │   ├── delivery-notifications.ts      ← Handles delivery status push notifications
    │   └── index.ts
    │
    ├── services/
    │   ├── DeliveryNotificationService.ts
    │   ├── ImagePreloader.ts
    │   ├── NativeNotificationService.ts   ← Local (non-push) notifications
    │   ├── OrderNotificationHandler.ts
    │   └── test-notifications.ts          ← Dev helpers to test push notifications
    │
    ├── session/
    │   ├── SessionProvider.tsx            ← Reads stored token on app launch, sets auth state
    │   ├── RouteGuard.tsx                 ← Redirects unauthenticated users to /login
    │   └── token.ts                       ← Read/write Sanctum token via Expo SecureStore
    │
    ├── state/                             ← Global state (Zustand stores)
    │   ├── cart.ts                        ← Cart items array
    │   ├── cart-sync.ts                   ← ★ Cart sync logic. syncWithServer() + loadFromServer(). Start here for cart bugs.
    │   ├── notifications.ts               ← Notification list state
    │   ├── orders.ts                      ← Orders list state
    │   └── index.ts
    │
    ├── theme/
    │   ├── index.ts                       ← Colors, typography, spacing, shadows
    │   └── infoSheet.ts                   ← Bottom sheet theme tokens
    │
    ├── ui/                                ← Base UI primitives (used everywhere)
    │   ├── Button.tsx
    │   ├── Card.tsx
    │   ├── Chip.tsx
    │   ├── Price.tsx                      ← Formats Nepali rupee amounts
    │   ├── QuantityStepper.tsx
    │   ├── TextInput.tsx
    │   └── tokens.ts                      ← Design tokens (spacing, radius, etc.)
    │
    └── utils/
        ├── connectionDoctor.ts            ← Diagnoses connectivity issues
        ├── networkDetector.ts             ← Online/offline detection
        ├── urlHelper.ts                   ← Converts local image paths to correct API base URL
        ├── price.ts                       ← Number → "Rs X,XXX" formatting
        ├── search.ts                      ← Client-side product search helpers
        └── events.ts                      ← App-wide event bus
```

---

## Middleware — What Guards What

When a request comes in, these are the gates it passes through. If you get a 403 or redirect to login unexpectedly, check the middleware on that route.

| Middleware alias | File | What it checks |
|-----------------|------|---------------|
| `auth` | `Authenticate.php` | Is user logged in via session? |
| `admin` | `AdminMiddleware.php` | Does user have `admin` Spatie role? |
| `pos.access` | `RequirePosAccess.php` | Has user authenticated via the POS login? |
| `payment.access` | `RequirePaymentAccess.php` | Has user authenticated via the payment manager login? |
| `payment.manager` | `VerifyPaymentManagerSession.php` | Is payment manager session valid? |
| `creator` | `Creator.php` | Does user have creator role? |
| `is_creator` | `IsCreator.php` | Same (used in different route groups) |
| `wallet.auth` | `WalletAuthMiddleware.php` | Is wallet session authenticated? |
| `throttle:30,1` | Laravel built-in | 30 requests per minute (API public routes) |

**Middleware that runs on EVERY web request (you should know these exist):**
- `BranchContext` — Reads `session('selected_branch_id')` and shares the current branch with all views
- `RefreshApiToken` — Auto-refreshes the Sanctum token stored in session (keeps mobile-style auth alive)
- `RunScheduledTasks` — Runs `php artisan schedule:run` inline on every request (no cron job needed locally)
- `SyncCartFromDatabase` — On every web page load, syncs cart from `user_carts` DB into the session

---

## The Roles System

Uses **Spatie Laravel Permission** (`spatie/laravel-permission`). Roles are stored in the `roles` table and assigned via `model_has_roles`.

**Known roles in the system:**

| Role | Who has it | What it unlocks |
|------|-----------|----------------|
| `admin` | Restaurant owner / managers | Full admin panel (`/admin/*`) |
| `user` | Regular customers | Assigned automatically on registration |
| `employee` | POS staff | POS terminal |
| `employee.manager` | Senior POS staff | Manager features in POS |
| `employee.cashier` | Cashier role | Cash drawer access |
| `creator` | Referral program members | Creator portal (`/creator/*`) |
| `investor` | Financial investors | Investor portal (`/investor/*`) |

**Role check methods on `User` model:**
```php
$user->hasRole('admin')         // checks Spatie roles table
$user->isAdmin()                // shortcut — same as hasRole('admin')
$user->isEmployee()             // shortcut — hasRole('employee')
$user->hasBranchAccess($id)     // admin = all branches; employee = their branch only
```

**How to assign a role:**
```bash
php artisan tinker
User::find(1)->assignRole('admin');
```

---

## Authentication — Three Separate Login Systems

This is confusing until you know it. There are **three separate login flows**:

| Login URL | Session/guard | Who uses it | Middleware protecting those routes |
|-----------|--------------|-------------|-----------------------------------|
| `/login` | Default web session | Customers AND admins (same login, role checked after) | `auth` |
| `/pos/login` | POS session (`pos.access`) | POS terminal staff | `pos.access` |
| `/payment/login` | Payment session | Payment manager panel | `payment.access` or `payment.manager` |

**Mobile login:**
- `POST /api/auth/register` — register + get Sanctum token
- `POST /mobile-api/auth/login` — login + get Sanctum token (web route, not api route — legacy)
- Token is sent as `Authorization: Bearer <token>` header on all subsequent mobile requests
- Tokens expire after 24 hours and are refreshed via `POST /api/auth/refresh`

---

## Key URL Map (Where Things Live)

You can open these in a browser after starting the server:

| URL | What you see |
|-----|-------------|
| `/` | Customer home page |
| `/menu` | Full menu |
| `/cart` | Shopping cart |
| `/checkout` | Checkout flow |
| `/login` | Customer/admin login |
| `/register` | Customer registration |
| `/creator/register` | Creator registration |
| `/dashboard` | Customer dashboard (auth required) |
| `/orders` | Customer order history (auth required) |
| `/admin/dashboard` | Admin panel (admin role required) |
| `/admin/products` | Manage products |
| `/admin/orders` | Manage orders |
| `/admin/analytics/customers` | Customer analytics + churn |
| `/admin/inventory` | Inventory tracking |
| `/admin/campaigns` | Marketing campaigns |
| `/pos/login` | POS terminal login |
| `/pos` | POS terminal (after login) |
| `/customer/payment-viewer` | Customer-facing display screen (fullscreen, no auth) |
| `/payment/login` | Payment manager login |
| `/investor/dashboard` | Investor portal |
| `/creator/dashboard` | Creator portal |

---

## Database — Core Tables and Their Relationships

### The most important thing to understand: `user_carts`
This is the **single source of truth for cart data** for both web and mobile. Do not use `localStorage` or sessions as cart source of truth.

```
users
  ├── has one  → user_carts        (persistent cart)
  ├── has one  → credits_accounts  (Amako Credits balance)
  ├── has one  → ama_credits       (legacy credit system — kept for history)
  ├── has one  → employee          (if this user is staff)
  ├── has one  → creator           (if this user is a creator)
  ├── has one  → investor          (if this user is an investor)
  ├── has many → orders
  ├── has many → payments
  ├── has many → offer_claims
  ├── has many → user_badges       (loyalty badge progress)
  └── belongs to many → roles      (via Spatie model_has_roles)

orders
  ├── belongs to → users
  ├── belongs to → branches
  ├── has many  → order_items
  ├── has one   → payments
  └── has one   → deliveries       (if delivery order)

products
  ├── belongs to → categories
  └── has many  → order_items

branches
  ├── has many → tables            (dine-in tables)
  ├── has many → employees
  ├── has many → inventory_items
  └── has many → cash_drawers
```

### Credits — Two Systems (Read This Carefully)
There are **two credits systems** in the codebase. This caused confusion during development:

| System | Models | Tables | Status |
|--------|--------|--------|--------|
| **New (active)** | `AmaCredit`, `AmaCreditTransaction` | `ama_credits`, `ama_credit_transactions` | This is the one the code uses |
| **Legacy** | `CreditsAccount`, `CreditsTransaction` | `credits_accounts`, `credits_transactions` | Kept for historical data, not actively written to |

Use `User->amaCredit` and `User->addAmaCredits()` / `User->spendAmaCredits()` when writing new features.

### Badge + Theme System
Badges are earned through loyalty and unlock UI themes. The hierarchy:
```
badge_classes  (e.g. "Momo Loyalty", "Momo Engagement")
  └── badge_ranks  (bronze → silver → gold → elite)
        └── badge_tiers  (level 1, 2, 3 within each rank)

user_badges  — which badge a user has currently earned
badge_progress — how far they are toward the next badge

user_themes — which themes are unlocked/active for a user
  (bronze badge → bronze theme, gold badge → gold + silver + bronze themes, etc.)
```

### POS Order Numbers
| Format | Type |
|--------|------|
| `D-0001` | Dine-in |
| `T-0001` | Takeaway |
| `O-0001` | Online |

Web orders use: `ORD-YYYYMMDD-XXXXX` (generated by `OrderService::generateOrderNumber()`)

---

## Livewire Components (Real-Time UI)

**Livewire version: 2.12. Do not upgrade. Do not use v3 syntax.**

| Component | Template | What it does |
|-----------|----------|-------------|
| `CartManager` | `livewire/cart-manager.blade.php` | Navbar cart badge — listens for `cartUpdated` event and re-renders |
| `CartView` | `livewire/cart-view.blade.php` | The full cart page — wire:click for all mutations (add/remove/update/clear) |
| `NotificationBell` | `livewire/notification-bell.blade.php` | Navbar notification count |
| `PosTableGrid` | `livewire/pos-table-grid.blade.php` | POS dine-in table grid (48KB — complex, be careful) |

**v2 syntax cheatsheet:**
```php
// In component PHP file
$this->emit('eventName', $payload);           // tell other Livewire components
$this->dispatchBrowserEvent('name', $data);   // tell JavaScript
protected $listeners = ['eventName' => 'myMethod'];  // listen for events

// In JavaScript
window.livewire.emit('eventName', payload);   // tell Livewire from JS
window.addEventListener('name', e => {});     // listen for browser events from Livewire
```

---

## Services — Business Logic Lives Here

Controllers should be thin. Business logic goes in services. Call them from controllers like:
```php
public function __construct(private OrderService $orders) {}
public function store() { $this->orders->createOrder($data); }
```

The most important services for new features:

| Service | What to use it for |
|---------|-------------------|
| `OrderService` | Creating any order. Start here for all order-related bugs. |
| `PaymentService` | Processing payments. Delegates to the right gateway processor. |
| `CartCalculationService` | Totals, discounts, tax calculation. |
| `ExpoPushService` | Sending push notifications to mobile. |
| `MobileNotificationService` | Creating in-app notifications. |
| `InventoryService` | Stock adjustments, deductions on order. |
| `CampaignService` | Executing marketing campaigns. |
| `ChurnPredictionService` | Calculating which customers are at risk. |
| `AIOfferService` | Generating offers via OpenAI. Wraps in try/catch — returns null if OpenAI is down. |
| `ReferralService` | Tracking creator referral points. |

Payment processors live in `app/Services/Payment/`:
```
PaymentService.php          ← Entry point. Decides which processor to use.
ESewaPaymentProcessor.php   ← eSewa (active)
KhaltiPaymentProcessor.php  ← Khalti (code exists, disabled)
WalletPaymentProcessor.php  ← Amako Credits / Wallet
CashPaymentProcessor.php    ← Cash on delivery / POS cash
CardPaymentProcessor.php    ← Card payments
```

---

## Mobile App Structure (`amako-shop/`)

```
amako-shop/
├── app/                     ← Screens (Expo Router — file = route)
│   ├── (auth)/
│   │   ├── login.tsx        → /login
│   │   └── register.tsx     → /register
│   ├── (tabs)/              ← Bottom tab bar screens
│   │   ├── home.tsx         → Home tab
│   │   ├── menu.tsx         → Menu tab
│   │   ├── bulk.tsx         → Bulk orders tab
│   │   ├── finds.tsx        → Finds/creator feed tab
│   │   ├── help.tsx         → Help tab
│   │   ├── profile.tsx      → Profile tab
│   │   └── notifications.tsx
│   ├── item/[id].tsx        → Product detail screen
│   ├── cart.tsx             → Cart screen
│   ├── checkout.tsx         → Checkout flow
│   ├── payment.tsx          → Payment screen
│   ├── payment-success.tsx  → Success screen
│   ├── order/[id].tsx       → Order detail
│   └── order-tracking/[id].tsx → Live delivery tracking
│
└── src/
    ├── api/                 ← All HTTP calls. One file per feature (auth.ts, orders.ts, cart.ts, etc.)
    ├── state/
    │   ├── cart-sync.ts     ← Cart Zustand store + server sync logic. Most cart bugs start here.
    │   └── auth.ts          ← Auth state (token storage, user object)
    ├── config/
    │   └── api.ts           ← API_URL. Change this to switch between local and prod.
    ├── components/          ← Shared UI (ProductCard, OrderCard, etc.)
    ├── hooks/               ← Custom React hooks
    ├── services/            ← Non-API business logic
    ├── notifications/       ← Expo push notification handlers
    ├── theme/               ← Colors, typography, spacing constants
    └── ui/                  ← Base primitives (Button, Input, etc.)
```

**To switch between local and production API:**
```ts
// amako-shop/src/config/api.ts
export const API_URL = 'http://192.168.0.10:8000/api';  // local
// export const API_URL = 'https://amakomomo.com/api';  // production
```

---

## Cart System — How Web and Mobile Stay in Sync

This is the most complex cross-cutting concern. Read this before touching any cart code.

```
Customer action (web)             Customer action (mobile)
       ↓                                   ↓
resources/js/cart.js              amako-shop/src/state/cart-sync.ts
   saveCart()                          syncWithServer()
       ↓                                   ↓
POST /api/cart/sync  ←──────────────────────
       ↓
CartSyncController.php
       ↓
user_carts table  ← single source of truth
       ↑
CartView.php (Livewire) reads this on page load
```

**Rules:**
1. After any cart mutation (add/remove/update/clear), always call `syncWithServer()` (mobile) or `saveCart()` (web)
2. On mobile app launch and foreground resume, always call `loadFromServer()` — server wins
3. Never write cart logic that only saves to `localStorage` without also calling the server sync
4. `saveCart()` in `cart.js` also emits `window.livewire.emit('cartUpdated')` — this updates the navbar badge immediately. Do not remove that emit.

---

## Feature Status

> ✅ Done · 🔶 Partial (degrades gracefully) · ❌ Broken

### Web
| Feature area | Status | Notes |
|-------------|--------|-------|
| Customer auth (login/register/reset) | ✅ | |
| Menu browse + search + product detail | ✅ | |
| Cart (add/update/remove/clear) + DB sync | ✅ | |
| Checkout — eSewa | ✅ | |
| Checkout — Khalti | 🔶 | Routes exist, gateway disabled |
| Checkout — Wallet / Cash | ✅ | |
| Order history + tracking + delivery | ✅ | |
| Credits / wallet balance + top-up | ✅ | |
| Badge + theme system | ✅ | |
| Referral program | ✅ | |
| Offers (view/claim/apply) | ✅ | |
| Bulk orders | ✅ | |
| Notifications | ✅ | |
| Finds / creator feed | ✅ | |
| POS — orders, tables, cash drawer | ✅ | Run TableSeeder first |
| POS — customer display viewer | ✅ | `/customer/payment-viewer` |
| POS — Pusher real-time broadcast | ✅ | Needs Pusher credentials in .env |
| Admin — product/order/branch/employee mgmt | ✅ | |
| Admin — inventory + supply chain | ✅ | |
| Admin — customer analytics + churn | ✅ | |
| Admin — campaigns + rules engine | ✅ | |
| Admin — AI offers / campaign generation | 🔶 | Needs valid OpenAI key |
| Admin — weekly digest | 🔶 | Needs valid OpenAI key |
| Admin — revenue dashboard | 🔶 | Shows Rs 0 — not yet investigated |
| Investor portal | ✅ | |
| Creator portal | ✅ | |

### Mobile
| Feature area | Status | Notes |
|-------------|--------|-------|
| Login + registration | ✅ | |
| Home (featured products) | 🔶 | Empty locally — DB data mismatch |
| Menu browse + search | ✅ | |
| Product detail | ✅ | |
| Cart + server sync | ✅ | |
| Checkout + payment | ✅ | |
| Order history + detail + tracking | ✅ | |
| Offers + bulk orders + notifications | ✅ | |
| Finds feed | ✅ | |
| Profile + credits QR | ✅ | |
| Deep linking | 🔶 | Configured, needs verification |

**Overall: ~88% complete. The 12% partial features all degrade gracefully — nothing is fully broken.**

---

## Common Tasks — Where to Start

| Task | Start here |
|------|-----------|
| Add a menu item (admin) | `AdminProductController` → `resources/views/admin/products/` |
| Change what happens when an order is placed | `app/Services/OrderService.php` |
| Add a new payment method | Extend `AbstractPaymentProcessor`, register in `PaymentService` |
| Fix a cart bug | Check `cart.js` (web), `cart-sync.ts` (mobile), `CartSyncController` (API) |
| Add a new admin page | `routes/admin.php` → new controller in `Admin/` → new view in `views/admin/` |
| Send a push notification | `app/Services/ExpoPushService.php` |
| Add a new mobile screen | Create file in `amako-shop/app/` — Expo Router auto-registers it as a route |
| Add a new API endpoint | `routes/api.php` → `app/Http/Controllers/Api/` |
| Change POS behaviour | `public/js/payments/` — pick the right module |
| Add a Livewire component | Extend `Component`, register in `config/livewire.php`, create blade in `views/livewire/` |

---

## Hard Rules

Break these and you will break something in production:

1. **Livewire stays at 2.12** — PHP 8.3 is incompatible with Livewire 3. Do not upgrade.
2. **`maatwebsite/excel` stays at `3.1.x`** — later versions have breaking changes.
3. **Cart source of truth is `user_carts` DB table** — not `localStorage`, not session.
4. **No hardcoded secrets** — use `.env` and `config()`.
5. **No `_backup` or `_clean` file copies** — use git branches.
6. **POS JS stays as JS** — `public/js/payments/` uses cash drawer hardware, Web Audio, and `window.open()`. Do not convert to Livewire.
7. **`<header>` in POS views must have `overflow: visible`** — changing it to `hidden` clips the hamburger dropdown.
8. **`returnToDisplayBtn` (📺 Ads button) must stay visible** — do not add `display: none` to it.
9. **Use Livewire v2 event syntax only** — `$this->emit()`, not `$dispatch()`. `$listeners` array, not `#[On()]`.
10. **Do not add `.md` files to the project root** — except this README.

---

## Known Issues (Local Dev — Not Bugs to Fix)

| Issue | Why | Fix |
|-------|-----|-----|
| OpenAI features show fallback text | No valid API key locally | Expected. AI pages degrade gracefully. |
| Featured products empty on mobile | Local DB has different product data than prod | Cosmetic. Menu items still load. |
| Image URLs broken / wrong port | `APP_URL` not set to your local IP | Set `APP_URL=http://YOUR_IP:8000` in `.env`, run `php artisan config:clear` |
| POS dine-in shows no tables | `tables` table is empty | Run `php artisan db:seed --class=TableSeeder` |
| Khalti payment fails | Gateway disabled intentionally | Routes exist for when it's re-enabled |
| Revenue dashboard shows Rs 0 | Known bug, not yet investigated | Don't spend time on this unless assigned |
| Pusher not connecting | No Pusher credentials in `.env` | Create free account at pusher.com or leave blank (polling fallback works) |
