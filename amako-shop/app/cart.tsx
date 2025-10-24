import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Alert,
  RefreshControl,
  StatusBar,
  Platform,
  Animated,
} from 'react-native';

// Create animated ScrollView for native scroll tracking
const AnimatedScrollView = Animated.createAnimatedComponent(ScrollView);
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { useCartSyncStore } from '../src/state/cart-sync';
import { Money } from '../src/types';
import { sumMoney, multiplyMoney } from '../src/utils/price';
import { ScreenWithBottomNav } from '../src/components';
import LoadingSpinner from '../src/components/LoadingSpinner';
import { useMyOffers, useApplyOffer, useRemoveOffer } from '../src/api/offers';

export default function CartScreen() {
  const { items, subtotal, itemCount, updateQuantity, removeItem, clearCart } = useCartSyncStore();
  const [refreshing, setRefreshing] = useState(false);
  const [isPulling, setIsPulling] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;
  const [appliedOffer, setAppliedOffer] = useState<any>(null);
  const [showOffersList, setShowOffersList] = useState(false);
  
  // Fetch user's active offers
  const { data: myOffers = [] } = useMyOffers();
  const applyOfferMutation = useApplyOffer();
  const removeOfferMutation = useRemoveOffer();
  
  // Filter only active offers
  const activeOffers = myOffers.filter(claim => claim.status === 'active');

  // Track pulling state
  useEffect(() => {
    const listenerId = scrollY.addListener(({ value }) => {
      setIsPulling(value < -50);
    });
    return () => scrollY.removeListener(listenerId);
  }, [scrollY]);

  const onRefresh = React.useCallback(() => {
    setRefreshing(true);
    // Cart data is already reactive from Zustand, add minimum delay for loading animation
    const minDelay = new Promise(resolve => setTimeout(resolve, 2000));
    minDelay.then(() => setRefreshing(false));
  }, []);

  const handleUpdateQuantity = (itemId: string, variantId: string | undefined, addOns: string[], quantity: number) => {
    if (quantity <= 0) {
      handleRemoveItem(itemId, variantId, addOns);
    } else {
      updateQuantity(itemId, variantId, addOns, quantity);
    }
  };

  const handleRemoveItem = (itemId: string, variantId: string | undefined, addOns: string[]) => {
    Alert.alert(
      'Remove Item',
      'Are you sure you want to remove this item from your cart?',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Remove', style: 'destructive', onPress: () => removeItem(itemId, variantId, addOns) },
      ]
    );
  };

  const handleClearCart = () => {
    Alert.alert(
      'Clear Cart',
      'Are you sure you want to clear your entire cart?',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Clear', style: 'destructive', onPress: () => clearCart() },
      ]
    );
  };
  
  const handleApplyOffer = async (offerCode: string) => {
    try {
      const result = await applyOfferMutation.mutateAsync(offerCode);
      if (result.success) {
        setAppliedOffer(result.offer);
        Alert.alert('✅ Offer Applied!', `You saved Rs. ${result.discount_applied || 0}!`);
      }
    } catch (error: any) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to apply offer');
    }
  };
  
  const handleRemoveOffer = async () => {
    try {
      await removeOfferMutation.mutateAsync();
      setAppliedOffer(null);
    } catch (error: any) {
      Alert.alert('Error', 'Failed to remove offer');
    }
  };
  
  // Calculate discount if offer is applied
  const calculateDiscount = () => {
    if (!appliedOffer) return 0;
    
    const subtotalAmount = typeof subtotal === 'number' ? subtotal : subtotal.amount;
    const discount = (subtotalAmount * appliedOffer.discount) / 100;
    
    return Math.min(discount, appliedOffer.max_discount || discount);
  };
  
  const discountAmount = calculateDiscount();
  const totalWithDiscount = (typeof subtotal === 'number' ? subtotal : subtotal.amount) - discountAmount;

  // Check if cart contains bulk items
  const hasBulkItems = items.some(item => item.itemId.startsWith('bulk-'));
  
  const handleCheckout = () => {
    if (items.length === 0) {
      Alert.alert('Empty Cart', 'Your cart is empty. Please add some items first.');
      return;
    }
    router.push('/checkout');
  };

  const calculateItemTotal = (item: any): Money => {
    let unitPrice = item.unitBasePrice;
    
    // Add add-ons price
    if (item.addOns && item.addOns.length > 0) {
      const addOnsTotal = sumMoney(...item.addOns.map((a: any) => a.price));
      unitPrice = sumMoney(unitPrice, addOnsTotal);
    }
    
    return multiplyMoney(unitPrice, item.qty);
  };

  const calculateTax = (subtotal: Money): Money => {
    const taxRate = 13; // 13% tax rate
    return { currency: 'NPR', amount: subtotal.amount * (taxRate / 100) };
  };

  const tax = calculateTax(subtotal);
  const total: Money = { currency: 'NPR', amount: subtotal.amount + tax.amount };

  if (items.length === 0) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.container}>
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Shopping Cart</Text>
          </View>
          
          <View style={styles.emptyContainer}>
            <View style={styles.emptyIcon}>
              <Text style={styles.emptyIconText}>🛒</Text>
            </View>
            <Text style={styles.emptyTitle}>Your cart is empty</Text>
            <Text style={styles.emptySubtitle}>Looks like you haven't added any items to your cart yet.</Text>
            <TouchableOpacity style={styles.startShoppingButton} onPress={() => router.push('/(tabs)/home')}>
              <Text style={styles.startShoppingButtonText}>Start Shopping</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScreenWithBottomNav>
    );
  }

  return (
    <ScreenWithBottomNav>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Shopping Cart</Text>
          <Text style={styles.headerSubtitle}>{itemCount} items in your cart</Text>
        </View>

        <AnimatedScrollView
          style={styles.scrollView}
          onScroll={Animated.event(
            [{ nativeEvent: { contentOffset: { y: scrollY } } }],
            { useNativeDriver: true }
          )}
          scrollEventThrottle={16}
          refreshControl={
            <RefreshControl 
              refreshing={refreshing} 
              onRefresh={onRefresh}
              colors={['transparent']}
              tintColor="transparent"
              progressViewOffset={-9999}
            />
          }
        >
        {/* Progress Indicator */}
        <View style={styles.progressContainer}>
          <View style={styles.progressStep}>
            <View style={[styles.progressCircle, styles.progressCircleActive]}>
              <Text style={styles.progressNumber}>1</Text>
            </View>
            <Text style={styles.progressLabel}>Cart</Text>
          </View>
          <View style={styles.progressLine} />
          <View style={styles.progressStep}>
            <View style={styles.progressCircle}>
              <Text style={styles.progressNumberInactive}>2</Text>
            </View>
            <Text style={styles.progressLabelInactive}>Delivery Info</Text>
          </View>
          <View style={styles.progressLine} />
          <View style={styles.progressStep}>
            <View style={styles.progressCircle}>
              <Text style={styles.progressNumberInactive}>3</Text>
            </View>
            <Text style={styles.progressLabelInactive}>Payment</Text>
          </View>
        </View>

        {/* Cart Items */}
        <View style={styles.cartItemsContainer}>
          {items.map((item, index) => {
            const itemTotal = calculateItemTotal(item);
            const addOnsKey = item.addOns?.map(a => a.id).sort().join(',') || '';
            const uniqueKey = `${item.itemId}-${item.variantId || 'base'}-${addOnsKey}-${index}`;
            
            return (
              <View key={uniqueKey} style={styles.cartItem}>
                <View style={styles.cartItemImage}>
                  {item.imageUrl ? (
                    <Image source={{ uri: item.imageUrl }} style={styles.itemImage} />
                  ) : (
                    <View style={styles.itemImagePlaceholder}>
                      <Text style={styles.placeholderIcon}>🍽️</Text>
                    </View>
                  )}
                </View>
                
                <View style={styles.cartItemDetails}>
                  <Text style={styles.itemName}>{item.name}</Text>
                  <Text style={styles.itemPrice}>Rs.{item.unitBasePrice.amount.toFixed(2)} each</Text>
                  
                  {item.variantName && (
                    <Text style={styles.itemVariant}>Variant: {item.variantName}</Text>
                  )}
                  
                  {item.addOns && item.addOns.length > 0 && (
                    <Text style={styles.itemAddOns}>
                      Add-ons: {item.addOns.map(a => a.name).join(', ')}
                    </Text>
                  )}
                </View>
                
                <View style={styles.cartItemControls}>
                  <View style={styles.quantityControls}>
                    <TouchableOpacity
                      style={styles.quantityButton}
                      onPress={() => handleUpdateQuantity(item.itemId, item.variantId, item.addOns?.map(a => a.id) || [], item.qty - 1)}
                    >
                      <Text style={styles.quantityButtonText}>-</Text>
                    </TouchableOpacity>
                    <Text style={styles.quantityText}>{item.qty}</Text>
                    <TouchableOpacity
                      style={styles.quantityButton}
                      onPress={() => handleUpdateQuantity(item.itemId, item.variantId, item.addOns?.map(a => a.id) || [], item.qty + 1)}
                    >
                      <Text style={styles.quantityButtonText}>+</Text>
                    </TouchableOpacity>
                  </View>
                  
                  <Text style={styles.itemTotal}>Rs.{itemTotal.amount.toFixed(2)}</Text>
                  
                  <TouchableOpacity
                    style={styles.removeButton}
                    onPress={() => handleRemoveItem(item.itemId, item.variantId, item.addOns?.map(a => a.id) || [])}
                  >
                    <Text style={styles.removeButtonText}>Remove</Text>
                  </TouchableOpacity>
                </View>
              </View>
            );
          })}
        </View>

        {/* Available Offers Section */}
        {activeOffers.length > 0 && (
          <View style={styles.offersSection}>
            <View style={styles.offersSectionHeader}>
              <View style={styles.offersSectionTitleRow}>
                <Ionicons name="gift" size={20} color={colors.orange[500]} />
                <Text style={styles.offersSectionTitle}>
                  Available Offers ({activeOffers.length})
                </Text>
              </View>
              
              <TouchableOpacity onPress={() => router.push('/offers')}>
                <Text style={styles.viewAllOffersText}>View All</Text>
              </TouchableOpacity>
            </View>
            
            {/* Applied Offer */}
            {appliedOffer ? (
              <View style={styles.appliedOfferCard}>
                <View style={styles.appliedOfferInfo}>
                  <Ionicons name="checkmark-circle" size={20} color={colors.green[500]} />
                  <View style={styles.appliedOfferText}>
                    <Text style={styles.appliedOfferTitle}>
                      {appliedOffer.title}
                    </Text>
                    <Text style={styles.appliedOfferDiscount}>
                      {appliedOffer.discount}% OFF • Saving Rs. {discountAmount.toFixed(2)}
                    </Text>
                  </View>
                </View>
                <TouchableOpacity onPress={handleRemoveOffer}>
                  <Ionicons name="close-circle" size={20} color={colors.gray[400]} />
                </TouchableOpacity>
              </View>
            ) : (
              /* Show first 2 active offers */
              <>
                {activeOffers.slice(0, 2).map((claim) => (
                  <TouchableOpacity
                    key={claim.id}
                    style={styles.offerCard}
                    onPress={() => handleApplyOffer(claim.offer.code)}
                  >
                    <View style={styles.offerCardContent}>
                      <View style={styles.offerDiscountBadge}>
                        <Text style={styles.offerDiscountText}>
                          {claim.offer.discount}% OFF
                        </Text>
                      </View>
                      <View style={styles.offerCardText}>
                        <Text style={styles.offerCardTitle} numberOfLines={1}>
                          {claim.offer.title}
                        </Text>
                        <Text style={styles.offerCardCode}>
                          Code: {claim.offer.code}
                        </Text>
                      </View>
                    </View>
                    <Text style={styles.applyOfferText}>Apply</Text>
                  </TouchableOpacity>
                ))}
              </>
            )}
          </View>
        )}

        {/* Order Summary */}
        <View style={styles.orderSummary}>
          <Text style={styles.orderSummaryTitle}>Order Summary</Text>
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>Rs.{subtotal.amount.toFixed(2)}</Text>
          </View>
          
          {appliedOffer && discountAmount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Offer Discount</Text>
              <Text style={styles.summaryDiscountValue}>-Rs.{discountAmount.toFixed(2)}</Text>
            </View>
          )}
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Tax (13%)</Text>
            <Text style={styles.summaryValue}>Rs.{tax.amount.toFixed(2)}</Text>
          </View>
          
          <View style={[styles.summaryRow, styles.summaryTotal]}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>
              Rs.{(appliedOffer ? totalWithDiscount + tax.amount : total.amount).toFixed(2)}
            </Text>
          </View>
        </View>

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <TouchableOpacity style={styles.checkoutButton} onPress={handleCheckout}>
            <Ionicons name="location-outline" size={18} color={colors.white} style={{ marginRight: spacing.xs }} />
            <Text style={styles.checkoutButtonText}>
              {hasBulkItems ? 'Continue to Delivery Address' : 'Proceed to Checkout'}
            </Text>
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.clearButton} onPress={handleClearCart}>
            <Text style={styles.clearButtonText}>Clear Cart</Text>
          </TouchableOpacity>
        </View>
        </AnimatedScrollView>
        
        {/* Loading Overlay - Shows during pull and refresh */}
        {(isPulling || refreshing) && (
          <Animated.View 
            style={[
              styles.loadingOverlay,
              refreshing ? {
                opacity: 1,
                transform: [{ translateY: 0 }]
              } : {
                opacity: scrollY.interpolate({
                  inputRange: [-150, -50, 0],
                  outputRange: [1, 0.5, 0],
                  extrapolate: 'clamp',
                }),
                transform: [{
                  translateY: scrollY.interpolate({
                    inputRange: [-150, 0],
                    outputRange: [0, 150],
                    extrapolate: 'clamp',
                  })
                }]
              }
            ]}
          >
            <LoadingSpinner 
              size="large" 
              text={refreshing ? "Refreshing..." : "Pull to refresh"}
            />
          </Animated.View>
        )}
      </View>
    </ScreenWithBottomNav>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  header: {
    backgroundColor: colors.white,
    paddingHorizontal: spacing.lg,
    paddingTop: Platform.OS === 'ios' ? 50 : 40,
    paddingBottom: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  headerSubtitle: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginTop: spacing.xs,
  },
  scrollView: {
    flex: 1,
  },
  progressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.lg,
    backgroundColor: colors.white,
    marginBottom: spacing.md,
  },
  progressStep: {
    alignItems: 'center',
  },
  progressCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.gray[300],
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
  },
  progressCircleActive: {
    backgroundColor: colors.brand.primary,
  },
  progressNumber: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
  progressNumberInactive: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.gray[500],
  },
  progressLine: {
    width: 48,
    height: 2,
    backgroundColor: colors.gray[300],
    marginHorizontal: spacing.sm,
  },
  progressLabel: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: colors.brand.primary,
  },
  progressLabelInactive: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: colors.gray[500],
  },
  cartItemsContainer: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    marginBottom: spacing.lg,
  },
  cartItem: {
    flexDirection: 'row',
    padding: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  cartItemImage: {
    marginRight: spacing.md,
  },
  itemImage: {
    width: 64,
    height: 64,
    borderRadius: radius.md,
  },
  itemImagePlaceholder: {
    width: 64,
    height: 64,
    borderRadius: radius.md,
    backgroundColor: colors.gray[200],
    alignItems: 'center',
    justifyContent: 'center',
  },
  placeholderIcon: {
    fontSize: 24,
  },
  cartItemDetails: {
    flex: 1,
    marginRight: spacing.sm,
  },
  itemName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  itemPrice: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  itemVariant: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    marginBottom: spacing.xs,
  },
  itemAddOns: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
  },
  cartItemControls: {
    alignItems: 'flex-end',
  },
  quantityControls: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  quantityButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.gray[100],
    alignItems: 'center',
    justifyContent: 'center',
  },
  quantityButtonText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
  },
  quantityText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
    marginHorizontal: spacing.md,
    minWidth: 24,
    textAlign: 'center',
  },
  itemTotal: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  removeButton: {
    paddingVertical: spacing.xs,
  },
  removeButtonText: {
    fontSize: fontSizes.xs,
    color: colors.error[500],
    fontWeight: fontWeights.medium,
  },
  orderSummary: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  orderSummaryTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.md,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  summaryLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  summaryValue: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
  },
  summaryTotal: {
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
    paddingTop: spacing.sm,
    marginTop: spacing.sm,
  },
  summaryTotalLabel: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
  },
  summaryTotalValue: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
  },
  actionButtons: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl,
  },
  checkoutButton: {
    backgroundColor: colors.brand.primary,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    alignItems: 'center',
    marginBottom: spacing.sm,
    flexDirection: 'row',
    justifyContent: 'center',
  },
  checkoutButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
  clearButton: {
    backgroundColor: colors.gray[100],
    paddingVertical: spacing.sm,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  clearButtonText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
  },
  emptyIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: colors.gray[100],
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.lg,
  },
  emptyIconText: {
    fontSize: 32,
  },
  emptyTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  emptySubtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.xl,
  },
  startShoppingButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
  },
  startShoppingButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.15)',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1000,
  },
  offersSection: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.md,
    marginVertical: spacing.sm,
    padding: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.orange[200],
  },
  offersSectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  offersSectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  offersSectionTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
  },
  viewAllOffersText: {
    fontSize: fontSizes.sm,
    color: colors.orange[500],
    fontWeight: fontWeights.medium,
  },
  appliedOfferCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.green[50],
    padding: spacing.md,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.green[200],
  },
  appliedOfferInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    flex: 1,
  },
  appliedOfferText: {
    flex: 1,
  },
  appliedOfferTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.green[700],
    marginBottom: 2,
  },
  appliedOfferDiscount: {
    fontSize: fontSizes.xs,
    color: colors.green[600],
  },
  offerCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.orange[50],
    padding: spacing.sm,
    borderRadius: radius.md,
    marginBottom: spacing.xs,
  },
  offerCardContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    flex: 1,
  },
  offerDiscountBadge: {
    backgroundColor: colors.orange[500],
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: radius.sm,
  },
  offerDiscountText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  offerCardText: {
    flex: 1,
  },
  offerCardTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
    marginBottom: 2,
  },
  offerCardCode: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    fontFamily: 'monospace',
  },
  applyOfferText: {
    fontSize: fontSizes.sm,
    color: colors.orange[500],
    fontWeight: fontWeights.semibold,
  },
  summaryDiscountValue: {
    color: colors.green[600],
    fontWeight: fontWeights.semibold,
    fontSize: fontSizes.sm,
  },
});