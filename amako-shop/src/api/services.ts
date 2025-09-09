import { client as apiClient, createRequestConfig } from './client';
import { HealthResponse, ApiResponse } from './types';

// Health check service
export const healthService = {
  /**
   * Check API health status
   */
  check: async (): Promise<HealthResponse> => {
    const response = await apiClient.get<HealthResponse>('/health', createRequestConfig());
    return response.data;
  },
};

// Auth service
export const authService = {
  /**
   * Login user
   */
  login: async (email: string, password: string): Promise<ApiResponse<{ access_token: string; refresh_token: string }>> => {
    const response = await apiClient.post<ApiResponse<{ access_token: string; refresh_token: string }>>('/auth/login', {
      email,
      password,
    }, createRequestConfig());
    return response.data;
  },

  /**
   * Refresh access token
   */
  refreshToken: async (refreshToken: string): Promise<ApiResponse<{ access_token: string }>> => {
    const response = await apiClient.post<ApiResponse<{ access_token: string }>>('/auth/refresh', {
      refresh_token: refreshToken,
    }, createRequestConfig());
    return response.data;
  },

  /**
   * Logout user
   */
  logout: async (): Promise<ApiResponse<void>> => {
    const response = await apiClient.post<ApiResponse<void>>('/auth/logout', {}, createRequestConfig());
    return response.data;
  },
};

// User service
export const userService = {
  /**
   * Get current user profile
   */
  getProfile: async (): Promise<ApiResponse<any>> => {
    const response = await apiClient.get<ApiResponse<any>>('/user/profile', createRequestConfig());
    return response.data;
  },

  /**
   * Update user profile
   */
  updateProfile: async (profileData: any): Promise<ApiResponse<any>> => {
    const response = await apiClient.put<ApiResponse<any>>('/user/profile', profileData, createRequestConfig());
    return response.data;
  },
};

// Cart service
export const cartService = {
  /**
   * Get cart items
   */
  getItems: async (): Promise<ApiResponse<any[]>> => {
    const response = await apiClient.get<ApiResponse<any[]>>('/cart/items', createRequestConfig());
    return response.data;
  },

  /**
   * Add item to cart
   */
  addItem: async (itemId: string, quantity: number): Promise<ApiResponse<any>> => {
    const response = await apiClient.post<ApiResponse<any>>('/cart/items', {
      itemId,
      quantity,
    }, createRequestConfig());
    return response.data;
  },

  /**
   * Update cart item quantity
   */
  updateQuantity: async (itemId: string, quantity: number): Promise<ApiResponse<any>> => {
    const response = await apiClient.put<ApiResponse<any>>(`/cart/items/${itemId}`, {
      quantity,
    }, createRequestConfig());
    return response.data;
  },

  /**
   * Remove item from cart
   */
  removeItem: async (itemId: string): Promise<ApiResponse<void>> => {
    const response = await apiClient.delete<ApiResponse<void>>(`/cart/items/${itemId}`, createRequestConfig());
    return response.data;
  },

  /**
   * Clear cart
   */
  clear: async (): Promise<ApiResponse<void>> => {
    const response = await apiClient.delete<ApiResponse<void>>('/cart/items', createRequestConfig());
    return response.data;
  },
};

// Order service
export const orderService = {
  /**
   * Get user orders
   */
  getOrders: async (params?: { status?: string; page?: number; limit?: number }): Promise<ApiResponse<any[]>> => {
    const response = await apiClient.get<ApiResponse<any[]>>('/orders', {
      ...createRequestConfig(),
      params,
    });
    return response.data;
  },

  /**
   * Get specific order
   */
  getOrder: async (orderId: string): Promise<ApiResponse<any>> => {
    const response = await apiClient.get<ApiResponse<any>>(`/orders/${orderId}`, createRequestConfig());
    return response.data;
  },

  /**
   * Create new order
   */
  createOrder: async (orderData: any): Promise<ApiResponse<any>> => {
    const response = await apiClient.post<ApiResponse<any>>('/orders', orderData, createRequestConfig());
    return response.data;
  },

  /**
   * Cancel order
   */
  cancelOrder: async (orderId: string): Promise<ApiResponse<void>> => {
    const response = await apiClient.post<ApiResponse<void>>(`/orders/${orderId}/cancel`, {}, createRequestConfig());
    return response.data;
  },
};
