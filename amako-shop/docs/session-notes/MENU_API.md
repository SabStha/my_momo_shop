# Menu API Documentation

## Overview
The Menu API provides comprehensive menu management with offline fallback functionality. It includes React Query hooks for data fetching, caching, and synchronization, with bundled fallback data for instant UI rendering and offline support.

## Architecture

### Core Components
- **Menu Service**: API calls with offline fallback logic
- **React Query Hooks**: Data fetching, caching, and state management
- **Bundled Fallback Data**: Offline menu data for instant UI
- **Error Handling**: Normalized API errors with fallback strategies

### File Structure
```
src/api/
├── menu.ts              # Menu service with offline fallback
├── menu-hooks.ts        # React Query hooks for menu data
├── hooks.ts             # Main hooks index
└── index.ts             # API exports

assets/
└── menu.json            # Bundled fallback menu data
```

## Menu Service

### MenuService Class

The `MenuService` class provides methods for fetching menu data with automatic offline fallback:

```typescript
export class MenuService {
  // Get full menu (categories + items)
  static async getMenu(): Promise<{ categories: Category[]; items: MenuItem[] }>
  
  // Get specific menu item by ID
  static async getItem(id: string): Promise<MenuItem | null>
  
  // Get all categories
  static async getCategories(): Promise<Category[]>
  
  // Get items by category
  static async getItemsByCategory(categoryId: string): Promise<MenuItem[]>
  
  // Search items by query
  static async searchItems(query: string): Promise<MenuItem[]>
  
  // Check if using fallback data
  static async isUsingFallback(): Promise<boolean>
}
```

### Offline Fallback Strategy

1. **Primary**: Attempt API call to `/menu`, `/items/{id}`, etc.
2. **Fallback**: If API fails, return bundled data from `assets/menu.json`
3. **Seamless**: UI renders instantly with fallback data, then updates when API succeeds
4. **Transparent**: Components don't need to handle offline states manually

## React Query Hooks

### Core Menu Hooks

#### `useMenu()`
Fetches full menu with categories and items:

```tsx
import { useMenu } from '../src/api';

function MenuScreen() {
  const { data, isLoading, isError, error, refetch } = useMenu();
  
  if (isLoading) return <LoadingSpinner />;
  if (isError) return <ErrorMessage error={error} />;
  
  const { categories, items } = data || { categories: [], items: [] };
  
  return (
    <View>
      <CategoryList categories={categories} />
      <MenuItemList items={items} />
      <RefreshButton onPress={refetch} />
    </View>
  );
}
```

#### `useCategories()`
Fetches only categories:

```tsx
import { useCategories } from '../src/api';

function CategoryFilter() {
  const { data: categories, isLoading } = useCategories();
  
  return (
    <ScrollView horizontal>
      {categories?.map(category => (
        <CategoryChip key={category.id} category={category} />
      ))}
    </ScrollView>
  );
}
```

#### `useItem(id)`
Fetches specific menu item:

```tsx
import { useItem } from '../src/api';

function MenuItemDetail({ itemId }: { itemId: string }) {
  const { data: item, isLoading } = useItem(itemId);
  
  if (isLoading) return <LoadingSpinner />;
  if (!item) return <ItemNotFound />;
  
  return <MenuItemCard item={item} />;
}
```

#### `useItemsByCategory(categoryId)`
Fetches items filtered by category:

```tsx
import { useItemsByCategory } from '../src/api';

function CategoryItems({ categoryId }: { categoryId: string }) {
  const { data: items, isLoading } = useItemsByCategory(categoryId);
  
  return (
    <FlatList
      data={items}
      renderItem={({ item }) => <MenuItemCard item={item} />}
      keyExtractor={item => item.id}
    />
  );
}
```

#### `useSearchItems(query)`
Searches items by query string:

```tsx
import { useSearchItems } from '../src/api';

function SearchResults({ query }: { query: string }) {
  const { data: items, isLoading } = useSearchItems(query);
  
  return (
    <View>
      <Text>Found {items?.length || 0} items</Text>
      <FlatList
        data={items}
        renderItem={({ item }) => <MenuItemCard item={item} />}
        keyExtractor={item => item.id}
      />
    </View>
  );
}
```

### Utility Hooks

#### `useIsOffline()`
Checks if currently using fallback data:

```tsx
import { useIsOffline } from '../src/api';

function OfflineIndicator() {
  const { data: isOffline } = useIsOffline();
  
  if (!isOffline) return null;
  
  return (
    <View style={styles.offlineBanner}>
      <Text>You're viewing offline menu data</Text>
    </View>
  );
}
```

#### `useMenuWithCategories()`
Combines categories and items in one hook:

```tsx
import { useMenuWithCategories } from '../src/api';

function MenuScreen() {
  const { categories, items, isLoading, refetch } = useMenuWithCategories();
  
  return (
    <View>
      <CategoryFilter categories={categories} />
      <MenuItemList items={items} />
      <RefreshButton onPress={refetch} />
    </View>
  );
}
```

