import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { login, register, getProfile, logout, changePassword, uploadProfilePicture, LoginCredentials, RegisterCredentials, ChangePasswordCredentials } from './auth';
import { useSession } from '../session/SessionProvider';
import { router } from 'expo-router';
import { normalizeAxiosError } from './errors';
import { reset401Counter, setLoggingIn } from './client';

// Query keys
export const authQueryKeys = {
  profile: ['auth', 'profile'] as const,
} as const;

/**
 * Hook for user login
 */
export function useLogin() {
  const { setToken } = useSession();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (credentials: Parameters<typeof login>[0]) => {
      // Mark login as in progress to prevent premature 401 logout
      setLoggingIn(true);
      try {
        return await login(credentials);
      } catch (error) {
        setLoggingIn(false);
        throw error;
      }
    },
    onSuccess: async (data) => {
      console.log('🚀 [LOGIN DEBUG] ===== LOGIN SUCCESS START =====');
      console.log('🚀 [LOGIN DEBUG] Raw response data:', JSON.stringify(data, null, 2));
      console.log('🚀 [LOGIN DEBUG] Token:', data.token);
      console.log('🚀 [LOGIN DEBUG] User:', data.user);
      
      try {
        console.log('🚀 [LOGIN DEBUG] Step 1: Starting token storage...');
        
        // Store token in secure storage
        await setToken({
          token: data.token,
          user: data.user,
        });
        
        console.log('🚀 [LOGIN DEBUG] Step 1: ✅ Token stored successfully');

        // Reset 401 counter after successful login
        console.log('🚀 [LOGIN DEBUG] Step 2: Resetting 401 counter...');
        reset401Counter();
        console.log('🚀 [LOGIN DEBUG] Step 2: ✅ 401 counter reset');

        console.log('🚀 [LOGIN DEBUG] Step 3: Waiting for token propagation (1000ms)...');
        
        // Wait longer for token to propagate to API client and prevent race conditions
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        console.log('🚀 [LOGIN DEBUG] Step 3: ✅ Token propagation delay complete');

        // Invalidate and refetch user profile with error handling
        console.log('🚀 [LOGIN DEBUG] Step 4: Invalidating profile queries...');
        try {
          await queryClient.invalidateQueries({ queryKey: authQueryKeys.profile });
          console.log('🚀 [LOGIN DEBUG] Step 4: ✅ Profile queries invalidated');
        } catch (error) {
          console.warn('🚀 [LOGIN DEBUG] Step 4: ⚠️ Profile invalidation failed (non-critical):', error);
          // Don't throw - this shouldn't break the login flow
        }

        console.log('🚀 [LOGIN DEBUG] Step 5: Clearing login flag...');
        // Clear login flag before navigation
        setLoggingIn(false);
        console.log('🚀 [LOGIN DEBUG] Step 5: ✅ Login flag cleared');

        console.log('🚀 [LOGIN DEBUG] Step 6: Preparing navigation (100ms delay)...');
        
        // Add small delay before navigation to ensure all state is updated
        setTimeout(() => {
          console.log('🚀 [LOGIN DEBUG] Step 6: Attempting navigation...');
          try {
            router.replace('/(tabs)');
            console.log('🚀 [LOGIN DEBUG] Step 6: ✅ Navigation successful - replaced with /(tabs)');
          } catch (error) {
            console.error('🚀 [LOGIN DEBUG] Step 6: ❌ Navigation failed:', error);
            console.log('🚀 [LOGIN DEBUG] Step 6: 🔄 Attempting fallback navigation...');
            // Fallback navigation
            try {
              router.push('/(tabs)/home');
              console.log('🚀 [LOGIN DEBUG] Step 6: ✅ Fallback navigation successful');
            } catch (fallbackError) {
              console.error('🚀 [LOGIN DEBUG] Step 6: ❌ Fallback navigation also failed:', fallbackError);
            }
          }
        }, 100);
        
        console.log('🚀 [LOGIN DEBUG] ===== LOGIN SUCCESS END =====');
      } catch (error) {
        console.error('🚀 [LOGIN DEBUG] ❌ CRITICAL ERROR in post-login flow:', error);
        console.error('🚀 [LOGIN DEBUG] Error details:', {
          message: error.message,
          stack: error.stack,
          name: error.name
        });
        setLoggingIn(false);
        throw error;
      }
    },
    onError: (error) => {
      console.error('Login failed:', error);
      setLoggingIn(false);
      throw normalizeAxiosError(error);
    },
  });
}

