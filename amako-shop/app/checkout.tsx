import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { router } from 'expo-router';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { useCartStore } from '../src/state/cart';
import { TextInput, Button } from '../src/ui';
import { Money } from '../src/types';
import { sumMoney, multiplyMoney } from '../src/utils/price';
import { ScreenWithBottomNav } from '../src/components';

// Validation schema
const checkoutSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Please enter a valid email address'),
  phone: z.string().min(10, 'Phone number must be at least 10 digits'),
  address: z.string().min(10, 'Please enter a complete address'),
  city: z.string().min(2, 'Please enter a valid city'),
  deliveryInstructions: z.string().optional(),
});

type CheckoutFormData = z.infer<typeof checkoutSchema>;

export default function CheckoutScreen() {
  const { items, subtotal, itemCount } = useCartStore();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    control,
    handleSubmit,
    formState: { errors, isValid },
  } = useForm<CheckoutFormData>({
    resolver: zodResolver(checkoutSchema),
    mode: 'onBlur',
  });

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

  const onSubmit = async (data: CheckoutFormData) => {
    if (items.length === 0) {
      Alert.alert('Empty Cart', 'Your cart is empty. Please add some items first.');
      return;
    }

    setIsSubmitting(true);
    
    try {
      // Store checkout data for payment page
      const checkoutData = {
        ...data,
        items: items,
        subtotal: subtotal,
        tax: tax,
        total: total,
        itemCount: itemCount,
      };
      
      // In a real app, you would save this to storage or send to API
      console.log('Checkout data:', checkoutData);
      
      // Navigate to payment page
      router.push('/payment');
    } catch (error) {
      Alert.alert('Error', 'Something went wrong. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (items.length === 0) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.container}>
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Checkout</Text>
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
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Complete Your Order</Text>
        <Text style={styles.headerSubtitle}>Step 2: Enter your delivery information</Text>
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
          <View style={styles.progressLine} />
          <View style={styles.progressStep}>
            <View style={styles.progressCircle}>
              <Text style={styles.progressNumberInactive}>3</Text>
            </View>
            <Text style={styles.progressLabelInactive}>Payment</Text>
          </View>
        </View>

        {/* Checkout Form */}
        <View style={styles.formContainer}>
          <View style={styles.formSection}>
            <Text style={styles.sectionTitle}>Personal Information</Text>
            
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Full Name *</Text>
              <Controller
                control={control}
                name="name"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Enter your full name"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    error={!!errors.name}
                    style={styles.input}
                  />
                )}
              />
              {errors.name && <Text style={styles.errorText}>{errors.name.message}</Text>}
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.label}>Email Address *</Text>
              <Controller
                control={control}
                name="email"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Enter your email address"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    keyboardType="email-address"
                    autoCapitalize="none"
                    error={!!errors.email}
                    style={styles.input}
                  />
                )}
              />
              {errors.email && <Text style={styles.errorText}>{errors.email.message}</Text>}
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.label}>Phone Number *</Text>
              <Controller
                control={control}
                name="phone"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Enter your phone number"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    keyboardType="phone-pad"
                    error={!!errors.phone}
                    style={styles.input}
                  />
                )}
              />
              {errors.phone && <Text style={styles.errorText}>{errors.phone.message}</Text>}
            </View>
          </View>

          <View style={styles.formSection}>
            <Text style={styles.sectionTitle}>Delivery Address</Text>
            
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Address *</Text>
              <Controller
                control={control}
                name="address"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Enter your complete address"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    multiline
                    numberOfLines={3}
                    error={!!errors.address}
                    style={[styles.input, styles.textArea]}
                  />
                )}
              />
              {errors.address && <Text style={styles.errorText}>{errors.address.message}</Text>}
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.label}>City *</Text>
              <Controller
                control={control}
                name="city"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Enter your city"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    error={!!errors.city}
                    style={styles.input}
                  />
                )}
              />
              {errors.city && <Text style={styles.errorText}>{errors.city.message}</Text>}
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.label}>Delivery Instructions (Optional)</Text>
              <Controller
                control={control}
                name="deliveryInstructions"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Any special delivery instructions?"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    multiline
                    numberOfLines={2}
                    style={[styles.input, styles.textArea]}
                  />
                )}
              />
            </View>
          </View>
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

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <Button
            title="Proceed to Payment"
            onPress={handleSubmit(onSubmit)}
            variant="solid"
            size="lg"
            disabled={!isValid || isSubmitting}
            loading={isSubmitting}
            style={styles.paymentButton}
          />
          
          <TouchableOpacity style={styles.backButton} onPress={() => router.back()}>
            <Text style={styles.backButtonText}>Back to Cart</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
      </KeyboardAvoidingView>
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
  progressLineActive: {
    backgroundColor: colors.brand.primary,
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
  formContainer: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  formSection: {
    marginBottom: spacing.xl,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginBottom: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
    paddingBottom: spacing.sm,
  },
  inputContainer: {
    marginBottom: spacing.lg,
  },
  label: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
    marginBottom: spacing.sm,
  },
  input: {
    marginBottom: 0,
  },
  textArea: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  errorText: {
    fontSize: fontSizes.sm,
    color: colors.error[500],
    marginTop: spacing.xs,
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