## Query Configuration

### Default Settings
All menu hooks use consistent configuration:

```typescript
const commonQueryOptions = {
  staleTime: 5 * 60 * 1000,  // 5 minutes
  retry: 1,                   // Retry once on failure
  retryDelay: 1000,           // Wait 1 second before retry
} as const;
```

### Query Keys
Organized query key structure for efficient caching:

```typescript
export const menuQueryKeys = {
  all: ['menu'] as const,
  categories: () => [...menuQueryKeys.all, 'categories'] as const,
  items: () => [...menuQueryKeys.all, 'items'] as const,
  item: (id: string) => [...menuQueryKeys.all, 'item', id] as const,
  byCategory: (categoryId: string) => [...menuQueryKeys.all, 'category', categoryId] as const,
  search: (query: string) => [...menuQueryKeys.all, 'search', query] as const,
} as const;
```

## Offline Fallback Data

### Bundled Menu Data
Located at `assets/menu.json`, provides instant UI rendering:

```json
{
  "categories": [
    { "id": "cat-momo", "name": "Momo" },
    { "id": "cat-drinks", "name": "Drinks" }
  ],
  "items": [
    {
      "id": "itm-classic-momo",
      "name": "Classic Chicken Momo",
      "desc": "Juicy chicken, house spice blend.",
      "basePrice": { "currency": "NPR", "amount": 180 },
      "variants": [
        { "id": "v6", "name": "6 pcs", "priceDiff": { "currency": "NPR", "amount": 0 } },
        { "id": "v10", "name": "10 pcs", "priceDiff": { "currency": "NPR", "amount": 120 } }
      ],
      "addOns": [
        { "id": "a-chili", "name": "Extra Chili Sauce", "price": { "currency": "NPR", "amount": 20 } }
      ],
      "categoryId": "cat-momo",
      "isAvailable": true
    }
  ]
}
```

### Fallback Features
- **Instant Rendering**: UI shows immediately with bundled data
- **Complete Coverage**: All menu categories and items included
- **Realistic Data**: Sample data matches production structure
- **Type Safety**: Full TypeScript support with proper interfaces

## Error Handling

### Normalized Errors
All errors are normalized to `ApiError` interface:

```typescript
interface ApiError {
  message: string;
  code?: string;
}
```

### Fallback Strategy
1. **API Success**: Return API data
2. **API Failure**: Log warning and return fallback data
3. **Fallback Unavailable**: Return empty arrays/objects
4. **Error Normalization**: Convert all errors to consistent format

## Usage Examples

### Building a Complete Menu Screen

```tsx
import React, { useState } from 'react';
import { View, FlatList, RefreshControl } from 'react-native';
import { useMenu, useIsOffline } from '../src/api';
import { CategoryFilter, MenuItemCard, OfflineIndicator } from '../src/components';

export function MenuScreen() {
  const [selectedCategory, setSelectedCategory] = useState<string | null>(null);
  const { data, isLoading, refetch, isFetching } = useMenu();
  const { data: isOffline } = useIsOffline();
  
  const { categories = [], items = [] } = data || {};
  
  // Filter items by selected category
  const filteredItems = selectedCategory 
    ? items.filter(item => item.categoryId === selectedCategory)
    : items;
  
  return (
    <View style={styles.container}>
      <OfflineIndicator isOffline={isOffline} />
      
      <CategoryFilter
        categories={categories}
        selectedCategory={selectedCategory}
        onSelectCategory={setSelectedCategory}
      />
      
      <FlatList
        data={filteredItems}
        renderItem={({ item }) => <MenuItemCard item={item} />}
        keyExtractor={item => item.id}
        refreshControl={
          <RefreshControl
            refreshing={isFetching}
            onRefresh={refetch}
          />
        }
      />
    </View>
  );
}
```

### Building a Search Interface

```tsx
import React, { useState } from 'react';
import { View, TextInput, FlatList } from 'react-native';
import { useSearchItems } from '../src/api';
import { MenuItemCard } from '../src/components';

export function SearchScreen() {
  const [query, setQuery] = useState('');
  const { data: searchResults, isLoading } = useSearchItems(query);
  
  return (
    <View style={styles.container}>
      <TextInput
        style={styles.searchInput}
        placeholder="Search menu items..."
        value={query}
        onChangeText={setQuery}
      />
      
      {query.length < 2 && (
        <Text style={styles.hint}>Enter at least 2 characters to search</Text>
      )}
      
      {query.length >= 2 && (
        <FlatList
          data={searchResults}
          renderItem={({ item }) => <MenuItemCard item={item} />}
          keyExtractor={item => item.id}
          ListEmptyComponent={
            <Text style={styles.noResults}>No items found for "{query}"</Text>
          }
        />
      )}
    </View>
  );
}
```

### Building a Category Filter

