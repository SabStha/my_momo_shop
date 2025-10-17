import { Stack } from 'expo-router';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SessionProvider } from '../src/session/SessionProvider';
import { RouteGuard } from '../src/session/RouteGuard';
import { useState, useEffect } from 'react';
import { ConnectionDoctor } from '../src/utils/connectionDoctor';
import CartAddedSheet from '../src/components/cart/CartAddedSheet';
import useCartSheet from '../src/hooks/useCartSheet';
import { NetworkDetector } from '../src/components/NetworkDetector';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { SplashScreen } from '../src/components/SplashScreen';
import { OrderDeliveredHandler } from '../src/components/OrderDeliveredHandler';

export default function RootLayout() {
  // Create QueryClient once per component instance
  const [queryClient] = useState(() => new QueryClient());
  
  // Splash screen state
  const [showSplash, setShowSplash] = useState(true);
  
  // Cart sheet hook
  const sheet = useCartSheet();
  
  // Expose for global use
  useEffect(() => {
    (global as any).openCartAddedSheet = sheet.open;
  }, [sheet.open]);

  // Run connection diagnostic on app mount
  useEffect(() => {
    if (__DEV__) {
      // Run diagnostic after a short delay to let the app initialize
      const timer = setTimeout(() => {
        ConnectionDoctor.logConnectionReport();
      }, 2000);
      
      return () => clearTimeout(timer);
    }
  }, []);

  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <QueryClientProvider client={queryClient}>
        <SessionProvider>
          <NetworkDetector>
            <Stack screenOptions={{ headerShown: false }}>
              <Stack.Screen name="(auth)" options={{ headerShown: false }} />
              <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
              <Stack.Screen name="index" options={{ headerShown: false }} />
              <Stack.Screen name="cart" options={{ headerShown: false }} />
              <Stack.Screen name="checkout" options={{ headerShown: false }} />
              <Stack.Screen name="branch-selection" options={{ headerShown: false }} />
              <Stack.Screen name="payment" options={{ headerShown: false }} />
              <Stack.Screen name="payment-success" options={{ headerShown: false }} />
              <Stack.Screen name="orders" options={{ headerShown: false }} />
              <Stack.Screen name="order/[id]" options={{ headerShown: false }} />
              <Stack.Screen name="order-tracking/[id]" options={{ headerShown: false }} />
              <Stack.Screen name="test-delivered-popup" options={{ headerShown: false }} />
            </Stack>
            <RouteGuard />
            {sheet.visible && (
              <CartAddedSheet
                visible={sheet.visible}
                payload={sheet.payload}
                onClose={sheet.close}
                onViewCart={sheet.viewCart}
                onCheckout={sheet.checkout}
              />
            )}
            
            {/* Order Delivered & Review Handler (inside QueryProvider for hooks) */}
            <OrderDeliveredHandler />
          </NetworkDetector>
        </SessionProvider>
      </QueryClientProvider>
      
      {showSplash && (
        <SplashScreen onFinish={() => setShowSplash(false)} />
      )}
    </GestureHandlerRootView>
  );
}
