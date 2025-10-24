import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { client } from './client';

export interface Offer {
  id: number;
  title: string;
  description: string;
  code: string;
  discount: number;
  type: string;
  min_purchase: number;
  max_discount: number;
  valid_until: string;
  valid_from: string;
  is_active: boolean;
  target_audience: string;
  ai_generated?: boolean;
  ai_reasoning?: string;
}

export interface OfferClaim {
  id: number;
  user_id: number;
  offer_id: number;
  offer: Offer;
  claimed_at: string;
  used_at: string | null;
  order_id: number | null;
  discount_applied: number | null;
  status: 'active' | 'used' | 'expired';
  created_at: string;
  updated_at: string;
}

export interface ClaimOfferResponse {
  success: boolean;
  message: string;
  offer?: Offer;
  claim_id?: number;
}

// Claim an offer by code
export const useClaimOffer = () => {
  const queryClient = useQueryClient();
  
  return useMutation<ClaimOfferResponse, Error, string>({
    mutationFn: async (offerCode: string) => {
      const response = await client.post('/offers/claim', { offer_code: offerCode });
      return response.data;
    },
    onSuccess: () => {
      // Invalidate queries to refresh data
      queryClient.invalidateQueries({ queryKey: ['my-offers'] });
      queryClient.invalidateQueries({ queryKey: ['available-offers'] });
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });
};

// Get user's claimed offers
export const useMyOffers = () => {
  return useQuery<OfferClaim[]>({
    queryKey: ['my-offers'],
    queryFn: async () => {
      const response = await client.get('/offers/my-claims');
      return response.data.claims || [];
    },
    staleTime: 30000, // 30 seconds
  });
};

// Get available offers for user
export const useAvailableOffers = () => {
  return useQuery<Offer[]>({
    queryKey: ['available-offers'],
    queryFn: async () => {
      const response = await client.get('/offers/available');
      return response.data.offers || [];
    },
    staleTime: 60000, // 1 minute
  });
};

// Apply offer to cart
export const useApplyOffer = () => {
  const queryClient = useQueryClient();
  
  return useMutation<any, Error, string>({
    mutationFn: async (offerCode: string) => {
      const response = await client.post('/offers/apply', { offer_code: offerCode });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });
};

// Remove offer from cart
export const useRemoveOffer = () => {
  const queryClient = useQueryClient();
  
  return useMutation<any, Error, void>({
    mutationFn: async () => {
      const response = await client.post('/offers/remove');
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });
};

