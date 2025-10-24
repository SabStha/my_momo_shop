# Cart & Notification Synchronization Guide

## Overview
This guide explains how cart and notifications are synchronized between the web and mobile applications.

## Current Status

### ✅ What's Implemented

1. **Server-Side Cart Storage**
   - Database table: `user_carts`
   - Each user has one cart stored on the server
   - Cart data is stored as JSON

2. **Cart Sync API Endpoints**
   - `GET /api/cart` - Get user's cart from server
   - `POST /api/cart/sync` - Sync cart to server
   - `POST /api/cart/clear` - Clear cart
   - `POST /api/cart/add-item` - Add item
   - `DELETE /api/cart/remove-item` - Remove item
   - `PUT /api/cart/update-quantity` - Update quantity

3. **Mobile App Cart Sync**
   - Uses `useCartSyncStore` (new sync-enabled store)
   - Automatically syncs with server when online
   - Loads server cart on login
   - Persists to AsyncStorage for offline use

4. **Web App Cart Sync**
   - Uses `CartSyncManager` JavaScript class
   - Automatically syncs with server when authenticated
   - Persists to localStorage for offline use

5. **Notification Sync**
   - Shared API endpoints for both platforms
   - Mobile: Uses existing notification hooks
   - Web: Uses `NotificationSyncManager` with polling

## How to Use

### Mobile App

The cart now automatically syncs when you:
- Log in (loads server cart)
- Add items to cart (syncs immediately)
- Update quantities (syncs immediately)
- Remove items (syncs immediately)
- Come back online after being offline

**No code changes needed** - just use the cart as normal!

### Web App

The cart automatically syncs when you:
- Load the page while logged in
- Add items to cart (if using CartSyncManager)
- Update quantities
- Remove items

## Troubleshooting

### Cart not syncing between platforms

1. **Check if user is logged in** on both platforms
2. **Check server cart**:
   ```bash
   php check_cart_sync.php
   ```
3. **Clear browser cache** and reload web app
4. **Close and reopen mobile app** to trigger sync

### To manually trigger sync:

**Mobile App:**
```typescript
import { useCartSyncStore } from './src/state/cart-sync';

const { loadFromServer, syncWithServer } = useCartSyncStore();

// Load cart from server
await loadFromServer();

// Sync current cart to server
await syncWithServer();
```

**Web App:**
Open browser console and run:
```javascript
// Load from server
window.cartSyncManager.loadFromServer();

// Sync to server
window.cartSyncManager.syncWithServer();

// Check sync status
console.log(window.cartSyncManager.getSyncStatus());
```

### Notifications not showing

1. **Check notification API**:
   ```bash
   curl http://localhost:8000/api/notifications \
     -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
   ```

2. **For web app**, check browser console:
   ```javascript
   window.notificationSyncManager.loadNotifications();
   console.log(window.notificationSyncManager.getNotificationStatus());
   ```

## Testing Sync

### Test Cart Sync

1. **Web to Mobile**:
   - Add items to cart on web
   - Close and reopen mobile app
   - Items should appear

2. **Mobile to Web**:
   - Add items to cart on mobile
   - Refresh web browser
   - Items should appear

### Test Notifications

1. Create a test notification:
   ```bash
   php artisan tinker
   ```
   ```php
   $user = User::first();
   $user->notify(new \App\Notifications\OrderStatusUpdated([
       'title' => 'Test Notification',
       'message' => 'This is a test',
   ]));
   ```

2. Check on both platforms

## Architecture

### Data Flow

```
Mobile App (AsyncStorage) <-> API Server (Database) <-> Web App (localStorage)
                                      ↓
                              user_carts table
```

### When Sync Happens

**Mobile App:**
- On login: Loads server cart
- On add/update/remove: Syncs to server
- On network reconnect: Syncs to server

**Web App:**
- On page load: Loads server cart (if authenticated)
- On add/update/remove: Syncs to server
- On network reconnect: Syncs to server

## Files Changed

### Mobile App
- `amako-shop/src/state/cart-sync.ts` - New sync-enabled cart store
- `amako-shop/src/session/SessionProvider.tsx` - Triggers sync on login
- All cart-using components updated to use `useCartSyncStore`

### Web App
- `public/js/cart-sync-manager.js` - New cart sync manager
- `public/js/notification-sync.js` - New notification sync manager
- `resources/views/layouts/app.blade.php` - Includes sync scripts

### Backend
- `app/Models/UserCart.php` - Cart model
- `app/Http/Controllers/Api/CartSyncController.php` - Cart API
- `database/migrations/*_create_user_carts_table.php` - Cart table
- `routes/api.php` - Cart sync routes

## Migration from Old System

The old cart system (localStorage/AsyncStorage only) is now deprecated.

All components have been updated to use the new sync-enabled stores:
- Mobile: `useCartSyncStore` instead of `useCartStore`
- Web: `cartSyncManager` instead of `cartManager`

The old stores still exist for backward compatibility but should not be used in new code.




