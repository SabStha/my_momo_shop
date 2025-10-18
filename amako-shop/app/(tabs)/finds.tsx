import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  Dimensions,
  RefreshControl,
  Alert,
  Image,
  Pressable,
  FlatList,
} from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../src/ui/tokens';
import { useFindsData, FindsCategory } from '../../src/api/finds-hooks';
import { useCartSyncStore } from '../../src/state/cart-sync';
import { LinearGradient } from 'expo-linear-gradient';

const { width: screenWidth } = Dimensions.get('window');
const itemWidth = (screenWidth - spacing.md * 3 - spacing.sm) / 2;

type Category = string; // Now dynamic from API

interface MerchandiseItem {
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

interface BulkPackage {
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

interface FindsConfig {
  finds_title: string;
  finds_subtitle: string;
  add_to_cart_text: string;
  unlockable_text: string;
  progress_message: string;
  earn_tooltip_message: string;
  urgency_badge_text: string;
  earn_badge_text: string;
}

// Categories are now fetched dynamically from the API

export default function FindsScreen() {
  const [refreshing, setRefreshing] = useState(false);
  const [wishlist, setWishlist] = useState<Set<number>>(new Set());
  const addToCart = useCartSyncStore((state) => state.addItem);
  
  // Get dynamic categories and other data from API
  const { data: findsData, isLoading, error, refetch } = useFindsData();
  const categories = findsData?.categories || [];
  
  // Debug logging
  console.log('🔍 Finds Page - Data received:', {
    hasData: !!findsData,
    categoriesCount: categories.length,
    hasError: !!error,
    isLoading,
  });
  
  if (findsData?.merchandise) {
    console.log('📦 Finds Page - Merchandise by category:');
    Object.keys(findsData.merchandise).forEach(cat => {
      const items = findsData.merchandise[cat as keyof typeof findsData.merchandise];
      console.log(`   • ${cat}: ${items?.length || 0} items`, items);
    });
  }
  
  // Set active category to first category from API, fallback to 'buyable'
  const [activeCategory, setActiveCategory] = useState<Category>(categories[0]?.key || 'buyable');
  
  // Update active category when categories are loaded
  React.useEffect(() => {
    if (categories.length > 0 && !categories.find(c => c.key === activeCategory)) {
      setActiveCategory(categories[0].key);
    }
  }, [categories, activeCategory]);

  const handleRefresh = async () => {
    setRefreshing(true);
    try {
      await refetch();
    } finally {
      setRefreshing(false);
    }
  };

  const toggleWishlist = (itemId: number) => {
    setWishlist(prev => {
      const newWishlist = new Set(prev);
      if (newWishlist.has(itemId)) {
        newWishlist.delete(itemId);
        showToast('Removed from wishlist');
      } else {
        newWishlist.add(itemId);
        showToast('Added to wishlist');
      }
      return newWishlist;
    });
  };

  const showToast = (message: string) => {
    Alert.alert('', message, [{ text: 'OK' }]);
  };

  const handleAddToCart = (item: MerchandiseItem) => {
    const cartItem = {
      itemId: `merchandise-${item.id}`,
      name: item.name,
      unitBasePrice: { currency: 'NPR' as const, amount: Number(item.price) },
      qty: 1,
      imageUrl: item.image_url,
    };

    addToCart(cartItem, (payload) => {
      // The cart store already provides the correct payload structure
      (global as any).openCartAddedSheet?.(payload);
    });
  };

  const getFilteredItems = (): MerchandiseItem[] => {
    if (!findsData?.merchandise) {
      console.log('⚠️ No merchandise data available');
      return [];
    }

    let items: MerchandiseItem[] = [];

    switch (activeCategory) {
      case 'buyable':
        items = [
          ...(findsData.merchandise.tshirts || []),
          ...(findsData.merchandise.accessories || []),
          ...(findsData.merchandise.toys || []),
          ...(findsData.merchandise.limited || []),
        ].filter(item => item.purchasable);
        break;
      
      case 'unlockable':
        items = [
          ...(findsData.merchandise.tshirts || []),
          ...(findsData.merchandise.accessories || []),
          ...(findsData.merchandise.toys || []),
          ...(findsData.merchandise.limited || []),
        ].filter(item => !item.purchasable);
        break;
      
      case 'tshirts':
        items = findsData.merchandise.tshirts || [];
        break;
      
      case 'accessories':
        items = findsData.merchandise.accessories || [];
        break;
      
      case 'toys':
        items = findsData.merchandise.toys || [];
        break;
      
      case 'limited':
        items = findsData.merchandise.limited || [];
        break;
      
      default:
        items = [];
    }

    console.log(`🔍 Filtered items for category "${activeCategory}":`, items.length, 'items');
    return items;
  };

  const renderMerchandiseItem = ({ item }: { item: MerchandiseItem }) => (
    <View style={styles.itemCard}>
      <Pressable style={styles.imageContainer}>
        <Image source={{ uri: item.image_url }} style={styles.itemImage} />
        
        {/* Gradient overlay */}
        <LinearGradient
          colors={['transparent', 'rgba(0,0,0,0.4)']}
          style={styles.imageGradient}
        />
        
        {/* Buy Now/Earn Badge - Top Right */}
        <View style={[
          styles.statusBadge,
          { backgroundColor: item.purchasable ? '#EF4444' : '#3B82F6' }
        ]}>
          <Text style={styles.statusBadgeText}>
            {item.purchasable ? 'Buy Now' : 'Earn'}
          </Text>
        </View>

        {/* Wishlist Button - Top Left */}
        <Pressable
          style={styles.wishlistButton}
          onPress={() => toggleWishlist(item.id)}
        >
          <MCI 
            name={wishlist.has(item.id) ? "heart" : "heart-outline"} 
            size={16} 
            color={wishlist.has(item.id) ? '#ef4444' : '#6b7280'} 
          />
        </Pressable>
        
        {/* Product info overlay */}
        <View style={styles.productInfoOverlay}>
          <View style={styles.mainContent}>
            {/* Left side - Text content */}
            <View style={styles.textContent}>
              <Text style={styles.productName} numberOfLines={2}>
                {item.name}
              </Text>
              <Text style={styles.productDescription} numberOfLines={2}>
                {item.description}
              </Text>
            </View>
            
            {/* Right side - Price and Add to Cart */}
            <View style={styles.rightContent}>
              <Text style={styles.price}>
                {item.formatted_price}
              </Text>
              {item.purchasable ? (
                <Pressable 
                  style={styles.addToCartButton} 
                  onPress={() => handleAddToCart(item)}
                >
                  <MCI name="shopping-outline" size={16} color={colors.white} />
                  <Text style={styles.addToCartText}>Add</Text>
                </Pressable>
              ) : (
                <View style={styles.unlockableButton}>
                  <Text style={styles.unlockableText}>Unlockable</Text>
                </View>
              )}
            </View>
          </View>
        </View>
      </Pressable>
    </View>
  );

  const renderBulkPackage = ({ item }: { item: BulkPackage }) => (
    <View style={styles.bulkCard}>
      <View style={styles.bulkImageContainer}>
        <View style={styles.bulkEmojiContainer}>
          <Text style={styles.bulkEmoji}>{item.emoji}</Text>
        </View>
        {item.badge && (
          <View style={[styles.bulkBadge, { backgroundColor: item.badge_color || colors.primary }]}>
            <Text style={styles.bulkBadgeText}>{item.badge}</Text>
          </View>
        )}
      </View>
      
      <View style={styles.bulkInfo}>
        <Text style={styles.bulkName}>{item.name}</Text>
        <Text style={styles.bulkDescription}>{item.description}</Text>
        
        <View style={styles.bulkItems}>
          {item.items.map((bulkItem, index) => (
            <View key={index} style={styles.bulkItem}>
              <Text style={styles.bulkItemName}>{bulkItem.name}</Text>
              <Text style={[
                styles.bulkItemPrice,
                { color: bulkItem.price < 0 ? '#10b981' : '#374151' }
              ]}>
                Rs. {bulkItem.price}
          </Text>
            </View>
          ))}
        </View>

        <View style={styles.bulkTotal}>
          <Text style={styles.bulkTotalLabel}>Total</Text>
          <Text style={styles.bulkTotalPrice}>Rs. {item.total_price}</Text>
        </View>

        <View style={styles.bulkActions}>
          <Pressable style={styles.bulkOrderButton}>
            <Text style={styles.bulkOrderText}>Order Now</Text>
          </Pressable>
          <Pressable style={styles.bulkCustomizeButton}>
            <Text style={styles.bulkCustomizeText}>Customize</Text>
          </Pressable>
        </View>
      </View>
    </View>
  );

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <MCI name="loading" size={32} color={colors.brand.primary} />
        <Text style={styles.loadingText}>Loading merchandise...</Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.errorContainer}>
        <MCI name="alert-circle" size={32} color={colors.error} />
        <Text style={styles.errorText}>Failed to load merchandise</Text>
        <Pressable style={styles.retryButton} onPress={handleRefresh}>
          <Text style={styles.retryText}>Retry</Text>
        </Pressable>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>
          {findsData?.config?.finds_title || "Ama's Finds"}
        </Text>
        <Text style={styles.headerSubtitle}>
          {findsData?.config?.finds_subtitle || "Buy some, earn others — welcome to Ama's Finds"}
        </Text>
      </View>

