import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import { client } from './client';
import { ApiError } from './errors';

export interface BulkPackage {
  id: number;
  name: string;
  description: string;
  emoji: string;
  type: string;
  package_key: string;
  items: Array<{
    name: string;
    quantity?: number | string;
    price?: number | string;
  }>;
  total_price: number | string;
  bulk_price?: number | string;
  image?: string;
  feeds_people?: string;
  savings_description?: string;
  original_price?: number | string;
  delivery_note?: string;
  deal_title?: string;
  badge?: string;
  badge_color?: string;
}

export interface Product {
  id: number;
  name: string;
  price: number | string;
  category: string;
  image?: string;
}

export interface BulkData {
  packages: {
    cooked: Record<string, BulkPackage>;
    frozen: Record<string, BulkPackage>;
  };
  products: Product[];
  bulkDiscountPercentage: number;
}

// Query keys
export const bulkQueryKeys = {
  all: ['bulk'] as const,
  data: () => [...bulkQueryKeys.all, 'data'] as const,
} as const;

// API functions
const fetchBulkData = async (): Promise<BulkData> => {
  try {
    const response = await client.get('/bulk/data');
    console.log('🔍 Bulk API Response:', response.data);
    console.log('🔍 Bulk API Products:', response.data?.products?.length || 0);
    return response.data;
  } catch (error) {
    console.log('Bulk API Error:', error);
    // No fallback - API-first approach
    throw error;
  }
};

// Hooks
export function useBulkData(options?: UseQueryOptions<BulkData, ApiError>) {
  return useQuery({
    queryKey: bulkQueryKeys.data(),
    queryFn: fetchBulkData,
    staleTime: 5 * 60 * 1000, // 5 minutes
    retry: 1,
    ...options,
  });
}
