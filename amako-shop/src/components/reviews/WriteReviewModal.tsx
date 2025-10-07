import React, { useState } from 'react';
import {
  View,
  Text,
  Modal,
  StyleSheet,
  Pressable,
  ScrollView,
  Alert,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Button, Card, TextInput, spacing, fontSizes, fontWeights, colors, radius } from '../../ui';

// Validation schema
const reviewSchema = z.object({
  rating: z.number().min(1, 'Please select a rating').max(5, 'Rating must be between 1-5'),
  comment: z.string().min(5, 'Please write at least 5 characters').max(500, 'Comment must be less than 500 characters'),
  orderItem: z.string().min(1, 'Please specify what you ordered'),
});

type ReviewFormData = z.infer<typeof reviewSchema>;

interface WriteReviewModalProps {
  visible: boolean;
  onClose: () => void;
  onSubmit: (review: ReviewFormData) => void;
  userOrderHistory?: string[];
}

export default function WriteReviewModal({ 
  visible, 
  onClose, 
  onSubmit,
  userOrderHistory = []
}: WriteReviewModalProps) {
  const [selectedRating, setSelectedRating] = useState(0);
  const [showValidationModal, setShowValidationModal] = useState(false);
  const [validationErrors, setValidationErrors] = useState<string[]>([]);

  const {
    control,
    handleSubmit,
    formState: { errors },
    reset,
    setValue,
  } = useForm<ReviewFormData>({
    resolver: zodResolver(reviewSchema),
    defaultValues: {
      rating: 0,
      comment: '',
      orderItem: '',
    },
  });

  const handleRatingSelect = (rating: number) => {
    setSelectedRating(rating);
    setValue('rating', rating);
  };

  const handleSubmitReview = (data: ReviewFormData) => {
    onSubmit(data);
    reset();
    setSelectedRating(0);
    onClose();
  };

  const handleClose = () => {
    reset();
    setSelectedRating(0);
    onClose();
  };

  const renderStars = (rating: number, interactive: boolean = false) => {
    return Array.from({ length: 5 }).map((_, index) => (
      <Pressable
        key={index}
        onPress={interactive ? () => handleRatingSelect(index + 1) : undefined}
        style={styles.starContainer}
      >
        <MCI
          name={index < rating ? 'star' : 'star-outline'}
          size={24}
          color={colors.brand.accent}
        />
      </Pressable>
    ));
  };

  return (
    <Modal
      visible={visible}
      animationType="slide"
      presentationStyle="pageSheet"
      onRequestClose={handleClose}
    >
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        <View style={styles.header}>
          <Pressable onPress={handleClose} style={styles.closeButton}>
            <MCI name="close" size={24} color={colors.gray[600]} />
          </Pressable>
          <Text style={styles.title}>Write a Review</Text>
          <View style={styles.placeholder} />
        </View>

        <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
          <Card style={styles.formCard} padding="lg" radius="lg" shadow="medium">
            {/* Rating Section */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Rate your experience</Text>
              <View style={styles.ratingContainer}>
                {renderStars(selectedRating, true)}
              </View>
              {selectedRating > 0 && (
                <Text style={styles.ratingText}>
                  {selectedRating === 1 && 'Poor'}
                  {selectedRating === 2 && 'Fair'}
                  {selectedRating === 3 && 'Good'}
                  {selectedRating === 4 && 'Very Good'}
                  {selectedRating === 5 && 'Excellent'}
                </Text>
              )}
              {errors.rating && (
                <Text style={styles.errorText}>{errors.rating.message}</Text>
              )}
            </View>

            {/* Order Item Section */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>What did you order?</Text>
              <Controller
                control={control}
                name="orderItem"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="e.g., Chicken Momo, Vegetable Momo"
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    error={!!errors.orderItem}
                    leftIcon={<MCI name="food" size={20} color={colors.gray[400]} />}
                  />
                )}
              />
              {errors.orderItem && (
                <Text style={styles.errorText}>{errors.orderItem.message}</Text>
              )}
            </View>

            {/* Comment Section */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Share your experience</Text>
              <Controller
                control={control}
                name="comment"
                render={({ field: { onChange, onBlur, value } }) => (
                  <TextInput
                    placeholder="Tell us about your experience with the food, delivery, and service..."
                    value={value}
                    onChangeText={onChange}
                    onBlur={onBlur}
                    multiline
                    numberOfLines={4}
                    style={styles.commentInput}
                    error={!!errors.comment}
                    leftIcon={<MCI name="message-text" size={20} color={colors.gray[400]} />}
                  />
                )}
              />
              {errors.comment && (
                <Text style={styles.errorText}>{errors.comment.message}</Text>
              )}
            </View>

            {/* Submit Button */}
            <Button
              title="Submit Review"
              onPress={handleSubmit(
                (data) => {
                  handleSubmitReview(data);
                },
                (errors) => {
                  // Show custom validation modal
                  const errorMessages = Object.values(errors).map(error => error?.message).filter(Boolean);
                  if (errorMessages.length > 0) {
                    setValidationErrors(errorMessages);
                    setShowValidationModal(true);
                  }
                }
              )}
              variant="solid"
              size="lg"
              style={styles.submitButton}
            />
          </Card>
        </ScrollView>
      </KeyboardAvoidingView>

      {/* Custom Validation Modal */}
      <Modal
        visible={showValidationModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowValidationModal(false)}
      >
        <View style={styles.validationOverlay}>
          <View style={styles.validationModal}>
            <View style={styles.validationHeader}>
              <View style={styles.validationIconContainer}>
                <MCI name="alert-circle" size={32} color={colors.error[500]} />
              </View>
              <Text style={styles.validationTitle}>Please Complete All Fields</Text>
              <Text style={styles.validationSubtitle}>
                We need a bit more information to submit your review
              </Text>
            </View>

            <View style={styles.validationContent}>
              {validationErrors.map((error, index) => (
                <View key={index} style={styles.validationErrorItem}>
                  <MCI name="circle-small" size={16} color={colors.error[500]} />
                  <Text style={styles.validationErrorText}>{error}</Text>
                </View>
              ))}
            </View>

            <View style={styles.validationActions}>
              <Pressable
                style={styles.validationButton}
                onPress={() => setShowValidationModal(false)}
              >
                <Text style={styles.validationButtonText}>Got it, I'll fix this</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
    </Modal>
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
    paddingVertical: spacing.md,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  closeButton: {
    padding: spacing.xs,
  },
  title: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  placeholder: {
    width: 40,
  },
  content: {
    flex: 1,
    padding: spacing.lg,
  },
  formCard: {
    marginBottom: spacing.xl,
  },
  section: {
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  ratingContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: spacing.sm,
  },
  starContainer: {
    padding: spacing.xs,
  },
  ratingText: {
    fontSize: fontSizes.sm,
    color: colors.brand.primary,
    textAlign: 'center',
    fontWeight: fontWeights.medium,
  },
  commentInput: {
    minHeight: 100,
    textAlignVertical: 'top',
  },
  errorText: {
    fontSize: fontSizes.sm,
    color: colors.error[500],
    marginTop: spacing.xs,
  },
  submitButton: {
    marginTop: spacing.md,
  },
  // Validation Modal Styles
  validationOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  validationModal: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.lg,
    width: '100%',
    maxWidth: 400,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 8,
  },
  validationHeader: {
    alignItems: 'center',
    marginBottom: spacing.lg,
  },
  validationIconContainer: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: colors.error[50],
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  validationTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  validationSubtitle: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 20,
  },
  validationContent: {
    marginBottom: spacing.lg,
  },
  validationErrorItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: spacing.sm,
    paddingHorizontal: spacing.sm,
  },
  validationErrorText: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
    marginLeft: spacing.sm,
    flex: 1,
    lineHeight: 20,
  },
  validationActions: {
    alignItems: 'center',
  },
  validationButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    minWidth: 200,
    alignItems: 'center',
  },
  validationButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
});
