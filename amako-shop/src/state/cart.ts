import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Money } from '../types';
import { sumMoney, multiplyMoney } from '../utils/price';

// Cart line item with variants and add-ons
export interface CartLine {
  itemId: string;
  name: string;
  unitBasePrice: Money;
  variantId?: string;
  variantName?: string;
  addOns?: { id: string; name: string; price: Money }[];
  qty: number;
  imageUrl?: string;
}

// Cart store interface
interface CartStore {
  // State
  items: CartLine[];
  lastAddedItem?: CartLine;
  
  // Actions
  addItem: (item: CartLine, afterAdd?: (payload: any) => void) => void;
  removeItem: (itemId: string, variantId?: string, addOns?: string[]) => void;
  updateQuantity: (itemId: string, variantId: string | undefined, addOns: string[], quantity: number) => void;
  clearCart: () => void;
  
  // Computed values (not functions to avoid recreation)
  subtotal: Money;
  itemCount: number;
  isEmpty: boolean;
}

// Helper function to create a unique key for cart items
function createItemKey(itemId: string, variantId?: string, addOns?: string[]): string {
  const addOnsKey = addOns?.sort().join(',') || '';
  return `${itemId}:${variantId || 'base'}:${addOnsKey}`;
}

// Helper function to find matching cart item
function findMatchingItem(
  items: CartLine[], 
  itemId: string, 
  variantId?: string, 
  addOns?: string[]
): CartLine | undefined {
  const targetAddOns = addOns?.sort() || [];
  
  if (!items || !Array.isArray(items)) {
    return undefined;
  }
  
  return items.find(item => {
    const itemAddOns = item.addOns?.map(a => a.id).sort() || [];
    
    return item.itemId === itemId && 
           item.variantId === variantId && 
           JSON.stringify(itemAddOns) === JSON.stringify(targetAddOns);
  });
}

// Helper function to calculate subtotal
function calculateSubtotal(items: CartLine[]): Money {
  if (items.length === 0) {
    return { currency: 'NPR', amount: 0 };
  }
  
  const subtotal = items.reduce((total, item) => {
    // Calculate unit price: base + variant + add-ons
    let unitPrice = item.unitBasePrice;
    
    // Add add-ons price
    if (item.addOns && item.addOns.length > 0) {
      const addOnsTotal = sumMoney(...item.addOns.map(a => a.price));
      unitPrice = sumMoney(unitPrice, addOnsTotal);
    }
    
    // Multiply by quantity
    const itemTotal = multiplyMoney(unitPrice, item.qty);
    
    return sumMoney(total, itemTotal);
  }, { currency: 'NPR', amount: 0 } as Money);
  
  return subtotal;
}

// Helper function to calculate item count
function calculateItemCount(items: CartLine[]): number {
  return items.reduce((total, item) => total + item.qty, 0);
}

