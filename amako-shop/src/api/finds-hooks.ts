import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import { client } from './client';
import { ApiError } from './errors';

export interface MerchandiseItem {
  id: number;
  name: string;
  description: string;
  price: number;
  formatted_price: string;
  image: string;
  image_url: string;
  category: string;
  model: string;
  purchasable: boolean;
  status: string;
  badge?: string;
  badge_color?: string;
}

export interface BulkPackage {
  id: number;
  name: string;
  description: string;
  emoji: string;
  badge?: string;
  badge_color?: string;
  items: Array<{
    name: string;
    price: number;
  }>;
  total_price: number;
}

export interface FindsConfig {
  finds_title: string;
  finds_subtitle: string;
  add_to_cart_text: string;
  unlockable_text: string;
  progress_message: string;
  earn_tooltip_message: string;
  urgency_badge_text: string;
  earn_badge_text: string;
}

export interface FindsCategory {
  key: string;
  label: string;
  icon: string;
  description: string;
}

export interface FindsData {
  categories: FindsCategory[];
  merchandise: {
    tshirts: MerchandiseItem[];
    accessories: MerchandiseItem[];
    toys: MerchandiseItem[];
    limited: MerchandiseItem[];
  };
  bulkPackages: BulkPackage[];
  config: FindsConfig;
}

// Query keys
export const findsQueryKeys = {
  all: ['finds'] as const,
  data: () => [...findsQueryKeys.all, 'data'] as const,
} as const;

