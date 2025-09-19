import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import { client } from './client';
import { normalizeAxiosError } from './errors';

// Query keys for home data
export const homeQueryKeys = {
  all: ['home'] as const,
  featuredProducts: () => [...homeQueryKeys.all, 'featured-products'] as const,
  homeStats: () => [...homeQueryKeys.all, 'stats'] as const,
  reviews: () => [...homeQueryKeys.all, 'reviews'] as const,
  storeInfo: () => [...homeQueryKeys.all, 'store-info'] as const,
} as const;

// Types
export interface FeaturedProduct {
  id: string;
  name: string;
  subtitle?: string;
  price: { currency: string; amount: number };
  imageUrl: string;
  isFeatured: boolean;
}

export interface HomeStats {
  happyCustomers: string;
  momoVarieties: string;
  rating: string;
}

export interface Review {
  id: string;
  name: string;
  rating: number;
  comment: string;
  orderItem: string;
  date: string;
}

export interface StoreInfo {
  address: string;
  phone: string;
  email: string;
  businessHours: {
    day: string;
    open: string;
    close: string;
    isOpen: boolean;
  }[];
  socialMedia: {
    facebook?: string;
    instagram?: string;
    twitter?: string;
  };
}

// API functions
const fetchFeaturedProducts = async (): Promise<FeaturedProduct[]> => {
  try {
    const response = await client.get('/products/featured');
    return response.data?.data || [];
  } catch (error) {
    // Fallback to mock data if API fails
    return [
      {
        id: '1',
        name: 'Classic Chicken Momo',
        subtitle: 'Juicy chicken, house spice blend',
        price: { currency: 'NPR', amount: 180 },
        imageUrl: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop',
        isFeatured: true,
      },
      {
        id: '2',
        name: 'Vegetable Momo',
        subtitle: 'Fresh vegetables, aromatic herbs',
        price: { currency: 'NPR', amount: 150 },
        imageUrl: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400&h=300&fit=crop',
        isFeatured: true,
      },
      {
        id: '3',
        name: 'Steamed Pork Momo',
        subtitle: 'Tender pork, traditional recipe',
        price: { currency: 'NPR', amount: 200 },
        imageUrl: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=400&h=300&fit=crop',
        isFeatured: true,
      },
      {
        id: '4',
        name: 'Masala Chai',
        subtitle: 'Spiced Indian tea with milk',
        price: { currency: 'NPR', amount: 50 },
        imageUrl: 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=400&h=300&fit=crop',
        isFeatured: true,
      },
    ];
  }
};

const fetchHomeStats = async (): Promise<HomeStats> => {
  try {
    const response = await client.get('/stats/home');
    return response.data?.data || {
      happyCustomers: '500+',
      momoVarieties: '25+',
      rating: '4.8★',
    };
  } catch (error) {
    // Fallback to mock data
    return {
      happyCustomers: '500+',
      momoVarieties: '25+',
      rating: '4.8★',
    };
  }
};

const fetchReviews = async (): Promise<Review[]> => {
  try {
    const response = await client.get('/reviews?featured=true');
    return response.data?.data || [];
  } catch (error) {
    // Fallback to mock data
    return [
      {
        id: '1',
        name: 'Sarah M.',
        rating: 5,
        comment: 'Amazing momos! Fresh and delicious. Will definitely order again.',
        orderItem: 'Chicken Momo',
        date: '2 days ago',
      },
      {
        id: '2',
        name: 'Raj K.',
        rating: 5,
        comment: 'Best momos in town! Fast delivery and great taste.',
        orderItem: 'Vegetable Momo',
        date: '1 week ago',
      },
      {
        id: '3',
        name: 'Priya S.',
        rating: 4,
        comment: 'Good quality and taste. Delivery was on time.',
        orderItem: 'Pork Momo',
        date: '2 weeks ago',
      },
    ];
  }
};

const fetchStoreInfo = async (): Promise<StoreInfo> => {
  try {
    const response = await client.get('/store/info');
    return response.data?.data || {
      address: '123 Momo Street, Kathmandu, Nepal',
      phone: '+977-1-2345678',
      email: 'info@amakoshop.com',
      businessHours: [
        { day: 'Monday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Tuesday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Wednesday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Thursday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Friday', open: '10:00', close: '23:00', isOpen: true },
        { day: 'Saturday', open: '10:00', close: '23:00', isOpen: true },
        { day: 'Sunday', open: '11:00', close: '21:00', isOpen: true },
      ],
      socialMedia: {
        facebook: 'https://facebook.com/amakoshop',
        instagram: 'https://instagram.com/amakoshop',
        twitter: 'https://twitter.com/amakoshop',
      },
    };
  } catch (error) {
    // Fallback to mock data
    return {
      address: '123 Momo Street, Kathmandu, Nepal',
      phone: '+977-1-2345678',
      email: 'info@amakoshop.com',
      businessHours: [
        { day: 'Monday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Tuesday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Wednesday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Thursday', open: '10:00', close: '22:00', isOpen: true },
        { day: 'Friday', open: '10:00', close: '23:00', isOpen: true },
        { day: 'Saturday', open: '10:00', close: '23:00', isOpen: true },
        { day: 'Sunday', open: '11:00', close: '21:00', isOpen: true },
      ],
      socialMedia: {
        facebook: 'https://facebook.com/amakoshop',
        instagram: 'https://instagram.com/amakoshop',
        twitter: 'https://twitter.com/amakoshop',
      },
    };
  }
};

// Hooks
export function useFeaturedProducts(options?: UseQueryOptions<FeaturedProduct[]>) {
  return useQuery({
    queryKey: homeQueryKeys.featuredProducts(),
    queryFn: fetchFeaturedProducts,
    staleTime: 5 * 60 * 1000, // 5 minutes
    retry: 1,
    ...options,
  });
}

export function useHomeStats(options?: UseQueryOptions<HomeStats>) {
  return useQuery({
    queryKey: homeQueryKeys.homeStats(),
    queryFn: fetchHomeStats,
    staleTime: 10 * 60 * 1000, // 10 minutes
    retry: 1,
    ...options,
  });
}

export function useReviews(options?: UseQueryOptions<Review[]>) {
  return useQuery({
    queryKey: homeQueryKeys.reviews(),
    queryFn: fetchReviews,
    staleTime: 5 * 60 * 1000, // 5 minutes
    retry: 1,
    ...options,
  });
}

export function useStoreInfo(options?: UseQueryOptions<StoreInfo>) {
  return useQuery({
    queryKey: homeQueryKeys.storeInfo(),
    queryFn: fetchStoreInfo,
    staleTime: 30 * 60 * 1000, // 30 minutes
    retry: 1,
    ...options,
  });
}
