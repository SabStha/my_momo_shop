import React, { useState, useMemo } from 'react';
import { View, Text, StyleSheet, ScrollView, Image, Alert } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useLocalSearchParams, router } from 'expo-router';
import { useItem } from '../../src/api';
import { SkeletonCard, ErrorState, ScreenWithBottomNav } from '../../src/components';
import { Button, Chip, QuantityStepper, Price, Card } from '../../src/ui';
import { spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { useAddItem } from '../../src/state/cart';
import { calculateItemTotal, formatMoney } from '../../src/utils/price';
import { MenuItem, Variant, AddOn, Money } from '../../src/types';

export default function ItemDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { data: item, isLoading, isError, error, refetch } = useItem(id || '');
  const addItem = useAddItem();
  const insets = useSafeAreaInsets();
  
  // Local state for selections
  const [selectedVariant, setSelectedVariant] = useState<Variant | null>(null);
  const [selectedAddOns, setSelectedAddOns] = useState<AddOn[]>([]);
  const [quantity, setQuantity] = useState(1);

  // Set default variant when item loads
  useMemo(() => {
    if (item && item.variants && item.variants.length > 0 && !selectedVariant) {
      setSelectedVariant(item.variants[0]);
    }
  }, [item, selectedVariant]);

  // Calculate total price
  const totalPrice = useMemo((): Money => {
    if (!item) return { currency: 'NPR', amount: 0 };
    
    return calculateItemTotal(
      item.basePrice,
      selectedVariant?.priceDiff,
      selectedAddOns.map(a => a.price),
      quantity
    );
  }, [item, selectedVariant, selectedAddOns, quantity]);

  // Handle add-on selection
  const toggleAddOn = (addon: AddOn) => {
    setSelectedAddOns(prev => {
      const isSelected = prev.some(a => a.id === addon.id);
      if (isSelected) {
        return prev.filter(a => a.id !== addon.id);
      } else {
        return [...prev, addon];
      }
    });
  };

  // Memoize cart line to prevent infinite loops
  const cartLine = useMemo(() => {
    if (!item) return null;

    return {
      itemId: item.id,
      name: item.name,
      unitBasePrice: item.basePrice,
      variantId: selectedVariant?.id,
      variantName: selectedVariant?.name,
      addOns: selectedAddOns.length > 0 ? selectedAddOns.map(addon => ({
        id: addon.id,
        name: addon.name,
        price: addon.price,
      })) : undefined,
      qty: quantity,
      imageUrl: item.imageUrl,
    };
  }, [item, selectedVariant, selectedAddOns, quantity]);

  // Handle add to cart
  const handleAddToCart = () => {
    if (!item || !cartLine) return;

    // Validate variant selection if variants exist
    if (item.variants && item.variants.length > 0 && !selectedVariant) {
      Alert.alert('Select Size', 'Please select a size before adding to cart.');
      return;
    }

    // Add to cart
    addItem(cartLine);

    // Show success message and redirect to cart
    Alert.alert(
      'Added to Cart!',
      `${item.name} has been added to your cart.`,
      [
        { text: 'Continue Shopping', style: 'cancel' },
        { text: 'View Cart', onPress: () => router.push('/cart') }
      ]
    );
  };

  if (isLoading) {
    return (
      <ScreenWithBottomNav>
        <SafeAreaView style={{ flex: 1 }}>
          <ScrollView
            style={{ flex: 1 }}
            contentContainerStyle={{
              paddingHorizontal: 16,
              paddingTop: 12,
              paddingBottom: insets.bottom + 160,
            }}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator
            nestedScrollEnabled
          >
            <View style={styles.container}>
              <View style={styles.skeletonImage} />
              <View style={styles.skeletonContent}>
                <View style={styles.skeletonTitle} />
                <View style={styles.skeletonDescription} />
                <View style={styles.skeletonPrice} />
              </View>
            </View>
          </ScrollView>
        </SafeAreaView>
      </ScreenWithBottomNav>
    );
  }

  if (isError || !item) {
    return (
      <ScreenWithBottomNav>
        <SafeAreaView style={{ flex: 1 }}>
          <ScrollView
            style={{ flex: 1 }}
            contentContainerStyle={{
              paddingHorizontal: 16,
              paddingTop: 12,
              paddingBottom: insets.bottom + 160,
            }}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator
            nestedScrollEnabled
          >
            <View style={styles.container}>
              <ErrorState
                message={error?.message || "Failed to load item details"}
                onRetry={refetch}
              />
            </View>
          </ScrollView>
        </SafeAreaView>
      </ScreenWithBottomNav>
    );
  }

  return (
    <ScreenWithBottomNav>
      <SafeAreaView style={{ flex: 1 }}>
      <ScrollView
        style={{ flex: 1 }}
        contentContainerStyle={{
          paddingHorizontal: 16,
          paddingTop: 12,
          // Leave space for the sticky footer so last card isn't hidden
          paddingBottom: insets.bottom + 160,
        }}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator
        nestedScrollEnabled
      >
        <View style={styles.container}>
          {/* Item Image */}
          <View style={styles.imageContainer}>
            {item.imageUrl ? (
              <Image
                source={{ uri: item.imageUrl }}
                style={styles.image}
                resizeMode="cover"
                accessibilityLabel={`Image of ${item.name}`}
              />
            ) : (
              <View style={styles.placeholderImage}>
                <Text style={styles.placeholderText}>🍽️</Text>
              </View>
            )}
          </View>

          {/* Item Details */}
          <View style={styles.content}>
            <Text style={styles.name}>{item.name}</Text>
            
            {item.desc && (
              <Text style={styles.description}>{item.desc}</Text>
            )}

            {/* Availability Status */}
            <View style={styles.availabilitySection}>
              <Text style={styles.availabilityLabel}>Status:</Text>
              <Text style={[
                styles.availabilityStatus,
                { color: item.isAvailable ? colors.success : colors.error }
              ]}>
                {item.isAvailable ? 'Available' : 'Currently Unavailable'}
              </Text>
            </View>

            {/* Variant Selector */}
            {item.variants && item.variants.length > 0 && (
              <View style={styles.section}>
                <Text style={styles.sectionTitle}>Select Size *</Text>
                <View style={styles.variantsContainer}>
                  {item.variants.map(variant => (
                    <Chip
                      key={variant.id}
                      label={`${variant.name} (+Rs. ${variant.priceDiff.amount})`}
                      selected={selectedVariant?.id === variant.id}
                      onPress={() => setSelectedVariant(variant)}
                      variant="primary"
                      size="md"
                      style={styles.variantChip}
                    />
                  ))}
                </View>
              </View>
            )}

            {/* Add-ons */}
            {item.addOns && item.addOns.length > 0 && (
              <View style={styles.section}>
                <Text style={styles.sectionTitle}>Add-ons (Optional)</Text>
                <View style={styles.addOnsContainer}>
                  {item.addOns.map(addon => (
                    <Chip
                      key={addon.id}
                      label={`${addon.name} (+Rs. ${addon.price.amount})`}
                      selected={selectedAddOns.some(a => a.id === addon.id)}
                      onPress={() => toggleAddOn(addon)}
                      variant="default"
                      size="md"
                      style={styles.addOnChip}
                    />
                  ))}
                </View>
              </View>
            )}

            {/* Quantity Selector */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Quantity</Text>
              <View style={styles.quantityContainer}>
                <QuantityStepper
                  value={quantity}
                  onValueChange={setQuantity}
                  min={1}
                  max={99}
                  size="lg"
                />
              </View>
            </View>

            {/* Price Summary */}
            <Card style={styles.priceCard} padding="lg" radius="lg" shadow="medium">
              <Text style={styles.priceCardTitle}>Price Summary</Text>
              
              <View style={styles.priceBreakdown}>
                <View style={styles.priceRow}>
                  <Text style={styles.priceLabel}>Base Price:</Text>
                  <Price value={item.basePrice} size="md" />
                </View>
                
                {selectedVariant && (
                  <View style={styles.priceRow}>
                    <Text style={styles.priceLabel}>Size ({selectedVariant.name}):</Text>
                    <Price value={selectedVariant.priceDiff} size="md" />
                  </View>
                )}
                
                {selectedAddOns.length > 0 && (
                  <View style={styles.priceRow}>
                    <Text style={styles.priceLabel}>Add-ons:</Text>
                    <Price 
                      value={{ currency: 'NPR' as const, amount: selectedAddOns.reduce((sum, a) => sum + a.price.amount, 0) }} 
                      size="md" 
                    />
                  </View>
                )}
                
                <View style={styles.priceRow}>
                  <Text style={styles.priceLabel}>Quantity:</Text>
                  <Text style={styles.quantityText}>× {quantity}</Text>
                </View>
                
                <View style={styles.divider} />
                
                <View style={styles.priceRow}>
                  <Text style={styles.totalLabel}>Total:</Text>
                  <Price value={totalPrice} size="lg" weight="bold" />
                </View>
              </View>
            </Card>
          </View>
        </View>
      </ScrollView>

      {/* Sticky Footer */}
      <View
        style={{
          position: "absolute",
          left: 0,
          right: 0,
          bottom: 0,
          paddingHorizontal: 16,
          paddingTop: 8,
          paddingBottom: Math.max(insets.bottom, 12),
          backgroundColor: "white",
          borderTopWidth: StyleSheet.hairlineWidth,
          borderColor: "#eee",
          shadowColor: "#000",
          shadowOpacity: 0.08,
          shadowRadius: 12,
          elevation: 8,
        }}
        pointerEvents="box-none"
      >
        <View style={{ flexDirection: "row", alignItems: "center", justifyContent: "space-between" }}>
          <View>
            <Text style={{ fontSize: 12, color: "#666" }}>Total</Text>
            <Price value={totalPrice} size="lg" weight="bold" />
          </View>
          <Button
            onPress={handleAddToCart}
            title="Add to Cart"
            variant="solid"
            size="lg"
            disabled={!item.isAvailable || (item.variants && item.variants.length > 0 && !selectedVariant)}
            leftIcon={<Text style={styles.cartIcon}>🛒</Text>}
          />
        </View>
      </View>
      </SafeAreaView>
    </ScreenWithBottomNav>
  );
}

const styles = StyleSheet.create({
  container: {
    // Removed flex: 1 to allow proper scrolling
  },
  imageContainer: {
    height: 250,
    backgroundColor: colors.gray[100],
  },
  image: {
    width: '100%',
    height: '100%',
  },
  placeholderImage: {
    width: '100%',
    height: '100%',
    backgroundColor: colors.gray[100],
    justifyContent: 'center',
    alignItems: 'center',
  },
  placeholderText: {
    fontSize: 80,
  },
  content: {
    padding: spacing.lg,
  },
  name: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.md,
    lineHeight: fontSizes.xxl * 1.2,
  },
  description: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    marginBottom: spacing.lg,
    lineHeight: fontSizes.md * 1.4,
  },
  availabilitySection: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.lg,
    paddingBottom: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  availabilityLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    marginRight: spacing.sm,
  },
  availabilityStatus: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  section: {
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginBottom: spacing.md,
  },
  variantsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  variantChip: {
    minWidth: 120,
  },
  addOnsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  addOnChip: {
    minWidth: 140,
  },
  quantityContainer: {
    alignItems: 'center',
  },
  priceCard: {
    marginBottom: spacing.lg,
  },
  priceCardTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginBottom: spacing.md,
    textAlign: 'center',
  },
  priceBreakdown: {
    gap: spacing.sm,
  },
  priceRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  priceLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  quantityText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  divider: {
    height: 1,
    backgroundColor: colors.gray[200],
    marginVertical: spacing.sm,
  },
  totalLabel: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
  },
  cartIcon: {
    fontSize: 20,
    marginRight: spacing.xs,
  },
  // Skeleton styles
  skeletonImage: {
    height: 250,
    backgroundColor: colors.gray[200],
  },
  skeletonContent: {
    padding: spacing.lg,
  },
  skeletonTitle: {
    height: 32,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    marginBottom: spacing.md,
  },
  skeletonDescription: {
    height: 20,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    marginBottom: spacing.lg,
    width: '80%',
  },
  skeletonPrice: {
    height: 28,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    width: '60%',
  },
});
