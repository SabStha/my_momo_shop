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

  useEffect(() => {
    // Only run redirect logic once after loading is complete
    if (loading || isRedirecting) {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Skipping redirect - loading:', loading, 'redirecting:', isRedirecting);
      }
      return;
    }

    // Skip if we've already initialized and nothing critical has changed
    if (hasInitialized && !loading) {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Already initialized, skipping check');
      }
      return;
    }

    const root = segments[0];
    const inAuth = root === "(auth)";
    const inTabs = root === "(tabs)";

    if (__DEV__) {
      console.log('🛡️ RouteGuard: Checking redirect - isAuthenticated:', isAuthenticated, 'root:', root, 'segments:', segments);
    }

    // Only redirect if we're in the wrong section
    if (isAuthenticated && inAuth) {
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
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Redirecting unauthenticated user from tabs to auth');
      }
      setIsRedirecting(true);
      router.replace("/(auth)/login");
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
  }, [isAuthenticated, loading, segments, router, hasInitialized]);

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
