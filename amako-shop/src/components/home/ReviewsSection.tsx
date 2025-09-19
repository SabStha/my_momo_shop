import React from 'react';
import { View, Text, ScrollView, StyleSheet, Pressable } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';

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
}

const defaultReviews: Review[] = [
  {
    id: '1',
    name: 'Sarah M.',
    rating: 5,
    comment: 'Amazing momos! Fresh and delicious. Will definitely order again.',
    orderItem: 'Chicken Momo',
    date: '2 days ago',
  },
  {
    id: '2',
    name: 'Raj K.',
    rating: 5,
    comment: 'Best momos in town! Fast delivery and great taste.',
    orderItem: 'Vegetable Momo',
    date: '1 week ago',
  },
  {
    id: '3',
    name: 'Priya S.',
    rating: 4,
    comment: 'Good quality and taste. Delivery was on time.',
    orderItem: 'Pork Momo',
    date: '2 weeks ago',
  },
];

export default function ReviewsSection({ 
  reviews = defaultReviews,
  averageRating = 4.5,
  totalReviews = 127,
  onWriteReview
}: ReviewsSectionProps) {
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
          <Text style={styles.averageRating}>{averageRating}</Text>
          <View style={styles.ratingStars}>
            {renderStars(Math.floor(averageRating))}
          </View>
          <Text style={styles.totalReviews}>
            Based on {totalReviews} reviews
          </Text>
        </View>
        
        {onWriteReview && (
          <Pressable style={styles.writeReviewButton} onPress={onWriteReview}>
            <MCI name="pencil" size={16} color={colors.white} />
            <Text style={styles.writeReviewText}>Write Review</Text>
          </Pressable>
        )}
      </View>

      {/* Reviews List */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.reviewsContainer}
      >
        {reviews.map(renderReview)}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  summaryContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.lg,
  },
  ratingSummary: {
    alignItems: 'center',
  },
  averageRating: {
    fontSize: fontSizes['3xl'],
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
  },
  ratingStars: {
    flexDirection: 'row',
    marginVertical: spacing.xs,
  },
  totalReviews: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
  },
  writeReviewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
  },
  writeReviewText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    marginLeft: spacing.xs,
  },
  reviewsContainer: {
    paddingRight: spacing.lg,
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
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  ratingContainer: {
    flexDirection: 'row',
  },
  date: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
  },
  comment: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    lineHeight: 20,
    marginBottom: spacing.sm,
  },
  orderItem: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    fontStyle: 'italic',
  },
});
