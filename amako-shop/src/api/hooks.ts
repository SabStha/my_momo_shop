import { useQuery, useMutation, useQueryClient, UseQueryOptions, UseMutationOptions } from '@tanstack/react-query';
import { healthService, authService, userService, cartService, orderService } from './services';
import { HealthResponse, ApiResponse } from './types';

// Query keys for React Query
export const queryKeys = {
  health: ['health'] as const,
  auth: ['auth'] as const,
  user: ['user'] as const,
  menu: ['menu'] as const,
  cart: ['cart'] as const,
  orders: ['orders'] as const,
} as const;

// Health check hook
import { useHealth } from './health';
export { useHealth };

// Auth hooks
export function useLogin() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) =>
      authService.login(email, password),
    onSuccess: (data) => {
      // Invalidate user queries after successful login
      queryClient.invalidateQueries({ queryKey: queryKeys.user });
    },
  });
}

export function useLogout() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: authService.logout,
    onSuccess: () => {
      // Clear all queries after logout
      queryClient.clear();
    },
  });
}

// User hooks
export function useUserProfile(options?: UseQueryOptions<ApiResponse<any>, Error>) {
  return useQuery({
    queryKey: queryKeys.user,
    queryFn: userService.getProfile,
    enabled: false, // Don't fetch automatically, call refetch() when needed
    ...options,
  });
}

export function useUpdateProfile() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: userService.updateProfile,
    onSuccess: () => {
      // Invalidate user profile after update
      queryClient.invalidateQueries({ queryKey: queryKeys.user });
    },
  });
}

// Menu hooks
export * from './menu-hooks';

// Cart hooks
export function useCartItems() {
  return useQuery({
    queryKey: queryKeys.cart,
    queryFn: cartService.getItems,
    staleTime: 0, // Always fresh
    gcTime: 5 * 60 * 1000, // 5 minutes
  });
}

export function useAddToCart() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ itemId, quantity }: { itemId: string; quantity: number }) =>
      cartService.addItem(itemId, quantity),
    onSuccess: () => {
      // Invalidate cart queries after adding item
      queryClient.invalidateQueries({ queryKey: queryKeys.cart });
    },
  });
}

export function useUpdateCartItem() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ itemId, quantity }: { itemId: string; quantity: number }) =>
      cartService.updateQuantity(itemId, quantity),
    onSuccess: () => {
      // Invalidate cart queries after updating item
      queryClient.invalidateQueries({ queryKey: queryKeys.cart });
    },
  });
}

export function useRemoveFromCart() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: cartService.removeItem,
    onSuccess: () => {
      // Invalidate cart queries after removing item
      queryClient.invalidateQueries({ queryKey: queryKeys.cart });
    },
  });
}

export function useClearCart() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: cartService.clear,
    onSuccess: () => {
      // Invalidate cart queries after clearing
      queryClient.invalidateQueries({ queryKey: queryKeys.cart });
    },
  });
}

// Order hooks
export function useOrders(params?: { status?: string; page?: number; limit?: number }) {
  return useQuery({
    queryKey: [...queryKeys.orders, params],
    queryFn: () => orderService.getOrders(params),
    staleTime: 2 * 60 * 1000, // 2 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes
  });
}

export function useOrder(orderId: string) {
  return useQuery({
    queryKey: [...queryKeys.orders, orderId],
    queryFn: () => orderService.getOrder(orderId),
    enabled: !!orderId,
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 15 * 60 * 1000, // 15 minutes
  });
}

export function useCreateOrder() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: orderService.createOrder,
    onSuccess: () => {
      // Invalidate orders and cart queries after creating order
      queryClient.invalidateQueries({ queryKey: queryKeys.orders });
      queryClient.invalidateQueries({ queryKey: queryKeys.cart });
    },
  });
}

export function useCancelOrder() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: orderService.cancelOrder,
    onSuccess: () => {
      // Invalidate orders queries after cancelling
      queryClient.invalidateQueries({ queryKey: queryKeys.orders });
    },
  });
}

// Utility hook to check if any query is loading
export function useIsAnyLoading() {
  const healthQuery = useHealth();
  
  return healthQuery.isLoading;
}

// Utility hook to check if any query has error
export function useHasAnyError() {
  const healthQuery = useHealth();
  
  return healthQuery.isError;
}
