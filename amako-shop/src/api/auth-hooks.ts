import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { login, register, getProfile, logout, changePassword, uploadProfilePicture, LoginCredentials, RegisterCredentials, ChangePasswordCredentials } from './auth';
import { useSession } from '../session/SessionProvider';
import { router } from 'expo-router';
import { normalizeAxiosError } from './errors';

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
    mutationFn: login,
    onSuccess: async (data) => {
      if (__DEV__) {
        console.log('🔐 Login Success - Raw response data:', JSON.stringify(data, null, 2));
        console.log('🔐 Login Success - Token:', data.token);
        console.log('🔐 Login Success - User:', data.user);
      }
      
      // Store token in secure storage
      await setToken({
        token: data.token,
        user: data.user,
      });

      // Invalidate and refetch user profile
      await queryClient.invalidateQueries({ queryKey: authQueryKeys.profile });

      // Navigate to main app
      router.replace('/(tabs)');
    },
    onError: (error) => {
      console.error('Login failed:', error);
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
    mutationFn: register,
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

        // Invalidate and refetch user profile
        await queryClient.invalidateQueries({ queryKey: authQueryKeys.profile });

        if (__DEV__) {
          console.log('🔐 Registration: Token stored and cache invalidated, navigating to home');
        }

        // Navigate to main app - using home tab specifically
        router.replace('/(tabs)/home');
      } catch (error) {
        console.error('🔐 Registration: Error in post-registration flow:', error);
        throw error;
      }
    },
    onError: (error) => {
      console.error('🔐 Registration failed:', error);
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
