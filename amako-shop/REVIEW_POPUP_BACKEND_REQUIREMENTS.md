# 📝 Review Popup Backend Requirements

## ✅ Frontend is Ready!

The review popup system is fully implemented and working. It automatically shows when:
1. User's order status changes to `delivered`
2. They receive a notification from the backend

## 🔧 Backend Requirements

When an order status changes to `delivered`, send a notification via `/api/notifications` with this structure:

```json
{
  "user_id": 123,
  "title": "Order Delivered!",
  "message": "Your order has been delivered. How was your experience?",
  "data": {
    "type": "order_delivered",
    "status": "delivered",
    "show_review_prompt": true,
    "order_id": 456,
    "order_number": "ORD-2024-001"
  }
}
```

### Required Fields:
- **`data.status`**: Must be `"delivered"`
- **`data.show_review_prompt`**: Must be `true`
- **`data.order_id`**: The order ID (number)
- **`data.order_number`**: The order number string (for display)

## 🧪 Testing

To test the popup without backend changes:

1. **Clear cache** (in app console):
   ```javascript
   clearDeliveredPopupCache()
   ```

2. **Create test notification** via Laravel:
   ```php
   // In your OrderController or wherever you mark orders as delivered:
   
   Notification::create([
       'user_id' => $order->user_id,
       'title' => 'Order Delivered!',
       'message' => 'Your order has been delivered',
       'data' => [
           'type' => 'order_delivered',
           'status' => 'delivered',
           'show_review_prompt' => true,
           'order_id' => $order->id,
           'order_number' => $order->order_number,
       ],
       'is_read' => false,
   ]);
   ```

## 📱 How It Works

1. Mobile app polls `/api/notifications` every few seconds
2. `useOrderDeliveredNotification` hook watches for new notifications
3. When it finds a delivered notification, it shows the popup
4. User can write a review or dismiss
5. Popup won't show again for the same order (tracked in AsyncStorage)

## 🎨 UI Flow

```
Order Delivered → Notification → Popup appears:
                                 ├─ "Write Review" → Review Modal
                                 └─ "Maybe Later" → Dismiss
```

All UI components are ready:
- ✅ `OrderDeliveredModal` - Celebration popup
- ✅ `WriteReviewModal` - Review form with validation
- ✅ `OrderDeliveredHandler` - Orchestrates the flow
- ✅ `useOrderDeliveredNotification` - Detects delivered orders

**Backend just needs to send the notification!** 🚀

