import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Money } from '../types';
import { sumMoney, multiplyMoney } from '../utils/price';
import { client } from '../api/client';

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

// Server cart item format
export interface ServerCartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
  image?: string;
  type?: string;
}

// Cart store interface with sync capabilities
interface CartSyncStore {
  // State
  items: CartLine[];
  lastAddedItem?: CartLine;
  isOnline: boolean;
  lastSyncTime?: Date;
  syncInProgress: boolean;
  
  // Actions
  addItem: (item: CartLine, afterAdd?: (payload: any) => void) => Promise<void>;
  removeItem: (itemId: string, variantId?: string, addOns?: string[]) => Promise<void>;
  updateQuantity: (itemId: string, variantId: string | undefined, addOns: string[], quantity: number) => Promise<void>;
  clearCart: () => Promise<void>;
  
  // Sync actions
  syncWithServer: () => Promise<void>;
  loadFromServer: () => Promise<void>;
  setOnlineStatus: (isOnline: boolean) => void;
  
  // Computed values
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
  
  return items.find(item => {
    const itemAddOns = item.addOns?.map(a => a.id).sort() || [];
    return item.itemId === itemId && 
           item.variantId === variantId && 
           JSON.stringify(itemAddOns) === JSON.stringify(targetAddOns);
  });
}

// Convert CartLine to ServerCartItem format
function cartLineToServerItem(item: CartLine): ServerCartItem {
  return {
    id: item.itemId,
    name: item.name,
    price: item.unitBasePrice.amount,
    quantity: item.qty,
    image: item.imageUrl,
    type: 'product'
  };
}

// Convert ServerCartItem to CartLine format
function serverItemToCartLine(item: ServerCartItem): CartLine {
  return {
    itemId: item.id,
    name: item.name,
    unitBasePrice: { currency: 'NPR', amount: item.price },
    qty: item.quantity,
    imageUrl: item.image,
  };
}

// Calculate subtotal
function calculateSubtotal(items: CartLine[]): Money {
  return items.reduce((total, item) => {
    const itemTotal = multiplyMoney(item.unitBasePrice, item.qty);
    return sumMoney(total, itemTotal);
  }, { currency: 'NPR', amount: 0 });
}

// Calculate item count
function calculateItemCount(items: CartLine[]): number {
  return items.reduce((count, item) => count + item.qty, 0);
}

