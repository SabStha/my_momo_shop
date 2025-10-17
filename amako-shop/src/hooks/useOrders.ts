import { useQuery } from '@tanstack/react-query';
import { getUserOrders, getOrder } from '../api/orders';

/**
 * Hook to fetch user's orders from backend
 */
export function useBackendOrders() {
  return useQuery({
    queryKey: ['orders'],
    queryFn: getUserOrders,
    staleTime: 10000, // 10 seconds
    refetchInterval: 15000, // Auto-refresh every 15 seconds
    refetchOnWindowFocus: true,
  });
}

/**
 * Hook to fetch a specific order from backend
 */
export function useBackendOrder(orderId: number) {
  return useQuery({
    queryKey: ['order', orderId],
    queryFn: () => getOrder(orderId),
    enabled: !!orderId,
    staleTime: 5000, // 5 seconds
    refetchInterval: 10000, // Auto-refresh every 10 seconds
    refetchOnWindowFocus: true,
  });
}

