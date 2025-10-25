# Enhanced Delivery Notifications - Image Style UI/UX

## Overview

Delivery notifications have been enhanced to match the beautiful UI/UX style from the reference image, featuring:

✅ **Clean, professional formatting**  
✅ **ETA display** (e.g., "ETA 18-22 min")  
✅ **Rider information** (e.g., "Rider Suman picked up")  
✅ **Progress bar** visual indicator  
✅ **Action buttons**: Track | Call rider | Help  

## What Changed

### 1. Backend (`app/Services/OrderNotificationService.php`)

#### Enhanced Notification Data

The backend now sends:
- **Rider information**: `rider_name`, `rider_phone`
- **ETA calculations**: `eta_min`, `eta_max`
- **Progress percentage**: Visual indicator (0-100)
- **Clean message formatting**: Matching image style

```php
// Example push data sent to mobile:
[
    'orderId' => 123,
    'order_number' => 'ORD-68F69EC',
    'status' => 'out_for_delivery',
    'rider_name' => 'Suman',
    'rider_phone' => '+977-9841234567',
    'eta_min' => 18,
    'eta_max' => 22,
    'progress' => 80
]
```

#### New Helper Methods

1. **`calculateETA()`**: Calculates estimated delivery time
   - Uses `estimated_delivery_time` from order if available
   - Falls back to status-based estimates
   - Returns buffer range (e.g., 18-22 min)

2. **`getProgressPercent()`**: Maps status to progress %
   - `pending`: 10%
   - `confirmed`: 20%
   - `preparing`: 40%
   - `ready`: 60%
   - `out_for_delivery`: 80%
   - `delivered`: 100%

3. **`getStatusMessage()`**: Enhanced with context
   - Includes rider name when available
   - Shows ETA in title for delivery statuses
   - Format: "Rider {name} picked up • {order_number}"

### 2. Frontend (`amako-shop/src/notifications/delivery-notifications.ts`)

#### Clean Notification Format

**Title Examples** (matching image):
- ✅ "Order received"
- 👨‍🍳 "Preparing your order"
- 🛵 "Delivery started — ETA 18-22 min"
- 📍 "Arriving soon — ETA 3-5 min"
- ✅ "Delivered! Enjoy your momos"

**Body Examples** (matching image):
- "Rider Suman picked up • ORD-68F69EC"
- "Rider Suman on the way • ORD-68F69EC"
- "Fresh momos being made • ORD-68F69EC"
- "Rate your experience • ORD-68F69EC"

#### Action Buttons

Clean, professional button layout:

**During Delivery:**
- `Track` - Opens order tracking
- `Call rider` - Direct call to rider (shown only if phone available)
- `Help` - Support/help screen

**After Delivery:**
- `Rate now` - Opens review modal

#### Progress Bar

Visual progress indicator displayed on Android:
- Shows current delivery stage
- Smooth updates as status changes
- Range: 0-100%

### 3. Integration Service (`amako-shop/src/services/OrderNotificationHandler.ts`)

New service that:
- Intercepts incoming push notifications
- Extracts rider info, ETA, and progress data
- Creates beautiful native notifications
- Handles notification actions

## Notification Flow

### 1. Order Status Changes
```
Backend detects status change
       ↓
OrderNotificationService calculates ETA & progress
       ↓
Push notification sent with enhanced data
       ↓
Mobile app receives notification
       ↓
OrderNotificationHandler processes data
       ↓
Beautiful native notification displayed
```

### 2. User Interaction
```
User sees notification with progress bar
       ↓
Taps "Track" button
       ↓
App opens order tracking screen
       ↓
Real-time delivery updates
```

## Notification Appearance

### Android

```
┌────────────────────────────────────────┐
│ 🛵 Delivery started — ETA 18-22 min   │
│                                        │
│ Rider Suman picked up • ORD-68F69EC   │
│                                        │
│ ████████████░░░░░░░░░░░ 80%          │
│                                        │
│ [Track] │ [Call rider] │ [Help]      │
└────────────────────────────────────────┘
```

### iOS

```
┌────────────────────────────────────────┐
│ Amako Momo          now   🛵           │
│                                        │
│ Delivery started — ETA 18-22 min      │
│ Rider Suman picked up • ORD-68F69EC   │
│                                        │
│               [Track] [Call rider]     │
└────────────────────────────────────────┘
```

## Key Features

