import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { 
  getNotifications, 
  markNotificationAsRead, 
  markAllNotificationsAsRead, 
  deleteNotification,
  getChurnRiskNotifications,
  Notification,
  NotificationsResponse 
} from '../api/notifications';

/**
 * Hook to fetch notifications with pagination
 */
export function useNotifications(page: number = 1, perPage: number = 20) {
  return useQuery({
    queryKey: ['notifications', page, perPage],
    queryFn: () => getNotifications(page, perPage),
    staleTime: 30000, // 30 seconds
    refetchOnWindowFocus: false,
    retry: 3,
    retryDelay: 1000,
  });
}

/**
 * Hook to fetch all notifications (infinite scroll)
 */
export function useAllNotifications() {
  return useQuery({
    queryKey: ['notifications', 'all'],
    queryFn: () => getNotifications(1, 100), // Get first 100 notifications
    staleTime: 30000,
    refetchOnWindowFocus: false,
    retry: 3,
    retryDelay: 1000,
  });
}

/**
 * Hook to fetch churn risk notifications
 */
export function useChurnRiskNotifications() {
  return useQuery({
    queryKey: ['notifications', 'churn-risks'],
    queryFn: getChurnRiskNotifications,
    staleTime: 60000, // 1 minute
    refetchOnWindowFocus: false,
  });
}

/**
 * Hook to mark a notification as read
 */
export function useMarkAsRead() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: markNotificationAsRead,
    onSuccess: () => {
      // Invalidate and refetch notifications
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });
}

/**
 * Hook to mark all notifications as read
 */
export function useMarkAllAsRead() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: markAllNotificationsAsRead,
    onSuccess: () => {
      // Invalidate and refetch notifications
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });
}

/**
 * Hook to delete a notification
 */
export function useDeleteNotification() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: deleteNotification,
    onSuccess: () => {
      // Invalidate and refetch notifications
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });
}

/**
 * Hook to get unread notifications count
 */
export function useUnreadCount() {
  const { data: notificationsData, isLoading, error } = useAllNotifications();
  
  // Safely handle undefined data
  const notifications = notificationsData?.notifications || [];
  const unreadCount = notifications.filter(
    (notification: Notification) => !notification.read_at
  ).length;
  
  return {
    unreadCount,
    hasUnread: unreadCount > 0,
    isLoading,
    error,
  };
}
