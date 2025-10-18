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
  console.log('🔄 Fetching loyalty data from API...');
  const response = await client.get('/loyalty');
  console.log('✅ Loyalty API Response:', {
    credits: response.data?.credits,
    tier: response.data?.tier,
    badgesCount: response.data?.badges?.length,
    totalBadges: response.data?.total_badges,
  });
  console.log('🏆 Full Badges Data:', JSON.stringify(response.data?.badges, null, 2));
  console.log('📈 Badge Progress:', JSON.stringify(response.data?.badge_progress, null, 2));
  return response.data;
}

// React Query hook
export function useLoyalty() {
  console.log('🎯 useLoyalty hook initialized');
  const result = useQuery({
    queryKey: ['loyalty'],
    queryFn: getLoyalty,
    staleTime: 60_000, // 1 minute
    retry: 2,
    retryDelay: 1000,
  });
  
  console.log('🎯 useLoyalty result:', {
    isLoading: result.isLoading,
    isError: result.isError,
    error: result.error,
    hasData: !!result.data,
    badgesCount: result.data?.badges?.length,
  });
  
  return result;
}
