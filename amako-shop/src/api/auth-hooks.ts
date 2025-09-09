import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { login, register, getProfile, logout, LoginCredentials, RegisterCredentials } from './auth';
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
      console.error('Registration failed:', error);
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
      // Clear token from secure storage
      await clearToken();

      // Clear all queries from cache
      await queryClient.clear();

      // Navigate to login
      router.replace('/(auth)/login');
    },
    onError: async (error) => {
      console.error('Logout failed:', error);
      
      // Even if logout fails on server, clear local token
      await clearToken();
      await queryClient.clear();
      router.replace('/(auth)/login');
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
