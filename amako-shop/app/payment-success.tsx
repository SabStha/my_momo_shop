import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Linking,
} from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { Button } from '../src/ui';
import { ScreenWithBottomNav } from '../src/components';
import { Ionicons } from '@expo/vector-icons';

export default function PaymentSuccessScreen() {
  const { order, amount, method } = useLocalSearchParams<{
    order?: string;
    amount?: string;
    method?: string;
  }>();

  const [orderNumber] = useState(order || `#${Date.now()}`);
  const [paymentAmount] = useState(amount || '0.00');
  const [paymentMethod] = useState(method || 'cash');

  const getPaymentMethodName = (method: string) => {
    switch (method) {
      case 'cash': return 'Cash on Delivery';
      case 'esewa': return 'eSewa';
      case 'khalti': return 'Khalti';
      case 'fonepay': return 'FonePay';
      case 'card': return 'Credit/Debit Card';
      default: return 'Unknown';
    }
  };

  const handleCallRestaurant = () => {
    const phoneNumber = '+977-1-1234567'; // Replace with actual restaurant phone
    Linking.openURL(`tel:${phoneNumber}`);
  };

  const handleViewMenu = () => {
    router.push('/(tabs)/menu');
  };

  const handleGoHome = () => {
    router.push('/(tabs)/home');
  };

  const handleViewOrderDetails = () => {
    // Navigate to orders page (replace to clear navigation stack)
    router.replace('/orders');
  };

  return (
    <ScreenWithBottomNav>
      <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.push('/(tabs)/home')}
        >
          <Ionicons name="home" size={24} color={colors.gray[700]} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Order Confirmed</Text>
        <View style={styles.headerIcon}>
          <Ionicons name="checkmark-done-circle" size={28} color="#10B981" />
        </View>
      </View>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Success Header */}
        <View style={styles.successHeader}>
          <View style={styles.successIcon}>
            <Text style={styles.successIconText}>✅</Text>
          </View>
          <Text style={styles.successTitle}>Payment Successful! 🎉</Text>
          <Text style={styles.successSubtitle}>
            Thank you for your order. We've received your payment and will start preparing your delicious momo right away!
          </Text>
        </View>

        {/* Order Details */}
        <View style={styles.orderDetails}>
          <Text style={styles.orderDetailsTitle}>Order Details</Text>
          
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Order Number:</Text>
            <Text style={styles.detailValue}>{orderNumber}</Text>
          </View>
          
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Payment Amount:</Text>
            <Text style={styles.detailValue}>Rs.{parseFloat(paymentAmount).toFixed(2)}</Text>
          </View>
          
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Payment Method:</Text>
            <Text style={styles.detailValue}>{getPaymentMethodName(paymentMethod)}</Text>
          </View>
          
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Estimated Delivery:</Text>
            <Text style={styles.detailValue}>30-45 minutes</Text>
          </View>
        </View>

        {/* Next Steps */}
        <View style={styles.nextSteps}>
          <Text style={styles.nextStepsTitle}>What happens next?</Text>
          
          <View style={styles.stepItem}>
            <View style={styles.stepIcon}>
              <Text style={styles.stepIconText}>📱</Text>
            </View>
            <Text style={styles.stepText}>We'll send you an SMS confirmation</Text>
          </View>
          
          <View style={styles.stepItem}>
            <View style={styles.stepIcon}>
              <Text style={styles.stepIconText}>👨‍🍳</Text>
            </View>
            <Text style={styles.stepText}>Our chefs will start preparing your order</Text>
          </View>
          
          <View style={styles.stepItem}>
            <View style={styles.stepIcon}>
              <Text style={styles.stepIconText}>🚚</Text>
            </View>
            <Text style={styles.stepText}>Your order will be delivered to your address</Text>
          </View>
        </View>

        {/* Contact Information */}
        <View style={styles.contactInfo}>
          <Text style={styles.contactTitle}>Need Help?</Text>
          <Text style={styles.contactSubtitle}>
            If you have any questions about your order, feel free to contact us:
          </Text>
          
          <TouchableOpacity style={styles.contactButton} onPress={handleCallRestaurant}>
            <Text style={styles.contactButtonIcon}>📞</Text>
            <Text style={styles.contactButtonText}>Call Restaurant</Text>
          </TouchableOpacity>
        </View>

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <TouchableOpacity 
            style={styles.viewOrderButton} 
            onPress={handleViewOrderDetails}
          >
            <Ionicons name="receipt-outline" size={20} color="#FFFFFF" />
            <Text style={styles.viewOrderButtonText}>View Order Details</Text>
          </TouchableOpacity>

          <Button
            title="Order Again"
            onPress={handleViewMenu}
            variant="solid"
            size="lg"
            style={styles.primaryButton}
          />
          
          <TouchableOpacity style={styles.secondaryButton} onPress={handleGoHome}>
            <Text style={styles.secondaryButtonText}>Back to Home</Text>
          </TouchableOpacity>
        </View>

        {/* Thank You Message */}
        <View style={styles.thankYou}>
          <Text style={styles.thankYouText}>
            Thank you for choosing us! We appreciate your business and look forward to serving you again. 🥟
          </Text>
        </View>
      </ScrollView>
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
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.md,
    paddingBottom: spacing.lg,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  backButton: {
    padding: spacing.sm,
    borderRadius: radius.sm,
    backgroundColor: colors.gray[100],
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    flex: 1,
  },
  headerIcon: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  successHeader: {
    alignItems: 'center',
    paddingVertical: spacing.xl,
    paddingHorizontal: spacing.lg,
    backgroundColor: colors.white,
    marginBottom: spacing.lg,
  },
  successIcon: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.green[100],
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.lg,
  },
  successIconText: {
    fontSize: 40,
  },
  successTitle: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    marginBottom: spacing.md,
  },
  successSubtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: fontSizes.md * 1.5,
  },
  orderDetails: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  orderDetailsTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.lg,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  detailLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  detailValue: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
  },
  nextSteps: {
    backgroundColor: colors.blue[50],
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  nextStepsTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.blue[900],
    marginBottom: spacing.lg,
  },
  stepItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  stepIcon: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.blue[100],
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.md,
  },
  stepIconText: {
    fontSize: 16,
  },
  stepText: {
    flex: 1,
    fontSize: fontSizes.sm,
    color: colors.blue[800],
    lineHeight: fontSizes.sm * 1.4,
  },
  contactInfo: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  contactTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  contactSubtitle: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.lg,
    lineHeight: fontSizes.sm * 1.4,
  },
  contactButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.green[50],
    padding: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.green[200],
  },
  contactButtonIcon: {
    fontSize: 20,
    marginRight: spacing.sm,
  },
  contactButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.green[700],
  },
  actionButtons: {
    paddingHorizontal: spacing.lg,
    marginBottom: spacing.lg,
    gap: spacing.sm,
  },
  viewOrderButton: {
    backgroundColor: '#6E0D25',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: '#6E0D25',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
    marginBottom: spacing.sm,
  },
  viewOrderButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#FFFFFF',
  },
  primaryButton: {
    marginBottom: spacing.sm,
  },
  secondaryButton: {
    backgroundColor: colors.gray[100],
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  secondaryButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  thankYou: {
    backgroundColor: colors.brand.primary + '10',
    marginHorizontal: spacing.lg,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.xl,
  },
  thankYouText: {
    fontSize: fontSizes.md,
    color: colors.brand.primary,
    textAlign: 'center',
    lineHeight: fontSizes.md * 1.5,
    fontWeight: fontWeights.medium,
  },
});
