# Orders Page Not Showing New Orders - Fix

## Problem Summary

The orders page in the mobile app was only showing old orders. When users placed a new order, it wouldn't appear in the orders list even after refreshing.

## Root Cause

The orders page was using a **local Zustand store** (`useOrders()`) instead of fetching data from the backend API. When a new order was created:

1. ✅ Order was saved to the backend database
2. ❌ Order was NOT saved to the local Zustand store
3. ❌ Orders page displayed only local store data (old orders)
4. ❌ New orders were invisible to the user

### Before
```typescript
// amako-shop/app/orders.tsx
import { useOrders, useRefreshOrders } from '../src/state/orders';

export default function OrdersScreen() {
  // Get orders from Zustand store (local only - NOT synced with backend)
  const orders = useOrders();
  const refreshOrders = useRefreshOrders();
  
  // This refresh only updated local state, not backend data
  const handleRefresh = async () => {
    setRefreshing(true);
    refreshOrders();
    setTimeout(() => setRefreshing(false), 1000);
  };
  // ...
}
```

## Solution Implemented

### 1. Updated Orders Page to Use Backend API

Changed the orders page to fetch data directly from the backend using React Query:

```typescript
// amako-shop/app/orders.tsx
import { useBackendOrders } from '../src/hooks/useOrders';

export default function OrdersScreen() {
  // Get orders from backend API (real-time data)
  const { data: orders = [], isLoading, error, refetch } = useBackendOrders();
  
  const handleRefresh = async () => {
    setRefreshing(true);
    try {
      await refetch(); // Fetches fresh data from backend
    } catch (err) {
      console.error('Failed to refresh orders:', err);
    } finally {
      setRefreshing(false);
    }
  };
  // ...
}
```

**Key Changes:**
- ✅ Switched from local Zustand store to backend API
- ✅ Added loading and error states
- ✅ Proper refresh functionality that fetches from backend
- ✅ Auto-refresh every 15 seconds (configured in `useBackendOrders`)

### 2. Updated Order Card Rendering

Updated the order card to use the backend order format:

```typescript
const renderOrderCard = (order: any) => {
  return (
    <TouchableOpacity onPress={() => router.push(`/order/${order.id}`)}>
      <Text>{order.order_number || `Order #${order.id}`}</Text>
      <Text>{formatDate(order.created_at)}</Text>  {/* Backend format */}
      <Text>Rs. {(order.total || order.total_amount || order.grand_total || 0).toFixed(2)}</Text>
    </TouchableOpacity>
  );
};
```

**Backend Order Format:**
- `id`: number (e.g., 123)
- `order_number`: string (e.g., "ORD-2025-001")
- `created_at`: ISO string (e.g., "2025-10-10T12:00:00Z")
- `total` / `total_amount` / `grand_total`: number
- `status`: 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

### 3. Added Loading and Error States

```typescript
// Show loading state
if (isLoading && !refreshing) {
  return (
    <View style={styles.loadingContainer}>
      <ActivityIndicator size="large" />
      <Text>Loading your orders...</Text>
    </View>
  );
}

// Show error state
if (error) {
  return (
    <View style={styles.errorStateContainer}>
      <Ionicons name="alert-circle-outline" size={64} />
      <Text>Failed to Load Orders</Text>
      <TouchableOpacity onPress={() => refetch()}>
        <Text>Try Again</Text>
      </TouchableOpacity>
    </View>
  );
}
```

### 4. Invalidate Cache After Order Creation

Updated the payment page to invalidate the orders cache after successfully creating an order:

```typescript
// amako-shop/app/payment.tsx
import { useQueryClient } from '@tanstack/react-query';

export default function PaymentScreen() {
  const queryClient = useQueryClient();
  
  const handlePayment = async () => {
    // ... create order ...
    
    // Invalidate orders cache so the orders page will show the new order
    queryClient.invalidateQueries({ queryKey: ['orders'] });
    console.log('✅ Orders cache invalidated - new order will appear in orders list');
    
    // ... show success modal ...
  };
}
```

**How It Works:**
1. User creates a new order
2. Order is saved to backend
3. Payment page invalidates the `['orders']` query cache
4. Orders page automatically refetches from backend (because cache is invalid)
5. New order appears in the list immediately

## Benefits

### Before
- 🔴 Only showed old orders from local store
- 🔴 New orders didn't appear
- 🔴 Manual refresh didn't help (only refreshed local state)
- 🔴 No loading or error states
- 🔴 Data could be stale or out of sync

### After
- ✅ Shows real-time orders from backend
- ✅ New orders appear immediately after creation
- ✅ Pull-to-refresh fetches fresh data
- ✅ Auto-refresh every 15 seconds
- ✅ Loading state while fetching
- ✅ Error state with retry button
- ✅ Data is always in sync with backend

## React Query Configuration

The `useBackendOrders` hook (in `amako-shop/src/hooks/useOrders.ts`) is configured with:

```typescript
export function useBackendOrders() {
  return useQuery({
    queryKey: ['orders'],
    queryFn: getUserOrders,
    staleTime: 10000,          // Data considered fresh for 10 seconds
    refetchInterval: 15000,    // Auto-refresh every 15 seconds
    refetchOnWindowFocus: true, // Refresh when user returns to app
  });
}
```

This ensures orders are always up-to-date without the user having to manually refresh.

## Files Changed

1. **`amako-shop/app/orders.tsx`**
   - Switched from `useOrders()` to `useBackendOrders()`
   - Updated order card rendering for backend format
   - Added loading and error states
   - Improved refresh functionality

2. **`amako-shop/app/payment.tsx`**
   - Added `useQueryClient()` hook
   - Invalidate orders cache after successful order creation
   - Ensures orders list updates immediately

## Testing

To verify the fix works:

1. **Create a new order:**
   - Go to menu, add items to cart
   - Proceed to checkout and payment
   - Complete the order

2. **Check orders page:**
   - Navigate to "My Orders"
   - New order should appear at the top immediately
   - Order details should match what was created

3. **Test refresh:**
   - Pull down to refresh
   - Orders should update from backend

4. **Test auto-refresh:**
   - Leave orders page open
   - Create a new order from another device/browser
   - After ~15 seconds, new order should appear automatically

5. **Test error handling:**
   - Turn off internet connection
   - Try to view orders
   - Should see error message with retry button
   - Turn on internet and tap retry
   - Orders should load successfully

## Migration Notes

The old Zustand orders store (`amako-shop/src/state/orders.ts`) is still in the codebase but is no longer used by the orders page. It can be safely removed in a future cleanup if no other components depend on it.

**To check if safe to remove:**
```bash
# Search for usage of Zustand orders store
grep -r "useOrders\|useOrderStore\|useRefreshOrders" amako-shop/
```

If only the orders state file itself references these hooks, it's safe to delete.







