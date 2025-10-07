import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  Dimensions,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';

interface OrderSuccessModalProps {
  visible: boolean;
  orderNumber: string;
  totalAmount: number;
  onViewOrder: () => void;
  onClose: () => void;
}

export default function OrderSuccessModal({
  visible,
  orderNumber,
  totalAmount,
  onViewOrder,
  onClose,
}: OrderSuccessModalProps) {
  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.modalContainer}>
          {/* Success Icon */}
          <View style={styles.iconContainer}>
            <View style={styles.iconBackground}>
              <Ionicons name="checkmark-circle" size={60} color={colors.success[500]} />
            </View>
          </View>

          {/* Success Message */}
          <Text style={styles.title}>Order Placed Successfully! 🎉</Text>
          <Text style={styles.subtitle}>
            Your order has been confirmed and is being prepared
          </Text>

          {/* Order Details */}
          <View style={styles.orderDetails}>
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Order Number:</Text>
              <Text style={styles.detailValue}>#{orderNumber}</Text>
            </View>
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Total Amount:</Text>
              <Text style={styles.detailValue}>Rs. {totalAmount.toFixed(2)}</Text>
            </View>
          </View>

          {/* Action Buttons */}
          <View style={styles.buttonContainer}>
            <TouchableOpacity
              style={styles.primaryButton}
              onPress={onViewOrder}
              activeOpacity={0.8}
            >
              <Ionicons name="eye-outline" size={20} color={colors.white} />
              <Text style={styles.primaryButtonText}>View Order Details</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.secondaryButton}
              onPress={onClose}
              activeOpacity={0.8}
            >
              <Text style={styles.secondaryButtonText}>Continue Shopping</Text>
            </TouchableOpacity>
          </View>

          {/* Estimated Delivery */}
          <View style={styles.deliveryInfo}>
            <Ionicons name="time-outline" size={16} color={colors.gray[600]} />
            <Text style={styles.deliveryText}>
              Estimated delivery: 25-35 minutes
            </Text>
          </View>
        </View>
      </View>
    </Modal>
  );
}

const { width } = Dimensions.get('window');

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  modalContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.xl,
    padding: spacing.xl,
    width: width * 0.9,
    maxWidth: 400,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.25,
    shadowRadius: 20,
    elevation: 10,
  },
  iconContainer: {
    marginBottom: spacing.lg,
  },
  iconBackground: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: colors.success[50],
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: colors.success[200],
  },
  title: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.xl,
    lineHeight: 22,
  },
  orderDetails: {
    width: '100%',
    backgroundColor: colors.gray[50],
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.xl,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  detailLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    fontWeight: fontWeights.medium,
  },
  detailValue: {
    fontSize: fontSizes.md,
    color: colors.gray[900],
    fontWeight: fontWeights.semibold,
  },
  buttonContainer: {
    width: '100%',
    gap: spacing.md,
    marginBottom: spacing.lg,
  },
  primaryButton: {
    backgroundColor: colors.brand.primary,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    gap: spacing.sm,
  },
  primaryButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
  secondaryButton: {
    backgroundColor: colors.gray[100],
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  secondaryButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  deliveryInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  deliveryText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    fontStyle: 'italic',
  },
});