      {/* Category Selection */}
      <View style={styles.categoryContainer}>
        <Text style={styles.categoryTitle}>
          Select Category: <Text style={styles.categoryActive}>{activeCategory.toUpperCase()}</Text>
        </Text>
        
        <View style={styles.categoryGrid}>
          {categories.map((category) => (
            <Pressable
              key={category.key}
              style={[
                styles.categoryButton,
                activeCategory === category.key && styles.categoryButtonActive
              ]}
              onPress={() => setActiveCategory(category.key)}
            >
              <Text style={styles.categoryIcon}>{category.icon}</Text>
              <Text style={[
                styles.categoryLabel,
                activeCategory === category.key && styles.categoryLabelActive
              ]}>
                {category.label}
          </Text>
            </Pressable>
          ))}
        </View>
      </View>

      {/* Category Selection Feedback */}
      <View style={styles.categoryFeedback}>
        <Text style={styles.categoryFeedbackText}>
          <Text style={styles.categoryFeedbackBold}>Selected Category:</Text> {activeCategory.toUpperCase()}
          {isLoading ? (
            <Text style={styles.categoryFeedbackLoading}> (Loading...)</Text>
          ) : (
            <Text style={styles.categoryFeedbackLoaded}> (Showing filtered results)</Text>
          )}
        </Text>
      </View>

