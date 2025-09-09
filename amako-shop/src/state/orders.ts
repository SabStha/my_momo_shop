import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { CartLine } from './cart';
import { Money } from '../types';
import { sumMoney } from '../utils/price';

// Order status types
export type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled';

// Order interface
export interface Order {
  id: string;
  items: CartLine[];
  subtotal: Money;
  deliveryFee: Money;
  tax: Money;
  total: Money;
  status: OrderStatus;
  paymentMethod: 'cash' | 'esewa';
  deliveryAddress: string;
  createdAt: Date;
  updatedAt: Date;
  estimatedDelivery?: Date;
  notes?: string;
}

// Order store interface
interface OrderStore {
  // State
  orders: Order[];
  
  // Actions
  createOrder: (orderData: Omit<Order, 'id' | 'createdAt' | 'updatedAt'>) => void;
  updateOrderStatus: (orderId: string, status: OrderStatus) => void;
  cancelOrder: (orderId: string) => void;
  refreshOrders: () => void;
  debugStorage: () => Promise<void>;
  
  // Selectors
  getOrder: (orderId: string) => Order | undefined;
  getOrdersByStatus: (status: OrderStatus) => Order[];
  getRecentOrders: (limit?: number) => Order[];
  getTotalOrders: () => number;
}

// Helper function to calculate total from subtotal, delivery fee, and tax
function calculateTotal(subtotal: Money, deliveryFee: Money, tax: Money): Money {
  return sumMoney(subtotal, sumMoney(deliveryFee, tax));
}

// Helper function to generate unique order ID
function generateOrderId(): string {
  return `order_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

export const useOrderStore = create<OrderStore>()(
  persist(
    (set, get) => ({
      // Initial state
      orders: [],
      
      // Actions
      createOrder: (orderData) => {
        const now = new Date();
        const newOrder: Order = {
          ...orderData,
          id: generateOrderId(),
          createdAt: now,
          updatedAt: now,
          total: calculateTotal(orderData.subtotal, orderData.deliveryFee, orderData.tax),
        };
        
        if (__DEV__) {
          console.log('📦 Creating new order:', newOrder);
          console.log('📦 Current orders count before:', get().orders.length);
        }
        
        set((state) => {
          const updatedOrders = [newOrder, ...state.orders];
          if (__DEV__) {
            console.log('📦 Updated orders count after:', updatedOrders.length);
            console.log('📦 All orders:', updatedOrders);
          }
          return {
            orders: updatedOrders,
          };
        });
      },
      
      updateOrderStatus: (orderId: string, status: OrderStatus) => {
        if (__DEV__) {
          console.log('📦 Updating order status:', orderId, 'to', status);
        }
        set((state) => ({
          orders: state.orders.map((order) =>
            order.id === orderId
              ? { ...order, status, updatedAt: new Date() }
              : order
          ),
        }));
      },
      
      cancelOrder: (orderId: string) => {
        if (__DEV__) {
          console.log('📦 Cancelling order:', orderId);
        }
        set((state) => ({
          orders: state.orders.map((order) =>
            order.id === orderId
              ? { ...order, status: 'cancelled' as OrderStatus, updatedAt: new Date() }
              : order
          ),
        }));
      },
      
      // Selectors
      getOrder: (orderId: string) => {
        const state = get();
        return state.orders.find((order) => order.id === orderId);
      },
      
      getOrdersByStatus: (status: OrderStatus) => {
        const state = get();
        return state.orders.filter((order) => order.status === status);
      },
      
      getRecentOrders: (limit = 10) => {
        const state = get();
        return state.orders
          .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
          .slice(0, limit);
      },
      
      getTotalOrders: () => {
        const state = get();
        return state.orders.length;
      },
      
      refreshOrders: () => {
        if (__DEV__) {
          console.log('📦 Refreshing orders from storage...');
        }
        // Force a re-render by updating the state
        set((state) => ({ ...state }));
      },
      
      // Debug function to manually check storage
      debugStorage: async () => {
        if (__DEV__) {
          try {
            const stored = await AsyncStorage.getItem('orders-storage');
            console.log('📦 Raw stored data:', stored);
            if (stored) {
              const parsed = JSON.parse(stored);
              console.log('📦 Parsed stored data:', parsed);
            }
          } catch (error) {
            console.error('📦 Storage check error:', error);
          }
        }
      },
    }),
    {
      name: 'orders-storage',
      storage: createJSONStorage(() => AsyncStorage),
      partialize: (state) => ({ orders: state.orders }),
      onRehydrateStorage: () => (state) => {
        if (__DEV__) {
          console.log('📦 Orders store rehydrated from storage:', state);
        }
      },
    }
  )
);

// Convenience hooks
export const useOrders = () => useOrderStore((state) => state.orders);
export const useRecentOrders = (limit?: number) => useOrderStore((state) => state.getRecentOrders(limit));
export const useOrdersByStatus = (status: OrderStatus) => useOrderStore((state) => state.getOrdersByStatus(status));
export const useTotalOrders = () => useOrderStore((state) => state.getTotalOrders());

// Action hooks
export const useCreateOrder = () => useOrderStore((state) => state.createOrder);
export const useUpdateOrderStatus = () => useOrderStore((state) => state.updateOrderStatus);
export const useCancelOrder = () => useOrderStore((state) => state.cancelOrder);
export const useRefreshOrders = () => useOrderStore((state) => state.refreshOrders);
export const useDebugStorage = () => useOrderStore((state) => state.debugStorage);

// Utility hook to get a specific order
export const useOrder = (orderId: string) => useOrderStore((state) => state.getOrder(orderId));
