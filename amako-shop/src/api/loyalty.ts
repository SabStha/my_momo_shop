import { useQuery } from '@tanstack/react-query';
import { client } from './client';

// Types
export interface LoyaltyBadge {
  id: string;
  name: string;
  tier: 'Bronze' | 'Silver' | 'Gold' | 'Platinum';
}

export interface LoyaltySummary {
  credits: number;
  tier: string;
  badges: LoyaltyBadge[];
}

// API function
export async function getLoyalty(): Promise<LoyaltySummary> {
  const response = await client.get('/loyalty');
  return response.data;
}

// React Query hook
export function useLoyalty() {
  return useQuery({
    queryKey: ['loyalty'],
    queryFn: getLoyalty,
    staleTime: 60_000, // 1 minute
  });
}
