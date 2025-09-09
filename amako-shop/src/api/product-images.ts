import { client as apiClient } from './client';

export interface ProductImage {
  id: number;
  name: string;
  image_path: string;
  image_url: string;
  tag: string;
  category: string;
}

export interface ProductImagesResponse {
  products: ProductImage[];
  count: number;
}

export interface ProductImageResponse {
  product_id: number;
  product_name: string;
  image_path: string;
  image_url: string;
}

/**
 * Fetch all product images from the database
 */
export const fetchProductImages = async (): Promise<ProductImagesResponse> => {
  try {
    if (__DEV__) {
      console.log('🔧 API: Starting fetchProductImages...');
      console.log('🔧 API: apiClient type:', typeof apiClient);
      console.log('🔧 API: apiClient.get type:', typeof apiClient?.get);
    }
    const response = await apiClient.get('/product-images');
    if (__DEV__) {
      console.log('🔧 API: Response received:', response);
    }
    return response.data;
  } catch (error) {
    if (__DEV__) {
      console.error('🔧 API: Error details:', error);
    }
    console.error('Failed to fetch product images:', error);
    throw error;
  }
};

/**
 * Fetch product image by product ID
 */
export const fetchProductImage = async (id: number): Promise<ProductImageResponse> => {
  try {
    const response = await apiClient.get(`/product-images/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Failed to fetch product image for ID ${id}:`, error);
    throw error;
  }
};

/**
 * Fetch product images by category/tag
 */
export const fetchProductImagesByCategory = async (category: string): Promise<ProductImagesResponse> => {
  try {
    const response = await apiClient.get(`/product-images/category/${category}`);
    return response.data;
  } catch (error) {
    console.error(`Failed to fetch product images for category ${category}:`, error);
    throw error;
  }
};

/**
 * Get image URL for a product by name (fallback to emoji if no image found)
 */
export const getProductImageUrl = async (productName: string): Promise<string | null> => {
  try {
    // Try to find a product with a similar name
    const allImages = await fetchProductImages();
    const productNameLower = productName.toLowerCase();
    
    // Find exact or partial match
    const matchingProduct = allImages.products.find(product => 
      product.name.toLowerCase().includes(productNameLower) ||
      productNameLower.includes(product.name.toLowerCase())
    );
    
    if (matchingProduct) {
      return matchingProduct.image_url;
    }
    
    return null;
  } catch (error) {
    console.error('Failed to get product image URL:', error);
    return null;
  }
};
