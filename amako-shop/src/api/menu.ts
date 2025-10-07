import { client } from './client';
import { normalizeAxiosError } from './errors';
import { Category, MenuItem, ApiError } from '../types';

// Import bundled fallback data
import bundledMenuData from '../../assets/menu.json';

// Types for the bundled data structure
interface BundledMenuData {
  categories: Category[];
  items: MenuItem[];
}

// Cast the bundled data to our expected type
const fallbackData = bundledMenuData as BundledMenuData;

/**
 * Menu service with offline fallback functionality
 */
export class MenuService {
  /**
   * Get full menu (categories + items) with offline fallback
   */
  static async getMenu(): Promise<{ categories: Category[]; items: MenuItem[] }> {
    try {
      console.log('🍽️ MenuService: Attempting to fetch menu from API...');
      // Try to fetch from API first
      const response = await client.get('/menu');
      console.log('🍽️ MenuService: API response received:', response.status);
      console.log('🍽️ MenuService: Response data structure:', {
        hasSuccess: !!response.data?.success,
        hasData: !!response.data?.data,
        itemsCount: response.data?.data?.items?.length || 0,
        categoriesCount: response.data?.data?.categories?.length || 0
      });
      
      if (response.data?.success && response.data?.data) {
        console.log('🍽️ MenuService: API data structure valid, returning API data');
        console.log('🍽️ MenuService: Sample API item:', response.data.data.items?.[0] ? {
          id: response.data.data.items[0].id,
          name: response.data.data.items[0].name,
          categoryId: response.data.data.items[0].categoryId
        } : 'No items');
        return response.data.data;
      }
      
      // If API response doesn't have expected structure, fall back to bundled data
      console.warn('🍽️ MenuService: API response structure unexpected, using fallback data');
      return fallbackData;
      
    } catch (error) {
      // Log the error for debugging
      console.warn('🍽️ MenuService: Failed to fetch menu from API, using fallback data:', error);
      
      // Return bundled fallback data
      return fallbackData;
    }
  }

  /**
   * Get a specific menu item by ID with offline fallback
   */
  static async getItem(id: string): Promise<MenuItem | null> {
    try {
      // Try to fetch from API first
      const response = await client.get(`/items/${id}`);
      
      if (response.data?.success && response.data?.data) {
        return response.data.data;
      }
      
      // If API response doesn't have expected structure, fall back to bundled data
      console.warn('API response structure unexpected, searching fallback data');
      return this.findItemInFallback(id);
      
    } catch (error) {
      // Log the error for debugging
      console.warn(`Failed to fetch item ${id} from API, searching fallback data:`, error);
      
      // Search in bundled fallback data
      return this.findItemInFallback(id);
    }
  }

  /**
   * Get categories with offline fallback
   */
  static async getCategories(): Promise<Category[]> {
    try {
      // Try to fetch from API first
      const response = await client.get('/categories');
      
      if (response.data?.success && response.data?.data) {
        return response.data.data;
      }
      
      // If API response doesn't have expected structure, fall back to bundled data
      console.warn('API response structure unexpected, using fallback categories');
      return fallbackData.categories;
      
    } catch (error) {
      // Log the error for debugging
      console.warn('Failed to fetch categories from API, using fallback data:', error);
      
      // Return bundled fallback data
      return fallbackData.categories;
    }
  }

  /**
   * Get items by category with offline fallback
   */
  static async getItemsByCategory(categoryId: string): Promise<MenuItem[]> {
    try {
      // Try to fetch from API first
      const response = await client.get(`/categories/${categoryId}/items`);
      
      if (response.data?.success && response.data?.data) {
        return response.data.data;
      }
      
      // If API response doesn't have expected structure, fall back to bundled data
      console.warn('API response structure unexpected, filtering fallback data');
      return this.filterItemsByCategory(categoryId);
      
    } catch (error) {
      // Log the error for debugging
      console.warn(`Failed to fetch items for category ${categoryId} from API, filtering fallback data:`, error);
      
      // Filter bundled fallback data
      return this.filterItemsByCategory(categoryId);
    }
  }

  /**
   * Search items by query with offline fallback
   */
  static async searchItems(query: string): Promise<MenuItem[]> {
    try {
      // Try to fetch from API first
      const response = await client.get(`/items/search?q=${encodeURIComponent(query)}`);
      
      if (response.data?.success && response.data?.data) {
        return response.data.data;
      }
      
      // If API response doesn't have expected structure, fall back to bundled data
      console.warn('API response structure unexpected, searching fallback data');
      return this.searchItemsInFallback(query);
      
    } catch (error) {
      // Log the error for debugging
      console.warn(`Failed to search items from API, searching fallback data:`, error);
      
      // Search in bundled fallback data
      return this.searchItemsInFallback(query);
    }
  }

  /**
   * Helper: Find item in fallback data by ID
   */
  private static findItemInFallback(id: string): MenuItem | null {
    const item = fallbackData.items.find(item => item.id === id);
    return item || null;
  }

  /**
   * Helper: Filter items by category in fallback data
   */
  private static filterItemsByCategory(categoryId: string): MenuItem[] {
    return fallbackData.items.filter(item => item.categoryId === categoryId);
  }

  /**
   * Helper: Search items in fallback data by query
   */
  private static searchItemsInFallback(query: string): MenuItem[] {
    const lowerQuery = query.toLowerCase();
    return fallbackData.items.filter(item => 
      item.name.toLowerCase().includes(lowerQuery) ||
      item.desc?.toLowerCase().includes(lowerQuery) ||
      item.categoryId.toLowerCase().includes(lowerQuery)
    );
  }

  /**
   * Get available fallback data for testing/development
   */
  static getFallbackData() {
    return fallbackData;
  }

  /**
   * Check if we're currently using fallback data
   * This can be useful for UI indicators
   */
  static async isUsingFallback(): Promise<boolean> {
    try {
      await client.get('/health');
      return false; // API is reachable
    } catch {
      return true; // Using fallback data
    }
  }
}

// Export the service instance
export const menuService = new MenuService();
