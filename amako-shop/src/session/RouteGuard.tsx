import { useEffect, useRef, useState } from "react";
import { useRouter, useSegments } from "expo-router";
import { View } from "react-native";
import { useSession } from "./SessionProvider";
import LoadingSpinner from "../components/LoadingSpinner";

// Simple loading component
function LoadingScreen() {
  return (
    <View style={{ 
      flex: 1, 
      justifyContent: 'center', 
      alignItems: 'center',
      backgroundColor: '#f5f5f5'
    }}>
      <LoadingSpinner size="large" text="Loading..." />
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
      return;
    }

    // Check if auth state or segments have actually changed
    const authChanged = lastAuthState.current !== isAuthenticated;
    const segmentsChanged = JSON.stringify(lastSegments.current) !== JSON.stringify(segments);
    
    if (!authChanged && !segmentsChanged && hasInitialized) {
      return;
    }

    // Update refs
    lastAuthState.current = isAuthenticated;
    lastSegments.current = [...segments];

    const root = segments[0];
    const inAuth = root === "(auth)";
    const inTabs = root === "(tabs)";
    
    // List of standalone routes that don't need auth redirect
    const standaloneRoutes = ['cart', 'checkout', 'branch-selection', 'payment', 'payment-success', 'orders', 'order', 'order-tracking', 'offers', 'item', 'notifications'];
    const isStandaloneRoute = standaloneRoutes.some(route => root === route || segments.some(seg => seg === route));

    // Handle routing based on authentication state
    if (isAuthenticated && inAuth) {
      // Authenticated user in auth screens → redirect to app
      console.log('🛡️ [ROUTE DEBUG] ===== REDIRECTING AUTHENTICATED USER =====');
      console.log('🛡️ [ROUTE DEBUG] From: auth screens');
      console.log('🛡️ [ROUTE DEBUG] To: /(tabs)/home');
      
      setIsRedirecting(true);
      console.log('🛡️ [ROUTE DEBUG] Step 1: Set redirecting to true');
      
      try {
        console.log('🛡️ [ROUTE DEBUG] Step 2: Attempting router.replace...');
        router.replace("/(tabs)/home");
        console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Navigation successful');
      } catch (error) {
        console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Navigation error:', error);
        console.log('🛡️ [ROUTE DEBUG] Step 2: 🔄 Attempting fallback navigation...');
        // Fallback navigation
        try {
          router.push("/(tabs)/home");
          console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Fallback navigation successful');
        } catch (fallbackError) {
          console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Fallback navigation failed:', fallbackError);
        }
      }
      
      setTimeout(() => {
        console.log('🛡️ [ROUTE DEBUG] Step 3: Clearing redirecting state...');
        setIsRedirecting(false);
        setHasInitialized(true);
        console.log('🛡️ [ROUTE DEBUG] Step 3: ✅ Redirect complete');
      }, 1000);
      
    } else if (!isAuthenticated && inTabs) {
      // Unauthenticated user in tabs → redirect to login
      console.log('🛡️ [ROUTE DEBUG] ===== REDIRECTING UNAUTHENTICATED USER =====');
      console.log('🛡️ [ROUTE DEBUG] From: tabs screens');
      console.log('🛡️ [ROUTE DEBUG] To: /(auth)/login');
      
      setIsRedirecting(true);
      console.log('🛡️ [ROUTE DEBUG] Step 1: Set redirecting to true');
      
      try {
        console.log('🛡️ [ROUTE DEBUG] Step 2: Attempting router.replace...');
        router.replace("/(auth)/login");
        console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Navigation successful');
      } catch (error) {
        console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Navigation error:', error);
        console.log('🛡️ [ROUTE DEBUG] Step 2: 🔄 Attempting fallback navigation...');
        // Fallback navigation
        try {
          router.push("/(auth)/login");
          console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Fallback navigation successful');
        } catch (fallbackError) {
          console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Fallback navigation failed:', fallbackError);
        }
      }
      
      setTimeout(() => {
        console.log('🛡️ [ROUTE DEBUG] Step 3: Clearing redirecting state...');
        setIsRedirecting(false);
        setHasInitialized(true);
        console.log('🛡️ [ROUTE DEBUG] Step 3: ✅ Redirect complete');
      }, 1000);
      
    } else if (!isAuthenticated && !inAuth && !inTabs && !isStandaloneRoute) {
      // Unauthenticated user at root/index (but not standalone routes) → redirect to login
      console.log('🛡️ [ROUTE DEBUG] ===== REDIRECTING FROM ROOT =====');
      console.log('🛡️ [ROUTE DEBUG] From: root/index');
      console.log('🛡️ [ROUTE DEBUG] To: /(auth)/login');
      
      setIsRedirecting(true);
      console.log('🛡️ [ROUTE DEBUG] Step 1: Set redirecting to true');
      
      try {
        console.log('🛡️ [ROUTE DEBUG] Step 2: Attempting router.replace...');
        router.replace("/(auth)/login");
        console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Navigation successful');
      } catch (error) {
        console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Navigation error:', error);
        console.log('🛡️ [ROUTE DEBUG] Step 2: 🔄 Attempting fallback navigation...');
        // Fallback navigation
        try {
          router.push("/(auth)/login");
          console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Fallback navigation successful');
        } catch (fallbackError) {
          console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Fallback navigation failed:', fallbackError);
        }
      }
      
      setTimeout(() => {
        console.log('🛡️ [ROUTE DEBUG] Step 3: Clearing redirecting state...');
        setIsRedirecting(false);
        setHasInitialized(true);
        console.log('🛡️ [ROUTE DEBUG] Step 3: ✅ Redirect complete');
      }, 1000);
      
    } else if (isAuthenticated && !inAuth && !inTabs && !isStandaloneRoute) {
      // Authenticated user at root/index (but not standalone routes) → redirect to home
      console.log('🛡️ [ROUTE DEBUG] ===== REDIRECTING AUTHENTICATED FROM ROOT =====');
      console.log('🛡️ [ROUTE DEBUG] From: root/index');
      console.log('🛡️ [ROUTE DEBUG] To: /(tabs)/home');
      
      setIsRedirecting(true);
      console.log('🛡️ [ROUTE DEBUG] Step 1: Set redirecting to true');
      
      try {
        console.log('🛡️ [ROUTE DEBUG] Step 2: Attempting router.replace...');
        router.replace("/(tabs)/home");
        console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Navigation successful');
      } catch (error) {
        console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Navigation error:', error);
        console.log('🛡️ [ROUTE DEBUG] Step 2: 🔄 Attempting fallback navigation...');
        // Fallback navigation
        try {
          router.push("/(tabs)/home");
          console.log('🛡️ [ROUTE DEBUG] Step 2: ✅ Fallback navigation successful');
        } catch (fallbackError) {
          console.error('🛡️ [ROUTE DEBUG] Step 2: ❌ Fallback navigation failed:', fallbackError);
        }
      }
      
      setTimeout(() => {
        console.log('🛡️ [ROUTE DEBUG] Step 3: Clearing redirecting state...');
        setIsRedirecting(false);
        setHasInitialized(true);
        console.log('🛡️ [ROUTE DEBUG] Step 3: ✅ Redirect complete');
      }, 1000);
      
    } else {
      console.log('🛡️ [ROUTE DEBUG] ===== NO REDIRECT NEEDED =====');
      console.log('🛡️ [ROUTE DEBUG] Current state is valid, no navigation required');
      setHasInitialized(true);
    }
  }, [isAuthenticated, loading, segments, hasInitialized, router]);

  // Show loading state while checking authentication or while redirecting
  if (loading || isRedirecting) {
    return <LoadingScreen />;
  }

  // If unauthenticated and already on a tabs route, show loading screen while the
  // redirect useEffect fires — prevents a brief flash of protected tab content.
  const currentRoot = segments[0];
  if (!isAuthenticated && currentRoot === "(tabs)") {
    return <LoadingScreen />;
  }

  return null;
}
