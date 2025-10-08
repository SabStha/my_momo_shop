import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import { MenuService } from './menu';
import { Category, MenuItem, ApiError } from '../types';
import { normalizeAxiosError } from './errors';

// Query keys for menu data
export const menuQueryKeys = {
  all: ['menu'] as const,
  categories: () => [...menuQueryKeys.all, 'categories'] as const,
  items: () => [...menuQueryKeys.all, 'items'] as const,
  item: (id: string) => [...menuQueryKeys.all, 'item', id] as const,
  byCategory: (categoryId: string) => [...menuQueryKeys.all, 'category', categoryId] as const,
  search: (query: string) => [...menuQueryKeys.all, 'search', query] as const,
} as const;

// Common query options
const commonQueryOptions = {
  staleTime: 5 * 60 * 1000, // 5 minutes
  retry: 1,
  retryDelay: 1000,
} as const;

// Memoized fallback data to prevent infinite loops
const fallbackData = MenuService.getFallbackData();
const fallbackCategories = fallbackData.categories;
const fallbackItems = fallbackData.items;

/**
 * Hook to fetch full menu (categories + items) with offline fallback
 */
export function useMenu(options?: UseQueryOptions<
  { categories: Category[]; items: MenuItem[] },
  ApiError
>) {
  return useQuery({
    queryKey: menuQueryKeys.all,
    queryFn: async () => {
      try {
        console.log('🍽️ useMenu: Calling MenuService.getMenu()');
        const result = await MenuService.getMenu();
        console.log('🍽️ useMenu: API result received:', {
          itemsCount: result.items?.length || 0,
          categoriesCount: result.categories?.length || 0,
          sampleItem: result.items?.[0] ? {
            id: result.items[0].id,
            name: result.items[0].name,
            categoryId: result.items[0].categoryId
          } : null
        });
        return result;
      } catch (error) {
        console.error('🍽️ useMenu: Error in queryFn:', error);
        throw normalizeAxiosError(error);
      }
    },
    // Remove initialData to force API call
    // initialData: fallbackData, // Instant UI with fallback data
    ...commonQueryOptions,
    ...options,
  });
}

/**
 * Hook to fetch categories with offline fallback
 */
export function useCategories(options?: UseQueryOptions<Category[], ApiError>) {
  return useQuery({
    queryKey: menuQueryKeys.categories(),
    queryFn: async () => {
      try {
        return await MenuService.getCategories();
      } catch (error) {
        throw normalizeAxiosError(error);
      }
    },
    // initialData: fallbackCategories, // REMOVED - Use API-first approach
    ...commonQueryOptions,
    ...options,
  });
}

/**
 * Hook to fetch a specific menu item by ID with offline fallback
 */
export function useItem(
  id: string,
  options?: UseQueryOptions<MenuItem | null, ApiError>
) {
  return useQuery({
    queryKey: menuQueryKeys.item(id),
    queryFn: async () => {
      try {
        return await MenuService.getItem(id);
      } catch (error) {
        throw normalizeAxiosError(error);
      }
    },
    enabled: !!id, // Only run query if ID is provided
    // initialData: fallbackItems.find((item: MenuItem) => item.id === id) || null, // REMOVED - API-first
    ...commonQueryOptions,
    ...options,
  });
}

/**
 * Hook to fetch items by category with offline fallback
 */
export function useItemsByCategory(
  categoryId: string,
  options?: UseQueryOptions<MenuItem[], ApiError>
) {
  return useQuery({
    queryKey: menuQueryKeys.byCategory(categoryId),
    queryFn: async () => {
      try {
        return await MenuService.getItemsByCategory(categoryId);
      } catch (error) {
        throw normalizeAxiosError(error);
      }
    },
    enabled: !!categoryId, // Only run query if categoryId is provided
    // initialData: fallbackItems.filter((item: MenuItem) => item.categoryId === categoryId), // REMOVED - API-first
    ...commonQueryOptions,
    ...options,
  });
}

/**
 * Hook to search items with offline fallback
 */
export function useSearchItems(
  query: string,
  options?: UseQueryOptions<MenuItem[], ApiError>
) {
  return useQuery({
    queryKey: menuQueryKeys.search(query),
    queryFn: async () => {
      try {
        return await MenuService.searchItems(query);
      } catch (error) {
        throw normalizeAxiosError(error);
      }
    },
    enabled: !!query && query.length >= 2, // Only run query if query is meaningful
    // initialData removed - API-first approach
    ...commonQueryOptions,
    ...options,
  });
}

/**
 * Hook to check if we're currently using fallback data
 * Useful for showing offline indicators in the UI
 */
export function useIsOffline(options?: UseQueryOptions<boolean, ApiError>) {
  return useQuery({
    queryKey: ['offline-status'],
    queryFn: async () => {
      try {
        return await MenuService.isUsingFallback();
      } catch (error) {
        // If we can't even check the health endpoint, we're offline
        return true;
      }
    },
    staleTime: 30 * 1000, // Check every 30 seconds
    refetchInterval: 30 * 1000,
    ...options,
  });
}

/**
 * Hook to get fallback data directly
 * Useful for components that need immediate access to bundled data
 */
export function useFallbackData() {
  return useQuery({
    queryKey: ['fallback-data'],
    queryFn: () => Promise.resolve(fallbackData),
    staleTime: Infinity, // Never goes stale
    gcTime: Infinity, // Never expires from cache
    enabled: false, // Don't run automatically
  });
}

/**
 * Convenience hook that combines categories and items
 * Useful for screens that need both pieces of data
 */
export function useMenuWithCategories(options?: UseQueryOptions<
  { categories: Category[]; items: MenuItem[] },
  ApiError
>) {
  const categoriesQuery = useCategories();
  const itemsQuery = useQuery({
    queryKey: menuQueryKeys.items(),
    queryFn: async () => {
      try {
        const menu = await MenuService.getMenu();
        return menu.items;
      } catch (error) {
        throw normalizeAxiosError(error);
      }
    },
    // initialData: fallbackItems, // REMOVED - API-first
    ...commonQueryOptions,
  });

  // Combine the queries
  const isLoading = categoriesQuery.isLoading || itemsQuery.isLoading;
  const isError = categoriesQuery.isError || itemsQuery.isError;
  const error = categoriesQuery.error || itemsQuery.error;
  const refetch = () => {
    categoriesQuery.refetch();
    itemsQuery.refetch();
  };

  return {
    categories: categoriesQuery.data || [],
    items: itemsQuery.data || [],
    isLoading,
    isError,
    error,
    refetch,
    // Individual query states
    categoriesQuery,
    itemsQuery,
  };
}
