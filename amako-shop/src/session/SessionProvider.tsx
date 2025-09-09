import React, { createContext, useContext, useEffect, useState, ReactNode, useCallback, useMemo } from 'react';
import { getToken, setToken, clearToken, AuthToken } from './token';
import { eventEmitter, AUTH_EVENTS } from '../utils/events';

interface SessionContextType {
  token: string | null;
  user: AuthToken['user'] | null;
  loading: boolean;
  setToken: (tokenData: AuthToken) => Promise<void>;
  clearToken: () => Promise<void>;
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

  if (__DEV__) {
    console.log('🔐 SessionProvider: Rendering...');
  }

  // Initialize session from storage
  useEffect(() => {
    const initializeSession = async () => {
      if (__DEV__) {
        console.log('🔐 SessionProvider: Starting session initialization...');
      }
      
      try {
        const tokenData = await getToken();
        if (tokenData) {
          if (__DEV__) {
            console.log('🔐 SessionProvider: Found existing token, user:', tokenData.user?.name);
          }
          setTokenState(tokenData.token);
          setUser(tokenData.user || null);
        } else {
          if (__DEV__) {
            console.log('🔐 SessionProvider: No existing token found');
          }
        }
      } catch (error) {
        console.error('🔐 SessionProvider: Failed to initialize session:', error);
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
    if (__DEV__) {
      console.log('🔐 SessionProvider: Setting new token for user:', tokenData.user?.name);
    }
    await setToken(tokenData);
    setTokenState(tokenData.token);
    setUser(tokenData.user || null);
  }, []);

  // Optimized clear token with useCallback
  const clearAuthToken = useCallback(async () => {
    if (__DEV__) {
      console.log('🔐 SessionProvider: Clearing token');
    }
    await clearToken();
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
  const isAuthenticated = useMemo(() => !!token, [token]);

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
    isAuthenticated,
  }), [token, user, loading, setAuthToken, clearAuthToken, isAuthenticated]);

  if (__DEV__) {
    console.log('🔐 SessionProvider: State:', {
      hasToken: !!token,
      hasUser: !!user,
      loading,
      isAuthenticated: !!token
    });
  }

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
      isAuthenticated: false,
    };
  }
  return context;
}