export const useCartStore = create<CartStore>()(
  persist(
    (set, get) => ({
      // Initial state
      items: [],
      lastAddedItem: undefined,
      subtotal: { currency: 'NPR', amount: 0 },
      itemCount: 0,
      isEmpty: true,
      
      // Actions
      addItem: (newItem: CartLine, afterAdd?: (payload: any) => void) => {
        set((state) => {
          const existingItem = findMatchingItem(
            state.items, 
            newItem.itemId, 
            newItem.variantId, 
            newItem.addOns?.map(a => a.id)
          );
          
          let newItems: CartLine[];
          let addedItem: CartLine = newItem; // Initialize with newItem as default
          
          if (existingItem) {
            // Update quantity of existing item
            newItems = state.items.map(item => {
              if (item === existingItem) {
                const updatedItem = { ...item, qty: item.qty + newItem.qty };
                addedItem = updatedItem;
                return updatedItem;
              }
              return item;
            });
          } else {
            // Add new item
            newItems = [...state.items, newItem];
            // addedItem is already set to newItem above
          }
          
          const newSubtotal = calculateSubtotal(newItems);
          const newItemCount = calculateItemCount(newItems);
          const newIsEmpty = newItems.length === 0;
          
          // Call afterAdd callback with payload
          if (afterAdd) {
            const payload = {
              name: addedItem.name,
              price: addedItem.unitBasePrice.amount,
              qty: newItem.qty,
              thumb: addedItem.imageUrl,
              cartCount: newItemCount,
              cartTotal: newSubtotal.amount
            };
            afterAdd(payload);
          }
          
          return { 
            items: newItems,
            lastAddedItem: addedItem,
            subtotal: newSubtotal,
            itemCount: newItemCount,
            isEmpty: newIsEmpty
          };
        });
      },
      
      removeItem: (itemId: string, variantId?: string, addOns?: string[]) => {
        set((state) => {
          const targetAddOns = addOns?.sort() || [];
          
          const newItems = state.items.filter(item => {
            const itemAddOns = item.addOns?.map(a => a.id).sort() || [];
            
            return !(item.itemId === itemId && 
                    item.variantId === variantId && 
                    JSON.stringify(itemAddOns) === JSON.stringify(targetAddOns));
          });
          
          const newSubtotal = calculateSubtotal(newItems);
          const newItemCount = calculateItemCount(newItems);
          const newIsEmpty = newItems.length === 0;
          
          return { 
            items: newItems,
            subtotal: newSubtotal,
            itemCount: newItemCount,
            isEmpty: newIsEmpty
          };
        });
      },
      
      updateQuantity: (itemId: string, variantId: string | undefined, addOns: string[], quantity: number) => {
        set((state) => {
          const targetAddOns = addOns?.sort() || [];
          
          const newItems = state.items.map(item => {
            const itemAddOns = item.addOns?.map(a => a.id).sort() || [];
            
            if (item.itemId === itemId && 
                item.variantId === variantId && 
                JSON.stringify(itemAddOns) === JSON.stringify(targetAddOns)) {
              return { ...item, qty: Math.max(1, quantity) };
            }
            return item;
          });
          
          const newSubtotal = calculateSubtotal(newItems);
          const newItemCount = calculateItemCount(newItems);
          const newIsEmpty = newItems.length === 0;
          
          return { 
            items: newItems,
            subtotal: newSubtotal,
            itemCount: newItemCount,
            isEmpty: newIsEmpty
          };
        });
      },
      
      clearCart: () => set({ 
        items: [], 
        lastAddedItem: undefined,
        subtotal: { currency: 'NPR', amount: 0 },
        itemCount: 0,
        isEmpty: true
      }),
    }),
    {
      name: 'cart-storage',
      storage: createJSONStorage(() => AsyncStorage),
      partialize: (state) => ({ 
        items: state.items,
        lastAddedItem: state.lastAddedItem,
        subtotal: state.subtotal,
        itemCount: state.itemCount,
        isEmpty: state.isEmpty
      }),
    }
  )
);

// Convenience hooks - now using memoized values instead of functions
export const useCartItems = () => useCartStore((state) => state.items);
export const useCartSubtotal = () => useCartStore((state) => state.subtotal);
export const useCartIsEmpty = () => useCartStore((state) => state.isEmpty);
export const useCartItemCount = () => useCartStore((state) => state.itemCount);
export const useLastAddedItem = () => useCartStore((state) => state.lastAddedItem);

// Helper function to get item quantity (for backward compatibility)
export const getItemQuantity = (itemId: string, variantId?: string, addOns?: string[]): number => {
  const state = useCartStore.getState();
  const item = findMatchingItem(state.items, itemId, variantId, addOns);
  return item?.qty || 0;
};

// Individual action hooks for React 19 compatibility
export const useAddItem = () => useCartStore((state) => state.addItem);
export const useRemoveItem = () => useCartStore((state) => state.removeItem);
export const useUpdateQuantity = () => useCartStore((state) => state.updateQuantity);
export const useClearCart = () => useCartStore((state) => state.clearCart);

// Legacy hook for backward compatibility (deprecated)
export const useCartActions = () => useCartStore((state) => ({
  addItem: state.addItem,
  removeItem: state.removeItem,
  updateQuantity: state.updateQuantity,
  clearCart: state.clearCart,
}));