export const useCartSyncStore = create<CartSyncStore>()(
  persist(
    (set, get) => ({
      // Initial state
      items: [],
      lastAddedItem: undefined,
      isOnline: true,
      lastSyncTime: undefined,
      syncInProgress: false,
      subtotal: { currency: 'NPR', amount: 0 },
      itemCount: 0,
      isEmpty: true,
      
      // Actions
      addItem: async (newItem: CartLine, afterAdd?: (payload: any) => void) => {
        set((state) => {
          const existingItem = findMatchingItem(
            state.items, 
            newItem.itemId, 
            newItem.variantId, 
            newItem.addOns?.map(a => a.id)
          );
          
          let newItems: CartLine[];
          let addedItem: CartLine = newItem;
          
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

        // Sync with server if online
        const { isOnline } = get();
        if (isOnline) {
          await get().syncWithServer();
        }
      },
      
      removeItem: async (itemId: string, variantId?: string, addOns?: string[]) => {
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

        // Sync with server if online
        const { isOnline } = get();
        if (isOnline) {
          await get().syncWithServer();
        }
      },
      
      updateQuantity: async (itemId: string, variantId: string | undefined, addOns: string[], quantity: number) => {
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

        // Sync with server if online
        const { isOnline } = get();
        if (isOnline) {
          await get().syncWithServer();
        }
      },
      
      clearCart: async () => {
        set({ 
          items: [], 
          lastAddedItem: undefined,
          subtotal: { currency: 'NPR', amount: 0 },
          itemCount: 0,
          isEmpty: true
        });

        // Sync with server if online
        const { isOnline } = get();
        if (isOnline) {
          await get().syncWithServer();
        }
      },

      // Sync actions
      syncWithServer: async () => {
        const { items, syncInProgress } = get();
        
        if (syncInProgress) return;
        
        set({ syncInProgress: true });
        
        try {
          const serverItems = items.map(cartLineToServerItem);
          
          const response = await client.post('/cart/sync', {
            items: serverItems
          });
          
          if (response.data.success) {
            set({ 
              lastSyncTime: new Date(),
              syncInProgress: false 
            });
            
            console.log('✅ Cart synced with server successfully');
          } else {
            console.error('❌ Cart sync failed:', response.data.message);
            set({ syncInProgress: false });
          }
        } catch (error) {
          console.error('❌ Cart sync error:', error);
          set({ syncInProgress: false });
        }
      },

      loadFromServer: async () => {
        console.log('🛒 [CART DEBUG] ===== LOADING FROM SERVER START =====');
        
        const { syncInProgress } = get();
        
        if (syncInProgress) {
          console.log('🛒 [CART DEBUG] ⚠️ Sync already in progress, skipping...');
          return;
        }
        
        console.log('🛒 [CART DEBUG] Step 1: Setting sync in progress...');
        set({ syncInProgress: true });
        console.log('🛒 [CART DEBUG] Step 1: ✅ Sync in progress set');
        
        try {
          console.log('🛒 [CART DEBUG] Step 2: Making API call to /cart...');
          const response = await client.get('/cart');
          console.log('🛒 [CART DEBUG] Step 2: ✅ API call successful');
          console.log('🛒 [CART DEBUG] Response status:', response.status);
          console.log('🛒 [CART DEBUG] Response data success:', response.data.success);
          
          if (response.data.success) {
            const serverItems = response.data.cart.items || [];
            console.log('🛒 [CART DEBUG] Step 3: Processing server items...');
            console.log('🛒 [CART DEBUG] Server items count:', serverItems.length);
            
            const cartLines = serverItems.map(serverItemToCartLine);
            
            const newSubtotal = calculateSubtotal(cartLines);
            const newItemCount = calculateItemCount(cartLines);
            const newIsEmpty = cartLines.length === 0;
            
            console.log('🛒 [CART DEBUG] Step 4: Updating cart state...');
            console.log('🛒 [CART DEBUG] New subtotal:', newSubtotal);
            console.log('🛒 [CART DEBUG] New item count:', newItemCount);
            console.log('🛒 [CART DEBUG] Is empty:', newIsEmpty);
            
            set({
              items: cartLines,
              subtotal: newSubtotal,
              itemCount: newItemCount,
              isEmpty: newIsEmpty,
              lastSyncTime: new Date(),
              syncInProgress: false
            });
            
            console.log('🛒 [CART DEBUG] Step 4: ✅ Cart state updated successfully');
            console.log('✅ Cart loaded from server successfully');
          } else {
            console.error('🛒 [CART DEBUG] ❌ Server returned error:', response.data.message);
            set({ syncInProgress: false });
          }
        } catch (error) {
          console.error('🛒 [CART DEBUG] ❌ Cart load error:', error);
          console.error('🛒 [CART DEBUG] Error details:', {
            message: error.message,
            status: error.status,
            code: error.code,
            response: error.response?.data
          });
          
          set({ syncInProgress: false });
          
          // If it's a 401 error, don't retry immediately to prevent crash loops
          if (error.status === 401) {
            console.warn('🛒 [CART DEBUG] ⚠️ 401 error - token may not be ready yet, will retry later');
            return;
          }
          
          // For other errors, set offline status to prevent further attempts
          console.log('🛒 [CART DEBUG] Setting offline status due to error...');
          set({ isOnline: false });
        }
        
        console.log('🛒 [CART DEBUG] ===== LOADING FROM SERVER END =====');
      },

      setOnlineStatus: (isOnline: boolean) => {
        set({ isOnline });
        
        // If coming back online, sync with server
        if (isOnline) {
          const { loadFromServer } = get();
          loadFromServer();
        }
      }
    }),
    {
      name: 'cart-sync-storage',
      storage: createJSONStorage(() => AsyncStorage),
      partialize: (state) => ({ 
        items: state.items,
        lastAddedItem: state.lastAddedItem,
        subtotal: state.subtotal,
        itemCount: state.itemCount,
        isEmpty: state.isEmpty,
        lastSyncTime: state.lastSyncTime,
      }),
    }
  )
);

// Convenience hooks
export const useCartItems = () => useCartSyncStore((state) => state.items);
export const useCartSubtotal = () => useCartSyncStore((state) => state.subtotal);
export const useCartItemCount = () => useCartSyncStore((state) => state.itemCount);
export const useCartIsEmpty = () => useCartSyncStore((state) => state.isEmpty);
export const useCartSyncStatus = () => useCartSyncStore((state) => ({
  isOnline: state.isOnline,
  syncInProgress: state.syncInProgress,
  lastSyncTime: state.lastSyncTime
}));

