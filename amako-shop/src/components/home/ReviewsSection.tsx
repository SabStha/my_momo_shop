import React, { useState } from 'react';
import { View, Text, ScrollView, StyleSheet, Pressable, Alert } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import WriteReviewModal from '../reviews/WriteReviewModal';
import { useCreateReview } from '../../api/reviews-hooks';
import { useSession } from '../../session/SessionProvider';

interface Review {
  id: string;
  name: string;
  rating: number;
  comment: string;
  orderItem: string;
  date: string;
}

interface ReviewsSectionProps {
  reviews?: Review[];
  averageRating?: number;
  totalReviews?: number;
  onWriteReview?: () => void;
  userOrderHistory?: string[];
}

export default function ReviewsSection({ 
  reviews: propReviews = [],
  averageRating = 0,
  totalReviews = 0,
  onWriteReview,
  userOrderHistory = []
}: ReviewsSectionProps) {
  const [showWriteReviewModal, setShowWriteReviewModal] = useState(false);
  const [reviews, setReviews] = useState(propReviews);
  const createReviewMutation = useCreateReview();
  const { user } = useSession();

  const handleWriteReview = () => {
    if (!user) {
      Alert.alert(
        'Login Required',
        'Please log in to write a review.',
        [{ text: 'OK' }]
      );
      return;
    }
    setShowWriteReviewModal(true);
  };

  const handleSubmitReview = async (reviewData: any) => {
    try {
      // Create a new review object to add to the list immediately
      const newReview: Review = {
        id: Date.now().toString(), // Temporary ID
        name: user?.name || 'Anonymous',
        rating: reviewData.rating,
        comment: reviewData.comment,
        orderItem: reviewData.orderItem,
        date: 'Just now',
      };

      // Add the review to the current reviews list
      setReviews(prevReviews => [newReview, ...prevReviews]);
      
      // Show success popup with better UX
      Alert.alert(
        '🎉 Review Submitted Successfully!',
        'Thank you for sharing your experience with us. Your review helps other customers make informed decisions.',
        [
          {
            text: 'Continue Shopping',
            style: 'default',
            onPress: () => {
              // Optionally scroll to reviews section or do something else
            }
          }
        ]
      );

      // TODO: Uncomment this when API is ready
      /*
      const result = await createReviewMutation.mutateAsync({
        ...reviewData,
        userId: user?.id,
      });
      */
    } catch (error: any) {
      Alert.alert(
        'Submission Failed',
        'There was an error submitting your review. Please try again.',
        [{ text: 'OK' }]
      );
    }
  };
  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }).map((_, index) => (
      <MCI
        key={index}
        name={index < rating ? 'star' : 'star-outline'}
        size={16}
        color={colors.brand.accent}
      />
    ));
  };

  const renderReview = (review: Review) => (
    <View key={review.id} style={styles.reviewCard}>
      <View style={styles.reviewHeader}>
        <View style={styles.userInfo}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {review.name.charAt(0).toUpperCase()}
            </Text>
          </View>
          <View style={styles.userDetails}>
            <Text style={styles.userName}>{review.name}</Text>
            <View style={styles.ratingContainer}>
              {renderStars(review.rating)}
            </View>
          </View>
        </View>
        <Text style={styles.date}>{review.date}</Text>
      </View>
      
      <Text style={styles.comment}>{review.comment}</Text>
      <Text style={styles.orderItem}>Ordered: {review.orderItem}</Text>
    </View>
  );

  return (
    <View style={styles.container}>
      {/* Rating Summary */}
      <View style={styles.summaryContainer}>
        <View style={styles.ratingSummary}>
          <Text style={styles.averageRating}>
            {averageRating > 0 ? averageRating.toFixed(1) : 'No reviews yet'}
          </Text>
          {averageRating > 0 && (
            <>
              <View style={styles.ratingStars}>
                {renderStars(Math.floor(averageRating))}
              </View>
              <Text style={styles.totalReviews}>
                Based on {totalReviews} reviews
              </Text>
            </>
          )}
          {averageRating === 0 && (
            <Text style={styles.totalReviews}>
              Be the first to review!
            </Text>
          )}
        </View>
        
        <Pressable style={styles.writeReviewButton} onPress={handleWriteReview}>
          <MCI name="pencil" size={16} color={colors.white} />
          <Text style={styles.writeReviewText}>Write Review</Text>
        </Pressable>
      </View>

      {/* Reviews List */}
      {reviews.length > 0 ? (
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.reviewsContainer}
        >
          {reviews.map(renderReview)}
        </ScrollView>
      ) : (
        <View style={styles.noReviewsContainer}>
          <MCI name="comment-text-outline" size={48} color={colors.gray[400]} />
          <Text style={styles.noReviewsText}>No reviews yet</Text>
          <Text style={styles.noReviewsSubtext}>
            Be the first to share your experience!
          </Text>
        </View>
      )}

      {/* Write Review Modal */}
      <WriteReviewModal
        visible={showWriteReviewModal}
        onClose={() => setShowWriteReviewModal(false)}
        onSubmit={handleSubmitReview}
        userOrderHistory={userOrderHistory}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: 0,
    paddingVertical: 0,
  },
  summaryContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.lg,
    paddingHorizontal: spacing.lg,
  },
  ratingSummary: {
    alignItems: 'center',
  },
  averageRating: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
  },
  ratingStars: {
    flexDirection: 'row',
    marginVertical: spacing.xs,
  },
  totalReviews: {
    fontSize: fontSizes.xs,
    color: colors.momo.mocha,
  },
  writeReviewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#eeaf00',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
  },
  writeReviewText: {
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    marginLeft: spacing.xs,
  },
  reviewsContainer: {
    paddingHorizontal: spacing.lg,
  },
  reviewCard: {
    width: 280,
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginRight: spacing.md,
    ...shadows.light,
  },
  reviewHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: spacing.sm,
  },
  userInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  avatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.brand.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.sm,
  },
  avatarText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
  },
  userDetails: {
    flex: 1,
  },
  userName: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  ratingContainer: {
    flexDirection: 'row',
  },
  date: {
    fontSize: 10,
    color: colors.gray[500],
  },
  comment: {
    fontSize: 10,
    color: colors.momo.mocha,
    lineHeight: 14,
    marginBottom: spacing.sm,
  },
  orderItem: {
    fontSize: 10,
    color: colors.gray[500],
    fontStyle: 'italic',
  },
  noReviewsContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.xl * 2,
    paddingHorizontal: spacing.lg,
  },
  noReviewsText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
    marginTop: spacing.md,
    marginBottom: spacing.xs,
  },
  noReviewsSubtext: {
    fontSize: fontSizes.sm,
    color: colors.gray[500],
    textAlign: 'center',
  },
});