```tsx
import React from 'react';
import { ScrollView } from 'react-native';
import { useCategories } from '../src/api';
import { Chip } from '../src/ui';

export function CategoryFilter({ 
  selectedCategory, 
  onSelectCategory 
}: { 
  selectedCategory: string | null;
  onSelectCategory: (categoryId: string) => void;
}) {
  const { data: categories, isLoading } = useCategories();
  
  if (isLoading) return <CategorySkeleton />;
  
  return (
    <ScrollView 
      horizontal 
      showsHorizontalScrollIndicator={false}
      contentContainerStyle={styles.categoryContainer}
    >
      <Chip
        label="All"
        selected={!selectedCategory}
        onPress={() => onSelectCategory(null)}
        variant="primary"
      />
      
      {categories?.map(category => (
        <Chip
          key={category.id}
          label={category.name}
          selected={selectedCategory === category.id}
          onPress={() => onSelectCategory(category.id)}
          variant="primary"
        />
      ))}
    </ScrollView>
  );
}
```

## Performance Optimizations

### Caching Strategy
- **Stale Time**: 5 minutes for menu data
- **Cache Time**: 10 minutes for efficient memory usage
- **Background Updates**: Automatic refetching when app becomes active
- **Selective Invalidation**: Only invalidate relevant query keys

### Bundle Optimization
- **Tree Shaking**: Only import used hooks
- **Code Splitting**: Lazy load menu data when needed
- **Minimal Dependencies**: No external libraries for core functionality

## Testing

### Unit Testing
```typescript
import { renderHook, waitFor } from '@testing-library/react-hooks';
import { useMenu } from '../src/api';

test('useMenu returns fallback data when API fails', async () => {
  const { result } = renderHook(() => useMenu());
  
  await waitFor(() => {
    expect(result.current.data).toBeDefined();
    expect(result.current.data?.categories).toHaveLength(4);
    expect(result.current.data?.items).toHaveLength(7);
  });
});
```

### Integration Testing
```typescript
test('menu service falls back to bundled data on API failure', async () => {
  // Mock API failure
  jest.spyOn(apiClient, 'get').mockRejectedValue(new Error('Network error'));
  
  const result = await MenuService.getMenu();
  
  expect(result.categories).toHaveLength(4);
  expect(result.items).toHaveLength(7);
});
```

## Best Practices

### 1. Use Appropriate Hooks
```tsx
// ✅ Good - Use specific hook for your needs
const { data: categories } = useCategories();
const { data: items } = useItemsByCategory(categoryId);

// ❌ Avoid - Fetching full menu when you only need categories
const { data: menu } = useMenu();
const categories = menu?.categories || [];
```

### 2. Handle Loading States
```tsx
// ✅ Good - Show loading state
if (isLoading) return <LoadingSpinner />;

// ❌ Avoid - Rendering with undefined data
return <MenuItemList items={items} />; // items might be undefined
```

### 3. Implement Pull-to-Refresh
```tsx
// ✅ Good - Use refreshControl for better UX
<FlatList
  refreshControl={
    <RefreshControl refreshing={isFetching} onRefresh={refetch} />
  }
  data={items}
  renderItem={renderItem}
/>

// ❌ Avoid - Manual refresh buttons only
<Button onPress={refetch} title="Refresh" />
```

### 4. Show Offline Indicators
```tsx
// ✅ Good - Inform users about offline state
const { data: isOffline } = useIsOffline();

if (isOffline) {
  return <OfflineBanner message="Viewing offline menu data" />;
}

// ❌ Avoid - Hiding offline state from users
```

## Troubleshooting

### Common Issues

1. **Fallback Data Not Loading**
   - Check `assets/menu.json` exists and is valid JSON
   - Verify import path in `menu.ts`
   - Check TypeScript compilation

2. **API Calls Not Failing Gracefully**
   - Ensure error handling in service methods
   - Check network timeout settings
   - Verify API endpoint availability

3. **Cache Not Updating**
   - Check query key structure
   - Verify stale time settings
   - Use `refetch()` for manual updates

4. **Type Errors**
   - Ensure `MenuItem` and `Category` interfaces match bundled data
   - Check TypeScript compilation
   - Verify import/export statements

### Debug Mode
Enable debugging in development:

```typescript
if (__DEV__) {
  console.log('Menu Service Debug:', {
    fallbackData: MenuService.getFallbackData(),
    isOffline: await MenuService.isUsingFallback(),
  });
}
```

## Future Enhancements

### Planned Features
- [ ] **Real-time Updates**: WebSocket integration for live menu changes
- [ ] **Offline Queue**: Queue changes when offline, sync when online
- [ ] **Menu Versioning**: Track menu versions and show update notifications
- [ ] **Image Caching**: Cache menu item images for offline viewing
- [ ] **Search History**: Remember and suggest previous searches

### Technical Improvements
- [ ] **Background Sync**: Sync menu data in background
- [ ] **Incremental Updates**: Only fetch changed menu items
- [ ] **Compression**: Compress bundled menu data
- [ ] **Analytics**: Track menu usage and search patterns
- [ ] **A/B Testing**: Test different menu layouts and structures
