# Cart Store Documentation

## Overview
The cart store is implemented using Zustand with persistence via AsyncStorage. It provides a complete shopping cart solution with add, remove, update, and clear functionality, along with computed selectors for subtotal and item count.

## Features

### 🛒 **Core Functionality**
- Add items to cart (with quantity management)
- Remove individual items
- Update item quantities
- Clear entire cart
- Persistent storage across app sessions

### 📊 **Smart Selectors**
- **Subtotal**: Automatically calculated from items and quantities
- **Item Count**: Total number of items (sum of quantities)
- **Empty State**: Check if cart has no items
- **Individual Quantities**: Get quantity for specific items

### 💾 **Persistence**
- Automatic saving to AsyncStorage
- Survives app restarts
- Efficient partial state persistence

## Store Structure

### State Interface
```typescript
interface CartStore {
  items: CartItem[];
  addItem: (item: Omit<CartItem, 'quantity'>) => void;
  removeItem: (itemId: string) => void;
  updateQuantity: (itemId: string, quantity: number) => void;
  clearCart: () => void;
  getItemQuantity: (itemId: string) => number;
  getSubtotal: () => number;
  getItemCount: () => number;
  isEmpty: () => boolean;
}
```

### Cart Item Interface
```typescript
interface CartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
  image?: string;
  category?: string;
}
```

## Usage Examples

### Basic Store Usage
```tsx
import { useCartStore } from '../src/state/cart';

function MyComponent() {
  const { items, addItem, removeItem } = useCartStore();
  
  const handleAdd = () => {
    addItem({
      id: '1',
      name: 'Pizza',
      price: 12.99,
      category: 'Italian',
    });
  };
  
  return (
    <View>
      <Text>Items: {items.length}</Text>
      <Button onPress={handleAdd} title="Add Pizza" />
    </View>
  );
}
```

### Using Convenience Hooks
```tsx
import { 
  useCartItems, 
  useCartSubtotal, 
  useCartActions 
} from '../src/state/cart';

function CartSummary() {
  const items = useCartItems();
  const subtotal = useCartSubtotal();
  const { clearCart } = useCartActions();
  
  return (
    <View>
      <Text>Total: ${subtotal.toFixed(2)}</Text>
      <Text>Items: {items.length}</Text>
      <Button onPress={clearCart} title="Clear Cart" />
    </View>
  );
}
```

### Quantity Management
```tsx
import { useCartActions } from '../src/state/cart';

function QuantityControls({ itemId }: { itemId: string }) {
  const { updateQuantity } = useCartActions();
  
  const increment = () => updateQuantity(itemId, get().getItemQuantity(itemId) + 1);
  const decrement = () => updateQuantity(itemId, get().getItemQuantity(itemId) - 1);
  
  return (
    <View>
      <Button onPress={decrement} title="-" />
      <Text>{get().getItemQuantity(itemId)}</Text>
      <Button onPress={increment} title="+" />
    </View>
  );
}
```

## Available Hooks

### State Hooks
- `useCartItems()`: Get all cart items
- `useCartSubtotal()`: Get calculated subtotal
- `useCartItemCount()`: Get total item count
- `useCartIsEmpty()`: Check if cart is empty

### Action Hooks
- `useCartActions()`: Get all cart actions
  - `addItem(item)`
  - `removeItem(itemId)`
  - `updateQuantity(itemId, quantity)`
  - `clearCart()`

### Full Store Hook
- `useCartStore()`: Access the entire store with all state and actions

## Implementation Details

### Persistence Configuration
```typescript
persist(
  (set, get) => ({ /* store implementation */ }),
  {
    name: 'cart-storage',
    storage: createJSONStorage(() => AsyncStorage),
    partialize: (state) => ({ items: state.items }),
  }
)
```

### Smart Quantity Management
- **Add Item**: If item exists, increment quantity; otherwise add new item
- **Update Quantity**: Automatically removes item if quantity ≤ 0
- **Remove Item**: Completely removes item from cart

### Performance Optimizations
- **Selective Persistence**: Only persists `items` array
- **Efficient Updates**: Minimal re-renders with Zustand's subscription model
- **Computed Values**: Subtotal and counts are computed on-demand

## Cart Screen Features

### Empty State
- Friendly empty cart message
- Shopping cart emoji icon
- Encouraging text to add items

