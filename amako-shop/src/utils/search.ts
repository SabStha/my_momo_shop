import { MenuItem } from '../types';

/**
 * Simple fuzzy search implementation for menu items
 * Searches through item name and description
 */
export function fuzzySearch(items: MenuItem[], query: string): MenuItem[] {
  if (!query.trim()) return items;
  
  const searchTerm = query.toLowerCase().trim();
  
  return items.filter(item => {
    const name = item.name.toLowerCase();
    const description = (item.desc || '').toLowerCase();
    
    // Check if query is found in name or description
    return name.includes(searchTerm) || description.includes(searchTerm);
  });
}

/**
 * Search items by category and query
 */
export function searchItemsByCategory(
  items: MenuItem[], 
  categoryId: string | null, 
  query: string
): MenuItem[] {
  let filteredItems = items;
  
  // Filter by category first
  if (categoryId) {
    filteredItems = items.filter(item => item.categoryId === categoryId);
  }
  
  // Then apply search
  return fuzzySearch(filteredItems, query);
}
