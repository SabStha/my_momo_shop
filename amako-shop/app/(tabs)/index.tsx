import React, { useState, useMemo } from 'react';
import { 
  View, 
  Text, 
  FlatList, 
  RefreshControl, 
  StyleSheet, 
  Dimensions,
  Alert 
} from 'react-native';
import { router } from 'expo-router';
import { useMenu } from '../../src/api/menu-hooks';
import { 
  ItemCard, 
  CategoryFilter, 
  SearchInput, 
  SkeletonCard 
} from '../../src/components';
import { Card, Button } from '../../src/ui';
import { spacing, fontSizes, fontWeights, colors } from '../../src/ui';
import { MenuItem, Category } from '../../src/types';

const { width: screenWidth } = Dimensions.get('window');
const numColumns = 2;
const itemWidth = (screenWidth - spacing.lg * 3) / numColumns;

export default function MenuScreen() {
  // Local UI state
  const [selectedCategoryId, setSelectedCategoryId] = useState<string | 'all'>('all');
  const [query, setQuery] = useState('');
  const [refreshing, setRefreshing] = useState(false);

  // Fetch menu data
  const { data, isLoading, isError, error, refetch } = useMenu();

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

  if (isLoading && !data) {
    return (
      <View style={styles.container}>
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

  if (isError) {
    return (
      <View style={styles.container}>
        {renderOffersBanner()}
        {renderErrorState()}
      </View>
    );
  }

  return (
    <View style={styles.container}>
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
    backgroundColor: colors.background,
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.md,
  },
  searchInput: {
    marginBottom: spacing.md,
  },
  offersBanner: {
    marginBottom: spacing.lg,
    backgroundColor: colors.primary[50],
    borderColor: colors.primary[200],
    borderWidth: 1,
  },
  offersTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.primary[700],
    marginBottom: spacing.xs,
  },
  offersMessage: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
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
    color: colors.error[600],
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  errorMessage: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
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
    color: colors.text.primary,
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  emptyMessage: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
    textAlign: 'center',
    marginBottom: spacing.lg,
    lineHeight: 22,
  },
  clearFiltersButton: {
    minWidth: 140,
  },
});
