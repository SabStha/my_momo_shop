# Amako Momo Shop — Claude Code Reference

## Project Overview

Laravel 10 + React Native (Expo) food ordering app for **Amako Momo Shop**.
- Web frontend: Blade + Tailwind + Livewire 2.12
- Mobile frontend: `amako-shop/` (React Native / Expo)
- Backend: Laravel 10 serving both web (Blade) and mobile (REST API via Sanctum)

---

## How to Run Locally

**Backend (Laravel):**
```bash
# From my_momo_shop/
php -S 0.0.0.0:8000 -t public
```

**Mobile app:**
```bash
cd amako-shop
npx expo start --clear
```

**Database:** MySQL via XAMPP — database name: `database4`

---

## Architecture

| Layer | Detail |
|-------|--------|
| Web auth | PHP sessions (Blade forms) |
| Mobile auth | Laravel Sanctum tokens |
| Cart storage | `user_carts` table — single source of truth for both web and mobile |
| Web components | Livewire 2.12 (`CartManager`, `CartView`, `NotificationBell`) |
| Admin panel | Blade views under `resources/views/admin/` |
| POS payment UI | `public/js/payment-manager.js` loaded via `asset()` — leave as-is, genuinely needs JS |

---

## Key Files

| File | Purpose |
|------|---------|
| `routes/api.php` | Mobile REST API routes |
| `routes/admin.php` | Admin panel routes |
| `routes/web.php` | Public web routes (thin orchestrator — include sub-route files) |
| `routes/user.php` | Authenticated user routes |
| `routes/pos.php` | POS terminal routes |
| `routes/auth.php` | Login/register/password routes |
| `routes/public.php` | Guest-accessible pages |
| `app/Services/OrderService.php` | All order creation logic |
| `app/Services/Payment/` | Payment gateway processors (eSewa, Khalti, Wallet, Card, Cash) |
| `app/Http/Livewire/CartManager.php` | Navbar cart badge — Livewire 2.12 component |
| `app/Http/Livewire/NotificationBell.php` | Notification bell — Livewire 2.12 component |
| `app/Http/Livewire/CartView.php` | Cart page items — Livewire 2.12 component |
| `amako-shop/src/config/api.ts` | Mobile API base URL config |
| `amako-shop/src/state/cart-sync.ts` | Mobile cart sync with backend |

---

## Earlier Fixes (March 2026)

- **Cart sync fixed:** `resources/js/cart.js` `saveCart()` now POSTs to `/api/cart/sync` after every mutation so web cart writes to `user_carts` DB. Previously only wrote to `localStorage`.
- **Livewire badge reactivity fixed:** `saveCart()` calls `window.livewire.emit('cartUpdated')` so navbar badge updates immediately on add-to-cart, not just on the 10 s poll.
- **CartView component built:** `app/Http/Livewire/CartView.php` is now a full component — `updateQuantity`, `removeItem`, `clearCart` all use `wire:click`. Cart page no longer uses JS DOM manipulation for items.
- **Dead JS files removed:** `public/js/cart-fixed.js`, `cart-sync-manager.js`, `cart-server.js` deleted. `resources/js/cart.js` (compiled via Vite) is the sole JS cart system.
- **Blade `@livewire` in JS comment fixed:** `@livewire('cart-view')` inside a `//` comment in `cart/index.blade.php` was being processed by Blade and injecting component HTML into the script block — replaced with plain text.
- **Admin wallet routes fixed:** `route('wallet.index')` → `route('admin.wallet.index')` across `admin.blade.php`, `WalletController.php`, `WalletTopUpController.php`, and 4 wallet blade views (11 occurrences). Was crashing every admin page load.
- **WeeklyDigestService OpenAI crash fixed:** `catch (\Exception $e)` → `catch (\Throwable $e)` so SDK errors and PHP `Error` subtypes are caught. Controller also wrapped in try-catch so page loads with a graceful fallback when OpenAI is unavailable.
- **AIPopupService OpenAI crash fixed:** same `\Throwable` treatment — degrades gracefully with no crash.
- **SANCTUM_STATEFUL_DOMAINS updated:** Added `192.168.0.10:8000` to `.env` so browser session auth works for `/api/cart/sync` when accessing via local IP.
- **payment-manager.js refactored to 6 modules:** Loads from `public/js/payments/` via `asset()`. Genuinely needs JS (order polling, cash drawer hardware, Web Audio, `window.open()`). Do not Livewire-ify.
- **TableSeeder created:** `tables` table was empty; seeder populates tables for all 3 branches (Main, North, South).

---

## What NOT To Do

- **Do not** use PHP sessions for cart storage — use the `user_carts` table.
- **Do not** hardcode API keys or secrets — use `.env`.
- **Do not** create `_backup` or `_clean` copies of files — use git instead.
- **Do not** add `.md` documentation files to the project root.
- **Do not** upgrade Livewire beyond 2.12 — PHP 8.3 is incompatible with Livewire 3.
- **Do not** upgrade `maatwebsite/excel` beyond `3.1.x`.
- **Do not** emit Livewire v3 syntax (`#[On(...)]`, `$dispatch`) — project uses Livewire v2.
  - v2 event syntax: `$this->emit('eventName')` / `$listeners` array / `$this->dispatchBrowserEvent('name')`
  - From JS: `window.livewire.emit('eventName')`

---

## Cart System Status (March 19, 2026)