### Item Display
- **CartItem Component**: Individual item display with:
  - Item image (or placeholder)
  - Name, category, and price
  - Quantity controls (+/- buttons)
  - Remove button (trash icon)
  - Item total calculation

### Cart Summary
- **Subtotal Display**: Shows total cost and item count
- **Action Buttons**: Clear cart and checkout options
- **Fixed Positioning**: Stays at bottom of screen

### Quantity Controls
- **Increment/Decrement**: +/- buttons for quantity adjustment
- **Smart Disabling**: Decrement disabled when quantity = 1
- **Visual Feedback**: Different colors for active/inactive states

## Tab Badge Integration

### Cart Tab Badge
- **Dynamic Badge**: Shows current item count
- **Smart Display**: Only shows when items > 0
- **Overflow Handling**: Shows "99+" for counts > 99
- **Real-time Updates**: Automatically updates when cart changes

## Testing the Cart

### Sample Menu Items
The Menu tab includes sample items for testing:
- Margherita Pizza ($12.99)
- Chicken Burger ($8.99)
- Caesar Salad ($6.99)
- Pasta Carbonara ($11.99)
- Chocolate Cake ($4.99)

### Adding Items
1. Navigate to Menu tab
2. Tap "Add to Cart" on any sample item
3. Navigate to Cart tab to see added items
4. Use quantity controls to adjust amounts

### Cart Management
- **Update Quantities**: Use +/- buttons on each item
- **Remove Items**: Tap trash icon on individual items
- **Clear Cart**: Use "Clear Cart" button (with confirmation)
- **Checkout**: Placeholder for future implementation

## Best Practices

### 1. Use Convenience Hooks
```tsx
// ✅ Good - Use specific hooks
const items = useCartItems();
const subtotal = useCartSubtotal();

// ❌ Avoid - Accessing full store unnecessarily
const { items, getSubtotal } = useCartStore();
const subtotal = getSubtotal();
```

### 2. Handle Empty States
```tsx
function CartComponent() {
  const isEmpty = useCartIsEmpty();
  
  if (isEmpty) {
    return <EmptyCartMessage />;
  }
  
  return <CartItems />;
}
```

### 3. Optimize Re-renders
```tsx
// ✅ Good - Component only re-renders when items change
const items = useCartItems();

// ❌ Avoid - Component re-renders on any store change
const { items } = useCartStore();
```

### 4. Use Actions Efficiently
```tsx
// ✅ Good - Get actions once
const { addItem, removeItem } = useCartActions();

// ❌ Avoid - Getting actions on every render
const addItem = useCartStore(state => state.addItem);
```

## Future Enhancements

### Planned Features
- [ ] **Cart Validation**: Check item availability before checkout
- [ ] **Save for Later**: Save items without purchasing
- [ ] **Cart Sharing**: Share cart with others
- [ ] **Bulk Operations**: Select multiple items for actions
- [ ] **Cart History**: Remember previous carts

### Technical Improvements
- [ ] **Offline Support**: Queue cart changes when offline
- [ ] **Sync with Server**: Keep local cart in sync with backend
- [ ] **Cart Analytics**: Track cart behavior and conversions
- [ ] **A/B Testing**: Test different cart layouts and flows

## Troubleshooting

### Common Issues

1. **Items Not Persisting**
   - Check AsyncStorage permissions
   - Verify storage key name
   - Ensure proper error handling

2. **Performance Issues**
   - Use specific selectors instead of full store
   - Avoid unnecessary re-renders
   - Check for memory leaks in large carts

3. **State Synchronization**
   - Verify store initialization
   - Check for multiple store instances
   - Ensure proper cleanup on unmount

### Debug Mode
Enable cart debugging in development:
```typescript
if (__DEV__) {
  console.log('Cart Items:', useCartStore.getState().items);
  console.log('Cart Subtotal:', useCartStore.getState().getSubtotal());
}
```

## Integration with Other Systems

### API Layer
- **Cart Service**: Backend cart operations
- **Synchronization**: Keep local and remote carts in sync
- **Validation**: Server-side cart validation

### Navigation
- **Tab Badge**: Real-time cart count display
- **Deep Linking**: Navigate directly to cart
- **Cart State**: Preserve cart across navigation

### State Management
- **Zustand Integration**: Seamless state management
- **React Query**: Cache cart data from API
- **Persistence**: Automatic state restoration
