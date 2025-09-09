import { useState, useEffect, useCallback } from 'react';
import { fetchProductImages, getProductImageUrl as apiGetProductImageUrl, ProductImage } from '../api/product-images';

interface UseProductImagesReturn {
  productImages: ProductImage[];
  getImageUrl: (productName: string) => Promise<string | null>;
  isLoading: boolean;
  error: string | null;
  refreshImages: () => Promise<void>;
}

/**
 * Hook to manage product images with caching and fallbacks
 */
export const useProductImages = (): UseProductImagesReturn => {
  const [productImages, setProductImages] = useState<ProductImage[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [imageCache, setImageCache] = useState<Map<string, string>>(new Map());

  // Fetch all product images
  const fetchImages = useCallback(async () => {
    try {
      if (__DEV__) {
        console.log('🔧 useProductImages: Starting to fetch images...');
      }
      setIsLoading(true);
      setError(null);
      const response = await fetchProductImages();
      if (__DEV__) {
        console.log('🔧 useProductImages: Response received:', response);
      }
      setProductImages(response.products);
    } catch (err) {
      if (__DEV__) {
        console.error('🔧 useProductImages: Error details:', err);
      }
      setError(err instanceof Error ? err.message : 'Failed to fetch product images');
      console.error('Error fetching product images:', err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Get image URL for a specific product
  const getImageUrl = useCallback(async (productName: string): Promise<string | null> => {
    // Check cache first
    if (imageCache.has(productName)) {
      return imageCache.get(productName) || null;
    }

    try {
      // Try to get from database
      const imageUrl = await apiGetProductImageUrl(productName);
      
      if (imageUrl) {
        // Cache the result
        setImageCache(prev => new Map(prev).set(productName, imageUrl));
        return imageUrl;
      }
      
      return null;
    } catch (err) {
      console.error(`Error getting image for ${productName}:`, err);
      return null;
    }
  }, [imageCache]);

  // Refresh images
  const refreshImages = useCallback(async () => {
    await fetchImages();
  }, [fetchImages]);

  // Load images on mount
  useEffect(() => {
    fetchImages();
  }, [fetchImages]);

  return {
    productImages,
    getImageUrl,
    isLoading,
    error,
    refreshImages,
  };
};

/**
 * Hook to get a single product image
 */
export const useProductImage = (productName: string) => {
  const [imageUrl, setImageUrl] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;

    const loadImage = async () => {
      try {
        setIsLoading(true);
        setError(null);
        
        const url = await apiGetProductImageUrl(productName);
        
        if (isMounted) {
          setImageUrl(url);
        }
      } catch (err) {
        if (isMounted) {
          setError(err instanceof Error ? err.message : 'Failed to load image');
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    if (productName) {
      loadImage();
    }

    return () => {
      isMounted = false;
    };
  }, [productName]);

  return { imageUrl, isLoading, error };
};
