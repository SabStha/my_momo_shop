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
    const response = await client.get('/products/featured');
    return response.data?.data || [];
  } catch (error) {
    console.log('Featured Products API Error:', error);
    // Fallback to mock data if API fails - using actual images from web app
    return [
      {
        id: '1',
        name: 'Classic Pork Momo',
        subtitle: 'Juicy pork, house spice blend',
        price: { currency: 'NPR', amount: 180 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(1),
        isFeatured: true,
        ingredients: 'Ground pork, onions, garlic, ginger, coriander, cumin, turmeric, salt, pepper',
        allergens: 'May contain gluten',
        calories: '320 kcal',
        preparation_time: '15-20 minutes',
        spice_level: 'Medium',
        serving_size: '8 pieces',
        is_vegetarian: false,
        is_vegan: false,
        is_gluten_free: false,
      },
      {
        id: '2',
        name: 'Vegetable Momo',
        subtitle: 'Fresh vegetables, aromatic herbs',
        price: { currency: 'NPR', amount: 150 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(2),
        isFeatured: true,
        ingredients: 'Cabbage, carrots, onions, garlic, ginger, coriander, cumin, turmeric, salt',
        allergens: 'May contain gluten',
        calories: '280 kcal',
        preparation_time: '12-15 minutes',
        spice_level: 'Mild',
        serving_size: '8 pieces',
        is_vegetarian: true,
        is_vegan: true,
        is_gluten_free: false,
      },
      {
        id: '3',
        name: 'Spicy Chicken Momo',
        subtitle: 'Tender chicken, traditional recipe',
        price: { currency: 'NPR', amount: 200 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(3),
        isFeatured: true,
      },
      {
        id: '4',
        name: 'Paneer Momo',
        subtitle: 'Fresh cottage cheese, aromatic spices',
        price: { currency: 'NPR', amount: 160 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(1),
        isFeatured: true,
      },
      {
        id: '5',
        name: 'Cheese Corn Momo',
        subtitle: 'Melted cheese with sweet corn',
        price: { currency: 'NPR', amount: 170 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(2),
        isFeatured: true,
      },
      {
        id: '6',
        name: 'Tandoori Momo',
        subtitle: 'Smoky tandoori flavored momos',
        price: { currency: 'NPR', amount: 190 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(3),
        isFeatured: true,
      },
      {
        id: '7',
        name: 'Fried Chicken Momo',
        subtitle: 'Crispy fried chicken momos',
        price: { currency: 'NPR', amount: 220 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(1),
        isFeatured: true,
      },
      {
        id: '8',
        name: 'Chilli Garlic Momo',
        subtitle: 'Spicy chilli garlic flavored',
        price: { currency: 'NPR', amount: 185 },
        imageUrl: require('../utils/urlHelper').getBannerUrl(2),
        isFeatured: true,
      },
    ];
  }
};

const fetchHomeStats = async (): Promise<HomeStats> => {
  try {
    const response = await client.get('/stats/home');
    return response.data?.data || {
      orders_delivered: '1500+',
      happy_customers: '21+',
      years_in_business: '3+',
      momo_varieties: '21+',
      growth_percentage: '15',
      satisfaction_rate: '98',
      customer_rating: '4.5⭐',
    };
  } catch (error) {
    // Fallback to mock data
    return {
      orders_delivered: '1500+',
      happy_customers: '21+',
      years_in_business: '3+',
      momo_varieties: '21+',
      growth_percentage: '15',
      satisfaction_rate: '98',
      customer_rating: '4.5⭐',
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

const fetchBenefitsData = async (): Promise<BenefitsData> => {
  try {
    const response = await client.get('/home/benefits');
    return response.data?.data || {
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
          value: '179+',
          label: 'Orders Delivered',
          icon: 'truck-delivery',
          trend: '+-100% this month',
          trendIcon: 'trending-up',
        },
        {
          id: '2',
          value: '21+',
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
        subtitle: 'From our kitchen to your heart — here\'s why thousands trust us with their favorite comfort food.',
        ctaText: 'Try Our Momos Today'
      }
    };
  } catch (error) {
    console.log('Benefits API Error:', error);
    // Fallback data
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
          value: '179+',
          label: 'Orders Delivered',
          icon: 'truck-delivery',
          trend: '+-100% this month',
          trendIcon: 'trending-up',
        },
        {
          id: '2',
          value: '21+',
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
        subtitle: 'From our kitchen to your heart — here\'s why thousands trust us with their favorite comfort food.',
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
