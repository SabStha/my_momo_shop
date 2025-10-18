import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  Image,
  StatusBar,
  Platform,
} from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { useQueryClient } from '@tanstack/react-query';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { useCartSyncStore } from '../src/state/cart-sync';
import { Button } from '../src/ui';
import { Money } from '../src/types';
import { sumMoney, multiplyMoney } from '../src/utils/price';
import { ScreenWithBottomNav, OrderSuccessModal } from '../src/components';
import { createOrder as createOrderAPI, CreateOrderRequest } from '../src/api/orders';
import { useUserProfile } from '../src/api/user-hooks';

type PaymentMethod = 'amako_credits' | 'cash' | 'esewa' | 'khalti' | 'fonepay' | 'card';

export default function PaymentScreen() {
  const queryClient = useQueryClient();
  const { branchId, branchName, deliveryFee } = useLocalSearchParams<{
    branchId?: string;
    branchName?: string;
    deliveryFee?: string;
  }>();
  
  const { items, subtotal, itemCount, clearCart } = useCartSyncStore();
  const { data: userProfile } = useUserProfile();
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<PaymentMethod | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [orderNumber, setOrderNumber] = useState('');
  const [createdOrderId, setCreatedOrderId] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const isNavigatingAway = React.useRef(false);

  // Silently redirect if cart is empty (no alert popups) - but not if we're intentionally navigating away
  useEffect(() => {
    if (items.length === 0 && !isNavigatingAway.current) {
      console.log('🚨 PAYMENT: Cart is empty, silently redirecting to cart...');
      router.replace('/cart');
    }
  }, [items.length]);

  const calculateTax = (subtotal: Money): Money => {
    const taxRate = 13; // 13% tax rate
    return { currency: 'NPR', amount: subtotal.amount * (taxRate / 100) };
  };

  const tax = calculateTax(subtotal);
  const total: Money = { currency: 'NPR', amount: subtotal.amount + tax.amount };

  const paymentMethods = [
    {
      id: 'amako_credits' as PaymentMethod,
      name: 'Amako Credits',
      description: 'Pay with your Amako wallet credits',
      icon: '💰',
      available: true,
      featured: true, // Highlight this payment method
    },
    {
      id: 'cash' as PaymentMethod,
      name: 'Cash on Delivery',
      description: 'Pay when your order arrives',
      icon: '💵',
      available: true,
    },
    {
      id: 'esewa' as PaymentMethod,
      name: 'eSewa',
      description: 'Pay with your eSewa wallet',
      icon: '📱',
      available: true,
    },
    {
      id: 'khalti' as PaymentMethod,
      name: 'Khalti',
      description: 'Pay with Khalti digital wallet',
      icon: '💳',
      available: true,
    },
    {
      id: 'fonepay' as PaymentMethod,
      name: 'FonePay',
      description: 'Pay with FonePay',
      icon: '📲',
      available: true,
    },
    {
      id: 'card' as PaymentMethod,
      name: 'Credit/Debit Card',
      description: 'Pay with your card',
      icon: '💳',
      available: false, // Temporarily disabled
    },
  ];

  const handlePayment = async () => {
    if (!selectedPaymentMethod) {
      Alert.alert('Select Payment Method', 'Please select a payment method to continue.');
      return;
    }

    if (items.length === 0) {
      console.log('🚨 PAYMENT: Empty cart in handlePayment, redirecting...');
      router.replace('/cart');
      return;
    }

    if (!userProfile) {
      Alert.alert('Error', 'User profile not found. Please log in again.');
      return;
    }

    setIsProcessing(true);
    setErrorMessage('');

    try {
      // Prepare order data for API
      const orderData: CreateOrderRequest = {
        branch_id: branchId ? parseInt(branchId) : 1,
        name: userProfile.name || 'Customer',
        email: userProfile.email || 'customer@email.com',
        phone: userProfile.phone || '',
        city: userProfile.city || 'Kathmandu',
        ward_number: userProfile.ward_number,
        area_locality: userProfile.area_locality,
        building_name: userProfile.building_name,
        detailed_directions: userProfile.detailed_directions,
        payment_method: selectedPaymentMethod, // Send amako_credits directly (backend handles both)
        items: items.map(item => {
          // Defensive: handle cases where itemId might be undefined
          const itemIdStr = item.itemId || (item as any).id || '';
          if (!itemIdStr) {
            console.error('❌ Payment error: item missing itemId:', item);
            throw new Error('Invalid cart item: missing ID. Please clear your cart and try again.');
          }
          
          // Extract product ID (remove 'custom-' prefix if exists, then get first part)
          const productId = itemIdStr.replace(/^custom-/, '').split('-')[0];
          
          return {
            product_id: productId,
            quantity: item.qty,
            type: 'product',
          };
        }),
        total: total.amount,
      };

      console.log('📦 Sending order to API:', orderData);

      // Create order via API
      const result = await createOrderAPI(orderData);

      if (!result.success) {
        // Handle business closed error
        if (result.business_status === 'closed') {
          Alert.alert(
            'Business Closed',
            result.message || 'We are currently closed. Please try again during business hours.',
            [{ text: 'OK', onPress: () => router.replace('/cart') }]
          );
          return;
        }

        // Handle other errors
        throw new Error(result.message || 'Failed to create order');
      }

      // Order created successfully ON BACKEND
      console.log('✅ Backend order created successfully!');
      console.log('✅ Full response:', result);
      console.log('✅ Order object:', result.order);
      console.log('✅ Order number:', result.order?.order_number);
      console.log('✅ Order ID:', result.order?.order_id || result.order?.id);
      
      // Validate that we have the essential order data
      // Backend returns either 'order_id' or 'id' depending on the endpoint
      const backendOrderId = result.order?.order_id || result.order?.id;
      const newOrderNumber = result.order?.order_number;
      
      if (!result.order || !backendOrderId) {
        console.error('❌ Order creation succeeded but missing order data:', result);
        throw new Error('Order was created but server did not return order details. Please check your orders page.');
      }
      
      // NEVER use timestamp as fallback - this causes 404 errors when fetching order later
      if (!newOrderNumber || !backendOrderId) {
        console.error('❌ Missing order number or ID:', { newOrderNumber, backendOrderId });
        throw new Error('Order creation failed: missing order information');
      }
      
      const orderId = backendOrderId.toString();

      // Show success modal (don't save to local storage anymore, use backend as source of truth)
      console.log('📱 Showing success modal to user');
      console.log('📱 Order number:', newOrderNumber);
      console.log('📱 Order ID:', orderId);
      
      // Invalidate orders cache so the orders page will show the new order
      queryClient.invalidateQueries({ queryKey: ['orders'] });
      console.log('✅ Orders cache invalidated - new order will appear in orders list');
      
      setOrderNumber(newOrderNumber);
      setCreatedOrderId(orderId);
      setShowSuccessModal(true);
      
    } catch (error: any) {
      console.error('❌ Payment error:', error);
      const message = error.message || 'Something went wrong with your order. Please try again.';
      setErrorMessage(message);
      
      // If cart is corrupted, offer to clear it
      if (message.includes('Invalid cart item') || message.includes('missing ID')) {
        Alert.alert(
          'Cart Error',
          'Your cart data appears to be corrupted. Would you like to clear the cart and start fresh?',
          [
            { text: 'Cancel', style: 'cancel' },
            { 
              text: 'Clear Cart', 
              style: 'destructive',
              onPress: () => {
                clearCart();
                router.replace('/menu');
              }
            }
          ]
        );
      } else {
        Alert.alert('Order Failed', message);
      }
    } finally {
      setIsProcessing(false);
    }
  };

  const handleViewOrder = () => {
    setShowSuccessModal(false);
    // Set flag to prevent redirect when clearing cart
    isNavigatingAway.current = true;
    
    console.log('📱 Navigating to orders page');
    
    // Clear cart
    clearCart();
    
    // Navigate to orders list page (order will be at the top as most recent)
    setTimeout(() => {
      router.replace('/orders');
    }, 100);
  };

  const handleCloseModal = () => {
    setShowSuccessModal(false);
    // Set flag to prevent redirect when clearing cart
    isNavigatingAway.current = true;
    
    console.log('📱 Closing modal, clearing cart and navigating home');
    
    // Clear cart and navigate to home
    clearCart();
    
    // Small delay to ensure state updates before navigation
    setTimeout(() => {
      router.replace('/(tabs)/home');
    }, 100);
  };

  if (items.length === 0) {
    return (
      <ScreenWithBottomNav>
        <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
        <View style={styles.container}>
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Payment</Text>
          </View>
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyTitle}>Your cart is empty</Text>
            <TouchableOpacity style={styles.backToCartButton} onPress={() => router.push('/cart')}>
              <Text style={styles.backToCartButtonText}>Back to Cart</Text>
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
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Payment</Text>
          <Text style={styles.headerSubtitle}>Step 3: Choose your payment method</Text>
        </View>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Progress Indicator */}
        <View style={styles.progressContainer}>
          <View style={styles.progressStep}>
            <View style={[styles.progressCircle, styles.progressCircleActive]}>
              <Text style={styles.progressNumber}>1</Text>
            </View>
            <Text style={styles.progressLabel}>Cart</Text>
          </View>
          <View style={[styles.progressLine, styles.progressLineActive]} />
          <View style={styles.progressStep}>
            <View style={[styles.progressCircle, styles.progressCircleActive]}>
              <Text style={styles.progressNumber}>2</Text>
            </View>
            <Text style={styles.progressLabel}>Delivery Info</Text>
          </View>
          <View style={[styles.progressLine, styles.progressLineActive]} />
          <View style={styles.progressStep}>
            <View style={[styles.progressCircle, styles.progressCircleActive]}>
              <Text style={styles.progressNumber}>3</Text>
            </View>
            <Text style={styles.progressLabel}>Payment</Text>
          </View>
        </View>

        {/* Selected Branch Info */}
        {branchName && (
          <View style={styles.branchInfoContainer}>
            <View style={styles.branchInfoHeader}>
              <Ionicons name="location" size={20} color={colors.brand.primary} />
              <Text style={styles.branchInfoTitle}>Delivery Branch</Text>
            </View>
            <View style={styles.branchInfoContent}>
              <Text style={styles.branchName}>{branchName}</Text>
              <View style={styles.branchDetails}>
                <View style={styles.branchDetailItem}>
                  <Ionicons name="bicycle" size={16} color={colors.gray[600]} />
                  <Text style={styles.branchDetailText}>
                    Delivery Fee: Rs. {deliveryFee || '0'}
                  </Text>
                </View>
              </View>
            </View>
            <TouchableOpacity 
              style={styles.changeBranchButton}
              onPress={() => router.back()}
            >
              <Text style={styles.changeBranchText}>Change Branch</Text>
              <Ionicons name="chevron-forward" size={16} color={colors.brand.primary} />
            </TouchableOpacity>
          </View>
        )}

        {/* Payment Methods */}
        <View style={styles.paymentMethodsContainer}>
          <Text style={styles.sectionTitle}>Choose Payment Method</Text>
          
          {paymentMethods.map((method) => (
            <TouchableOpacity
              key={method.id}
              style={[
                styles.paymentMethod,
                selectedPaymentMethod === method.id && styles.paymentMethodSelected,
                !method.available && styles.paymentMethodDisabled,
              ]}
              onPress={() => method.available && setSelectedPaymentMethod(method.id)}
              disabled={!method.available}
            >
              <View style={styles.paymentMethodContent}>
                <View style={styles.paymentMethodIcon}>
                  <Text style={styles.paymentMethodIconText}>{method.icon}</Text>
                </View>
                
                <View style={styles.paymentMethodInfo}>
                  <Text style={[
                    styles.paymentMethodName,
                    !method.available && styles.paymentMethodNameDisabled,
                  ]}>
                    {method.name}
                  </Text>
                  <Text style={[
                    styles.paymentMethodDescription,
                    !method.available && styles.paymentMethodDescriptionDisabled,
                  ]}>
                    {method.description}
                  </Text>
                </View>
                
                <View style={styles.paymentMethodSelector}>
                  {selectedPaymentMethod === method.id ? (
                    <View style={styles.radioSelected}>
                      <View style={styles.radioSelectedInner} />
                    </View>
                  ) : (
                    <View style={styles.radioUnselected} />
                  )}
                </View>
              </View>
              
              {!method.available && (
                <View style={styles.unavailableOverlay}>
                  <Text style={styles.unavailableText}>Coming Soon</Text>
                </View>
              )}
            </TouchableOpacity>
          ))}
        </View>

        {/* Order Summary */}
        <View style={styles.orderSummary}>
          <Text style={styles.orderSummaryTitle}>Order Summary</Text>
          
          {items.map((item, index) => {
            const itemTotal = { currency: 'NPR', amount: item.unitBasePrice.amount * item.qty };
            return (
              <View key={index} style={styles.summaryItem}>
                <Text style={styles.summaryItemName}>{item.name} × {item.qty}</Text>
                <Text style={styles.summaryItemPrice}>Rs.{itemTotal.amount.toFixed(2)}</Text>
              </View>
            );
          })}
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>Rs.{subtotal.amount.toFixed(2)}</Text>
          </View>
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Tax (13%)</Text>
            <Text style={styles.summaryValue}>Rs.{tax.amount.toFixed(2)}</Text>
          </View>
          
          <View style={[styles.summaryRow, styles.summaryTotal]}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>Rs.{total.amount.toFixed(2)}</Text>
          </View>
        </View>

        {/* Security Notice */}
        <View style={styles.securityNotice}>
          <Text style={styles.securityIcon}>🔒</Text>
          <Text style={styles.securityText}>
            Your payment information is secure and encrypted. We never store your payment details.
          </Text>
        </View>

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <Button
            title={isProcessing ? "Processing Payment..." : "Complete Payment"}
            onPress={handlePayment}
            variant="solid"
            size="lg"
            disabled={!selectedPaymentMethod || isProcessing}
            loading={isProcessing}
            style={styles.paymentButton}
          />
          
          <TouchableOpacity style={styles.backButton} onPress={() => router.back()}>
            <Text style={styles.backButtonText}>Back to Checkout</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
      </View>
      
      {/* Order Success Modal */}
      <OrderSuccessModal
        visible={showSuccessModal}
        orderNumber={orderNumber}
        totalAmount={total.amount}
        onViewOrder={handleViewOrder}
        onClose={handleCloseModal}
      />
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
  progressLine: {
    width: 48,
    height: 2,
    backgroundColor: colors.gray[300],
    marginHorizontal: spacing.sm,
  },
  progressLineActive: {
    backgroundColor: colors.brand.primary,
  },
  progressLabel: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: colors.brand.primary,
  },
  branchInfoContainer: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    marginBottom: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    borderWidth: 1,
    borderColor: colors.brand.primary + '20',
  },
  branchInfoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
    gap: spacing.sm,
  },
  branchInfoTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.brand.primary,
  },
  branchInfoContent: {
    marginBottom: spacing.md,
  },
  branchName: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  branchDetails: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  branchDetailItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  branchDetailText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  changeBranchButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    paddingVertical: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
    paddingTop: spacing.md,
  },
  changeBranchText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.brand.primary,
  },
  paymentMethodsContainer: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.lg,
  },
  paymentMethod: {
    borderWidth: 2,
    borderColor: colors.gray[200],
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    position: 'relative',
  },
  paymentMethodSelected: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary + '10',
  },
  paymentMethodDisabled: {
    opacity: 0.5,
  },
  paymentMethodContent: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: spacing.md,
  },
  paymentMethodIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.gray[100],
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.md,
  },
  paymentMethodIconText: {
    fontSize: 24,
  },
  paymentMethodInfo: {
    flex: 1,
  },
  paymentMethodName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  paymentMethodNameDisabled: {
    color: colors.gray[500],
  },
  paymentMethodDescription: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  paymentMethodDescriptionDisabled: {
    color: colors.gray[400],
  },
  paymentMethodSelector: {
    marginLeft: spacing.md,
  },
  radioSelected: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: colors.brand.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  radioSelectedInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: colors.brand.primary,
  },
  radioUnselected: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: colors.gray[300],
  },
  unavailableOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(255, 255, 255, 0.8)',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
  },
  unavailableText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
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
  summaryItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  summaryItemName: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    flex: 1,
  },
  summaryItemPrice: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
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
  securityNotice: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.blue[50],
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.md,
    marginBottom: spacing.lg,
  },
  securityIcon: {
    fontSize: 20,
    marginRight: spacing.sm,
  },
  securityText: {
    flex: 1,
    fontSize: fontSizes.sm,
    color: colors.blue[700],
    lineHeight: fontSizes.sm * 1.4,
  },
  actionButtons: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl,
  },
  paymentButton: {
    marginBottom: spacing.sm,
  },
  backButton: {
    backgroundColor: colors.gray[100],
    paddingVertical: spacing.sm,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  backButtonText: {
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
  emptyTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.lg,
  },
  backToCartButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
  },
  backToCartButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
});