### 1. **Threading** (Same notification updates)
- Each order has unique identifier: `order:{orderId}`
- Status updates replace previous notification
- Clean notification shade (no spam)

### 2. **Priority Levels**
- **Heads-up** (Important): picked_up, out_for_delivery, arriving, delivered
- **Silent** (Less critical): pending, confirmed, preparing

### 3. **Smart ETA Display**
- Shows ETA only when relevant (delivery in progress)
- Range format: "18-22 min" (not "20 min" to manage expectations)
- Updates as delivery progresses

### 4. **Rider Context**
- Shows rider name when assigned
- "Call rider" button appears only if phone available
- Personal touch improves trust

### 5. **Progress Visualization**
- Android: Progress bar in notification
- iOS: Relevance score for notification ordering
- Clear visual feedback

## Status Messages

| Status | Title | Body Example |
|--------|-------|--------------|
| `pending` | 📝 Order received | Order ORD-68F69EC • We're processing your order |
| `confirmed` | ✅ Order confirmed | Order ORD-68F69EC • Kitchen preparing your momos |
| `preparing` | 👨‍🍳 Preparing your order | Fresh momos being made • ORD-68F69EC |
| `ready` | 📦 Order ready | Rider Suman will pick up soon • ORD-68F69EC |
| `out_for_delivery` | 🛵 Delivery started — ETA 18-22 min | Rider Suman picked up • ORD-68F69EC |
| `arriving` | 📍 Arriving soon — ETA 3-5 min | Rider Suman arriving • ORD-68F69EC |
| `delivered` | ✅ Delivered! Enjoy your momos | Rate your experience • ORD-68F69EC |

## Setup Required

### Backend (Already done ✅)

1. Ensure `Order` model has relationships:
   ```php
   public function deliveryDriver() {
       return $this->belongsTo(User::class, 'driver_id');
   }
   ```

2. Set `estimated_delivery_time` when assigning driver:
   ```php
   $order->estimated_delivery_time = now()->addMinutes(25);
   $order->save();
   ```

### Mobile App

1. **Initialize on app start**:
   ```typescript
   import { initializeDeliveryNotifications } from './src/notifications/delivery-notifications';
   import { setupOrderNotificationListener } from './src/services/OrderNotificationHandler';
   
   // In your app initialization (App.tsx or similar)
   await initializeDeliveryNotifications();
   setupOrderNotificationListener();
   ```

2. **Request permissions**:
   ```typescript
   import * as Notifications from 'expo-notifications';
   
   const { status } = await Notifications.requestPermissionsAsync();
   ```

## Testing

### Test Different Statuses

```typescript
import { upsertOrderNotification } from './src/notifications/delivery-notifications';

// Test delivery with all data
await upsertOrderNotification(123, 'out_for_delivery', {
  orderNumber: 'ORD-68F69EC',
  riderName: 'Suman',
  riderPhone: '+977-9841234567',
  etaMin: [18, 22],
  percent: 80
});

// Test without rider (early stages)
await upsertOrderNotification(123, 'preparing', {
  orderNumber: 'ORD-68F69EC',
  percent: 40
});

// Test delivered
await upsertOrderNotification(123, 'delivered', {
  orderNumber: 'ORD-68F69EC',
  percent: 100
});
```

### Check Notification Appearance

1. **Android**: Look for progress bar and action buttons
2. **iOS**: Check notification grouping and action buttons
3. **Both**: Verify ETA shows correctly and rider name displays

## Benefits

✅ **Professional appearance** - Matches modern food delivery apps  
✅ **Better UX** - Users know exactly what's happening  
✅ **Clear actions** - Easy to track or call rider  
✅ **Visual feedback** - Progress bar shows delivery stage  
✅ **Personal touch** - Rider name builds trust  
✅ **Smart timing** - Heads-up only for important updates  
✅ **Clean notification shade** - Updates replace, don't spam  

## Notes

- Progress bar only shows on Android (iOS limitation)
- Action buttons limited to 3-4 for best UX
- ETA automatically calculates buffer (±5 min)
- All emojis are consistent with brand personality
- Notification color matches brand (#FF6B35 orange)

## Status

✅ **COMPLETE** - Production ready  
🎨 **UI/UX** - Matches reference image style  
📱 **Cross-platform** - Works on iOS & Android  
🔔 **Professional** - Enterprise-grade notifications

