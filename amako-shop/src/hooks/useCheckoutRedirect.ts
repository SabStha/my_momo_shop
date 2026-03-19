import { useEffect } from 'react';
import { router, useSegments } from 'expo-router';
import { useCartSyncStore } from '../state/cart-sync';

/**
 * Hook to handle automatic redirection to cart screen if items are empty
 * while the user is on the checkout flow.
 */
export const useCheckoutRedirect = () => {
    const { items } = useCartSyncStore();
    const segments = useSegments();

    useEffect(() => {
        const currentRoute = segments[0] as string;
        // Check if we are currently on the checkout screen or any of its sub-segments
        const isCheckoutActive = currentRoute === 'checkout' || segments.some((seg) => seg === 'checkout');

        if (items.length === 0 && isCheckoutActive) {
            console.log('🚨 CHECKOUT: Cart is empty, silently redirecting to cart...');
            router.replace('/cart');
        } else if (items.length === 0) {
            console.log('🚨 CHECKOUT: Cart is empty but not on checkout screen, skipping redirect');
        }
    }, [items.length, segments]);
};
