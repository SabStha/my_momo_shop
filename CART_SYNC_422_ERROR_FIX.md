# Cart Sync 422 Error & Cart Crash Fix

## Problem Summary

The app was experiencing two critical cart-related errors:

1. **422 Cart Sync Error**: Backend rejecting cart sync with validation error `items.0.id` is required
2. **Cart Screen Crash**: `TypeError: item.itemId.startsWith is not a function` - `itemId` was undefined

## Root Cause

Corrupted cart items were persisted in AsyncStorage where `itemId` was `undefined`. This caused:
- Backend validation to fail (422 error) because it requires `items.*.id` to be a `required|string`
- Cart screen to crash when trying to call `.startsWith()` on undefined

## Files Modified

### 1. `amako-shop/src/state/cart-sync.ts`

#### Changes Made:

**a) Enhanced `cartLineToServerItem()` function:**
- Changed return type from `ServerCartItem` to `ServerCartItem | null`
- Added validation to check all required fields (`itemId`, `name`, `unitBasePrice.amount`, `qty`)
- Returns `null` for invalid items with warning log

**b) Improved `syncWithServer()` function:**
- Filters out invalid items before syncing
- Automatically removes corrupted items from cart state
- Handles 422 validation errors by cleaning up corrupted items
- Prevents sending empty or invalid data to backend

**c) Enhanced `loadFromServer()` function:**
- Validates items loaded from server
- Filters out any invalid items
- Logs warning when invalid items are detected

**d) Added `onRehydrateStorage` callback:**
- Automatically cleans up corrupted items when loading from AsyncStorage
- Recalculates subtotal, item count, and isEmpty state
- Logs warning about cleaned up items

### 2. `amako-shop/app/cart.tsx`

#### Changes Made:

**Fixed `hasBulkItems` check (line 158):**
```typescript
// Before (would crash if itemId is undefined):
const hasBulkItems = items.some(item => item.itemId.startsWith('bulk-'));

// After (safe check with validation):
const hasBulkItems = items.some(item => 
  item.itemId && 
  typeof item.itemId === 'string' && 
  item.itemId.startsWith('bulk-')
);
```

## How It Works

### Data Validation Flow

1. **On App Start** (Storage Rehydration):
   - `onRehydrateStorage` checks all persisted items
   - Filters out items with missing `itemId`, `name`, `unitBasePrice`, or invalid `qty`
   - Updates cart state with only valid items

2. **During Cart Sync**:
   - Before sending to server, filters out invalid items
   - If invalid items found, updates local state immediately
   - Converts valid items to server format
   - Only sends valid items to backend

3. **On Server Load**:
   - Receives items from server
   - Validates each item during conversion
   - Filters out any invalid items
   - Updates cart with clean data

4. **On 422 Error**:
   - Detects validation error from backend
   - Filters cart items again
   - Removes any items that fail validation
   - Updates cart state with clean items

### Validation Criteria

An item is considered **valid** if:
- `itemId` exists and is truthy
- `name` exists and is truthy
- `unitBasePrice.amount` is defined (can be 0)
- `qty` is greater than 0

## Testing

To verify the fix:

1. **Test Cart Screen**: Navigate to `/cart` - should not crash
2. **Test Cart Sync**: Add items to cart - should sync without 422 errors
3. **Test with Corrupted Data**: 
   - App should automatically clean up invalid items on startup
   - Should log warnings about cleaned items
4. **Test Empty Cart**: Clear cart - should not cause errors

## Prevention

The fix includes multiple layers of protection:

1. **Validation at source**: Check items before converting to server format
2. **Validation during sync**: Filter invalid items before sending
3. **Validation on load**: Filter invalid items from server response
4. **Validation on rehydration**: Clean up persisted corrupted data
5. **Safe UI checks**: Use optional chaining and type checks in UI

## Logs to Watch For

The fix adds helpful console logs:

- `⚠️ Invalid cart item, skipping:` - Invalid item detected during conversion
- `⚠️ Found and removed X invalid cart items` - Invalid items removed before sync
- `⚠️ Filtered out X invalid items from server` - Invalid items filtered from server response
- `⚠️ Cleaned up X corrupted items from persisted storage` - Corrupted items removed on app start
- `🛒 [SYNC] Validation error 422 - checking for corrupted items` - Handling validation error

## Expected Behavior

After this fix:
- ✅ Cart screen loads without crashes
- ✅ Cart syncs successfully (no 422 errors)
- ✅ Corrupted items are automatically removed
- ✅ User experience is uninterrupted
- ✅ Clear warning logs for debugging

## Related Files

- `app/Http/Controllers/Api/CartSyncController.php` - Backend validation (line 64-72)
- `amako-shop/src/state/cart-sync.ts` - Cart state management
- `amako-shop/app/cart.tsx` - Cart UI component

## Status

✅ **FIXED** - All validation and safety checks in place

