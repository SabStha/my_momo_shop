import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'amako-auth-token';

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
    if (__DEV__) {
      console.log('🔑 Token: Attempting to get token from SecureStore...');
    }
    
    const tokenData = await SecureStore.getItemAsync(TOKEN_KEY);
    
    if (__DEV__) {
      console.log('🔑 Token: SecureStore response:', tokenData ? 'Token found' : 'No token');
      if (tokenData) {
        console.log('🔑 Token: Raw token data:', tokenData.substring(0, 100) + '...');
      }
    }
    
    if (!tokenData) {
      return null;
    }
    
    const parsed = JSON.parse(tokenData);
    if (__DEV__) {
      console.log('🔑 Token: Parsed token data:', {
        hasToken: !!parsed.token,
        hasUser: !!parsed.user,
        userName: parsed.user?.name,
        fullParsed: parsed
      });
    }
    
    return parsed;
  } catch (error) {
    console.error('🔑 Token: Error getting token:', error);
    return null;
  }
}

/**
 * Store the authentication token securely
 */
export async function setToken(tokenData: AuthToken): Promise<void> {
  try {
    if (__DEV__) {
      console.log('🔑 Token: Storing token for user:', tokenData.user?.name);
      console.log('🔑 Token: Token data to store:', JSON.stringify(tokenData, null, 2));
    }
    
    const serialized = JSON.stringify(tokenData);
    await SecureStore.setItemAsync(TOKEN_KEY, serialized);
    
    if (__DEV__) {
      console.log('🔑 Token: Token stored successfully');
      console.log('🔑 Token: Serialized data:', serialized);
    }
  } catch (error) {
    console.error('🔑 Token: Error setting token:', error);
    throw error;
  }
}

/**
 * Clear the stored authentication token
 */
export async function clearToken(): Promise<void> {
  try {
    if (__DEV__) {
      console.log('🔑 Token: Clearing stored token...');
    }
    
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    
    if (__DEV__) {
      console.log('🔑 Token: Token cleared successfully');
    }
  } catch (error) {
    console.error('🔑 Token: Error clearing token:', error);
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
