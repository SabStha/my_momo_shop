# 📱 Test Notifications on Your Device

## 🎯 Available Notifications

You have 4 types of notifications ready to test:

1. **🎁 Offer Notifications** - Special deals and promotions
2. **🛵 Delivery Notifications** - Order tracking and delivery updates (Enhanced!)
3. **⚡ Flash Sale Notifications** - Limited time offers
4. **📢 System Notifications** - App updates and announcements

## 🚀 Quick Test Methods

### Method 1: Using API Calls (From Your Device)

#### Step 1: Open your terminal on computer

```bash
# Navigate to your project
cd C:\Users\user\my_momo_shop

# Start Laravel server if not already running
php artisan serve
```

#### Step 2: Get your auth token

From your mobile app, you're already logged in. Your device token is registered.

#### Step 3: Test Each Notification Type

##### Test Offer Notification (20% OFF)
```bash
curl -X POST https://amakomomo.com/api/test/notification/offer \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

##### Test Delivery Notification (Like image style!)
```bash
curl -X POST https://amakomomo.com/api/test/notification/delivery \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

To test different statuses:
```bash
# Test "out_for_delivery" with rider (default)
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=out_for_delivery

# Test "ready for pickup"
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=ready

# Test "preparing"
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=preparing

# Test "delivered"
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=delivered
```

##### Test Flash Sale Notification
```bash
curl -X POST https://amakomomo.com/api/test/notification/flash-sale \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

##### Test System Notification
```bash
curl -X POST https://amakomomo.com/api/test/notification/system \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

##### Test ALL Notifications at Once
```bash
curl -X POST https://amakomomo.com/api/test/notification/all \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Method 2: Using Browser (Easier!)

1. **Login to your app** on device
2. **Open browser** on your computer
3. **Go to**: `https://amakomomo.com/admin` and login
4. **Open browser console** (F12 > Console tab)
5. **Run these commands**:

```javascript
// Test Offer Notification
fetch('https://amakomomo.com/api/test/notification/offer', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Content-Type': 'application/json'
  }
}).then(r => r.json()).then(console.log);

// Test Delivery Notification
fetch('https://amakomomo.com/api/test/notification/delivery', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Content-Type': 'application/json'
  }
}).then(r => r.json()).then(console.log);

// Test Flash Sale
fetch('https://amakomomo.com/api/test/notification/flash-sale', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Content-Type': 'application/json'
  }
}).then(r => r.json()).then(console.log);
```

### Method 3: Using Artisan Command (Simplest!)

```bash
# From your project directory
php artisan tinker
```

Then run:

```php
// Get your user
$user = User::find(1); // Or your user ID

// Test Offer Notification
$service = app(App\Services\MobileNotificationService::class);
$offer = App\Models\Offer::where('code', 'TEST20')->first() ?? App\Models\Offer::create([
    'code' => 'TEST20',
    'title' => '🎉 20% Off Special!',
    'description' => 'Limited time offer! Get 20% off on all momos.',
    'discount' => 20,
    'type' => 'percentage',
    'min_purchase' => 500,
    'max_discount' => 200,
    'valid_from' => now(),
    'valid_until' => now()->addDays(1),
    'is_active' => true,
]);
$service->sendOfferNotification($user, $offer);

// Test Delivery Notification
$orderService = app(App\Services\OrderNotificationService::class);
$order = $user->orders()->latest()->first();
if ($order) {
    $orderService->sendOrderStatusNotification($order, 'out_for_delivery', 'ready');
}
```

## 📱 What You'll See

### 1. Offer Notification
```
┌──────────────────────────────────────────┐
│ 🎉 20% Off Special!                      │
│                                          │
│ Limited time offer! Get 20% off on all  │
│ momos. Valid for next 24 hours!         │
│                                          │
│ [View Offer]                            │
└──────────────────────────────────────────┘
```

### 2. Delivery Notification (Enhanced!)
```
┌──────────────────────────────────────────┐
│ 🛵 Delivery started — ETA 18-22 min     │
│                                          │
│ Rider Suman picked up • ORD-68F69EC     │
│                                          │
│ ████████████░░░░░░░░░░░ 80%            │
│                                          │
│  [Track]  │  [Call rider]  │  [Help]   │
└──────────────────────────────────────────┘
```

