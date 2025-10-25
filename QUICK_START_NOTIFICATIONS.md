# Quick Start: Enhanced Delivery Notifications

## 🎯 What You Have Now

Beautiful delivery notifications matching the image style with:
- ✅ **ETA Display**: "Delivery started — ETA 18-22 min"
- ✅ **Rider Info**: "Rider Suman picked up • ORD-68F69EC"
- ✅ **Progress Bar**: Visual indicator (Android)
- ✅ **Action Buttons**: Track | Call rider | Help

## 🚀 Quick Test

### Option 1: Test from App (Recommended)

Add a test button to your app:

```typescript
// In any screen (e.g., home or debug screen)
import { NotificationTests } from './src/services/test-notifications';

<TouchableOpacity 
  style={styles.testButton}
  onPress={() => NotificationTests.deliveryStarted()}
>
  <Text>Test Delivery Notification</Text>
</TouchableOpacity>
```

### Option 2: Test from Backend

When you update an order status:

```php
use App\Services\OrderNotificationService;

// In your order controller or service
$notificationService = app(OrderNotificationService::class);

// This will automatically send beautiful notifications
$notificationService->sendOrderStatusNotification(
    $order,
    'out_for_delivery',  // new status
    'ready'              // old status
);
```

## 📱 What You'll See

### Android Notification

```
╔══════════════════════════════════════════╗
║ 🛵 Delivery started — ETA 18-22 min     ║
║                                          ║
║ Rider Suman picked up • ORD-68F69EC     ║
║                                          ║
║ ████████████░░░░░░░░░░░ 80%            ║
║                                          ║
║  [Track]  │  [Call rider]  │  [Help]   ║
╚══════════════════════════════════════════╝
```

### iOS Notification

```
╔══════════════════════════════════════════╗
║  Amako Momo          now         🛵      ║
║                                          ║
║  Delivery started — ETA 18-22 min       ║
║  Rider Suman picked up • ORD-68F69EC    ║
║                                          ║
║          [Track]  [Call rider]          ║
╚══════════════════════════════════════════╝
```

## 🔧 Backend Setup (If not done)

Make sure your `Order` model has a relationship to the delivery driver:

```php
// In app/Models/Order.php
public function deliveryDriver()
{
    return $this->belongsTo(User::class, 'driver_id');
}
```

When assigning a driver, set the ETA:

```php
$order->driver_id = $driver->id;
$order->estimated_delivery_time = now()->addMinutes(20);
$order->save();
```

## 🎨 Customization

### Change Brand Color

Edit `amako-shop/src/notifications/delivery-notifications.ts`:

```typescript
color: '#FF6B35', // Your brand orange
```

### Adjust ETA Estimates

Edit `app/Services/OrderNotificationService.php`:

```php
$estimates = [
    'out_for_delivery' => ['min' => 15, 'max' => 25],
    'ready' => ['min' => 20, 'max' => 30],
    // ... adjust as needed
];
```

### Modify Messages

Edit the `getStatusMessage()` method in `app/Services/OrderNotificationService.php`:

```php
'out_for_delivery' => [
    'title' => "🛵 Delivery started{$etaStr}",
    'body' => "{$riderInfo}picked up • {$orderNumber}"
],
```

## 🐛 Troubleshooting

### Notifications Not Showing?

1. **Check permissions**:
   ```typescript
   import * as Notifications from 'expo-notifications';
   const { status } = await Notifications.getPermissionsAsync();
   console.log('Permission status:', status);
   ```

2. **Check channels** (Android):
   ```typescript
   const channels = await Notifications.getNotificationChannelsAsync();
   console.log('Channels:', channels);
   ```

3. **Check logs**:
   - Look for `🔔 [DELIVERY NOTIFICATION]` logs
   - Look for `🔔 [ORDER NOTIFICATION]` logs

### Progress Bar Not Showing?

Progress bars are **Android-only**. iOS doesn't support progress bars in notifications.

### Action Buttons Not Working?

Make sure notification handler is registered:

```typescript
// Should see this log on app start
"🔔 [NOTIFICATION HANDLER] Response handler registered"
```

## 📊 Status to Notification Mapping

| Order Status | Notification Title | Shows ETA? | Shows Rider? |
|-------------|-------------------|-----------|-------------|
| `pending` | 📝 Order received | No | No |
| `confirmed` | ✅ Order confirmed | No | No |
| `preparing` | 👨‍🍳 Preparing your order | No | No |
| `ready` | 📦 Order ready | No | Yes (if assigned) |
| `out_for_delivery` | 🛵 Delivery started — ETA X-Y min | **Yes** | **Yes** |
| `arriving` | 📍 Arriving soon — ETA X-Y min | **Yes** | **Yes** |
| `delivered` | ✅ Delivered! Enjoy your momos | No | No |

## 🎯 Action Button Behaviors

| Button | Action |
|--------|--------|
| **Track** | Opens `/order-tracking/{orderId}` |
| **Call rider** | Opens `tel:{rider_phone}` |
| **Help** | Opens `/help?orderId={orderId}` |
| **Rate now** | Opens `/order/{orderId}?review=true` |

## ✅ Production Checklist

- [x] Backend sends rider info (`rider_name`, `rider_phone`)
- [x] Backend calculates ETA (`eta_min`, `eta_max`)
- [x] Backend sends progress percentage
- [x] Mobile app initializes notification system
- [x] Notification channels created (Android)
- [x] Action buttons registered (iOS)
- [ ] Test on real device (notifications don't work in simulator)
- [ ] Request notification permissions on first launch
- [ ] Register device token with backend

## 📝 Next Steps

1. **Test the notification**: Use the test functions
2. **Verify action buttons work**: Tap each button
3. **Check real order flow**: Place a test order and track it
4. **Verify on both platforms**: iOS and Android
5. **Monitor logs**: Check for any errors

## 🆘 Need Help?

Check the detailed documentation:
- `ENHANCED_DELIVERY_NOTIFICATIONS.md` - Full technical details
- `amako-shop/src/notifications/delivery-notifications.ts` - Notification logic
- `app/Services/OrderNotificationService.php` - Backend service

## 🎉 Done!

Your notifications now look professional and match modern food delivery app standards!

