import { client } from './client';
import { Money } from '../types';

export interface OrderItem {
  product_id: string;
  quantity: number;
  type?: 'product' | 'bulk';
}

export interface OrderAddress {
  city: string;
  ward_number?: string;
  area_locality?: string;
  building_name?: string;
  detailed_directions?: string;
}

export interface CreateOrderRequest {
  branch_id: number;
  name: string;
  email: string;
  phone: string;
  city: string;
  ward_number?: string;
  area_locality?: string;
  building_name?: string;
  detailed_directions?: string;
  payment_method: 'cash' | 'esewa' | 'khalti' | 'fonepay' | 'card' | 'amako_credits';
  items: OrderItem[];
  total: number;
  applied_offer?: string;
  gps_location?: {
    latitude: number;
    longitude: number;
  };
}

export interface Order {
  id: number;
  order_number: string;
  user_id?: number;
  branch_id: number;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  delivery_address: string;
  payment_method: string;
  subtotal: number;
  tax_amount: number;
  discount: number;
  total: number;
  total_amount: number;
  grand_total: number;
  order_type: string;
  status: 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled';
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  created_at: string;
  updated_at: string;
}

export interface CreateOrderResponse {
  success: boolean;
  message?: string;
  order?: Order;
  business_status?: 'open' | 'closed';
}

/**
 * Create a new order
 */
export async function createOrder(orderData: CreateOrderRequest): Promise<CreateOrderResponse> {
  if (__DEV__) {
    console.log('📦 Creating order via API:', {
      branch_id: orderData.branch_id,
      payment_method: orderData.payment_method,
      items_count: orderData.items.length,
      total: orderData.total,
    });
  }

  try {
    const response = await client.post('/orders', orderData);
    
    if (__DEV__) {
      console.log('✅ Order created successfully on backend:', response.data);
      console.log('✅ Order will appear in payment manager');
    }

    return {
      success: true,
      order: response.data.order || response.data,
    };
  } catch (error: any) {
    if (__DEV__) {
      console.error('❌ Order creation failed:', error);
      console.error('❌ Error code:', error.code);
      console.error('❌ Error status:', error.status);
    }

    // Handle business closed error (HTTP 423 or code HTTP_423)
    if (error.status === 423 || error.code === 'HTTP_423' || error.response?.status === 423) {
      console.log('🚫 Business is closed - order rejected by backend');
      return {
        success: false,
        message: error.details?.message || error.message || 'We are currently closed. Please try again during business hours.',
        business_status: 'closed',
      };
    }

    // Handle validation errors (422 status)
    if (error.status === 422 || error.response?.status === 422) {
      const validationErrors = error.response?.data?.errors || error.details?.errors;
      if (validationErrors) {
        const firstError = Object.values(validationErrors)[0];
        return {
          success: false,
          message: Array.isArray(firstError) ? firstError[0] : error.message,
        };
      }
    }

    // Handle network errors (no response from server)
    if (error.code === 'NETWORK_ERROR' || error.code === 'CONNECTION_ERROR' || (!error.response && !error.status)) {
      console.error('❌ Network error - cannot reach server');
      return {
        success: false,
        message: 'Cannot connect to server. Please check your internet connection.',
      };
    }

    // Handle other errors
    return {
      success: false,
      message: error.message || error.details?.message || 'Failed to create order',
    };
  }
}

/**
 * Get user's orders from backend
 */
export async function getUserOrders(): Promise<Order[]> {
  try {
    if (__DEV__) {
      console.log('📦 Fetching orders from backend API...');
    }
    
    const response = await client.get('/orders');
    
    if (__DEV__) {
      console.log('✅ Orders fetched:', response.data);
    }
    
    return response.data.orders || response.data || [];
  } catch (error: any) {
    if (__DEV__) {
      console.error('❌ Failed to fetch orders:', error);
    }
    return [];
  }
}

/**
 * Get a specific order by ID
 */
export async function getOrder(orderId: number): Promise<Order | null> {
  try {
    const response = await client.get(`/orders/${orderId}`);
    return response.data.order || response.data;
  } catch (error: any) {
    if (__DEV__) {
      console.error('❌ Failed to fetch order:', error);
    }
    return null;
  }
}

/**
 * Update order status
 */
export async function updateOrderStatus(orderId: number, status: string): Promise<boolean> {
  try {
    await client.post(`/orders/${orderId}/status`, { status });
    return true;
  } catch (error: any) {
    if (__DEV__) {
      console.error('❌ Failed to update order status:', error);
    }
    return false;
  }
}