### 3. Flash Sale Notification
```
┌──────────────────────────────────────────┐
│ ⚡ Flash Sale!                           │
│                                          │
│ Limited time only! Get 30% off on all   │
│ momos for the next 2 hours!             │
│                                          │
│ [Shop Now]                              │
└──────────────────────────────────────────┘
```

### 4. System Notification
```
┌──────────────────────────────────────────┐
│ 📢 App Update Available                  │
│                                          │
│ A new version of Amako Momo app is      │
│ available. Update now for the best      │
│ experience!                              │
└──────────────────────────────────────────┘
```

## 🔧 Troubleshooting

### Notifications Not Appearing?

1. **Check device token is registered:**
   ```bash
   php artisan tinker
   ```
   ```php
   $user = User::find(1);
   $user->devices; // Should show your device
   ```

2. **Check notification permissions:**
   - Open your app settings
   - Make sure notifications are enabled

3. **Check server logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for: `"Mobile notification sent"` or `"Push notification sent"`

4. **Check Expo logs:**
   - In your app, notifications should appear in the system tray
   - Check app console for any errors

### Device Token Not Registered?

If your device isn't registered:
1. **Logout and login again** in your app
2. **Grant notification permissions** when prompted
3. **Check logs** for device registration

## 📊 Test Scenarios

### Scenario 1: New Offer Promotion
```bash
# Send offer notification
curl -X POST https://amakomomo.com/api/test/notification/offer

# User sees notification
# Taps notification
# Opens menu with offer applied
```

### Scenario 2: Full Delivery Cycle
```bash
# 1. Order confirmed
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=confirmed

# 2. Preparing (silent notification)
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=preparing

# 3. Ready for pickup
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=ready

# 4. Delivery started (heads-up, with ETA!)
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=out_for_delivery

# 5. Delivered (with rate prompt)
curl -X POST https://amakomomo.com/api/test/notification/delivery?status=delivered
```

### Scenario 3: Flash Sale Blast
```bash
# Send flash sale to all users
curl -X POST https://amakomomo.com/api/test/notification/flash-sale

# Creates urgency
# Users rush to app to claim deal
```

## 🎯 Production Use

These test endpoints are for **testing only**. In production:

### Offers are sent automatically by:
- **AI Offer System** - Analyzes user behavior
- **Automated Triggers** - Cart abandonment, inactivity
- **Manual Admin** - Admin dashboard offer creation

### Delivery notifications are sent automatically by:
- **Order Status Changes** - When driver updates order
- **Real-time Tracking** - As delivery progresses
- **Smart ETA** - Calculated based on location

### Flash Sales are triggered by:
- **Admin Dashboard** - Schedule flash sales
- **Cron Jobs** - Automated based on time
- **Events** - Special occasions

## 🎨 Customization

All notification messages are in:
- **Backend**: `app/Services/OrderNotificationService.php`
- **Backend**: `app/Services/MobileNotificationService.php`
- **Frontend**: `amako-shop/src/notifications/delivery-notifications.ts`

## ✅ Checklist

Test each notification type:
- [ ] Offer notification received
- [ ] Delivery notification received (with ETA and rider)
- [ ] Flash sale notification received
- [ ] System notification received
- [ ] Notification actions work (Track, Call, etc.)
- [ ] Progress bar shows (Android)
- [ ] Notification threading works (updates replace)
- [ ] Silent vs heads-up priority correct

## 🆘 Need Help?

If something doesn't work:
1. Check `storage/logs/laravel.log`
2. Check your device token: `App\Models\Device::where('user_id', YOUR_ID)->get()`
3. Verify expo push service: `App\Services\ExpoPushService`
4. Make sure app has notification permissions

---

## 🎉 Ready to Test!

Pick a method above and start testing your beautiful notifications! 🚀

**Recommended order:**
1. Test delivery notification (most impressive!)
2. Test offer notification
3. Test flash sale
4. Test system notification

