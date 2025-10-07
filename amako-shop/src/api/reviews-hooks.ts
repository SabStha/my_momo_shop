import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { getReviews, createReview, getUserReviews, getProductReviews, CreateReviewData } from './reviews';
import { normalizeAxiosError } from './errors';

// Query keys
export const reviewsQueryKeys = {
  all: ['reviews'] as const,
  lists: () => [...reviewsQueryKeys.all, 'list'] as const,
  list: (filters: Record<string, any>) => [...reviewsQueryKeys.lists(), { filters }] as const,
  details: () => [...reviewsQueryKeys.all, 'detail'] as const,
  detail: (id: string) => [...reviewsQueryKeys.details(), id] as const,
  user: (userId: string) => [...reviewsQueryKeys.all, 'user', userId] as const,
  product: (productId: string) => [...reviewsQueryKeys.all, 'product', productId] as const,
};

/**
 * Hook for fetching all reviews
 */
export function useReviews() {
  return useQuery({
    queryKey: reviewsQueryKeys.lists(),
    queryFn: getReviews,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * Hook for fetching user reviews
 */
export function useUserReviews(userId: string) {
  return useQuery({
    queryKey: reviewsQueryKeys.user(userId),
    queryFn: () => getUserReviews(userId),
    enabled: !!userId,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * Hook for fetching product reviews
 */
export function useProductReviews(productId: string) {
  return useQuery({
    queryKey: reviewsQueryKeys.product(productId),
    queryFn: () => getProductReviews(productId),
    enabled: !!productId,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * Hook for creating a new review
 */
export function useCreateReview() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createReview,
    onSuccess: (data) => {
      // Invalidate and refetch reviews
      queryClient.invalidateQueries({ queryKey: reviewsQueryKeys.lists() });
      
      // If the review has a userId, invalidate user reviews
      if (data.data.userId) {
        queryClient.invalidateQueries({ 
          queryKey: reviewsQueryKeys.user(data.data.userId) 
        });
      }
    },
    onError: (error) => {
      console.error('Create review failed:', error);
      throw normalizeAxiosError(error);
    },
  });
}
