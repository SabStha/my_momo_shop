import React from 'react';
import { Alert } from 'react-native';
import { OrderDeliveredModal } from './modals/OrderDeliveredModal';
import WriteReviewModal from './reviews/WriteReviewModal';
import { useOrderDeliveredNotification } from '../hooks/useOrderDeliveredNotification';
import { client } from '../api/client';

export function OrderDeliveredHandler() {
  const deliveredNotification = useOrderDeliveredNotification();

  console.log('🎯 OrderDeliveredHandler rendered:', {
    showDeliveredModal: deliveredNotification.showDeliveredModal,
    orderNumber: deliveredNotification.deliveredOrderNumber,
  });

  // Handle review submission
  const handleReviewSubmit = async (review: any) => {
    try {
      console.log('⭐ Submitting review:', {
        rating: review.rating,
        comment: review.comment,
        orderItem: review.orderItem,
      });

      const response = await client.post('/reviews', {
        rating: review.rating,
        comment: review.comment,
        orderItem: review.orderItem,
      });

      console.log('✅ Review submission response:', response.data);

      if (response.data.success) {
        Alert.alert(
          'Thank You! ⭐',
          'Your review has been submitted successfully!',
          [{ text: 'OK' }]
        );
      } else {
        Alert.alert('Error', response.data.message || 'Failed to submit review');
      }
    } catch (error: any) {
      console.error('❌ Failed to submit review:', error);
      console.error('❌ Error details:', {
        message: error.message,
        status: error.status,
        code: error.code,
      });
      Alert.alert('Error', 'Failed to submit review. Please try again.');
    }
  };

  return (
    <>
      {/* Order Delivered Modal */}
      <OrderDeliveredModal
        visible={deliveredNotification.showDeliveredModal}
        orderNumber={deliveredNotification.deliveredOrderNumber}
        orderId={deliveredNotification.deliveredOrderId}
        onClose={deliveredNotification.handleCloseDeliveredModal}
        onWriteReview={deliveredNotification.handleOpenReviewModal}
      />
      
      {/* Write Review Modal */}
      <WriteReviewModal
        visible={deliveredNotification.showReviewModal}
        onClose={deliveredNotification.handleCloseReviewModal}
        onSubmit={handleReviewSubmit}
      />
    </>
  );
}

