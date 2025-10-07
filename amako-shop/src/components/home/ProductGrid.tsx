import React from 'react';
import { View, Text, FlatList, StyleSheet } from 'react-native';
import { colors, spacing } from '../../ui/tokens';
import ProductCard from './ProductCard';

const numColumns = 2;

interface Product {
  id: string;
  name: string;
  subtitle?: string;
  price: { currency: string; amount: number };
  imageUrl: string;
  isFeatured?: boolean;
}

interface ProductGridProps {
  products: Product[];
  onProductPress?: (product: Product) => void;
  onProductInfoPress?: (product: Product) => void;
  onAddToCart?: (item: any) => void;
  isLoading?: boolean;
}

export default function ProductGrid({ 
  products, 
  onProductPress,
  onProductInfoPress,
  onAddToCart,
  isLoading = false 
}: ProductGridProps) {
  const renderProduct = ({ item }: { item: Product }) => (
    <View style={styles.itemContainer}>
      <ProductCard
        product={item}
        onPress={() => onProductPress?.(item)}
        onInfoPress={onProductInfoPress}
        onAddToCart={onAddToCart}
      />
    </View>
  );

  const renderEmpty = () => (
    <View style={styles.emptyContainer}>
      <Text style={styles.emptyText}>No featured products available</Text>
    </View>
  );

  if (isLoading) {
    return (
      <View style={styles.container}>
        <View style={styles.grid}>
          {Array.from({ length: 4 }).map((_, index) => (
            <View key={index} style={styles.itemContainer}>
              <View style={styles.skeletonCard} />
            </View>
          ))}
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <FlatList
        data={products}
        renderItem={renderProduct}
        keyExtractor={(item) => item.id}
        numColumns={numColumns}
        columnWrapperStyle={styles.row}
        contentContainerStyle={styles.listContainer}
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={renderEmpty}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: spacing.lg,
  },
  listContainer: {
    paddingBottom: spacing.lg,
  },
  row: {
    justifyContent: 'space-between',
  },
  itemContainer: {
    flex: 1,
    minWidth: 0,
    maxWidth: '48%', // Allow for gap between items
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: spacing.xl,
  },
  emptyText: {
    fontSize: 16,
    color: colors.gray[500],
    textAlign: 'center',
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  skeletonCard: {
    flex: 1,
    minWidth: 0,
    maxWidth: '48%',
    height: 200,
    backgroundColor: colors.gray[200],
    borderRadius: 12,
  },
});
