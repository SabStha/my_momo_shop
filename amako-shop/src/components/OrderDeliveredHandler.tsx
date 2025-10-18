import React from 'react';
import { Alert } from 'react-native';
import { OrderDeliveredModal } from './modals/OrderDeliveredModal';
import WriteReviewModal from './reviews/WriteReviewModal';
import { useOrderDeliveredNotification } from '../hooks/useOrderDeliveredNotification';
import { client } from '../api/client';
import { useQueryClient } from '@tanstack/react-query';
import { useSession } from '../session/SessionProvider';

export function OrderDeliveredHandler() {
  const deliveredNotification = useOrderDeliveredNotification();
  const queryClient = useQueryClient();
  const { user } = useSession();

  console.log('🎯 OrderDeliveredHandler rendered:', {
    showDeliveredModal: deliveredNotification.showDeliveredModal,
    orderNumber: deliveredNotification.deliveredOrderNumber,
    userId: user?.id,
    userName: user?.name,
  });

  // Handle review submission
  const handleReviewSubmit = async (review: any) => {
    try {
      console.log('⭐ Submitting review from OrderDeliveredModal:', {
        rating: review.rating,
        comment: review.comment,
        orderItem: review.orderItem,
        orderId: deliveredNotification.deliveredOrderId,
        orderNumber: deliveredNotification.deliveredOrderNumber,
        userId: user?.id,
        userName: user?.name,
      });

      const response = await client.post('/reviews', {
        rating: review.rating,
        comment: review.comment,
        orderItem: review.orderItem,
        order_id: deliveredNotification.deliveredOrderId, // Add order ID
        order_number: deliveredNotification.deliveredOrderNumber, // Add order number for reference
        userId: user?.id, // Explicitly pass user ID
      });

      console.log('✅ Review submission response:', response.data);
      console.log('✅ Full response object:', JSON.stringify(response, null, 2));

      if (response.data.success) {
        // Invalidate reviews cache to trigger immediate refresh
        queryClient.invalidateQueries({ queryKey: ['reviews'] });
        queryClient.invalidateQueries({ queryKey: ['loyalty'] }); // Refresh loyalty for credits update
        console.log('🔄 Reviews and loyalty cache invalidated - will refresh');
        
        // Close review modal
        deliveredNotification.handleCloseReviewModal();
        
        // Show appropriate success message based on action
        const isUpdate = response.data.action === 'updated';
        const pointsAwarded = response.data.points_awarded || 0;
        
        let message = isUpdate 
          ? 'Your review has been updated successfully!' 
          : 'Your review has been submitted successfully!';
        
        if (pointsAwarded > 0) {
          message += `\n\n🎁 You earned ${pointsAwarded} Ama Credits!`;
        }
        
        Alert.alert(
          isUpdate ? 'Review Updated! ⭐' : 'Thank You! ⭐',
          message,
          [{ text: 'OK' }]
        );
      } else {
        Alert.alert('Error', response.data.message || 'Failed to submit review');
      }
    } catch (error: any) {
      console.error('❌ Failed to submit review from OrderDeliveredModal:', error);
      console.error('❌ Error details:', {
        message: error.message,
        status: error.status,
        code: error.code,
        response: error.response?.data,
      });
      Alert.alert('Error', 'Failed to submit review. Please try again.');
    }
  };

  // Memoize modals to prevent unnecessary re-renders
  return (
    <>
      {/* Order Delivered Modal */}
      {deliveredNotification.showDeliveredModal && (
        <OrderDeliveredModal
          visible={deliveredNotification.showDeliveredModal}
          orderNumber={deliveredNotification.deliveredOrderNumber}
          orderId={deliveredNotification.deliveredOrderId}
          onClose={deliveredNotification.handleCloseDeliveredModal}
          onWriteReview={deliveredNotification.handleOpenReviewModal}
        />
      )}
      
      {/* Write Review Modal - Only render when visible */}
      {deliveredNotification.showReviewModal && (
        <WriteReviewModal
          visible={deliveredNotification.showReviewModal}
          onClose={deliveredNotification.handleCloseReviewModal}
          onSubmit={handleReviewSubmit}
        />
      )}
    </>
  );
}

