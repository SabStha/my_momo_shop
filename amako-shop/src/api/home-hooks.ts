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
  benefits: () => [...homeQueryKeys.all, 'benefits'] as const,
} as const;

// Types
export interface FeaturedProduct {
  id: string;
  name: string;
  subtitle?: string;
  price: { currency: string; amount: number };
  image?: string; // Backend sometimes sends 'image' instead of 'imageUrl'
  imageUrl: string;
  isFeatured: boolean;
  ingredients?: string;
  allergens?: string;
  calories?: string;
  preparation_time?: string;
  spice_level?: string;
  serving_size?: string;
  is_vegetarian?: boolean;
  is_vegan?: boolean;
  is_gluten_free?: boolean;
}

export interface HomeStats {
  orders_delivered?: string;
  happy_customers?: string;
  years_in_business?: string;
  momo_varieties?: string;
  growth_percentage?: string;
  satisfaction_rate?: string;
  customer_rating?: string;
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

export interface Benefit {
  id: string;
  emoji: string;
  title: string;
  description: string;
}

export interface StatItem {
  id: string;
  value: string;
  label: string;
  icon: string;
  trend: string;
  trendIcon: string;
}

export interface BenefitsData {
  benefits: Benefit[];
  stats: StatItem[];
  content: {
    title: string;
    subtitle: string;
    ctaText: string;
  };
}

// API functions
const fetchFeaturedProducts = async (): Promise<FeaturedProduct[]> => {
  try {
    // Fetch both featured products AND menu highlights
    const response = await client.get('/menu');
    const raw = response.data;
    const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
    const menuData = parsed?.data;

    if (menuData?.items) {
      // Filter for EITHER featured OR menu highlight items (handles boolean, int, and string "1")
      const featured = menuData.items.filter((item: any) =>
        item.is_featured == 1 || item.is_menu_highlight == 1
      );
      
      console.log('🎯 Featured/Highlight products found:', featured.length);
      console.log('🎯 Sample raw item:', featured[0] ? {
        name: featured[0].name,
        is_featured: featured[0].is_featured,
        is_menu_highlight: featured[0].is_menu_highlight,
        isFeatured: featured[0].isFeatured,
      } : 'None');

      // Transform to FeaturedProduct format.
      // Use ?? false (nullish coalescing) so explicit false/0 from the API is
      // preserved as-is, and only null/undefined defaults to false.
      // imageUrl is set alongside image so both field names work downstream.
      const mapped = featured.map((item: any) => {
        const imageUrl = item.image || item.imageUrl || '';
        const mappedItem = {
          id: item.id.toString(),
          name: item.name,
          subtitle: item.desc || item.description || 'Delicious and authentic',
          price: {
            amount: parseFloat(item.price) || 0,
            currency: 'NPR',
          },
          image: imageUrl,
          imageUrl: imageUrl,
          rating: item.rating || 4.5,
          reviewCount: item.review_count || 0,
        };
        return {
          ...mappedItem,
          is_featured: item.is_featured ?? false,
          is_menu_highlight: item.is_menu_highlight ?? false,
        };
      });

      console.log('🎯 Mapped result count:', mapped.length, '| sample flags:', mapped[0] ? {
        is_featured: mapped[0].is_featured,
        is_menu_highlight: mapped[0].is_menu_highlight,
      } : 'None');

      return mapped;
    }
    
    return [];
  } catch (error) {
    console.log('Featured Products API Error:', error);
    // Return empty rather than re-throwing — home screen should degrade gracefully
    // if the menu endpoint is temporarily unavailable (e.g. dev server busy).
    return [];
  }
};

const fetchHomeStats = async (): Promise<HomeStats> => {
  try {
    const response = await client.get('/stats/home');
    return response.data?.data || {
      orders_delivered: '0+',
      happy_customers: '0+',
      years_in_business: '1+',
      momo_varieties: '0+',
      growth_percentage: '0',
      satisfaction_rate: '100',
      customer_rating: 'No reviews yet',
    };
  } catch (error) {
    console.log('Home Stats API Error:', error);
    // Return empty/zero stats when API fails
    return {
      orders_delivered: '0+',
      happy_customers: '0+',
      years_in_business: '1+',
      momo_varieties: '0+',
      growth_percentage: '0',
      satisfaction_rate: '100',
      customer_rating: 'No reviews yet',
    };
  }
};

const fetchReviews = async (): Promise<Review[]> => {
  try {
    console.log('🔄 Fetching reviews from API...');
    const response = await client.get('/reviews?featured=true');
    console.log('✅ Reviews API response:', {
      success: response.data?.success,
      count: response.data?.count,
      reviewsLength: response.data?.data?.length,
    });
    
    if (response.data?.data && Array.isArray(response.data.data)) {
      console.log('📊 Reviews from API:', response.data.data.length, 'reviews');
      return response.data.data;
    }
    
    console.warn('⚠️ No reviews in API response, returning empty array');
    return [];
  } catch (error) {
    console.error('❌ Reviews API Error:', error);
    console.error('❌ Error details:', {
      message: (error as any).message,
      status: (error as any).status,
      code: (error as any).code,
    });
    console.log('⚠️ Using fallback reviews data from production database');
    // Return actual reviews from database as fallback
    return [
      {
        id: '2',
        name: 'Sabs',
        rating: 5,
        comment: 'Vcxfhv',
        orderItem: 'Hbvcc',
        date: 'Oct 16, 2025',
      },
      {
        id: '1',
        name: 'Anonymous',
        rating: 5,
        comment: 'Hhbbbb',
        orderItem: 'Bbb',
        date: 'Oct 09, 2025',
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

const fetchBenefitsData = async (): Promise<BenefitsData> => {
  try {
    console.log('🔄 Fetching benefits data from API...');
    const response = await client.get('/home/benefits');
    console.log('✅ Benefits API response:', response.data);
    
    if (response.data?.data) {
      console.log('📊 Stats from API:', response.data.data.stats);
      return response.data.data;
    }
    
    console.warn('⚠️ No data in API response, using fallback');
    return {
      benefits: [
        {
          id: '1',
          emoji: '🥬',
          title: 'Fresh Ingredients',
          description: 'High-quality ingredients sourced daily.',
        },
        {
          id: '2',
          emoji: '👩‍🍳',
          title: 'Authentic Recipes',
          description: 'Traditional Nepalese recipes.',
        },
        {
          id: '3',
          emoji: '🚚',
          title: 'Fast Delivery',
          description: '25 minutes average delivery.',
        },
      ],
      stats: [
        {
          id: '1',
          value: '0+',
          label: 'Orders Delivered',
          icon: 'truck-delivery',
          trend: 'Just getting started',
          trendIcon: 'trending-up',
        },
        {
          id: '2',
          value: '0+',
          label: 'Happy Customers',
          icon: 'account-heart',
          trend: 'Building our community',
          trendIcon: 'emoticon-happy',
        },
        {
          id: '3',
          value: '1+',
          label: 'Years in Business',
          icon: 'trophy',
          trend: 'Trusted brand',
          trendIcon: 'shield-check',
        },
      ],
      content: {
        title: '✨ Why Choose Ama Ko Shop?',
        subtitle: 'From our kitchen to your heart — authentic momos made with love.',
        ctaText: 'Try Our Momos Today'
      }
    };
  } catch (error) {
    console.error('❌ Benefits API Error:', error);
    console.error('❌ Error details:', {
      message: (error as any).message,
      status: (error as any).status,
      code: (error as any).code,
    });
    console.log('⚠️ Using fallback data with real stats from verification');
    // Fallback data - using real numbers from database verification
    return {
      benefits: [
        {
          id: '1',
          emoji: '🥬',
          title: 'Fresh Ingredients',
          description: 'High-quality ingredients sourced daily.',
        },
        {
          id: '2',
          emoji: '👩‍🍳',
          title: 'Authentic Recipes',
          description: 'Traditional Nepalese recipes.',
        },
        {
          id: '3',
          emoji: '🚚',
          title: 'Fast Delivery',
          description: '25 minutes average delivery.',
        },
      ],
      stats: [
        {
          id: '1',
          value: '25+',
          label: 'Orders Delivered',
          icon: 'truck-delivery',
          trend: 'Growing fast',
          trendIcon: 'trending-up',
        },
        {
          id: '2',
          value: '1+',
          label: 'Happy Customers',
          icon: 'account-heart',
          trend: '100% satisfaction',
          trendIcon: 'emoticon-happy',
        },
        {
          id: '3',
          value: '1+',
          label: 'Years in Business',
          icon: 'trophy',
          trend: 'Trusted brand',
          trendIcon: 'shield-check',
        },
      ],
      content: {
        title: '✨ Why Choose Ama Ko Shop?',
        subtitle: 'From our kitchen to your heart — authentic momos made with love.',
        ctaText: 'Try Our Momos Today'
      }
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

export function useBenefitsData(options?: UseQueryOptions<BenefitsData>) {
  return useQuery({
    queryKey: homeQueryKeys.benefits(),
    queryFn: fetchBenefitsData,
    staleTime: 15 * 60 * 1000, // 15 minutes
    retry: 1,
    ...options,
  });
}
