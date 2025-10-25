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

// Applied offer interface
export interface AppliedOffer {
  id: number;
  code: string;
  title: string;
  description: string;
  discount: number;
  min_purchase: number;
  max_discount: number;
}

// Cart store interface with sync capabilities
interface CartSyncStore {
  // State
  items: CartLine[];
  lastAddedItem?: CartLine;
  appliedOffer: AppliedOffer | null;
  isOnline: boolean;
  lastSyncTime?: Date;
  syncInProgress: boolean;
  
  // Actions
  addItem: (item: CartLine, afterAdd?: (payload: any) => void) => Promise<void>;
  removeItem: (itemId: string, variantId?: string, addOns?: string[]) => Promise<void>;
  updateQuantity: (itemId: string, variantId: string | undefined, addOns: string[], quantity: number) => Promise<void>;
  clearCart: () => Promise<void>;
  setAppliedOffer: (offer: AppliedOffer | null) => void;
  clearAppliedOffer: () => void;
  
  // Sync actions
  syncWithServer: () => Promise<void>;
  loadFromServer: () => Promise<void>;
  setOnlineStatus: (isOnline: boolean) => void;
  
  // Computed values
  subtotal: Money;
  itemCount: number;
  isEmpty: boolean;
  discountAmount: number;
  totalAfterDiscount: number;
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
function cartLineToServerItem(item: CartLine): ServerCartItem | null {
  // Validate required fields
  if (!item.itemId || !item.name || !item.unitBasePrice?.amount || !item.qty) {
    console.warn('⚠️ Invalid cart item, skipping:', item);
    return null;
  }
  
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
      appliedOffer: null,
      isOnline: true,
      lastSyncTime: undefined,
      syncInProgress: false,
      subtotal: { currency: 'NPR', amount: 0 },
      itemCount: 0,
      isEmpty: true,
      discountAmount: 0,
      totalAfterDiscount: 0,
      
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
        console.log('🛒 [CLEAR CART] ===== CLEARING CART START =====');
        
        set({ 
          items: [], 
          lastAddedItem: undefined,
          appliedOffer: null,
          subtotal: { currency: 'NPR', amount: 0 },
          itemCount: 0,
          isEmpty: true,
          discountAmount: 0,
          totalAfterDiscount: 0,
          lastSyncTime: new Date(), // Set sync time to prevent immediate reload
        });
        
        console.log('🛒 [CLEAR CART] Step 1: ✅ Local state cleared');

        // Clear server cart
        const { isOnline } = get();
        if (isOnline) {
          try {
            console.log('🛒 [CLEAR CART] Step 2: Calling server clear endpoint...');
            await client.post('/cart/clear');
            console.log('🛒 [CLEAR CART] Step 2: ✅ Server cart cleared successfully');
          } catch (error) {
            console.error('🛒 [CLEAR CART] ❌ Failed to clear server cart:', error);
            // Don't fail the clear operation if server clear fails
            // Local cart is already cleared which is what matters most
          }
        }
        
        console.log('🛒 [CLEAR CART] ===== CLEARING CART END =====');
      },
      
      setAppliedOffer: (offer: AppliedOffer | null) => {
        set((state) => {
          const subtotalAmount = state.subtotal.amount;
          let discountAmount = 0;
          
          if (offer) {
            const calculatedDiscount = (subtotalAmount * offer.discount) / 100;
            discountAmount = Math.min(calculatedDiscount, offer.max_discount || calculatedDiscount);
          }
          
          const totalAfterDiscount = subtotalAmount - discountAmount;
          
          return {
            appliedOffer: offer,
            discountAmount,
            totalAfterDiscount
          };
        });
      },
      
      clearAppliedOffer: () => {
        set({ 
          appliedOffer: null,
          discountAmount: 0,
          totalAfterDiscount: 0
        });
      },