/**
 * Hook for user registration
 */
export function useRegister() {
  const { setToken } = useSession();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (credentials: Parameters<typeof register>[0]) => {
      // Mark registration as in progress to prevent premature 401 logout
      setLoggingIn(true);
      try {
        return await register(credentials);
      } catch (error) {
        setLoggingIn(false);
        throw error;
      }
    },
    onSuccess: async (data) => {
      if (__DEV__) {
        console.log('🔐 Registration Success - Raw response data:', JSON.stringify(data, null, 2));
        console.log('🔐 Registration Success - Token:', data.token);
        console.log('🔐 Registration Success - User:', data.user);
      }
      
      try {
        // Store token in secure storage
        await setToken({
          token: data.token,
          user: data.user,
        });

        // Reset 401 counter after successful registration
        reset401Counter();

        if (__DEV__) {
          console.log('🔐 Registration: Token stored, waiting for propagation...');
        }

        // Wait a bit for token to propagate to API client
        await new Promise(resolve => setTimeout(resolve, 500));

        // Invalidate and refetch user profile
        await queryClient.invalidateQueries({ queryKey: authQueryKeys.profile });

        if (__DEV__) {
          console.log('🔐 Registration: Complete, navigating to home');
        }

        // Clear login flag before navigation
        setLoggingIn(false);

        // Navigate to main app - using home tab specifically
        router.replace('/(tabs)/home');
      } catch (error) {
        console.error('🔐 Registration: Error in post-registration flow:', error);
        setLoggingIn(false);
        throw error;
      }
    },
    onError: (error) => {
      console.error('🔐 Registration failed:', error);
      setLoggingIn(false);
      throw normalizeAxiosError(error);
    },
  });
}

/**
 * Hook for user logout
 */
export function useLogout() {
  const { clearToken } = useSession();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: logout,
    onSuccess: async () => {
      console.log('🔐 Logout: Server logout successful');
      // Clear token from secure storage
      await clearToken();
      console.log('🔐 Logout: Token cleared from storage');

      // Clear all queries from cache
      await queryClient.clear();
      console.log('🔐 Logout: Query cache cleared');

      // Navigate to login
      console.log('🔐 Logout: Navigating to login');
      setTimeout(() => {
        router.push('/(auth)/login');
      }, 100);
    },
    onError: async (error) => {
      console.error('🔐 Logout: Server logout failed:', error);
      
      // Even if logout fails on server, clear local token
      await clearToken();
      console.log('🔐 Logout: Token cleared from storage (fallback)');
      
      await queryClient.clear();
      console.log('🔐 Logout: Query cache cleared (fallback)');
      
      console.log('🔐 Logout: Navigating to login (fallback)');
      setTimeout(() => {
        router.push('/(auth)/login');
      }, 100);
    },
  });
}

/**
 * Hook for getting user profile
 */
export function useProfile() {
  return useQuery({
    queryKey: authQueryKeys.profile,
    queryFn: getProfile,
    enabled: false, // Don't run automatically, only when explicitly called
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * Hook for refreshing user profile
 */
export function useRefreshProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: getProfile,
    onSuccess: (data) => {
      // Update profile in cache
      queryClient.setQueryData(authQueryKeys.profile, data);
    },
    onError: (error) => {
      console.error('Profile refresh failed:', error);
      throw normalizeAxiosError(error);
    },
  });
}

/**
 * Hook for changing user password
 */
export function useChangePassword() {
  return useMutation({
    mutationFn: changePassword,
    onSuccess: (data) => {
      console.log('🔐 Change Password: Success', data.message);
    },
    onError: (error) => {
      console.error('🔐 Change Password: Failed', error);
      throw normalizeAxiosError(error);
    },
  });
}

/**
 * Hook for uploading profile picture
 */
export function useUploadProfilePicture() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: uploadProfilePicture,
    onSuccess: (data) => {
      console.log('📸 Upload Profile Picture: Success', data.message);
      // Invalidate and refetch user profile to show new picture
      queryClient.invalidateQueries({ queryKey: authQueryKeys.profile });
    },
    onError: (error) => {
      console.error('📸 Upload Profile Picture: Failed', error);
      throw normalizeAxiosError(error);
    },
  });
}
