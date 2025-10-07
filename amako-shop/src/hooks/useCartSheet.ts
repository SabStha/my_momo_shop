import {useState, useCallback} from 'react';
import {router} from 'expo-router';

export type CartAddedPayload = {
  name: string; price: number; qty: number; thumb?: string; cartCount: number; cartTotal: number;
};

export default function useCartSheet() {
  const [visible, setVisible] = useState(false);
  const [payload, setPayload] = useState<CartAddedPayload | null>(null);

  const open = useCallback((p: CartAddedPayload) => { setPayload(p); setVisible(true); }, []);
  const close = useCallback(() => setVisible(false), []);
  const viewCart = useCallback(() => { setVisible(false); router.push('/cart'); }, []);
  const checkout = useCallback(() => { setVisible(false); router.push('/checkout'); }, []);

  return {visible, payload, open, close, viewCart, checkout};
}
