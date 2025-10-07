import axios from 'axios';
import { API_BASE_URL } from '../config/api';

export interface Review {
  id: string;
  name: string;
  rating: number;
  comment: string;
  orderItem: string;
  date: string;
  userId?: string;
}

export interface CreateReviewData {
  rating: number;
  comment: string;
  orderItem: string;
  userId?: string;
}

export interface ReviewsResponse {
  success: boolean;
  data: Review[];
  message?: string;
}

export interface CreateReviewResponse {
  success: boolean;
  data: Review;
  message?: string;
}

// Get all reviews
export const getReviews = async (): Promise<ReviewsResponse> => {
  try {
    const response = await axios.get(`${API_BASE_URL}/reviews`);
    return response.data;
  } catch (error) {
    console.error('Error fetching reviews:', error);
    throw error;
  }
};

// Create a new review
export const createReview = async (reviewData: CreateReviewData): Promise<CreateReviewResponse> => {
  try {
    const response = await axios.post(`${API_BASE_URL}/reviews`, reviewData);
    return response.data;
  } catch (error) {
    console.error('Error creating review:', error);
    throw error;
  }
};

// Get reviews for a specific user
export const getUserReviews = async (userId: string): Promise<ReviewsResponse> => {
  try {
    const response = await axios.get(`${API_BASE_URL}/reviews/user/${userId}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching user reviews:', error);
    throw error;
  }
};

// Get reviews for a specific product
export const getProductReviews = async (productId: string): Promise<ReviewsResponse> => {
  try {
    const response = await axios.get(`${API_BASE_URL}/reviews/product/${productId}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching product reviews:', error);
    throw error;
  }
};