      {/* Content */}
      <ScrollView
        style={styles.content}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
        }
      >
        {activeCategory === 'bulk' ? (
          <FlatList
            data={findsData?.bulkPackages || []}
            renderItem={renderBulkPackage}
            keyExtractor={(item) => `bulk-${item.id}`}
            numColumns={1}
            scrollEnabled={false}
            contentContainerStyle={styles.bulkList}
          />
        ) : (
          <FlatList
            data={getFilteredItems()}
            renderItem={renderMerchandiseItem}
            keyExtractor={(item) => `merchandise-${item.id}`}
            numColumns={2}
            scrollEnabled={false}
            contentContainerStyle={styles.itemsList}
          />
        )}
    </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F4E9E1',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F4E9E1',
  },
  loadingText: {
    marginTop: spacing.md,
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F4E9E1',
    padding: spacing.lg,
  },
  errorText: {
    marginTop: spacing.md,
    fontSize: fontSizes.md,
    color: colors.error,
    textAlign: 'center',
  },
  retryButton: {
    marginTop: spacing.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
    backgroundColor: colors.brand.primary,
    borderRadius: radius.md,
  },
  retryText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  header: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#6E0D25',
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  headerSubtitle: {
    fontSize: fontSizes.xs,
    color: '#6E0D25',
    textAlign: 'center',
    fontWeight: fontWeights.medium,
  },
  categoryContainer: {
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    paddingVertical: spacing.xs,
    paddingHorizontal: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(110, 13, 37, 0.2)',
  },
  categoryTitle: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
    color: '#6E0D25',
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  categoryActive: {
    fontWeight: fontWeights.bold,
    color: '#6E0D25',
  },
  categoryGrid: {
    flexDirection: 'row',
    flexWrap: 'nowrap',
    justifyContent: 'space-between',
    gap: 2,
  },
  categoryButton: {
    backgroundColor: colors.white,
    paddingHorizontal: 4,
    paddingVertical: 4,
    borderRadius: radius.md,
    alignItems: 'center',
    width: '13%',
    minHeight: 28,
    borderWidth: 1,
    borderColor: colors.gray[300],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  categoryButtonActive: {
    backgroundColor: '#6E0D25',
    borderColor: '#6E0D25',
    transform: [{ scale: 1.05 }],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 6,
  },
  categoryIcon: {
    fontSize: 12,
    marginBottom: 1,
  },
  categoryLabel: {
    fontSize: 8,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
  },
  categoryLabelActive: {
    color: colors.white,
  },
  categoryFeedback: {
    backgroundColor: '#dbeafe',
    borderLeftWidth: 4,
    borderLeftColor: '#60a5fa',
    padding: spacing.sm,
    marginHorizontal: spacing.sm,
    marginBottom: spacing.md,
    borderRadius: radius.sm,
  },
  categoryFeedbackText: {
    fontSize: fontSizes.xs,
    color: '#1e40af',
  },
  categoryFeedbackBold: {
    fontWeight: fontWeights.bold,
  },
  categoryFeedbackLoading: {
    color: '#2563eb',
  },
  categoryFeedbackLoaded: {
    color: '#2563eb',
  },
  content: {
    flex: 1,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    marginTop: spacing.md,
  },
  itemsList: {
    paddingBottom: spacing.lg,
    paddingHorizontal: spacing.xs,
  },
  bulkList: {
    paddingBottom: spacing.lg,
  },
  itemCard: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    marginBottom: spacing.sm,
    marginHorizontal: spacing.xs,
    overflow: 'hidden',
    width: itemWidth,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    height: 240,
  },
  imageContainer: {
    position: 'relative',
    height: 240,
    overflow: 'hidden',
    backgroundColor: '#f0f0f0',
  },
  itemImage: {
    width: '100%',
    height: '100%',
  },
  imageGradient: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 120,
  },
  wishlistButton: {
    position: 'absolute',
    top: spacing.sm,
    left: spacing.sm,
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    borderRadius: 20,
    width: 32,
    height: 32,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 10,
  },
  statusBadge: {
    position: 'absolute',
    top: spacing.sm,
    right: spacing.sm,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statusBadgeText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  productInfoOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: spacing.xs,
    zIndex: 10,
  },
  mainContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    minHeight: 60,
  },
  textContent: {
    flex: 1,
    marginRight: spacing.xs,
    maxWidth: '65%',
  },
  rightContent: {
    alignItems: 'flex-end',
  },
  productName: {
    fontSize: 9,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.xs,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: radius.sm,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 1,
    lineHeight: 11,
  },
  productDescription: {
    fontSize: 8,
    color: colors.white,
    marginBottom: spacing.xs,
    opacity: 0.9,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: radius.sm,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 1,
    lineHeight: 10,
  },
  price: {
    fontSize: 11,
    fontWeight: fontWeights.bold,
    color: '#FCD34D',
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    marginBottom: 4,
  },
  addToCartButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EF4444',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    shadowColor: '#EF4444',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 2,
    elevation: 2,
    alignSelf: 'flex-end',
  },
  addToCartText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    marginLeft: spacing.sm,
  },
  unlockableButton: {
    backgroundColor: '#6B7280',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    alignItems: 'center',
  },
  unlockableText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
  },
  // Bulk package styles
  bulkCard: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    marginBottom: spacing.lg,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.25,
    shadowRadius: 25,
    elevation: 12,
  },
  bulkImageContainer: {
    height: 120,
    backgroundColor: '#6E0D25',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  bulkEmojiContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  bulkEmoji: {
    fontSize: 48,
  },
  bulkBadge: {
    position: 'absolute',
    top: spacing.sm,
    right: spacing.sm,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
  },
  bulkBadgeText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  bulkInfo: {
    padding: spacing.lg,
  },
  bulkName: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginBottom: spacing.sm,
  },
  bulkDescription: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.md,
  },
  bulkItems: {
    marginBottom: spacing.md,
  },
  bulkItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: spacing.xs,
  },
  bulkItemName: {
    fontSize: fontSizes.xs,
    color: colors.gray[700],
    flex: 1,
    marginRight: spacing.sm,
  },
  bulkItemPrice: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
  },
  bulkTotal: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
    paddingTop: spacing.sm,
    marginBottom: spacing.md,
  },
  bulkTotalLabel: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.gray[800],
  },
  bulkTotalPrice: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#6E0D25',
  },
  bulkActions: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  bulkOrderButton: {
    flex: 1,
    backgroundColor: '#6E0D25',
    paddingVertical: spacing.sm,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  bulkOrderText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
  },
  bulkCustomizeButton: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#6E0D25',
    paddingVertical: spacing.sm,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  bulkCustomizeText: {
    color: '#6E0D25',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
  },
});