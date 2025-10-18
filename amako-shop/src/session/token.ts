import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'amako-auth-token';

// Enable verbose token logging (set to false to reduce console noise)
const VERBOSE_TOKEN_LOGS = false;

export interface AuthToken {
  token: string;
  user?: {
    id: string;
    name: string;
    email?: string;
    phone?: string;
  };
}

/**
 * Get the stored authentication token
 */
export async function getToken(): Promise<AuthToken | null> {
  try {
    const tokenData = await SecureStore.getItemAsync(TOKEN_KEY);
    
    if (!tokenData) {
      return null;
    }
    
    const parsed = JSON.parse(tokenData);
    
    if (__DEV__ && VERBOSE_TOKEN_LOGS) {
      console.log('🔑 Token loaded for:', parsed.user?.name);
    }
    
    return parsed;
  } catch (error) {
    if (__DEV__) {
      console.error('🔑 Token error:', error);
    }
    return null;
  }
}

/**
 * Store the authentication token securely
 */
export async function setToken(tokenData: AuthToken): Promise<void> {
  try {
    const serialized = JSON.stringify(tokenData);
    await SecureStore.setItemAsync(TOKEN_KEY, serialized);
    
    if (__DEV__ && VERBOSE_TOKEN_LOGS) {
      console.log('🔑 Token stored for:', tokenData.user?.name);
    }
  } catch (error) {
    if (__DEV__) {
      console.error('🔑 Token error setting token:', error);
    }
    throw error;
  }
}

/**
 * Clear the stored authentication token
 */
export async function clearToken(): Promise<void> {
  try {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    
    if (__DEV__ && VERBOSE_TOKEN_LOGS) {
      console.log('🔑 Token cleared');
    }
  } catch (error) {
    if (__DEV__) {
      console.error('🔑 Token error clearing:', error);
    }
    throw error;
  }
}

/**
 * Clear any corrupted tokens and reset authentication state
 */
export async function resetAuthState(): Promise<void> {
  try {
    if (__DEV__) {
      console.log('🔑 Token: Resetting authentication state...');
    }
    
    // Try to get the current token to see if it's corrupted
    const currentToken = await SecureStore.getItemAsync(TOKEN_KEY);
    if (currentToken) {
      try {
        JSON.parse(currentToken);
        if (__DEV__) {
          console.log('🔑 Token: Current token is valid, no reset needed');
        }
        return;
      } catch (parseError) {
        if (__DEV__) {
          console.log('🔑 Token: Current token is corrupted, clearing it');
        }
        await SecureStore.deleteItemAsync(TOKEN_KEY);
      }
    }
    
    if (__DEV__) {
      console.log('🔑 Token: Authentication state reset successfully');
    }
  } catch (error) {
    console.error('🔑 Token: Error resetting auth state:', error);
    throw error;
  }
}

/**
 * Check if a token exists and is valid
 */
export async function hasValidToken(): Promise<boolean> {
  try {
    const token = await getToken();
    const isValid = !!token?.token;
    
    if (__DEV__) {
      console.log('🔑 Token: Token validation check:', isValid);
    }
    
    return isValid;
  } catch (error) {
    if (__DEV__) {
      console.log('🔑 Token: Token validation failed:', error);
    }
    return false;
  }
}
