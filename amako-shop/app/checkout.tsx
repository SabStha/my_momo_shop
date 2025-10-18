import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  StatusBar,
} from 'react-native';
import { router, useSegments } from 'expo-router';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { useCartSyncStore } from '../src/state/cart-sync';
import { TextInput, Button } from '../src/ui';
import { Money } from '../src/types';
import { sumMoney, multiplyMoney } from '../src/utils/price';
import { ScreenWithBottomNav } from '../src/components';
import { useUserProfile, useUpdateUserProfile } from '../src/api/user-hooks';
import { useSession } from '../src/session/SessionProvider';
import { useCallback } from 'react';

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
  const { items, subtotal, itemCount } = useCartSyncStore();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { user } = useSession();
  const { data: userProfile, isLoading: profileLoading, error: profileError } = useUserProfile();
  const updateProfile = useUpdateUserProfile();
  const segments = useSegments();
  
  // GPS Location state
  const [gpsLocation, setGpsLocation] = useState<{
    latitude: number;
    longitude: number;
    address?: string;
  } | null>(null);
  const [isLoadingLocation, setIsLoadingLocation] = useState(false);
  
  // Log profile loading status
  useEffect(() => {
    if (profileError) {
      console.log('⚠️ CHECKOUT: Profile loading failed, will skip auto-fill:', profileError);
    }
  }, [profileError]);

  const {
    control,
    handleSubmit,
    formState: { errors, isValid },
    setValue,
    reset,
    trigger,
  } = useForm<CheckoutFormData>({
    resolver: zodResolver(checkoutSchema),
    mode: 'onBlur',
    defaultValues: {
      name: '',
      email: '',
      phone: '',
      address: '',
      city: '',
      deliveryInstructions: '',
    },
  });

  // Auto-fill form from user profile
  useEffect(() => {
    if (userProfile) {
      console.log('📝 Auto-filling checkout form with user data:', userProfile);
      
      // Auto-fill from session user (name and email)
      if (user?.name) {
        setValue('name', user.name, { shouldValidate: true });
      }
      if (user?.email) {
        setValue('email', user.email, { shouldValidate: true });
      }
      if (user?.phone || userProfile.phone) {
        setValue('phone', user.phone || userProfile.phone || '', { shouldValidate: true });
      }
      
      // Auto-fill delivery address from profile
      if (userProfile.city) {
        setValue('city', userProfile.city, { shouldValidate: true });
      }
      if (userProfile.area_locality) {
        setValue('address', userProfile.area_locality, { shouldValidate: true });
      }
      if (userProfile.detailed_directions) {
        setValue('deliveryInstructions', userProfile.detailed_directions, { shouldValidate: true });
      }
      
      // Trigger validation for the entire form after auto-fill
      trigger();
    }
  }, [userProfile, user, setValue, trigger]);

  // Silently redirect if cart is empty (no alert popups) - but only if checkout is the active screen
  useEffect(() => {
    const currentRoute = segments[0] as string;
    const isCheckoutActive = currentRoute === 'checkout' || segments.some((seg) => seg === 'checkout');
    if (items.length === 0 && isCheckoutActive) {
      console.log('🚨 CHECKOUT: Cart is empty, silently redirecting to cart...');
      router.replace('/cart');
    } else if (items.length === 0) {
      console.log('🚨 CHECKOUT: Cart is empty but not on checkout screen, skipping redirect');
    }
  }, [items.length, segments]);

  // Get GPS Location
  const handleGetLocation = async () => {
    setIsLoadingLocation(true);
    try {
      // Request permission
      const { status } = await Location.requestForegroundPermissionsAsync();
      
      if (status !== 'granted') {
        Alert.alert(
          'Permission Denied',
          'Please enable location permissions in your device settings to use GPS location for delivery.',
          [{ text: 'OK' }]
        );
        setIsLoadingLocation(false);
        return;
      }

      // Get current location
      const location = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.High,
      });

      console.log('📍 GPS Location obtained:', location.coords);

      // Try to reverse geocode to get address
      try {
        const reverseGeocode = await Location.reverseGeocodeAsync({
          latitude: location.coords.latitude,
          longitude: location.coords.longitude,
        });

        if (reverseGeocode && reverseGeocode.length > 0) {
          const addressData = reverseGeocode[0];
          const addressString = [
            addressData.street,
            addressData.district,
            addressData.city,
            addressData.region,
          ].filter(Boolean).join(', ');

          console.log('📍 Reverse geocoded address:', addressString);

          setGpsLocation({
            latitude: location.coords.latitude,
            longitude: location.coords.longitude,
            address: addressString,
          });

          // Auto-fill address fields if available
          if (addressData.city) {
            setValue('city', addressData.city, { shouldValidate: true });
          }
          if (addressString) {
            setValue('address', addressString, { shouldValidate: true });
          }

          Alert.alert(
            'Location Captured',
            `GPS coordinates saved! Your location will be shared with the delivery driver.\n\nCoordinates: ${location.coords.latitude.toFixed(6)}, ${location.coords.longitude.toFixed(6)}`,
            [{ text: 'OK' }]
          );
        } else {
          // No address found, just save coordinates
          setGpsLocation({
            latitude: location.coords.latitude,
            longitude: location.coords.longitude,
          });

          Alert.alert(
            'Location Captured',
            `GPS coordinates saved successfully!\n\nLat: ${location.coords.latitude.toFixed(6)}\nLng: ${location.coords.longitude.toFixed(6)}\n\nPlease enter your address manually below.`,
            [{ text: 'OK' }]
          );
        }
      } catch (geocodeError) {
        console.log('⚠️ Reverse geocoding failed:', geocodeError);
        
        // Save coordinates anyway
        setGpsLocation({
          latitude: location.coords.latitude,
          longitude: location.coords.longitude,
        });

        Alert.alert(
          'Location Captured',
          `GPS coordinates saved!\n\nLat: ${location.coords.latitude.toFixed(6)}\nLng: ${location.coords.longitude.toFixed(6)}\n\nPlease enter your address manually.`,
          [{ text: 'OK' }]
        );
      }
    } catch (error) {
      console.error('❌ Error getting location:', error);
      Alert.alert(
        'Location Error',
        'Unable to get your location. Please make sure GPS is enabled and try again, or enter your address manually.',
        [{ text: 'OK' }]
      );
    } finally {
      setIsLoadingLocation(false);
    }
  };

  const calculateTax = (subtotal: Money): Money => {
    const taxRate = 13; // 13% tax rate
    return { currency: 'NPR', amount: subtotal.amount * (taxRate / 100) };
  };

  const tax = calculateTax(subtotal);
  const total: Money = { currency: 'NPR', amount: subtotal.amount + tax.amount };

  const onSubmit = async (data: CheckoutFormData) => {
    if (items.length === 0) {
      console.log('🚨 CHECKOUT: Empty cart in onSubmit, redirecting...');
      router.replace('/cart');
      return;
    }

    setIsSubmitting(true);
    
    try {
      // Save delivery details to user profile for future orders
      console.log('📝 Saving delivery details to user profile...');
      try {
        await updateProfile.mutateAsync({
          name: data.name,
          email: data.email,
          phone: data.phone,
          city: data.city,
          address: data.address,
          deliveryInstructions: data.deliveryInstructions,
        });
        console.log('✅ Delivery details saved successfully');
      } catch (profileError) {
        console.error('⚠️ Failed to save profile, continuing with checkout:', profileError);
        // Don't block checkout if profile save fails
      }
      
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
      
      // Log GPS location if available
      if (gpsLocation) {
        console.log('📍 GPS Location for delivery:', gpsLocation);
      }
      
      // Navigate to branch selection page with GPS data if available
      router.push({
        pathname: '/branch-selection',
        params: gpsLocation ? {
          latitude: gpsLocation.latitude.toString(),
          longitude: gpsLocation.longitude.toString(),
        } : {},
      });
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
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
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
            
            {/* GPS Location Button */}
            <TouchableOpacity
              style={styles.gpsButton}
              onPress={handleGetLocation}
              disabled={isLoadingLocation}
              activeOpacity={0.7}
            >
              <View style={styles.gpsButtonContent}>
                {isLoadingLocation ? (
                  <ActivityIndicator size="small" color={colors.white} />
                ) : (
                  <Ionicons name="location" size={24} color={colors.white} />
                )}
                <View style={styles.gpsButtonTextContainer}>
                  <Text style={styles.gpsButtonText}>
                    {isLoadingLocation ? 'Getting Location...' : 'Use My Current Location (GPS)'}
                  </Text>
                  <Text style={styles.gpsButtonSubtext}>
                    Share GPS for accurate delivery
                  </Text>
                </View>
              </View>
            </TouchableOpacity>

            {/* GPS Location Display */}
            {gpsLocation && (
              <View style={styles.gpsLocationDisplay}>
                <View style={styles.gpsLocationHeader}>
                  <Ionicons name="checkmark-circle" size={20} color={colors.success[500]} />
                  <Text style={styles.gpsLocationTitle}>GPS Location Captured</Text>
                </View>
                <View style={styles.gpsCoordinates}>
                  <Text style={styles.gpsCoordinateText}>
                    📍 Lat: {gpsLocation.latitude.toFixed(6)}
                  </Text>
                  <Text style={styles.gpsCoordinateText}>
                    📍 Lng: {gpsLocation.longitude.toFixed(6)}
                  </Text>
                </View>
                {gpsLocation.address && (
                  <Text style={styles.gpsAddressText}>
                    📌 {gpsLocation.address}
                  </Text>
                )}
                <Text style={styles.gpsInfoText}>
                  ✅ This location will be shared with your delivery driver
                </Text>
              </View>
            )}
            
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
  // GPS Location Styles
  gpsButton: {
    backgroundColor: colors.brand.primary,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  gpsButtonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: spacing.md,
    gap: spacing.md,
  },
  gpsButtonTextContainer: {
    flex: 1,
  },
  gpsButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
    marginBottom: 2,
  },
  gpsButtonSubtext: {
    fontSize: fontSizes.xs,
    color: colors.white,
    opacity: 0.9,
  },
  gpsLocationDisplay: {
    backgroundColor: colors.success[50],
    borderRadius: radius.lg,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: colors.success[200],
  },
  gpsLocationHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
    gap: spacing.xs,
  },
  gpsLocationTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.success[700],
  },
  gpsCoordinates: {
    backgroundColor: colors.white,
    borderRadius: radius.md,
    padding: spacing.sm,
    marginBottom: spacing.sm,
  },
  gpsCoordinateText: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    marginBottom: 2,
  },
  gpsAddressText: {
    fontSize: fontSizes.sm,
    color: colors.success[800],
    marginBottom: spacing.xs,
    lineHeight: 20,
  },
  gpsInfoText: {
    fontSize: fontSizes.xs,
    color: colors.success[600],
    fontStyle: 'italic',
  },
});