import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { ScreenWithBottomNav, OrderDeliveredModal, WriteReviewModal } from '../src/components';
import { colors, spacing, fontSizes, fontWeights } from '../src/ui/tokens';
import { Alert } from 'react-native';

export default function TestDeliveredPopupScreen() {
  const [showDeliveredModal, setShowDeliveredModal] = useState(false);
  const [showReviewModal, setShowReviewModal] = useState(false);

  const handleOpenReviewModal = () => {
    setShowDeliveredModal(false);
    setTimeout(() => {
      setShowReviewModal(true);
    }, 300);
  };

  const handleReviewSubmit = (review: any) => {
    console.log('Review submitted:', review);
    Alert.alert('Success', 'Review would be submitted to backend');
    setShowReviewModal(false);
  };

  return (
    <ScreenWithBottomNav>
      <View style={styles.container}>
        <Text style={styles.title}>Test Delivered Popup</Text>
        <Text style={styles.subtitle}>Tap button to test the popup</Text>

        <TouchableOpacity
          style={styles.testButton}
          onPress={() => setShowDeliveredModal(true)}
        >
          <Text style={styles.buttonText}>🎉 Show Delivered Popup</Text>
        </TouchableOpacity>
      </View>

      <OrderDeliveredModal
        visible={showDeliveredModal}
        orderNumber="ORD-TEST-12345"
        orderId={999}
        onClose={() => setShowDeliveredModal(false)}
        onWriteReview={handleOpenReviewModal}
      />

      <WriteReviewModal
        visible={showReviewModal}
        onClose={() => setShowReviewModal(false)}
        onSubmit={handleReviewSubmit}
      />
    </ScreenWithBottomNav>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.xl,
    backgroundColor: colors.gray[50],
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    marginBottom: spacing.xl,
  },
  testButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.lg,
    borderRadius: 12,
  },
  buttonText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
});







