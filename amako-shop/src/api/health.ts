import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import { healthService } from './services';
import { HealthResponse } from './types';

// Query key for health checks
export const healthQueryKey = ['health'] as const;

/**
 * Hook to check API health status
 */
export function useHealth(options?: UseQueryOptions<HealthResponse, Error>) {
  return useQuery({
    queryKey: healthQueryKey,
    queryFn: healthService.check,
    staleTime: 30000, // 30 seconds
    gcTime: 5 * 60 * 1000, // 5 minutes
    retry: 3,
    retryDelay: 1000,
    ...options,
  });
}
