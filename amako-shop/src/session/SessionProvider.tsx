import React, { createContext, useContext, useEffect, useState, ReactNode, useCallback, useMemo, useRef } from 'react';
import { getToken, setToken, clearToken, resetAuthState, AuthToken } from './token';
import { eventEmitter, AUTH_EVENTS } from '../utils/events';
import { useCartSyncStore } from '../state/cart-sync';
import { reset401Counter } from '../api/client';

interface SessionContextType {
  token: string | null;
  user: AuthToken['user'] | null;
  loading: boolean;
  setToken: (tokenData: AuthToken) => Promise<void>;
  clearToken: () => Promise<void>;
  resetAuthState: () => Promise<void>;
  isAuthenticated: boolean;
}

const SessionContext = createContext<SessionContextType | undefined>(undefined);

interface SessionProviderProps {
  children: ReactNode;
}

export function SessionProvider({ children }: SessionProviderProps) {
  const [token, setTokenState] = useState<string | null>(null);
  const [user, setUser] = useState<AuthToken['user'] | null>(null);
  const [loading, setLoading] = useState(true);
  
  // Cart sync store
  const { loadFromServer, setOnlineStatus } = useCartSyncStore();

  // Initialize session from storage
  useEffect(() => {
    const initializeSession = async () => {
      if (__DEV__) {
        console.log('🔐 SessionProvider: Starting session initialization...');
      }
      
      try {
        // First, reset any corrupted auth state
        await resetAuthState();
        
        const tokenData = await getToken();
        if (tokenData && tokenData.token) {
          if (__DEV__) {
            console.log('🔐 SessionProvider: Found valid token, user:', tokenData.user?.name);
          }
          setTokenState(tokenData.token);
          setUser(tokenData.user || null);
          
          // Reset 401 counter since we have a valid token
          reset401Counter();
          
          // Delay cart sync to prevent race conditions during app initialization
          if (__DEV__) {
            console.log('🛒 SessionProvider: Delaying cart sync to prevent race conditions...');
          }
          
          setTimeout(async () => {
            try {
              if (__DEV__) {
                console.log('🛒 SessionProvider: Initializing cart sync for authenticated user');
              }
              await loadFromServer();
            } catch (error) {
              console.error('🛒 SessionProvider: Cart sync failed during initialization:', error);
              // Don't throw - this shouldn't break the app initialization
            }
          }, 500); // 500ms delay for app initialization
        } else {
          if (__DEV__) {
            console.log('🔐 SessionProvider: No valid token found');
          }
          setTokenState(null);
          setUser(null);
        }
      } catch (error) {
        console.error('🔐 SessionProvider: Failed to initialize session:', error);
        setTokenState(null);
        setUser(null);
      } finally {
        setLoading(false);
        if (__DEV__) {
          console.log('🔐 SessionProvider: Session initialization complete, loading:', false);
        }
      }
    };

    initializeSession();
  }, []);

  // Optimized auth token setter with useCallback
  const setAuthToken = useCallback(async (tokenData: AuthToken) => {
    console.log('🔄 [SESSION DEBUG] ===== SETTING AUTH TOKEN START =====');
    console.log('🔄 [SESSION DEBUG] User:', tokenData.user?.name);
    console.log('🔄 [SESSION DEBUG] Token length:', tokenData.token?.length);
    
    console.log('🔄 [SESSION DEBUG] Step 1: Storing token in secure storage...');
    await setToken(tokenData);
    console.log('🔄 [SESSION DEBUG] Step 1: ✅ Token stored in secure storage');
    
    console.log('🔄 [SESSION DEBUG] Step 2: Updating session state...');
    setTokenState(tokenData.token);
    setUser(tokenData.user || null);
    console.log('🔄 [SESSION DEBUG] Step 2: ✅ Session state updated');
    
    console.log('🔄 [SESSION DEBUG] Step 3: Scheduling cart sync (1000ms delay)...');
    
    // Delay cart sync to prevent race conditions
    setTimeout(async () => {
      console.log('🔄 [SESSION DEBUG] Step 3: Starting delayed cart sync...');
      try {
        await loadFromServer();
        console.log('🔄 [SESSION DEBUG] Step 3: ✅ Cart sync completed successfully');
      } catch (error) {
        console.error('🔄 [SESSION DEBUG] Step 3: ❌ Cart sync failed during login:', error);
        console.error('🔄 [SESSION DEBUG] Error details:', {
          message: error.message,
          status: error.status,
          code: error.code
        });
        // Don't throw - this shouldn't break the login flow
      }
    }, 1000); // 1 second delay to ensure token is propagated
    
    console.log('🔄 [SESSION DEBUG] ===== SETTING AUTH TOKEN END =====');
  }, [loadFromServer]);

  // Optimized clear token with useCallback
  const clearAuthToken = useCallback(async () => {
    if (__DEV__) {
      console.log('🔐 SessionProvider: Clearing token');
    }
    await clearToken();
    setTokenState(null);
    setUser(null);
  }, []);

  // Reset auth state (clear corrupted tokens)
  const resetAuthStateCallback = useCallback(async () => {
    if (__DEV__) {
      console.log('🔐 SessionProvider: Resetting auth state');
    }
    await resetAuthState();
    setTokenState(null);
    setUser(null);
  }, []);

  // Memoized event handlers to prevent recreation
  const handleUnauthorized = useCallback(() => {
    if (__DEV__) {
      console.log('🔐 SessionProvider: Handling unauthorized event');
    }
    setTokenState(null);
    setUser(null);
  }, []);

  const handleLogout = useCallback(() => {
    if (__DEV__) {
      console.log('🔐 SessionProvider: Handling logout event');
    }
    setTokenState(null);
    setUser(null);
  }, []);

  // Memoized derived state
  const isAuthenticated = useMemo(() => {
    const auth = !!token;
    if (__DEV__) {
      console.log('🔐 SessionProvider: isAuthenticated check - token:', !!token, 'auth:', auth);
    }
    return auth;
  }, [token]);

  // Listen for auth events - single useEffect with proper cleanup
  useEffect(() => {
    if (__DEV__) {
      console.log('🔐 SessionProvider: Setting up event listeners...');
    }
    
    // Register event listeners
    eventEmitter.on(AUTH_EVENTS.UNAUTHORIZED, handleUnauthorized);
    eventEmitter.on(AUTH_EVENTS.LOGOUT, handleLogout);

    // Cleanup function
    return () => {
      eventEmitter.off(AUTH_EVENTS.UNAUTHORIZED, handleUnauthorized);
      eventEmitter.off(AUTH_EVENTS.LOGOUT, handleLogout);
    };
  }, [handleUnauthorized, handleLogout]);

  // Memoized context value with optimized dependencies
  const value = useMemo(() => ({
    token,
    user,
    loading,
    setToken: setAuthToken,
    clearToken: clearAuthToken,
    resetAuthState: resetAuthStateCallback,
    isAuthenticated,
  }), [token, user, loading, setAuthToken, clearAuthToken, resetAuthStateCallback, isAuthenticated]);

  // Removed state logging to prevent console spam during renders

  return (
    <SessionContext.Provider value={value}>
      {children}
    </SessionContext.Provider>
  );
}

export function useSession(): SessionContextType {
  const context = useContext(SessionContext);
  if (context === undefined) {
    // Return a default context instead of throwing an error
    // This prevents crashes during initial render
    if (__DEV__) {
      console.warn('🔐 useSession: Called outside SessionProvider, returning default context');
    }
    return {
      token: null,
      user: null,
      loading: true,
      setToken: async () => {},
      clearToken: async () => {},
      resetAuthState: async () => {},
      isAuthenticated: false,
    };
  }
  return context;
}
