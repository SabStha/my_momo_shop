import React, { useState, useMemo, useEffect } from 'react';
import { 
  View, 
  Text, 
  FlatList, 
  RefreshControl, 
  StyleSheet, 
  Dimensions,
  Alert,
  TouchableOpacity 
} from 'react-native';
import { router } from 'expo-router';
import { useMenu } from '../../src/api/menu-hooks';
import { 
  ItemCard, 
  CategoryFilter, 
  SearchInput, 
  SkeletonCard,
  FeaturedCarousel,
  StatsRow 
} from '../../src/components';
import { Card, Button } from '../../src/ui';
import { spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { MenuItem, Category } from '../../src/types';

const { width: screenWidth } = Dimensions.get('window');
const numColumns = 2;
const itemWidth = (screenWidth - spacing.lg * 3) / numColumns;

export default function MenuScreen() {
  // Local UI state
  const [selectedCategoryId, setSelectedCategoryId] = useState<string | 'all'>('all');
  const [query, setQuery] = useState('');
  const [refreshing, setRefreshing] = useState(false);

  // Fetch menu data with timeout
  const { data, isLoading, isError, error, refetch } = useMenu({
    retry: 1, // Only retry once
    retryDelay: 2000, // Wait 2 seconds before retry
    staleTime: 30000, // Consider data stale after 30 seconds
  });

  // Featured carousel items (matching Laravel web carousel)
  const featuredItems = [
    {
      id: '1',
      title: 'Premium Nepali Momo',
      subtitle: 'Authentic flavors from the Himalayas',
      imageUrl: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=800&h=400&fit=crop',
      onPress: () => console.log('Navigate to menu'),
    },
    {
      id: '2', 
      title: 'Special Combo Offers',
      subtitle: 'Get more for less with our combo deals',
      imageUrl: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=800&h=400&fit=crop',
      onPress: () => console.log('Navigate to menu'),
    },
    {
      id: '3',
      title: 'Fresh Daily Specials',
      subtitle: 'New flavors added every day',
      imageUrl: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&h=400&fit=crop',
      onPress: () => console.log('Navigate to menu'),
    },
  ];

  // Filter items based on search and category
  const filteredItems = useMemo(() => {
    if (!data?.items) return [];
    
    let items = data.items;
    
    // Apply search filter
    if (query.trim()) {
      const searchLower = query.toLowerCase();
      items = items.filter(item => 
        item.name.toLowerCase().includes(searchLower) ||
        (item.desc && item.desc.toLowerCase().includes(searchLower))
      );
    }
    
    // Apply category filter
    if (selectedCategoryId !== 'all') {
      items = items.filter(item => item.categoryId === selectedCategoryId);
    }
    
    return items;
  }, [data?.items, query, selectedCategoryId]);

  // Handle refresh
  const handleRefresh = async () => {
    setRefreshing(true);
    try {
      await refetch();
    } finally {
      setRefreshing(false);
    }
  };

  // Handle retry on error
  const handleRetry = () => {
    refetch();
  };

  // Render skeleton loading
  const renderSkeletonItems = () => {
    const skeletonItems = Array.from({ length: 6 }, (_, index) => (
      <View key={index} style={{ width: itemWidth, marginBottom: spacing.md }}>
        <SkeletonCard height={200} />
      </View>
    ));

    return (
      <View style={styles.gridContainer}>
        {skeletonItems}
      </View>
    );
  };

  // Render menu item
  const renderMenuItem = ({ item }: { item: MenuItem }) => (
    <ItemCard 
      item={item} 
      onPress={() => router.push(`/item/${item.id}`)}
    />
  );

  // Render error state
  const renderErrorState = () => (
    <View style={styles.errorContainer}>
      <Card style={styles.errorCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.errorTitle}>Something went wrong</Text>
        <Text style={styles.errorMessage}>
          {error?.message || 'Failed to load menu items. Please try again.'}
        </Text>
        <Button
          title="Retry"
          onPress={handleRetry}
          variant="solid"
          size="md"
          style={styles.retryButton}
        />
      </Card>
    </View>
  );

  // Render empty state
  const renderEmptyState = () => (
    <View style={styles.emptyContainer}>
      <Text style={styles.emptyTitle}>No items found</Text>
      <Text style={styles.emptyMessage}>
        {query.trim() 
          ? `No items match "${query}" in ${selectedCategoryId === 'all' ? 'all categories' : 'this category'}`
          : 'No items available in this category'
        }
      </Text>
      <Button
        title="Clear Filters"
        onPress={() => {
          setQuery('');
          setSelectedCategoryId('all');
        }}
        variant="outline"
        size="md"
        style={styles.clearFiltersButton}
      />
    </View>
  );

  // Render offers banner
  const renderOffersBanner = () => (
    <Card style={styles.offersBanner} padding="md" radius="md" shadow="light">
      <Text style={styles.offersTitle}>🎉 Offers Coming Soon!</Text>
      <Text style={styles.offersMessage}>
        Stay tuned for exciting deals and discounts on your favorite momos and drinks.
      </Text>
    </Card>
  );

  // Show loading state only for a limited time, then show fallback
  const [showFallback, setShowFallback] = useState(false);
  
  useEffect(() => {
    if (isLoading && !data) {
      const timer = setTimeout(() => {
        setShowFallback(true);
      }, 5000); // Show fallback after 5 seconds
      
      return () => clearTimeout(timer);
    } else {
      setShowFallback(false);
    }
  }, [isLoading, data]);

  if (isLoading && !data && !showFallback) {
    return (
      <View style={styles.container}>
        {/* Featured Carousel */}
        <FeaturedCarousel items={featuredItems} />
        
        {/* Stats Row */}
        <StatsRow />
        
        {renderOffersBanner()}
        <SearchInput 
          value={query} 
          onChangeText={setQuery}
          style={styles.searchInput}
        />
        <CategoryFilter
          categories={[]}
          selectedCategory={null}
          onSelectCategory={() => {}}
          isLoading={true}
        />
        {renderSkeletonItems()}
      </View>
    );
  }

  // Show fallback data if loading takes too long
  if (showFallback && !data) {
    return (
      <View style={styles.container}>
        {/* Featured Carousel */}
        <FeaturedCarousel items={featuredItems} />
        
        {/* Stats Row */}
        <StatsRow />
        
        {renderOffersBanner()}
        <SearchInput 
          value={query} 
          onChangeText={setQuery}
          style={styles.searchInput}
        />
        <CategoryFilter
          categories={[]}
          selectedCategory={null}
          onSelectCategory={() => {}}
          isLoading={false}
        />
        <View style={styles.fallbackContainer}>
          <Text style={styles.fallbackTitle}>Using Offline Menu</Text>
          <Text style={styles.fallbackMessage}>
            Unable to connect to server. Showing cached menu items.
          </Text>
          <TouchableOpacity 
            style={styles.retryButton}
            onPress={() => {
              setShowFallback(false);
              refetch();
            }}
          >
            <Text style={styles.retryButtonText}>Try Again</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  if (isError) {
    return (
      <View style={styles.container}>
        {/* Featured Carousel */}
        <FeaturedCarousel items={featuredItems} />
        
        {/* Stats Row */}
        <StatsRow />
        
        {renderOffersBanner()}
        {renderErrorState()}
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Featured Carousel */}
      <FeaturedCarousel items={featuredItems} />

      {/* Stats Row */}
      <StatsRow />

      {/* Offers Banner */}
      {renderOffersBanner()}

      {/* Search Input */}
      <SearchInput 
        value={query} 
        onChangeText={setQuery}
        style={styles.searchInput}
      />

      {/* Category Filter */}
      {data?.categories && (
        <CategoryFilter
          categories={data.categories}
          selectedCategory={selectedCategoryId === 'all' ? null : selectedCategoryId}
          onSelectCategory={(categoryId) => 
            setSelectedCategoryId(categoryId || 'all')
          }
        />
      )}

      {/* Menu Items Grid */}
      {filteredItems.length > 0 ? (
        <FlatList
          data={filteredItems}
          renderItem={renderMenuItem}
          keyExtractor={(item) => item.id}
          numColumns={numColumns}
          columnWrapperStyle={styles.row}
          contentContainerStyle={styles.listContainer}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              colors={[colors.primary[500]]}
              tintColor={colors.primary[500]}
            />
          }
          ListFooterComponent={<View style={styles.listFooter} />}
        />
      ) : (
        <View style={styles.emptyContainer}>
          {renderEmptyState()}
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.momo.sand, // Momo sand background like Laravel
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.md,
  },
  searchInput: {
    marginBottom: spacing.md,
  },
  offersBanner: {
    marginBottom: spacing.lg,
    backgroundColor: colors.momo.cream, // Momo cream background
    borderColor: colors.brand.primary, // Maroon border
    borderWidth: 2,
    borderRadius: radius.md,
  },
  offersTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary, // Maroon text
    marginBottom: spacing.xs,
  },
  offersMessage: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha, // Momo mocha text
    lineHeight: 20,
  },
  listContainer: {
    paddingBottom: spacing.xl,
  },
  row: {
    justifyContent: 'space-between',
  },
  gridContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  listFooter: {
    height: spacing.xl,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  errorCard: {
    alignItems: 'center',
    maxWidth: 300,
  },
  errorTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary, // Maroon text
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  errorMessage: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha, // Momo mocha text
    textAlign: 'center',
    marginBottom: spacing.lg,
    lineHeight: 22,
  },
  retryButton: {
    minWidth: 120,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  emptyTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary, // Maroon text
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  emptyMessage: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha, // Momo mocha text
    textAlign: 'center',
    marginBottom: spacing.lg,
    lineHeight: 22,
  },
  clearFiltersButton: {
    minWidth: 140,
  },
  fallbackContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  fallbackTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  fallbackMessage: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha,
    textAlign: 'center',
    marginBottom: spacing.lg,
    lineHeight: 22,
  },
  retryButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: 8,
    minWidth: 120,
  },
  retryButtonText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    textAlign: 'center',
  },
});
