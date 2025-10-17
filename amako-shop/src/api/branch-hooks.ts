import { useQuery } from '@tanstack/react-query';
import { client } from './client';

export interface Branch {
  id: number;
  name: string;
  address: string;
  phone: string;
  latitude?: number;
  longitude?: number;
  delivery_fee?: number;
  delivery_radius_km?: number;
}

/**
 * Fetch all active branches
 */
export function useBranches() {
  return useQuery<Branch[]>({
    queryKey: ['branches'],
    queryFn: async () => {
      const response = await client.get<Branch[]>('/branches');
      console.log('🏢 Branches fetched:', response.data);
      return response.data;
    },
    staleTime: 1000 * 60 * 5, // Cache for 5 minutes
  });
}

/**
 * Fetch branches with distance calculation based on user location
 */
export function useBranchesWithDistance(latitude?: number, longitude?: number) {
  return useQuery<Branch[]>({
    queryKey: ['branches', 'with-distance', latitude, longitude],
    queryFn: async () => {
      const params = latitude && longitude 
        ? { lat: latitude, lng: longitude }
        : {};
      
      const response = await client.get<Branch[]>('/branches', { params });
      console.log('🏢 Branches with distance fetched:', response.data);
      return response.data;
    },
    enabled: !!latitude && !!longitude, // Only fetch if coordinates are available
    staleTime: 1000 * 60 * 5,
  });
}

