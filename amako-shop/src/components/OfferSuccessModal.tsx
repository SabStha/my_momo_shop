import React, { useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  Animated,
  Dimensions,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../ui/tokens';

const { width } = Dimensions.get('window');

interface OfferSuccessModalProps {
  visible: boolean;
  onClose: () => void;
  type: 'claimed' | 'applied';
  offerTitle: string;
  discount: number;
  savingsAmount?: number;
  onViewOffers?: () => void;
  onUseNow?: () => void;
}

export default function OfferSuccessModal({
  visible,
  onClose,
  type,
  offerTitle,
  discount,
  savingsAmount,
  onViewOffers,
  onUseNow,
}: OfferSuccessModalProps) {
  const scaleAnim = useRef(new Animated.Value(0)).current;
  const checkmarkScale = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    if (visible) {
      // Animate modal in
      Animated.spring(scaleAnim, {
        toValue: 1,
        tension: 50,
        friction: 7,
        useNativeDriver: true,
      }).start();

      // Animate checkmark with delay
      setTimeout(() => {
        Animated.spring(checkmarkScale, {
          toValue: 1,
          tension: 100,
          friction: 5,
          useNativeDriver: true,
        }).start();
      }, 200);
    } else {
      scaleAnim.setValue(0);
      checkmarkScale.setValue(0);
    }
  }, [visible]);

  const handleClose = () => {
    Animated.timing(scaleAnim, {
      toValue: 0,
      duration: 200,
      useNativeDriver: true,
    }).start(() => {
      onClose();
    });
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={handleClose}
    >
      <View style={styles.overlay}>
        <TouchableOpacity
          style={styles.overlayTouchable}
          activeOpacity={1}
          onPress={handleClose}
        />
        
        <Animated.View
          style={[
            styles.container,
            {
              transform: [{ scale: scaleAnim }],
            },
          ]}
        >
          {/* Success Icon */}
          <Animated.View
            style={[
              styles.iconContainer,
              {
                transform: [{ scale: checkmarkScale }],
              },
            ]}
          >
            <View style={styles.iconCircle}>
              <Ionicons name="checkmark" size={48} color={colors.white} />
            </View>
          </Animated.View>

          {/* Title */}
          <Text style={styles.title}>
            {type === 'claimed' ? '🎉 Offer Claimed!' : '✨ Offer Applied!'}
          </Text>

          {/* Offer Details */}
          <View style={styles.offerDetails}>
            <Text style={styles.offerTitle}>{offerTitle}</Text>
            <View style={styles.discountBadge}>
              <Text style={styles.discountText}>{discount}% OFF</Text>
            </View>
          </View>

          {/* Savings Amount */}
          {savingsAmount !== undefined && savingsAmount > 0 && (
            <View style={styles.savingsContainer}>
              <Text style={styles.savingsLabel}>You're saving</Text>
              <Text style={styles.savingsAmount}>Rs. {savingsAmount.toFixed(2)}</Text>
            </View>
          )}

          {/* Message */}
          <Text style={styles.message}>
            {type === 'claimed'
              ? 'Your offer has been added to My Offers. Use it on your next order!'
              : 'Your discount has been applied to this order.'}
          </Text>

          {/* Actions */}
          <View style={styles.actions}>
            {type === 'claimed' && onViewOffers && (
              <TouchableOpacity
                style={styles.primaryButton}
                onPress={() => {
                  handleClose();
                  onViewOffers();
                }}
              >
                <Ionicons name="gift" size={20} color={colors.white} />
                <Text style={styles.primaryButtonText}>View My Offers</Text>
              </TouchableOpacity>
            )}
            
            {type === 'claimed' && onUseNow && (
              <TouchableOpacity
                style={styles.secondaryButton}
                onPress={() => {
                  handleClose();
                  onUseNow();
                }}
              >
                <Text style={styles.secondaryButtonText}>Use Now</Text>
              </TouchableOpacity>
            )}
            
            {type === 'applied' && (
              <TouchableOpacity
                style={styles.primaryButton}
                onPress={handleClose}
              >
                <Text style={styles.primaryButtonText}>Continue Shopping</Text>
              </TouchableOpacity>
            )}
          </View>

          {/* Close Button */}
          <TouchableOpacity style={styles.closeButton} onPress={handleClose}>
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
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  overlayTouchable: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  container: {
    backgroundColor: colors.white,
    borderRadius: radius.xl,
    padding: spacing.xl,
    width: width - 48,
    maxWidth: 400,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.3,
    shadowRadius: 20,
    elevation: 10,
  },
  iconContainer: {
    marginBottom: spacing.lg,
  },
  iconCircle: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: colors.green[500],
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: colors.green[500],
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 5,
  },
  title: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.md,
    textAlign: 'center',
  },
  offerDetails: {
    alignItems: 'center',
    marginBottom: spacing.lg,
    width: '100%',
  },
  offerTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  discountBadge: {
    backgroundColor: colors.orange[50],
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    borderWidth: 2,
    borderColor: colors.orange[500],
  },
  discountText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.orange[600],
  },
  savingsContainer: {
    backgroundColor: colors.green[50],
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    width: '100%',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.green[200],
  },
  savingsLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  savingsAmount: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold,
    color: colors.green[600],
  },
  message: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: spacing.lg,
  },
  actions: {
    width: '100%',
    gap: spacing.sm,
  },
  primaryButton: {
    backgroundColor: colors.brand.primary,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: spacing.sm,
    shadowColor: colors.brand.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  primaryButtonText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
  },
  secondaryButton: {
    backgroundColor: colors.white,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    borderWidth: 2,
    borderColor: colors.brand.primary,
    alignItems: 'center',
  },
  secondaryButtonText: {
    color: colors.brand.primary,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  closeButton: {
    position: 'absolute',
    top: spacing.md,
    right: spacing.md,
    padding: spacing.sm,
  },
});

