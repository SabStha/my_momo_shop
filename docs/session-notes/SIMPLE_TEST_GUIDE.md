# 🚀 Simple Test Guide - Notifications on Your Device

## ✅ Prerequisites

1. ✅ Your mobile app is built and installed
2. ✅ You are logged into the app
3. ✅ Laravel server is running on your computer

## 🎯 Easiest Way to Test

### Option 1: Using PowerShell Script (Recommended!)

1. **Open PowerShell** on your computer (Windows key + type "PowerShell")

2. **Navigate to your project:**
   ```powershell
   cd C:\Users\user\my_momo_shop
   ```

3. **Run the test script:**
   ```powershell
   .\test-notifications.ps1
   ```

4. **Follow the menu:**
   ```
   1. 🎁 Offer Notification (20% OFF)
   2. 🛵 Delivery Notification (Enhanced with ETA!)
   3. ⚡ Flash Sale Notification
   4. 📢 System Notification
   5. 🎯 Test ALL Notifications
   ```

5. **Check your device!** 📱

### Option 2: Using Artisan Tinker (Direct)

1. **Open terminal:**
   ```bash
   cd C:\Users\user\my_momo_shop
   php artisan tinker
   ```

2. **Run ONE of these:**

   #### Test Offer Notification
   ```php
   $user = User::first();
   $service = app(App\Services\MobileNotificationService::class);
   $offer = App\Models\Offer::where('code', 'TEST20')->first();
   if (!$offer) {
       $offer = App\Models\Offer::create([
           'code' => 'TEST20',
           'title' => '🎉 20% Off Special!',
           'description' => 'Limited time! Get 20% off on all momos.',
           'discount' => 20,
           'type' => 'percentage',
           'min_purchase' => 500,
           'max_discount' => 200,
           'valid_from' => now(),
           'valid_until' => now()->addDays(1),
           'is_active' => true,
       ]);
   }
   $service->sendOfferNotification($user, $offer);
   ```

   #### Test Delivery Notification (Beautiful!)
   ```php
   $user = User::first();
   $order = $user->orders()->latest()->first();
   if ($order) {
       $service = app(App\Services\OrderNotificationService::class);
       $service->sendOrderStatusNotification($order, 'out_for_delivery', 'ready');
       echo "✅ Notification sent! Check your device!";
   } else {
       echo "❌ No orders found. Place an order first.";
   }
   ```

### Option 3: Using Web Browser

1. **Login to admin panel:** `https://amakomomo.com/admin`

2. **Open browser console:** Press `F12` > Console tab

3. **Get your user ID:**
   ```javascript
   // Check who you are
   fetch('https://amakomomo.com/api/user', {
     headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
   }).then(r => r.json()).then(console.log);
   ```

4. **Send test notification:**
   ```javascript
   // Test Delivery Notification (the beautiful one!)
   fetch('https://amakomomo.com/api/test/notification/delivery', {
     method: 'POST',
     headers: {
       'Authorization': 'Bearer ' + localStorage.getItem('token'),
       'Content-Type': 'application/json'
     }
   })
   .then(r => r.json())
   .then(data => {
     console.log('✅ Sent!', data);
     alert('Check your device now! 📱');
   });
   ```

## 📱 What You Should See

### Offer Notification
```
🎉 20% Off Special!
Limited time! Get 20% off on all momos.
Valid for next 24 hours!
```

### Delivery Notification (Enhanced!)
```
🛵 Delivery started — ETA 18-22 min
Rider Suman picked up • ORD-68F69EC
[Progress Bar] 80%
[Track] [Call rider] [Help]
```

### Flash Sale
```
⚡ Flash Sale!
Limited time only! Get 30% off on all momos
for the next 2 hours!
```

## 🔧 Troubleshooting

### "No orders found" error?

**Solution:** Place a test order first:
1. Open your mobile app
2. Add items to cart
3. Complete checkout
4. Then test delivery notifications

### Notifications not appearing?

**Check:**
1. ✅ App is open (or in background)
2. ✅ Notification permissions enabled
3. ✅ Logged into the app
4. ✅ Laravel server running: `php artisan serve`

**Verify device is registered:**
```bash
php artisan tinker
```
```php
$user = User::first();
$user->devices;  // Should show at least 1 device
```

If no devices:
1. Logout from app
2. Login again
3. Grant notification permissions
4. Try again

### Still not working?

**Check logs:**
```bash
# In project directory
tail -f storage/logs/laravel.log
```

Look for:
- `"Mobile notification sent"`
- `"Push notification sent to devices"`

## 🎯 Quick Test Commands

### Just want to test the enhanced delivery notification?

**PowerShell (simplest):**
```powershell
cd C:\Users\user\my_momo_shop
.\test-notifications.ps1
# Choose option 2
```

**Tinker (direct):**
```bash
php artisan tinker
```
```php
$user = User::first();
$order = $user->orders()->latest()->first();
if ($order) {
    app(App\Services\OrderNotificationService::class)
        ->sendOrderStatusNotification($order, 'out_for_delivery', 'ready');
}
```

## 📊 Test Sequence (Full Experience)

Test the complete delivery journey:

```powershell
# Run these one by one in PowerShell
.\test-notifications.ps1
# Then select:
# 6 > 1 (Confirmed)
# Wait 5 seconds
# 6 > 2 (Preparing)
# Wait 5 seconds
# 6 > 3 (Ready)
# Wait 5 seconds
# 6 > 4 (Out for Delivery) ⬅️ Most impressive!
# Wait 5 seconds
# 6 > 6 (Delivered)
```

Watch your notification shade update beautifully! 🎨

## ✅ Success Checklist

- [ ] Offer notification received
- [ ] Delivery notification received with ETA
- [ ] Progress bar visible (Android)
- [ ] Action buttons work (Track, Call rider, Help)
- [ ] Rider name displays
- [ ] Order number shows correctly
- [ ] Notification updates in place (no spam)
- [ ] Can tap notification to open app

## 🎉 You're Ready!

The easiest way to start:
```powershell
cd C:\Users\user\my_momo_shop
.\test-notifications.ps1
```

Then choose option **2** (Delivery Notification) to see the beautiful notification like in the image! 📱✨

