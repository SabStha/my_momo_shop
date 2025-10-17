import {useState, useCallback} from 'react';
import {router} from 'expo-router';

export type CartAddedPayload = {
  name: string; price: number; qty: number; thumb?: string; cartCount: number; cartTotal: number;
};

export default function useCartSheet() {
  const [visible, setVisible] = useState(false);
  const [payload, setPayload] = useState<CartAddedPayload | null>(null);

  const open = useCallback((p: CartAddedPayload) => { 
    console.log('🛒 CartSheet: Opening with payload:', p);
    setPayload(p); 
    setVisible(true); 
  }, []);
  
  const close = useCallback(() => {
    console.log('🛒 CartSheet: Closing');
    setVisible(false);
  }, []);
  
  const viewCart = useCallback(() => { 
    console.log('🛒 CartSheet: Navigating to /cart');
    setVisible(false);
    // Use setTimeout to ensure modal closes before navigation
    setTimeout(() => {
      try {
        router.push('/cart');
        console.log('🛒 CartSheet: Navigation to /cart successful');
      } catch (error) {
        console.error('🛒 CartSheet: Error navigating to /cart:', error);
      }
    }, 100);
  }, []);
  
  const checkout = useCallback(() => { 
    console.log('🛒 CartSheet: Navigating to /checkout');
    setVisible(false);
    // Use setTimeout to ensure modal closes before navigation
    setTimeout(() => {
      try {
        router.push('/checkout');
        console.log('🛒 CartSheet: Navigation to /checkout successful');
      } catch (error) {
        console.error('🛒 CartSheet: Error navigating to /checkout:', error);
      }
    }, 100);
  }, []);

  return {visible, payload, open, close, viewCart, checkout};
}
