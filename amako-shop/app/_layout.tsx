import { Stack } from 'expo-router';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SessionProvider } from '../src/session/SessionProvider';
import { RouteGuard } from '../src/session/RouteGuard';
import { useState, useEffect } from 'react';
import { ConnectionDoctor } from '../src/utils/connectionDoctor';

export default function RootLayout() {
  // Create QueryClient once per component instance
  const [queryClient] = useState(() => new QueryClient());

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
    <QueryClientProvider client={queryClient}>
      <SessionProvider>
        <Stack screenOptions={{ headerShown: false }}>
          <Stack.Screen name="(auth)" options={{ headerShown: false }} />
          <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
          <Stack.Screen name="index" options={{ headerShown: false }} />
        </Stack>
        <RouteGuard />
      </SessionProvider>
    </QueryClientProvider>
  );
}
