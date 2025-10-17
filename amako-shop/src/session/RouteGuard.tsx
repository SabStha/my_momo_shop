import { useEffect, useRef, useState } from "react";
import { useRouter, useSegments } from "expo-router";
import { View, Text, ActivityIndicator } from "react-native";
import { useSession } from "./SessionProvider";

// Simple loading component
function LoadingScreen() {
  return (
    <View style={{ 
      flex: 1, 
      justifyContent: 'center', 
      alignItems: 'center',
      backgroundColor: '#f5f5f5'
    }}>
      <ActivityIndicator size="large" color="#007AFF" />
      <Text style={{ marginTop: 16, fontSize: 16, color: '#666' }}>
        Loading...
      </Text>
    </View>
  );
}

export function RouteGuard() {
  const { isAuthenticated, loading } = useSession();
  const segments = useSegments();
  const router = useRouter();
  const [isRedirecting, setIsRedirecting] = useState(false);
  const [hasInitialized, setHasInitialized] = useState(false);
  const lastAuthState = useRef<boolean | null>(null);
  const lastSegments = useRef<string[]>([]);

  useEffect(() => {
    // Only run redirect logic once after loading is complete
    if (loading || isRedirecting) {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Skipping redirect - loading:', loading, 'redirecting:', isRedirecting);
      }
      return;
    }

    // Check if auth state or segments have actually changed
    const authChanged = lastAuthState.current !== isAuthenticated;
    const segmentsChanged = JSON.stringify(lastSegments.current) !== JSON.stringify(segments);
    
    if (!authChanged && !segmentsChanged && hasInitialized) {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: No changes detected, skipping check');
      }
      return;
    }

    // Update refs
    lastAuthState.current = isAuthenticated;
    lastSegments.current = [...segments];

    const root = segments[0];
    const inAuth = root === "(auth)";
    const inTabs = root === "(tabs)";
    
    // List of standalone routes that don't need auth redirect
    const standaloneRoutes = ['cart', 'checkout', 'branch-selection', 'payment', 'payment-success', 'orders', 'order', 'order-tracking'];
    const isStandaloneRoute = standaloneRoutes.some(route => root === route || segments.some(seg => seg === route));

    if (__DEV__) {
      console.log('🛡️ RouteGuard: Checking redirect - isAuthenticated:', isAuthenticated, 'root:', root, 'segments:', segments, 'isStandalone:', isStandaloneRoute);
    }

    // Handle routing based on authentication state
    if (isAuthenticated && inAuth) {
      // Authenticated user in auth screens → redirect to app
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Redirecting authenticated user from auth to tabs');
      }
      setIsRedirecting(true);
      router.replace("/(tabs)/home");
      setTimeout(() => {
        setIsRedirecting(false);
        setHasInitialized(true);
      }, 1000);
    } else if (!isAuthenticated && inTabs) {
      // Unauthenticated user in tabs → redirect to login
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Redirecting unauthenticated user from tabs to auth');
      }
      setIsRedirecting(true);
      router.replace("/(auth)/login");
      setTimeout(() => {
        setIsRedirecting(false);
        setHasInitialized(true);
      }, 1000);
    } else if (!isAuthenticated && !inAuth && !inTabs && !isStandaloneRoute) {
      // Unauthenticated user at root/index (but not standalone routes) → redirect to login
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Redirecting unauthenticated user from root to login');
      }
      setIsRedirecting(true);
      router.replace("/(auth)/login");
      setTimeout(() => {
        setIsRedirecting(false);
        setHasInitialized(true);
      }, 1000);
    } else if (isAuthenticated && !inAuth && !inTabs && !isStandaloneRoute) {
      // Authenticated user at root/index (but not standalone routes) → redirect to home
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Redirecting authenticated user from root to home');
      }
      setIsRedirecting(true);
      router.replace("/(tabs)/home");
      setTimeout(() => {
        setIsRedirecting(false);
        setHasInitialized(true);
      }, 1000);
    } else {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: No redirect needed');
      }
      setHasInitialized(true);
    }
  }, [isAuthenticated, loading, segments, hasInitialized, router]);

  // Show loading state while checking authentication or while redirecting
  if (loading || isRedirecting) {
    if (__DEV__) {
      console.log('🛡️ RouteGuard: Showing loading screen - loading:', loading, 'redirecting:', isRedirecting);
    }
    return <LoadingScreen />;
  }

  if (__DEV__) {
    console.log('🛡️ RouteGuard: No redirect needed, returning null');
  }

  return null;
}
