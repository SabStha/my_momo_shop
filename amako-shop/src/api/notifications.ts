import { client } from './client';

export interface Notification {
  id: string;
  type: string;
  notifiable_type: string;
  notifiable_id: string;
  data: {
    title: string;
    message: string;
    type: 'order' | 'payment' | 'promotion' | 'system' | 'churn';
    order_id?: string;
    amount?: number;
    currency?: string;
    action_url?: string;
    image_url?: string;
  };
  read_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface NotificationsResponse {
  notifications: Notification[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface MarkAsReadResponse {
  success: boolean;
  message: string;
}

/**
 * Get user notifications with pagination
 */
export async function getNotifications(page: number = 1, perPage: number = 20): Promise<NotificationsResponse> {
  try {
    const response = await client.get('/notifications', {
      params: { page, per_page: perPage }
    });
    
    if (__DEV__) {
      console.log('📱 Notifications:', response.data?.notifications?.length || 0, 'items (Page', page, ')');
    }
    
    return response.data;
  } catch (error: any) {
    if (__DEV__) {
      console.warn('⚠️ Notifications API failed:', error.message, '- returning empty notifications');
    }
    
    // Return empty notifications instead of throwing error to prevent logout
    return {
      notifications: [],
      pagination: {
        current_page: page,
        last_page: 1,
        per_page: perPage,
        total: 0
      }
    };
  }
}

/**
 * Mark a specific notification as read
 */
export async function markNotificationAsRead(notificationId: string): Promise<MarkAsReadResponse> {
  const response = await client.post('/notifications/mark-as-read', {
    notification_id: notificationId
  });
  
  if (__DEV__) {
    console.log('✅ Mark as read response:', response.data);
  }
  
  return response.data;
}

/**
 * Mark all notifications as read
 */
export async function markAllNotificationsAsRead(): Promise<MarkAsReadResponse> {
  const response = await client.post('/notifications/mark-all-as-read');
  
  if (__DEV__) {
    console.log('✅ Mark all as read response:', response.data);
  }
  
  return response.data;
}

/**
 * Delete a specific notification
 */
export async function deleteNotification(notificationId: string): Promise<MarkAsReadResponse> {
  const response = await client.delete(`/notifications/${notificationId}`);
  
  if (__DEV__) {
    console.log('🗑️ Delete notification response:', response.data);
  }
  
  return response.data;
}

/**
 * Get churn risk notifications
 */
export async function getChurnRiskNotifications(): Promise<Notification[]> {
  const response = await client.get('/notifications/churn-risks');
  
  if (__DEV__) {
    console.log('⚠️ Churn risk notifications:', response.data);
  }
  
  return response.data;
}
