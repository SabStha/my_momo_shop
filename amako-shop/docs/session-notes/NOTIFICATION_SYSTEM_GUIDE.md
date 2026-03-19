# 🔔 Notification System Guide

This guide covers the complete notification system implementation for the Amako Shop mobile app, matching the functionality from the web application.

## ✅ **What's Been Implemented**

### **1. Backend API Endpoints**
- **GET `/api/notifications`** - Fetch user notifications with pagination
- **POST `/api/notifications/mark-as-read`** - Mark specific notification as read
- **POST `/api/notifications/mark-all-as-read`** - Mark all notifications as read
- **DELETE `/api/notifications/{id}`** - Delete specific notification
- **GET `/api/notifications/churn-risks`** - Get churn risk notifications

### **2. Mobile App Components**

#### **API Layer**
- **`src/api/notifications.ts`** - API service functions
- **`src/hooks/useNotifications.ts`** - React Query hooks for data fetching
- **`src/state/notifications.ts`** - Zustand store for local state

#### **UI Components**
- **`src/components/notifications/NotificationCard.tsx`** - Individual notification card
- **`app/(tabs)/notifications.tsx`** - Main notifications screen
- **Updated navigation components** - BottomBar and TopBar with notification badges

### **3. Features Implemented**

#### **Notification Types**
- ✅ **Order Notifications** - Order status updates, confirmations
- ✅ **Payment Notifications** - Payment confirmations, failures
- ✅ **Promotion Notifications** - Special offers, discounts
- ✅ **System Notifications** - App updates, maintenance
- ✅ **Churn Risk Notifications** - Customer retention alerts

#### **User Interface**
- ✅ **Notification List** - Paginated list with pull-to-refresh
- ✅ **Unread Indicators** - Badge counts in navigation
- ✅ **Mark as Read** - Individual and bulk actions
- ✅ **Delete Notifications** - Remove unwanted notifications
- ✅ **Empty State** - Friendly message when no notifications
- ✅ **Loading States** - Proper loading indicators
- ✅ **Error Handling** - Graceful error states

#### **Navigation Integration**
- ✅ **Bottom Tab** - Notifications tab with unread badge
- ✅ **Top Bar** - Notification bell with count
- ✅ **Deep Linking** - Navigate to specific screens from notifications

## 🚀 **How to Use**

### **For Users**
1. **View Notifications**: Tap the bell icon in the top bar or notifications tab
2. **Mark as Read**: Tap individual notifications or use "Mark All Read"
3. **Delete Notifications**: Swipe or use the delete button
4. **Navigate**: Tap notifications to go to related screens (orders, payments, etc.)

### **For Developers**
1. **Add New Notification Types**: Update the `Notification` interface in `src/api/notifications.ts`
2. **Customize Styling**: Modify styles in component files
3. **Add Navigation**: Update `handleNotificationPress` in notifications screen
4. **Test Notifications**: Use the API endpoints to create test notifications

## 📱 **Notification Types & Icons**

| Type | Icon | Color | Description |
|------|------|-------|-------------|
| Order | 🛒 | Blue | Order status updates |
| Payment | 💳 | Green | Payment confirmations |
| Promotion | 🎁 | Yellow | Special offers |
| System | ⚙️ | Gray | App updates |
| Churn | ⚠️ | Red | Customer retention |

## 🔧 **API Integration**

### **Fetch Notifications**
```typescript
const { data, isLoading, error } = useNotifications(page, perPage);
```

### **Mark as Read**
```typescript
const markAsRead = useMarkAsRead();
await markAsRead.mutateAsync(notificationId);
```

### **Get Unread Count**
```typescript
const { unreadCount, hasUnread } = useUnreadCount();
```

## 🎨 **UI Components**

### **NotificationCard Features**
- **Visual Indicators**: Different icons and colors for notification types
- **Read/Unread States**: Visual distinction between read and unread
- **Time Display**: Relative time (e.g., "2h ago", "1d ago")
- **Actions**: Mark as read and delete buttons
- **Responsive Design**: Adapts to different screen sizes

### **Navigation Badges**
- **Top Bar**: Bell icon with unread count
- **Bottom Tab**: Notifications tab with badge
- **Real-time Updates**: Badges update automatically

## 📊 **State Management**

### **React Query Integration**
- **Caching**: Notifications are cached for 30 seconds
- **Background Updates**: Automatic refetching when needed
- **Optimistic Updates**: UI updates immediately for better UX

### **Zustand Store**
- **Local State**: Manages notification preferences
- **Persistence**: Settings are saved locally
- **Real-time**: Updates across components

## 🔄 **Data Flow**

1. **User Opens App** → Check for unread notifications
2. **API Call** → Fetch notifications from backend
3. **State Update** → Update local state and UI
4. **User Interaction** → Mark as read, delete, navigate
5. **Optimistic Update** → Update UI immediately
6. **API Sync** → Sync changes with backend

## 🛠️ **Customization**

### **Adding New Notification Types**
1. Update the `Notification` interface
2. Add new icon and color mappings
3. Update the notification card component
4. Add navigation logic for new types

### **Styling Customization**
- **Colors**: Modify color tokens in `src/ui/tokens.ts`
- **Spacing**: Update spacing values
- **Typography**: Change font sizes and weights
- **Animations**: Add custom animations

## 🧪 **Testing**

### **Manual Testing**
1. **Create Test Notifications**: Use the API to create sample notifications
2. **Test Navigation**: Verify deep linking works correctly
3. **Test Actions**: Mark as read, delete, refresh
4. **Test States**: Empty state, loading, error states

### **API Testing**
```bash
# Get notifications
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/notifications

# Mark as read
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"notification_id":"1"}' \
  http://localhost:8000/api/notifications/mark-as-read
```

## 📈 **Performance Optimizations**

- **Pagination**: Load notifications in batches
- **Caching**: React Query caching reduces API calls
- **Optimistic Updates**: Immediate UI feedback
- **Lazy Loading**: Load notifications on demand
- **Memory Management**: Proper cleanup of listeners

## 🔒 **Security Features**

- **Authentication**: All endpoints require valid tokens
- **Authorization**: Users can only access their own notifications
- **Input Validation**: Proper validation of all inputs
- **Rate Limiting**: API endpoints are rate limited

## 🎯 **Future Enhancements**

- **Push Notifications**: Real-time push notifications
- **Notification Preferences**: User-customizable settings
- **Rich Notifications**: Images and actions in notifications
- **Notification History**: Extended history with search
- **Bulk Actions**: Select multiple notifications for actions

---

**🎉 Your notification system is now fully functional and matches the web application!**
