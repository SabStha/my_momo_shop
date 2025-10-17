import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  Animated,
  Dimensions,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';
import { router } from 'expo-router';

const { width } = Dimensions.get('window');

interface OrderDeliveredModalProps {
  visible: boolean;
  orderNumber: string;
  orderId: number;
  onClose: () => void;
  onWriteReview: () => void;
}

export function OrderDeliveredModal({
  visible,
  orderNumber,
  orderId,
  onClose,
  onWriteReview,
}: OrderDeliveredModalProps) {
  const [scaleAnim] = useState(new Animated.Value(0));

  React.useEffect(() => {
    console.log('🎨 OrderDeliveredModal visibility changed:', visible);
    if (visible) {
      console.log('✅ Showing OrderDeliveredModal with animation');
      console.log('📦 Order:', orderNumber, orderId);
      Animated.spring(scaleAnim, {
        toValue: 1,
        useNativeDriver: true,
        tension: 50,
        friction: 7,
      }).start();
    } else {
      scaleAnim.setValue(0);
    }
  }, [visible]);

  const handleWriteReview = () => {
    onClose();
    // Small delay to ensure modal closes before opening review
    setTimeout(() => {
      onWriteReview();
    }, 300);
  };

  const handleViewOrder = () => {
    onClose();
    setTimeout(() => {
      router.push(`/order/${orderId}`);
    }, 100);
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <TouchableOpacity
          style={styles.overlayTouch}
          activeOpacity={1}
          onPress={onClose}
        />
        
        <Animated.View
          style={[
            styles.modalContainer,
            {
              transform: [{ scale: scaleAnim }],
            },
          ]}
        >
          {/* Success Icon */}
          <View style={styles.iconContainer}>
            <View style={styles.iconCircle}>
              <Ionicons name="checkmark-circle" size={80} color={colors.success[500]} />
            </View>
          </View>

          {/* Header */}
          <View style={styles.header}>
            <Text style={styles.title}>Order Delivered! 🎉</Text>
            <Text style={styles.subtitle}>
              Your order has been successfully delivered
            </Text>
          </View>

          {/* Order Info */}
          <View style={styles.orderInfo}>
            <View style={styles.orderInfoRow}>
              <Text style={styles.orderInfoLabel}>Order Number</Text>
              <Text style={styles.orderInfoValue}>{orderNumber}</Text>
            </View>
          </View>

          {/* Enjoyment Message */}
          <View style={styles.messageContainer}>
            <Text style={styles.messageIcon}>🥟</Text>
            <Text style={styles.message}>
              We hope you enjoy your delicious momos!
            </Text>
          </View>

          {/* Review Prompt */}
          <View style={styles.reviewPrompt}>
            <View style={styles.starsRow}>
              {[1, 2, 3, 4, 5].map((star) => (
                <Ionicons
                  key={star}
                  name="star"
                  size={24}
                  color={colors.warning[400]}
                />
              ))}
            </View>
            <Text style={styles.reviewPromptText}>
              How was your experience?
            </Text>
            <Text style={styles.reviewPromptSubtext}>
              Your feedback helps us serve you better
            </Text>
          </View>

          {/* Action Buttons */}
          <View style={styles.actions}>
            <TouchableOpacity
              style={styles.reviewButton}
              onPress={handleWriteReview}
              activeOpacity={0.8}
            >
              <Ionicons name="star-outline" size={20} color={colors.white} />
              <Text style={styles.reviewButtonText}>Write a Review</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.viewOrderButton}
              onPress={handleViewOrder}
              activeOpacity={0.8}
            >
              <Ionicons name="receipt-outline" size={20} color={colors.brand.primary} />
              <Text style={styles.viewOrderButtonText}>View Order</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.closeButton}
              onPress={onClose}
              activeOpacity={0.8}
            >
              <Text style={styles.closeButtonText}>Close</Text>
            </TouchableOpacity>
          </View>

          {/* Close Icon */}
          <TouchableOpacity
            style={styles.closeIcon}
            onPress={onClose}
            activeOpacity={0.7}
          >
            <Ionicons name="close" size={24} color={colors.gray[400]} />
          </TouchableOpacity>
        </Animated.View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.lg,
  },
  overlayTouch: {
    ...StyleSheet.absoluteFillObject,
  },
  modalContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.xl,
    width: Math.min(width - spacing.xl * 2, 400),
    maxWidth: '100%',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.3,
    shadowRadius: 20,
    elevation: 10,
    overflow: 'hidden',
  },
  iconContainer: {
    alignItems: 'center',
    paddingTop: spacing.xl,
    paddingBottom: spacing.md,
  },
  iconCircle: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: colors.success[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
  header: {
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
    paddingBottom: spacing.lg,
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: fontSizes.md * 1.5,
  },
  orderInfo: {
    backgroundColor: colors.blue[50],
    marginHorizontal: spacing.lg,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.lg,
  },
  orderInfoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  orderInfoLabel: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.blue[700],
  },
  orderInfoValue: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.blue[900],
  },
  messageContainer: {
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
    marginBottom: spacing.lg,
  },
  messageIcon: {
    fontSize: 40,
    marginBottom: spacing.sm,
  },
  message: {
    fontSize: fontSizes.md,
    color: colors.gray[700],
    textAlign: 'center',
    lineHeight: fontSizes.md * 1.5,
  },
  reviewPrompt: {
    backgroundColor: colors.warning[50],
    marginHorizontal: spacing.lg,
    padding: spacing.lg,
    borderRadius: radius.lg,
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  starsRow: {
    flexDirection: 'row',
    marginBottom: spacing.sm,
  },
  reviewPromptText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  reviewPromptSubtext: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
  },
  actions: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl,
    gap: spacing.sm,
  },
  reviewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.brand.primary,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: colors.brand.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  reviewButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  viewOrderButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.white,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    gap: spacing.sm,
    borderWidth: 2,
    borderColor: colors.brand.primary,
  },
  viewOrderButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.brand.primary,
  },
  closeButton: {
    paddingVertical: spacing.sm,
    alignItems: 'center',
  },
  closeButtonText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[500],
  },
  closeIcon: {
    position: 'absolute',
    top: spacing.md,
    right: spacing.md,
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: colors.gray[100],
    alignItems: 'center',
    justifyContent: 'center',
  },
});

