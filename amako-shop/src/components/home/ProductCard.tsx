import React, { useState } from 'react';
import { View, Text, Image, Pressable, StyleSheet, Dimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import { useCartSyncStore } from '../../state/cart-sync';

const { width: screenWidth } = Dimensions.get('window');

interface Product {
  id: string;
  name: string;
  subtitle?: string;
  price: { currency: string; amount: number };
  imageUrl: string;
  isFeatured?: boolean;
  ingredients?: string;
  allergens?: string;
  calories?: string;
  preparation_time?: string;
  spice_level?: string;
  serving_size?: string;
  is_vegetarian?: boolean;
  is_vegan?: boolean;
  is_gluten_free?: boolean;
}

interface ProductCardProps {
  product: Product;
  onPress?: () => void;
  onInfoPress?: (product: Product) => void;
  onAddToCart?: (item: any) => void;
}

export default function ProductCard({ product, onPress, onInfoPress, onAddToCart }: ProductCardProps) {
  const addToCart = useCartSyncStore((state) => state.addItem);
  const [isAdding, setIsAdding] = useState(false);

  const handleAddToCart = async () => {
    if (isAdding) return;
    
    setIsAdding(true);
    
    const cartItem = {
      itemId: product.id,
      name: product.name,
      unitBasePrice: { currency: 'NPR' as const, amount: product.price.amount },
      qty: 1,
      imageUrl: product.imageUrl,
    };
    
    // Add to cart with callback to open the new sheet
    addToCart(cartItem, (payload) => {
      // Open the new cart added sheet
      (global as any).openCartAddedSheet?.(payload);
    });
    
    // Reset loading state after a short delay
    setTimeout(() => {
      setIsAdding(false);
    }, 500);
  };

  const formatPrice = (price: { currency: string; amount: number }) => {
    return `Rs.${Math.round(price.amount)}`;
  };

  return (
    <View style={styles.container}>
      {/* Image Section */}
      <Pressable 
        style={styles.imageContainer}
        onPress={() => onInfoPress?.(product)}
      >
        <Image
          source={{ uri: product.imageUrl }}
          style={styles.image}
          resizeMode="cover"
          onError={(error) => console.log('Featured Product Image Error:', error.nativeEvent.error, 'URL:', product.imageUrl)}
          onLoad={() => console.log('Featured Product Image Loaded:', product.imageUrl)}
        />
        
        {/* Gradient overlay */}
        <LinearGradient
          colors={['transparent', 'rgba(0,0,0,0.4)']}
          style={styles.imageGradient}
        />
        
        {/* Featured badge */}
        {product.isFeatured && (
          <View style={styles.featuredBadge}>
            <Text style={styles.featuredText}>⭐ Featured</Text>
          </View>
        )}
        
        {/* Product info overlay */}
        <View style={styles.productInfoOverlay}>
          <View style={styles.mainContent}>
            {/* Left side - Text content and Info button */}
            <View style={styles.textContent}>
              <Text style={styles.productName}>
                {product.name}
              </Text>
              {/* Info Button */}
              <Pressable
                style={styles.infoButton}
                onPress={() => onInfoPress?.(product)}
              >
                <MCI name="information-outline" size={10} color={colors.white} />
                <Text style={styles.infoButtonText}>Info</Text>
              </Pressable>
            </View>
            
            {/* Right side - Price and Add to Cart */}
            <View style={styles.rightContent}>
              <Text style={styles.price}>
                {formatPrice(product.price)}
              </Text>
              {/* Add to Cart Button */}
              <Pressable 
                style={[styles.addToCartButton, isAdding && styles.addToCartButtonLoading]} 
                onPress={handleAddToCart}
                disabled={isAdding}
              >
                {isAdding ? (
                  <MCI name="loading" size={16} color={colors.white} />
                ) : (
                  <MCI name="shopping-outline" size={16} color={colors.white} />
                )}
                <Text style={styles.addToCartText}>
                  {isAdding ? 'Adding...' : 'Add'}
                </Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Pressable>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    marginBottom: spacing.sm,
    overflow: 'hidden',
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
    backgroundColor: '#f0f0f0', // Fallback to see container
  },
  image: {
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
  featuredBadge: {
    position: 'absolute',
    top: spacing.sm,
    right: spacing.sm,
    backgroundColor: '#152039',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  featuredText: {
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
    fontSize: 10,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.xs,
    backgroundColor: 'rgba(0,0,0,0.75)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    lineHeight: 12,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
  },
  productDescription: {
    fontSize: 9,
    color: colors.white,
    marginBottom: spacing.xs,
    opacity: 0.95,
    backgroundColor: 'rgba(0,0,0,0.7)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    lineHeight: 11,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
  },
  price: {
    fontSize: 12,
    fontWeight: fontWeights.bold,
    color: '#FCD34D',
    backgroundColor: 'rgba(0,0,0,0.8)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    textShadowColor: 'rgba(0,0,0,0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    marginBottom: 4,
    borderWidth: 1,
    borderColor: 'rgba(252, 211, 77, 0.3)',
  },
  infoButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#3B82F6',
    paddingHorizontal: spacing.xs,
    paddingVertical: 2,
    borderRadius: radius.sm,
    gap: 2,
    shadowColor: '#3B82F6',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 1,
    elevation: 1,
    alignSelf: 'flex-start',
    marginTop: 2,
  },
  infoButtonText: {
    color: colors.white,
    fontSize: 8,
    fontWeight: fontWeights.medium,
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
  addToCartButtonLoading: {
    opacity: 0.7,
  },
});