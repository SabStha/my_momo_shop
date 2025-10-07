import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  Image,
} from 'react-native';
import { router } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { useCartStore } from '../src/state/cart';
import { useCreateOrder } from '../src/state/orders';
import { Button } from '../src/ui';
import { Money } from '../src/types';
import { sumMoney, multiplyMoney } from '../src/utils/price';
import { ScreenWithBottomNav, OrderSuccessModal } from '../src/components';

type PaymentMethod = 'cash' | 'esewa' | 'khalti' | 'fonepay' | 'card';

export default function PaymentScreen() {
  const { items, subtotal, itemCount, clearCart } = useCartStore();
  const createOrder = useCreateOrder();
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<PaymentMethod | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [orderNumber, setOrderNumber] = useState('');
  const [createdOrderId, setCreatedOrderId] = useState('');

  useEffect(() => {
    if (items.length === 0) {
      Alert.alert('Empty Cart', 'Your cart is empty. Please add some items first.', [
        { text: 'OK', onPress: () => router.push('/cart') },
      ]);
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
      Alert.alert('Empty Cart', 'Your cart is empty. Please add some items first.');
      return;
    }

    setIsProcessing(true);

    try {
      // Simulate payment processing
      await new Promise(resolve => setTimeout(resolve, 2000));

      // Generate order number
      const newOrderNumber = `#${Date.now()}`;

      // Create order in the orders store
      const orderId = createOrder({
        items: items,
        subtotal: subtotal,
        deliveryFee: { currency: 'NPR', amount: 0 }, // Free delivery
        tax: tax,
        total: total,
        status: 'pending',
        paymentMethod: selectedPaymentMethod,
        deliveryAddress: 'Your delivery address', // This should come from checkout form
        notes: 'Special delivery instructions', // This should come from checkout form
      });

      // Set order number and show success modal
      setOrderNumber(newOrderNumber);
      setCreatedOrderId(orderId);
      setShowSuccessModal(true);
      
    } catch (error) {
      Alert.alert('Payment Failed', 'Something went wrong with your payment. Please try again.');
    } finally {
      setIsProcessing(false);
    }
  };

  const handleViewOrder = () => {
    setShowSuccessModal(false);
    // Navigate to the order details page using the actual order ID
    router.push(`/order/${createdOrderId}`);
    // Clear cart after navigation to avoid empty cart modal
    setTimeout(() => {
      clearCart();
    }, 1000);
  };

  const handleCloseModal = () => {
    setShowSuccessModal(false);
    clearCart();
    router.push('/(tabs)/home');
  };

  if (items.length === 0) {
    return (
      <ScreenWithBottomNav>
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
    paddingVertical: spacing.md,
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