      // Sync actions
      syncWithServer: async () => {
        let { items, syncInProgress } = get();
        
        if (syncInProgress) return;
        
        set({ syncInProgress: true });
        
        try {
          // Filter out invalid items (items with undefined itemId, name, etc.)
          const validItems = items.filter(item => 
            item.itemId && 
            item.name && 
            item.unitBasePrice?.amount !== undefined && 
            item.qty > 0
          );
          
          // If we filtered out invalid items, update the cart state
          if (validItems.length !== items.length) {
            console.warn('⚠️ Found and removed', items.length - validItems.length, 'invalid cart items');
            const newSubtotal = calculateSubtotal(validItems);
            const newItemCount = calculateItemCount(validItems);
            const newIsEmpty = validItems.length === 0;
            
            set({
              items: validItems,
              subtotal: newSubtotal,
              itemCount: newItemCount,
              isEmpty: newIsEmpty
            });
            
            items = validItems; // Update items for sync
          }
          
          // Convert to server format, filtering out any nulls from conversion errors
          const serverItems = items
            .map(cartLineToServerItem)
            .filter((item): item is ServerCartItem => item !== null);
          
          console.log('🛒 [SYNC] Syncing cart with server:', serverItems.length, 'items');
          
          // If there are no valid items, clear the cart on server
          if (serverItems.length === 0) {
            console.log('🛒 [SYNC] No valid items to sync, cart is empty');
            set({ 
              lastSyncTime: new Date(),
              syncInProgress: false 
            });
            return;
          }
          
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
        } catch (error: any) {
          console.error('❌ Cart sync error:', error);
          
          // If sync fails (like with invalid items), just clear the sync flag
          // Don't let it break the cart functionality
          set({ syncInProgress: false });
          
          // If it's a validation error (422), the items might be corrupted
          // Clear invalid items from cart
          if (error?.status === 422) {
            console.warn('🛒 [SYNC] Validation error 422 - checking for corrupted items');
            const validItems = items.filter(item => 
              item.itemId && 
              item.name && 
              item.unitBasePrice?.amount !== undefined && 
              item.qty > 0
            );
            
            if (validItems.length !== items.length) {
              console.warn('🛒 [SYNC] Removing corrupted items from cart');
              const newSubtotal = calculateSubtotal(validItems);
              const newItemCount = calculateItemCount(validItems);
              const newIsEmpty = validItems.length === 0;
              
              set({
                items: validItems,
                subtotal: newSubtotal,
                itemCount: newItemCount,
                isEmpty: newIsEmpty
              });
            }
          }
        }
      },

      loadFromServer: async () => {
        console.log('🛒 [CART DEBUG] ===== LOADING FROM SERVER START =====');
        
        const { syncInProgress, items: currentItems } = get();
        
        if (syncInProgress) {
          console.log('🛒 [CART DEBUG] ⚠️ Sync already in progress, skipping...');
          return;
        }
        
        // Don't reload if cart was just cleared (within last 5 seconds)
        const lastClearTime = get().lastSyncTime;
        if (lastClearTime && currentItems.length === 0) {
          const timeSinceClear = Date.now() - lastClearTime.getTime();
          if (timeSinceClear < 5000) {
            console.log('🛒 [CART DEBUG] ⚠️ Cart was recently cleared, skipping server load to prevent refill');
            return;
          }
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
            
            // Convert server items to cart lines and filter out any invalid items
            const cartLines = serverItems
              .map(serverItemToCartLine)
              .filter((item: CartLine) => 
                item.itemId && 
                item.name && 
                item.unitBasePrice?.amount !== undefined && 
                item.qty > 0
              );
            
            if (cartLines.length !== serverItems.length) {
              console.warn('⚠️ Filtered out', serverItems.length - cartLines.length, 'invalid items from server');
            }
            
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
        } catch (error: any) {
          console.error('🛒 [CART DEBUG] ❌ Cart load error:', error);
          console.error('🛒 [CART DEBUG] Error details:', {
            message: error?.message,
            status: error?.status,
            code: error?.code,
            response: error?.response?.data
          });
          
          set({ syncInProgress: false });
          
          // If it's a 401 error, don't retry immediately to prevent crash loops
          if (error?.status === 401) {
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
        appliedOffer: state.appliedOffer,
        subtotal: state.subtotal,
        itemCount: state.itemCount,
        isEmpty: state.isEmpty,
        discountAmount: state.discountAmount,
        totalAfterDiscount: state.totalAfterDiscount,
        lastSyncTime: state.lastSyncTime,
      }),
      onRehydrateStorage: () => (state) => {
        // Clean up any invalid items after rehydrating from storage
        if (state && state.items.length > 0) {
          const validItems = state.items.filter(item => 
            item.itemId && 
            item.name && 
            item.unitBasePrice?.amount !== undefined && 
            item.qty > 0
          );
          
          if (validItems.length !== state.items.length) {
            console.warn('⚠️ Cleaned up', state.items.length - validItems.length, 'corrupted items from persisted storage');
            const newSubtotal = calculateSubtotal(validItems);
            const newItemCount = calculateItemCount(validItems);
            const newIsEmpty = validItems.length === 0;
            
            state.items = validItems;
            state.subtotal = newSubtotal;
            state.itemCount = newItemCount;
            state.isEmpty = newIsEmpty;
          }
        }
      },
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

