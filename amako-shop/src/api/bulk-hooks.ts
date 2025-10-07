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
    // Fallback to mock data if API fails
    return {
      packages: {
        cooked: {},
        frozen: {}
      },
      products: [
        { id: 1, name: 'Cheese Corn Momos', price: 6.00, category: 'buff', image: '/storage/products/foods/cheese-corn-momos.jpg' },
        { id: 2, name: 'Paneer Momos', price: 6.00, category: 'buff', image: '/storage/products/foods/paneer-momos.jpg' },
        { id: 3, name: 'Chicken Momos', price: 8.00, category: 'chicken', image: '/storage/products/foods/chicken-momos.jpg' },
        { id: 4, name: 'Veg Momos', price: 5.00, category: 'veg', image: '/storage/products/foods/veg-momos.jpg' },
        { id: 5, name: 'Coca Cola', price: 50.00, category: 'cold', image: '/storage/products/drinks/coca-cola.jpg' },
        { id: 6, name: 'French Fries', price: 120.00, category: 'side', image: '/storage/products/sides/french-fries.jpg' },
        { id: 7, name: 'Chocolate Brownie', price: 150.00, category: 'desserts', image: '/storage/products/desserts/chocolate-brownie.jpg' },
        { id: 8, name: 'Hot Chocolate', price: 80.00, category: 'hot', image: '/storage/products/drinks/hot-chocolate.jpg' },
      ],
      bulkDiscountPercentage: 15,
    };
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
