# Push Notifications Setup Guide

This guide covers the complete setup of the push notification system for the Amako Shop app.

## 🚀 **What's Been Implemented**

### **1. Database Schema**
- **`devices` table**: Stores device tokens for push notifications
- **Fields**: `id`, `user_id`, `token`, `platform`, `last_used_at`, `timestamps`
- **Indexes**: Optimized for user lookups and platform filtering

### **2. Backend Components**
- **`Device` model**: Eloquent model with relationships and scopes
- **`DeviceController`**: API endpoint for device registration (`POST /devices`)
- **`ExpoPushService`**: Service for sending notifications via Expo's push service
- **Service binding**: Registered in `AppServiceProvider`

### **3. Frontend Integration**
- **`NotificationsProvider`**: React Native context for notification management
- **`Toast` component**: In-app notification display
- **Device registration**: Automatic token upload when authenticated
- **Navigation**: Automatic routing to order screens on notification tap

### **4. Order Status Notifications**
- **Admin order updates**: Push notifications when staff change order status
- **Payment completion**: Notifications when orders are marked as paid
- **Status tracking**: Real-time updates for order progress

## 📋 **Setup Instructions**

### **Step 1: Run Database Migration**

Since `php artisan` commands are failing due to OpenAI configuration, use the manual migration script:

```bash
cd my_momo_shop
php scripts/run-devices-migration.php
```

This will create the `devices` table with proper structure and indexes.

### **Step 2: Verify Database Structure**

The migration script will show the table structure. You should see:

```
Table structure:
  - id: bigint(20) unsigned
  - user_id: bigint(20) unsigned
  - token: varchar(255)
  - platform: varchar(10)
  - last_used_at: timestamp
  - created_at: timestamp
  - updated_at: timestamp
```

### **Step 3: Test Device Registration**

The React Native app will automatically register devices when users log in. You can verify this by:

1. **Check the database**:
```sql
SELECT * FROM devices;
```

2. **Check Laravel logs** for device registration:
```bash
tail -f storage/logs/laravel.log | grep "Device token registered"
```

### **Step 4: Test Push Notifications**

#### **Option A: Use the Test Route (Development Only)**
```bash
# Send a test notification to your device
curl -X POST http://localhost:8000/api/notify/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

#### **Option B: Update an Order Status**
1. Go to admin panel
2. Update any order status (e.g., from "pending" to "ready")
3. Check your device for the push notification

## 🔧 **Configuration**

### **Environment Variables**
No additional environment variables are required. The system uses:
- **Expo Push Service**: `https://exp.host/--/api/v2/push/send`
- **Laravel Sanctum**: For API authentication

### **Service Bindings**
The `ExpoPushService` is automatically bound in `AppServiceProvider`:
```php
$this->app->singleton(\App\Services\ExpoPushService::class);
```

## 📱 **How It Works**

### **1. Device Registration Flow**
```
User Login → Generate Expo Token → POST /devices → Store in Database
```

### **2. Notification Flow**
```
Order Status Change → Find User's Devices → Send via Expo → Display on Device
```

### **3. Navigation Flow**
```
User Taps Notification → Extract orderId → Navigate to /order/[id]
```

## 🧪 **Testing**

### **Test Notification Data Structure**
```json
{
  "to": "expo-push-token",
  "title": "Order AMK-12345",
  "body": "Status: ready",
  "data": {
    "orderId": "12345",
    "code": "AMK-12345",
    "status": "ready"
  },
  "sound": "default",
  "channelId": "default"
}
```

### **Debug Logs**
The system provides comprehensive logging:

```bash
# Device registration
grep "Device token registered" storage/logs/laravel.log

# Push notification sending
grep "Push notifications sent" storage/logs/laravel.log

# Order status updates
grep "Push notification sent for order" storage/logs/laravel.log
```

## 🚨 **Troubleshooting**

### **Common Issues**

#### **1. "No tokens found" Error**
- **Cause**: User hasn't registered any devices
- **Solution**: Ensure the React Native app is properly configured and users are logging in

#### **2. "Failed to send push notification" Error**
- **Cause**: Expo service is down or network issues
- **Solution**: Check network connectivity and Expo service status

#### **3. "Device registration failed" Error**
- **Cause**: Database connection issues or validation failures
- **Solution**: Check database connectivity and validate request data

### **Debug Steps**

1. **Check device registration**:
```sql
SELECT COUNT(*) FROM devices WHERE user_id = [USER_ID];
```

2. **Verify API endpoint**:
```bash
curl -X POST http://localhost:8000/api/devices \
  -H "Authorization: Bearer [TOKEN]" \
  -H "Content-Type: application/json" \
  -d '{"token":"test","platform":"android"}'
```

3. **Check Laravel logs**:
```bash
tail -f storage/logs/laravel.log | grep -E "(Device|Push|Notification)"
```

## 🔄 **API Endpoints**

### **Device Management**
- **`POST /api/devices`**: Register/update device token
  - **Auth**: Required (auth:sanctum)
  - **Body**: `{ "token": "string", "platform": "android|ios" }`
  - **Response**: 204 No Content

### **Test Notifications (Dev Only)**
- **`POST /api/notify/test`**: Send test notification
  - **Auth**: Required (auth:sanctum)
  - **Response**: `{ "ok": true }` or `{ "msg": "no tokens" }`

## 📊 **Monitoring**

### **Key Metrics to Track**
- **Device registration rate**: How many users register devices
- **Notification delivery rate**: Success/failure of push notifications
- **User engagement**: How often users tap on notifications
- **Order completion rate**: Correlation with notification delivery

### **Log Analysis**
```bash
# Count successful device registrations
grep -c "Device token registered" storage/logs/laravel.log

# Count successful push notifications
grep -c "Push notifications sent successfully" storage/logs/laravel.log

# Count notification errors
grep -c "Failed to send push notification" storage/logs/laravel.log
```

## 🎯 **Next Steps**

1. **Run the migration**: `php scripts/run-devices-migration.php`
2. **Test device registration**: Log in with the React Native app
3. **Test notifications**: Update an order status in admin panel
4. **Monitor logs**: Check for any errors or issues
5. **Scale up**: Extend to other notification types (promotions, updates, etc.)

## 🆘 **Support**

If you encounter issues:

1. **Check the logs first**: `storage/logs/laravel.log`
2. **Verify database structure**: Ensure `devices` table exists
3. **Test API endpoints**: Use the test routes provided
4. **Check React Native logs**: Look for device registration errors

The system is designed to be robust and fail-safe, so notifications won't break other functionality even if there are issues.
