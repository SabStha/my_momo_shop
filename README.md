# Amako Momo Shop

Full-stack food ordering system for Amako Momo Shop with a web admin panel, POS terminal, and React Native mobile app.

---

## Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 10, PHP 8.1+ |
| Web frontend | Blade + Tailwind CSS + Livewire 2.12 |
| Mobile app | React Native / Expo 54 (`amako-shop/`) |
| Database | MySQL (database name: `database4`) |
| Web auth | PHP sessions |
| Mobile auth | Laravel Sanctum tokens |
| Real-time | Pusher |
| Payments | eSewa (active), Khalti (disabled — routes exist), Wallet, Card, Cash |

---

## Prerequisites

Make sure these are installed before setup:

- **PHP 8.1+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- **Composer** (PHP dependency manager)
- **MySQL 8** — easiest via [XAMPP](https://www.apachefriends.org/) on Windows
- **Node.js 18+** and npm
- **Git**

For the mobile app only:
- **Expo CLI** — `npm install -g expo-cli`
- **Expo Go** app on your phone, or an Android/iOS emulator

---

## Backend Setup

### 1. Clone the repo

```bash
git clone <repo-url>
cd my_momo_shop
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and update:
- `DB_PASSWORD` — your MySQL root password (blank for default XAMPP)
- `APP_URL` — set to your local IP so mobile can reach it, e.g. `http://192.168.0.10:8000`
- `SANCTUM_STATEFUL_DOMAINS` — same IP + port, e.g. `192.168.0.10:8000`

### 4. Create the database

Open phpMyAdmin (XAMPP) or MySQL CLI and run:

```sql
CREATE DATABASE database4 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed --class=BranchSeeder
php artisan db:seed --class=MenuSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=PaymentMethodSeeder
php artisan db:seed --class=TaxDeliverySettingsSeeder

# POS requires tables — run this too
php artisan db:seed --class=TableSeeder
```

> If you skip `TableSeeder` the POS dine-in flow will show no tables.

### 6. Build frontend assets

```bash
npm install
npm run build
```

### 7. Set storage permissions

```bash
php artisan storage:link
```

### 8. Run the dev server

```bash
php -S 0.0.0.0:8000 -t public
```

Backend is now running at `http://localhost:8000`.
Admin panel: `http://localhost:8000/admin`
POS terminal: `http://localhost:8000/pos`

---

## Mobile App Setup

The mobile app lives in `amako-shop/`.

### 1. Install dependencies

```bash
cd amako-shop
npm install
```

### 2. Point to your local backend

Edit `amako-shop/src/config/api.ts`:

```ts
const DEV_URL = 'http://YOUR_LOCAL_IP:8000/api';
```

Replace `YOUR_LOCAL_IP` with your machine's actual LAN IP (e.g. `192.168.0.10`).  
Find it with `ipconfig` (Windows) or `ifconfig` (Mac/Linux).

> Phone and dev machine must be on the same WiFi network.

### 3. Run the mobile app

```bash
npx expo start --clear
```

Scan the QR code with Expo Go on your phone, or press `a` for Android emulator / `i` for iOS simulator.

---

## Project Structure

```
my_momo_shop/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin panel controllers (~60 controllers)
│   │   │   ├── Api/            # Mobile REST API controllers
│   │   │   └── ...             # Web + shared controllers
│   │   └── Livewire/           # Livewire components (CartManager, CartView, NotificationBell)
│   ├── Models/
│   └── Services/
│       ├── OrderService.php    # All order creation logic lives here
│       └── Payment/            # eSewa, Khalti, Wallet, Card, Cash processors
├── routes/
│   ├── web.php                 # Thin orchestrator — includes sub-route files
│   ├── admin.php               # Admin panel routes (auth + admin middleware)
│   ├── api.php                 # Mobile REST API routes (Sanctum)
│   ├── pos.php                 # POS terminal routes
│   ├── user.php                # Authenticated user routes
│   ├── auth.php                # Login/register/password
│   └── public.php              # Guest pages
├── resources/views/
│   ├── admin/                  # Admin Blade views
│   └── ...
├── public/js/payments/         # POS payment modules (6 JS files — do not Livewire-ify)
│   ├── order-list.js
│   ├── payment-panel.js
│   ├── cash-drawer.js
│   ├── order-actions.js
│   ├── display-ads.js
│   └── payment-manager-init.js
└── amako-shop/                 # React Native / Expo mobile app
    └── src/
        ├── api/                # API hooks and clients
        ├── state/              # Zustand stores (cart-sync.ts is key)
        ├── config/api.ts       # API base URL — edit this for local dev
        └── ...
```

---

## Architecture Notes

- **Cart** — `user_carts` table is the single source of truth for both web and mobile. Do not use `localStorage` or PHP sessions as primary cart storage.
- **Livewire version** — project uses **Livewire 2.12**. Do NOT upgrade. PHP 8.3 breaks Livewire 3. Use v2 event syntax: `$this->emit()` / `$listeners` array / `window.livewire.emit()` from JS.
- **`maatwebsite/excel`** — stays at `3.1.x`. Do not upgrade.
- **POS JS modules** — `public/js/payments/` files load via `asset()`. They genuinely need vanilla JS (hardware drawer control, Web Audio, `window.open()`). Do not convert to Livewire.
- **Routes** — `web.php` is a thin orchestrator that includes sub-route files. Add new web routes to the appropriate sub-file, not directly to `web.php`.

---

## Environment Variables Reference

See `.env.example` for all variables with descriptions.

Key variables to set for local dev:

| Variable | What to set |
|----------|-------------|
| `APP_URL` | `http://YOUR_LOCAL_IP:8000` |
| `DB_DATABASE` | `database4` |
| `DB_PASSWORD` | your MySQL password (blank for XAMPP default) |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost,YOUR_LOCAL_IP:8000` |
| `PUSHER_*` | Get from Pusher dashboard (optional for local dev) |
| `OPENAI_API_KEY` | Optional — app degrades gracefully without it |
| `ESEWA_*` | eSewa merchant credentials (get from team lead) |

---

## Current Feature Status

### Working and Stable
- Web ordering flow (Blade/Livewire)
- Admin panel — products, orders, payments, employees, inventory, reports
- POS terminal — payment processing, cash drawer, display ads, session order numbers
- Mobile app — auth, menu browsing, ordering, loyalty/badges
- Wallet system (top-up, deduction, admin management)
- eSewa payment gateway
- Notification bell (Livewire)
- Weekly digest (AI-generated, degrades gracefully if OpenAI key missing)
- Customer analytics, churn prediction, campaigns

### In Progress / Partially Working
- **Cart sync (web ↔ mobile)** — main active issue. Logic exists but there are edge-case bugs where changes on one platform don't reflect on the other. Cart tab and web cart may diverge.
- **Admin dashboard revenue** — shows Rs 0 on the dashboard widget. Not yet investigated. Orders and payments data exists correctly in the DB.

### Known Bugs (non-blocking)
- "Your cart is empty" heading renders even when items are shown below it
- `cart.js` POST to `/api/cart/sync` is fire-and-forget — silent failure leaves localStorage and DB out of sync
- Featured products section is empty on mobile (local DB data mismatch — cosmetic only, menu loads fine)
- Local image URLs may resolve to wrong port if `APP_URL` is not set correctly in `.env`
- OpenAI key is invalid locally — AI popup and weekly digest fall back to placeholder text gracefully

### Disabled / Commented Out
- **Khalti payment gateway** — routes exist in `routes/api.php` and `routes/web.php` but are commented out. Integration was started but not finished.

---

## What To Work On (Prioritised)

### Phase 1 — Fix the fundamentals (do these first)
1. **Cart sync** — isolate and fix the remaining edge cases where web and mobile cart diverge
2. **Admin dashboard revenue Rs 0** — investigate `AdminDashboardController` and the revenue query
3. **Cart empty header bug** — simple UI fix in the cart Blade view

### Phase 2 — Complete incomplete features
4. **Khalti integration** — uncomment routes, wire up `KhaltiController`, test sandbox flow
5. **Featured products on mobile** — sync local DB product data or fix the featured query

### Phase 3 — Polish and scale
6. Automated test coverage (currently near zero)
7. API rate limiting
8. Admin UI for managing Pusher / notification templates
9. Mobile app push notification deep-linking

### Do NOT do yet (too early / risky)
- Livewire upgrade to v3 — blocked by PHP 8.3 incompatibility
- Rewriting POS JS modules in Livewire/React
- Adding new payment gateways before Khalti is finished
- Changing the cart storage model — `user_carts` is working, don't add complexity

---

## Team Workflow

- **Main branch** is `main` — it should always be deployable
- Create a feature branch for any non-trivial change: `git checkout -b feature/cart-sync-fix`
- One PR per feature/fix — don't bundle unrelated changes
- Before pushing, run `npm run build` so compiled assets are included
- Do NOT commit `.env` or any file with real API keys/passwords
- Do NOT create `_backup` or `_clean` copies of files — use git history instead

---

## Deployment

Production runs at `https://amakomomo.com`.

For production deploys (ask team lead for server access):
1. Push to `main`
2. SSH into server and run:
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
npm run build
```

---

## Getting Help

- Check `CLAUDE.md` in the project root for deeper technical notes on past fixes, cart system internals, and POS architecture
- Database schema: read the migration files in `database/migrations/` — they are the authoritative schema reference
- Payment flow: `app/Services/OrderService.php` is the entry point for all order creation
