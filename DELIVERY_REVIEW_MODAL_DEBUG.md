# Delivery Review Modal Debugging Guide

## 🎯 How the Review Modal System Works

### **Flow:**
1. **Driver confirms delivery** on `/delivery` page with photo
2. **Backend sends notification** with `status: 'delivered'` and `show_review_prompt: true`
3. **Mobile app receives notification** via polling (`useNotifications` hook)
4. **Hook checks for new delivered orders** (`useOrderDeliveredNotification`)
5. **Shows "Order Delivered" modal** with celebration
6. **User clicks "Write Review"** → Opens review modal
7. **Order ID saved to AsyncStorage** to prevent showing again

---

## 🐛 Debugging Steps

### **Step 1: Check if notification was received**
Open app and check console logs for:
```
📬 Total notifications: X
🔍 Checking for delivered notifications...
```

### **Step 2: Check notification data**
Look for:
```javascript
{
  status: 'delivered',
  show_review_prompt: true,
  order_id: 6,
  order_number: 'ORD-68F371A7CBEFF'
}
```

### **Step 3: Check if order was already shown**
Look for:
```
📦 Already shown orders: [6, 7, 8]
```

If your order ID is in this list, **the modal won't show again**.

---

## 🔧 How to Reset and Test Again

### **Method 1: Clear AsyncStorage Cache (Recommended)**
In your app console or add this code temporarily:

```javascript
// In metro console or add to your code temporarily
import AsyncStorage from '@react-native-async-storage/async-storage';

// Clear the delivered orders cache
await AsyncStorage.removeItem('shown_delivered_modals');

// Or use the global function (already exposed in the app)
clearDeliveredPopupCache();
```

### **Method 2: Use React Native Debugger**
1. Open React Native Debugger
2. In console, run:
```javascript
clearDeliveredPopupCache()
```
3. Refresh app

### **Method 3: Clear all AsyncStorage (Nuclear option)**
```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';
await AsyncStorage.clear();
```
⚠️ **Warning**: This clears ALL app data including auth tokens!

---

## 📱 Testing Procedure

### **Full Test Flow:**
1. **Clear cache**:
   ```javascript
   clearDeliveredPopupCache()
   ```

2. **Place a new order** (or use existing order)

3. **Set order to "ready"** status in admin panel

4. **Go to delivery dashboard** (`/delivery`)

5. **Accept the order** → Status becomes "out_for_delivery"

6. **Confirm delivery** with photo upload

7. **Open mobile app** (or pull to refresh if already open)

8. **Wait 10 seconds** for notification polling

9. **Check logs** for:
   ```
   🎉 SHOWING Order delivered popup for: ORD-XXXXX
   ✅ Modal state set to visible!
   ```

10. **Modal should appear** with:
    - "🎉 Order Delivered!"
    - Order number
    - "Write Review" button
    - "Close" button

---

## 🔍 Common Issues

### **Issue 1: Modal shows briefly then closes**
**Cause**: AsyncStorage already has this order ID  
**Fix**: Run `clearDeliveredPopupCache()`

### **Issue 2: Modal never shows**
**Cause**: Notification not received or wrong format  
**Check**:
- Backend sent notification with `show_review_prompt: true`
- App is polling notifications (check network tab)
- Order status is "delivered"

### **Issue 3: "WriteReviewModal RENDER, visible: true" but then immediately closes**
**Cause**: The "delivered" modal is showing, not the review modal  
**Flow**: Delivered Modal → Click "Write Review" → Review Modal  
**Check**: Make sure you're clicking "Write Review" button, not just closing

### **Issue 4: Logs show "Already shown orders: [6]"**
**Cause**: You already saw this modal for order #6  
**Fix**: Clear cache or test with a new order

---

## 📊 Expected Logs (Success Case)

```
🔍 Checking for delivered notifications...
📬 Total notifications: 12
📦 Already shown orders: []
📋 Checking notification: { status: 'delivered', show_review_prompt: true }
  → Is delivered: true, Not shown yet: true
✅ Delivered notifications found: 1
🎉 SHOWING Order delivered popup for: ORD-68F371A7CBEFF
📦 Order data: { order_id: 6, order_number: 'ORD-68F371A7CBEFF', status: 'delivered' }
✅ Modal state set to visible!
🎯 OrderDeliveredHandler rendered: { showDeliveredModal: true, orderNumber: 'ORD-68F371A7CBEFF' }
```

---

## 🎯 Quick Test Command

Add this to your app for quick testing:

```typescript
// Add to _layout.tsx or any component temporarily
import { useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

useEffect(() => {
  // Expose clear function globally
  (global as any).testDeliveryModal = async () => {
    await AsyncStorage.removeItem('shown_delivered_modals');
    console.log('✅ Cache cleared! Refresh notifications to see modal.');
  };
  
  console.log('💡 Run testDeliveryModal() to clear cache and test modal');
}, []);
```

Then in console:
```javascript
testDeliveryModal()
```

---

## 🚀 Verify Backend Notification

Check Laravel logs (`storage/logs/laravel.log`) for:

```
[2025-10-18 10:30:00] local.INFO: Mobile notification sent {"user_id":1,"order_id":6,"notification_id":"xxxxx"}
```

Or check database:
```sql
SELECT * FROM notifications 
WHERE notifiable_id = YOUR_USER_ID 
ORDER BY created_at DESC 
LIMIT 5;
```

Look for:
```json
{
  "data": {
    "order_id": 6,
    "status": "delivered",
    "show_review_prompt": true,
    "action": "order_delivered_review"
  }
}
```

---

## ✅ Success Checklist

- [ ] Order confirmed as delivered in database
- [ ] Notification created with `show_review_prompt: true`
- [ ] Mobile app received notification (check network tab)
- [ ] Console shows "SHOWING Order delivered popup"
- [ ] Order ID not in AsyncStorage cache
- [ ] Delivered modal appears
- [ ] Clicking "Write Review" opens review modal
- [ ] Review form can be submitted

---

## 🛠️ Force Show Modal (Development Only)

If you want to bypass all checks and force show the modal:

```typescript
// In OrderDeliveredHandler.tsx or any component
import { useState, useEffect } from 'react';

const [forceShowModal, setForceShowModal] = useState(false);

useEffect(() => {
  // Force show modal after 3 seconds for testing
  setTimeout(() => {
    setForceShowModal(true);
  }, 3000);
}, []);

// Then use forceShowModal || deliveredNotification.showDeliveredModal
```

---

**Remember**: The modal will only show ONCE per order to avoid annoying users. Always clear the cache when testing!