// API functions
const fetchFindsData = async (): Promise<FindsData> => {
  try {
    const response = await client.get('/finds/data');
    return response.data;
  } catch (error) {
    console.log('Finds API Error:', error);
    // Fallback to mock data if API fails
    return {
      categories: [
        {
          key: 'buyable',
          label: 'BUY',
          icon: '🛒',
          description: 'Items you can purchase directly',
        },
        {
          key: 'unlockable',
          label: 'EARN',
          icon: '🎁',
          description: 'Items you can earn by completing meals',
        },
        {
          key: 'tshirts',
          label: 'SHIRT',
          icon: '👕',
          description: 'Exclusive t-shirts and apparel',
        },
        {
          key: 'accessories',
          label: 'GIFT',
          icon: '🎁',
          description: 'Accessories and gift items',
        },
        {
          key: 'toys',
          label: 'TOYS',
          icon: '🧸',
          description: 'Fun toys and collectibles',
        },
        {
          key: 'limited',
          label: 'LIM',
          icon: '⚡',
          description: 'Limited edition items',
        },
      ],
      merchandise: {
        tshirts: [
          {
            id: 1,
            name: 'Amako Signature T-Shirt',
            description: 'Premium cotton t-shirt with Amako logo',
            price: 25.00,
            formatted_price: 'Rs.25.00',
            image: 'amako-tshirt.jpg',
            image_url: 'https://via.placeholder.com/300x300/6E0D25/FFFFFF?text=Amako+T-Shirt',
            category: 'tshirts',
            model: 'all',
            purchasable: true,
            status: 'active',
            badge: 'New',
            badge_color: '#10B981',
          },
          {
            id: 2,
            name: 'Amako Vintage T-Shirt',
            description: 'Retro style Amako t-shirt',
            price: 30.00,
            formatted_price: 'Rs.30.00',
            image: 'amako-vintage.jpg',
            image_url: 'https://via.placeholder.com/300x300/8B0D2F/FFFFFF?text=Vintage+T-Shirt',
            category: 'tshirts',
            model: 'all',
            purchasable: false,
            status: 'active',
            badge: 'Exclusive',
            badge_color: '#F59E0B',
          },
        ],
        accessories: [
          {
            id: 3,
            name: 'Amako Keychain',
            description: 'Metal keychain with Amako logo',
            price: 8.00,
            formatted_price: 'Rs.8.00',
            image: 'amako-keychain.jpg',
            image_url: 'https://via.placeholder.com/300x300/6E0D25/FFFFFF?text=Keychain',
            category: 'accessories',
            model: 'all',
            purchasable: true,
            status: 'active',
            badge: 'Popular',
            badge_color: '#EF4444',
          },
          {
            id: 4,
            name: 'Amako Mug',
            description: 'Ceramic coffee mug',
            price: 15.00,
            formatted_price: 'Rs.15.00',
            image: 'amako-mug.jpg',
            image_url: 'https://via.placeholder.com/300x300/8B0D2F/FFFFFF?text=Coffee+Mug',
            category: 'accessories',
            model: 'all',
            purchasable: false,
            status: 'active',
            badge: 'Gift',
            badge_color: '#8B5CF6',
          },
        ],
        toys: [
          {
            id: 5,
            name: 'Amako Plushie',
            description: 'Soft plush toy mascot',
            price: 20.00,
            formatted_price: 'Rs.20.00',
            image: 'amako-plushie.jpg',
            image_url: 'https://via.placeholder.com/300x300/6E0D25/FFFFFF?text=Plushie',
            category: 'toys',
            model: 'all',
            purchasable: true,
            status: 'active',
            badge: 'Cute',
            badge_color: '#EC4899',
          },
          {
            id: 6,
            name: 'Amako Action Figure',
            description: 'Collectible action figure',
            price: 35.00,
            formatted_price: 'Rs.35.00',
            image: 'amako-figure.jpg',
            image_url: 'https://via.placeholder.com/300x300/8B0D2F/FFFFFF?text=Action+Figure',
            category: 'toys',
            model: 'all',
            purchasable: false,
            status: 'active',
            badge: 'Rare',
            badge_color: '#F59E0B',
          },
        ],
        limited: [
          {
            id: 7,
            name: 'Amako Limited Hoodie',
            description: 'Limited edition hoodie',
            price: 60.00,
            formatted_price: 'Rs.60.00',
            image: 'amako-hoodie.jpg',
            image_url: 'https://via.placeholder.com/300x300/6E0D25/FFFFFF?text=Hoodie',
            category: 'limited',
            model: 'all',
            purchasable: true,
            status: 'active',
            badge: 'Limited',
            badge_color: '#DC2626',
          },
          {
            id: 8,
            name: 'Amako Anniversary Pin',
            description: 'Special anniversary pin',
            price: 12.00,
            formatted_price: 'Rs.12.00',
            image: 'amako-pin.jpg',
            image_url: 'https://via.placeholder.com/300x300/8B0D2F/FFFFFF?text=Pin',
            category: 'limited',
            model: 'all',
            purchasable: false,
            status: 'active',
            badge: 'Anniversary',
            badge_color: '#7C3AED',
          },
        ],
      },
      bulkPackages: [
        {
          id: 1,
          name: 'Amako Starter Pack',
          description: 'Perfect for new fans',
          emoji: '🎁',
          badge: 'Best Value',
          badge_color: '#10B981',
          items: [
            { name: 'Amako T-Shirt', price: 25.00 },
            { name: 'Amako Keychain', price: 8.00 },
            { name: 'Amako Sticker Pack', price: 5.00 },
          ],
          total_price: 30.00,
        },
        {
          id: 2,
          name: 'Amako Super Fan Pack',
          description: 'For the ultimate Amako fan',
          emoji: '⭐',
          badge: 'Premium',
          badge_color: '#F59E0B',
          items: [
            { name: 'Amako Hoodie', price: 60.00 },
            { name: 'Amako Mug', price: 15.00 },
            { name: 'Amako Plushie', price: 20.00 },
            { name: 'Amako Pin Set', price: 10.00 },
          ],
          total_price: 85.00,
        },
      ],
      config: {
        finds_title: "Ama's Finds",
        finds_subtitle: "Buy some, earn others — welcome to Ama's Finds",
        add_to_cart_text: "🛒 Add to Cart",
        unlockable_text: "🎁 Unlockable",
        progress_message: "🔥 You're 1 meal away from unlocking this!",
        earn_tooltip_message: "Unlock this by ordering the Couple Combo meal this month!",
        urgency_badge_text: "🔥 Buy Now",
        earn_badge_text: "🎁 Earn It",
      },
    };
  }
};

// Hooks
export function useFindsData(options?: UseQueryOptions<FindsData, ApiError>) {
  return useQuery({
    queryKey: findsQueryKeys.data(),
    queryFn: fetchFindsData,
    staleTime: 5 * 60 * 1000, // 5 minutes
    retry: 1,
    ...options,
  });
}
