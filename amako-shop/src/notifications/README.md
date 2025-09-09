# Notifications System

This module provides push notification functionality for the Amako Shop app using Expo Notifications.

## Features

- **Push Notification Permissions**: Requests notification permissions once per session
- **Expo Push Token**: Automatically generates and manages Expo push tokens
- **Android Channel**: Creates a default notification channel for Android
- **Device Registration**: Automatically registers device tokens with the backend when authenticated
- **In-App Notifications**: Shows lightweight toast notifications for order updates
- **Order Tracking**: Stores order IDs from notifications in memory for quick access

## Setup

### 1. EAS Project ID

You need to set your EAS project ID in `app.json`:

```json
{
  "expo": {
    "extra": {
      "eas": {
        "projectId": "your-actual-eas-project-id"
      }
    }
  }
}
```

### 2. Backend API Endpoint

The system expects a `POST /devices` endpoint that accepts:

```json
{
  "token": "expo-push-token",
  "platform": "android" | "ios"
}
```

### 3. App Layout Integration

The `NotificationsProvider` is already integrated in `app/_layout.tsx`:

```tsx
<QueryClientProvider client={queryClient}>
  <SessionProvider>
    <NotificationsProvider>
      <RouteGuard>
        <Stack screenOptions={{ headerShown: false }} />
      </RouteGuard>
    </NotificationsProvider>
  </SessionProvider>
</QueryClientProvider>
```

## Usage

### Using the Hook

```tsx
import { useNotifications } from '../src/notifications';

function MyComponent() {
  const { lastNotification, expoPushToken, openLastNotification } = useNotifications();
  
  // Check if there's a new notification
  if (lastNotification?.orderId) {
    console.log('New order update:', lastNotification.orderId, 'Status:', lastNotification.status);
  }
  
  // Access the push token
  console.log('Push token:', expoPushToken);
  
  // Navigate to order when notification is tapped
  const handleNotificationTap = () => {
    if (lastNotification?.orderId) {
      openLastNotification();
    }
  };
  
  return <View>...</View>;
}
```

### Sending Test Notifications

To test the system, you can send a push notification with:

```json
{
  "to": "expo-push-token",
  "title": "Order Update",
  "body": "Your order has been updated",
  "data": {
    "orderId": "12345",
    "status": "ready"
  }
}
```

### Notification Data Structure

Order notifications now include:
- `orderId`: The order identifier
- `status`: The order status (e.g., "ready", "preparing", "delivered")

### Navigation Helper

The `openLastNotification()` function automatically navigates to the order screen:
```tsx
const { openLastNotification } = useNotifications();

// Navigate to order screen
openLastNotification();
```

## Components

### NotificationsProvider

- Manages notification permissions
- Generates Expo push tokens
- Registers devices with backend
- Listens for incoming notifications
- Provides notification context

### Toast

- Lightweight in-app notification component
- Slides in from top for 3 seconds
- Automatically shows for order updates with status
- Displays format: "Order AMK-{orderId} is now {STATUS}"
- Positioned absolutely at the top of the screen

## API Integration

The system automatically:

1. **Requests permissions** when the app starts
2. **Creates Android channels** for proper notification display
3. **Generates push tokens** using your EAS project ID
4. **Registers devices** with your backend when users are authenticated
5. **Listens for notifications** and shows toasts for order updates
6. **Handles notification taps** and navigates to order screens automatically

## Troubleshooting

### Common Issues

1. **"EAS Project ID not found"**: Make sure you've set the project ID in `app.json`
2. **"Permission denied"**: User denied notification permissions
3. **"Device registration failed"**: Check your backend `/devices` endpoint
4. **"Toast not showing"**: Ensure the Toast component is properly mounted

### Debug Logs

In development mode, the system logs detailed information:

```
🔔 NotificationsProvider: Setting up notifications...
🔔 NotificationsProvider: Requesting notification permissions...
🔔 NotificationsProvider: Notification permissions granted
🔔 NotificationsProvider: Android channel created
🔔 NotificationsProvider: Getting Expo push token...
🔔 NotificationsProvider: Expo push token received: [token]
🔔 NotificationsProvider: Registering device token...
🔔 NotificationsProvider: Device registered successfully
```

## Next Steps

1. **Replace EAS Project ID**: Update `app.json` with your actual project ID
2. **Implement Backend**: Create the `/devices` endpoint in your Laravel API
3. **Test Notifications**: Send test push notifications to verify the system
4. **Customize Toast**: Modify the toast appearance and behavior as needed
5. **Add More Data**: Extend the notification data structure for other use cases
