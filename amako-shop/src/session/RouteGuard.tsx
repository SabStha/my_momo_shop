import { useEffect, useRef } from "react";
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

export function RouteGuard({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, loading } = useSession();
  const segments = useSegments();
  const router = useRouter();
  const redirectingRef = useRef(false);
  const hasRedirectedRef = useRef(false);

  useEffect(() => {
    if (loading || redirectingRef.current || hasRedirectedRef.current) {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Skipping redirect - loading:', loading, 'redirecting:', redirectingRef.current, 'hasRedirected:', hasRedirectedRef.current);
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
      redirectingRef.current = true;
      hasRedirectedRef.current = true;
      router.replace("/(tabs)");
      setTimeout(() => {
        redirectingRef.current = false;
      }, 100);
    } else if (!isAuthenticated && inTabs) {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: Redirecting unauthenticated user from tabs to auth');
      }
      redirectingRef.current = true;
      hasRedirectedRef.current = true;
      router.replace("/(auth)/login");
      setTimeout(() => {
        redirectingRef.current = false;
      }, 100);
    } else {
      if (__DEV__) {
        console.log('🛡️ RouteGuard: No redirect needed');
      }
    }
  }, [isAuthenticated, loading, segments, router]);

  // Show loading state while checking authentication or while redirecting
  if (loading || redirectingRef.current) {
    if (__DEV__) {
      console.log('🛡️ RouteGuard: Showing loading screen - loading:', loading, 'redirecting:', redirectingRef.current);
    }
    return <LoadingScreen />;
  }

  if (__DEV__) {
    console.log('🛡️ RouteGuard: Rendering children');
  }

  return <View style={{ flex: 1 }}>{children}</View>;
}