- `user_carts` table is single source of truth for both platforms
- **Mobile writes:** `cart-sync.ts` `syncWithServer()` → `POST /api/cart/sync` (CartSyncController) after every addItem/removeItem/updateQuantity
- **Web writes:** `cart.js` `saveCart()` → `POST /api/cart/sync` (same endpoint) — fire-and-forget async, no retry
- **Web Livewire writes:** `CartView.php` `saveCart()` → `updateCart()` on every wire:click mutation
- **Mobile reads:** `loadFromServer()` → `GET /api/cart` — server always overwrites local Zustand state
- **Web reads:** Livewire `CartView.php` `loadCart()` reads `user_carts.cart_data` directly from DB on mount

### Cart Write Sites (16 total)
| File | What |
|------|------|
| `CartView.php:51` | Dedup write-back on load |
| `CartView.php:101` | After remove/update/clear (wire:click) |
| `CartManager.php:89` | `updateQuantity()` — not wired to UI, dead write |
| `CartController.php:185,265,312,345,411` | Web add/update/remove/clear/sync endpoints |
| `CartSyncController.php:98,148,225,284,339` | Mobile sync/clear/add/remove/update endpoints |
| `CheckoutController.php:253` | Clears after web checkout |
| `OrderController.php:359` | Clears after order placed |

### Known Cart Bugs (as of March 19, 2026)
- Add works one direction sometimes but not always reflected cross-platform
- Remove not always syncing to server
- "Your cart is empty" header shows even when items are displayed below it
- `cart.js` POST is fire-and-forget — silent failure leaves localStorage and DB out of sync
- `CartSyncController.getCart()` bug fixed: previously wrote request items to DB on GET (reversed server-is-truth); now returns empty correctly
- Full audit completed — root cause of remaining bugs not yet isolated

---

## Payment Manager Status (March 2026)

- Refactored from monolith into **6 modules** under `public/js/payments/`:
  - `order-list.js` — order polling and list rendering
  - `payment-panel.js` — payment method selection and processing
  - `cash-drawer.js` — cash drawer hardware + balance tracking
  - `display-ads.js` — customer viewer ad rotation
  - `order-actions.js` — accept/ready/complete workflow buttons
  - `payment-manager-init.js` — wires all modules together
- Session order numbers: `D-0001` (dine-in), `T-0001` (takeaway), `O-0001` (online)
- Customer-facing display viewer at `/customer/payment-viewer` (fullscreen, cursor auto-hides after 3s)
- Pusher connected — credentials in `.env` (`PUSHER_APP_*`)
- Video ads system at `/admin/display-ads`
- Cash drawer balance tracking fixed
- `window.currentSessionStartTime` persisted to `localStorage.pmSessionStartTime` — survives page refresh
- `returnToDisplayBtn` (📺 Ads) is permanently visible in payment panel top bar — do not add `display:none`
- Hamburger dropdown: `<header>` must use `overflow:visible` not `overflow:hidden` — changing it clips the dropdown

---

## Pending Issues (March 19, 2026)

- **Cart sync between mobile and web** — main ongoing issue, audit complete, fix in progress
- **Admin dashboard revenue shows Rs 0** — not yet investigated
- **Featured products not showing on mobile** — local DB mismatch, cosmetic
- **`currentSessionStartTime` undefined error** — partially fixed (localStorage restore added), may still surface
- **Khalti routes commented out** — Khalti payment gateway disabled, routes exist but inactive

---

## Recent Fixes (March 19, 2026)

- **Duplicate `/api/menu` route removed** — line 375 in `routes/api.php` shadowed correct route, returned wrong shape `{success, items}` instead of `{success, data:{categories,items}}` — caused Rs 0.00 price on mobile and silent cart sync failure
- **Cart remove now syncs to server** — `syncWithServer()` had early return when items array was empty; removed so last-item removal now POSTs `{items:[]}` to clear server cart
- **Duplicate `livewire-cart-updated` listener removed** — second listener in `cart/index.blade.php` was writing unmapped Livewire items back to localStorage, corrupting web cart state
- **`CartSyncController::getCart()` write-on-GET bug fixed** — removed block that wrote request items to DB when server cart was empty (reversed server-is-truth contract)
- **`loadFromServer()` server-always-wins** — removed "recently cleared" guard and push-local-to-server branch; server state now always overwrites mobile local state
- **AppState foreground listener added** — mobile reloads cart from server whenever app returns to foreground
- **`useFocusEffect` race condition fixed** — cart tab only calls `loadFromServer()` when local cart is empty, preventing server overwrite of locally-added items mid-sync

---

## Current Known Issues

- **OpenAI key invalid locally:** `AIPopupService` and `WeeklyDigestService` degrade gracefully — pages load, AI text replaced with fallback string. Not a bug.
- **Featured products empty on mobile:** Local DB has different product data than production. Cosmetic only — menu items load fine.
- **Local images wrong port:** Some image URLs may resolve to the wrong port locally. Set `APP_URL=http://192.168.0.10:8000` in `.env` and run `php artisan config:clear` to fix.
- **POS tables empty locally:** `tables` DB table has 0 rows. Run the TableSeeder or manually insert rows via tinker to test POS dine-in flow.

---

## Testing

| Environment | API URL |
|-------------|---------|
| Local | `http://192.168.0.10:8000/api` |
| Production | `https://amakomomo.com/api` |

Switch by editing `API_URL` in `amako-shop/.env` (or `amako-shop/src/config/api.ts`).

---

## Livewire 2.12 Quick Reference

```php
// Emit to other components
$this->emit('eventName', $payload);

// Emit to browser (JavaScript)
$this->dispatchBrowserEvent('eventName', ['key' => 'value']);

// Listen in component
protected $listeners = ['eventName' => 'methodName'];
```

```js
// Emit from JS to Livewire components
window.livewire.emit('eventName', payload);

// Listen for browser events from Livewire
window.addEventListener('eventName', e => console.log(e.detail));
```
