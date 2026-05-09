import { useState, useEffect, useRef } from 'react';
import { useNotifications } from './useNotifications';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useQueryClient } from '@tanstack/react-query';
import { useSession } from '../session/SessionProvider';

const SHOWN_DELIVERED_KEY = 'shown_delivered_modals';

export function useOrderDeliveredNotification() {
  const [showDeliveredModal, setShowDeliveredModal] = useState(false);
  const [deliveredOrderNumber, setDeliveredOrderNumber] = useState('');
  const [deliveredOrderId, setDeliveredOrderId] = useState(0);
  const [showReviewModal, setShowReviewModal] = useState(false);
  const [isLoadingShownOrders, setIsLoadingShownOrders] = useState(true);
  
  const shownDeliveredOrdersRef = useRef<Set<string>>(new Set());
  const queryClient = useQueryClient();
  const { data: notificationsData, refetch } = useNotifications(1, 20);
  const { isAuthenticated } = useSession();

  // Load shown delivered orders from storage on mount
  useEffect(() => {
    loadShownDeliveredOrders();
  }, []);

  // Listen for app state changes to refresh notifications
  useEffect(() => {
    const { AppState } = require('react-native');
    
    const subscription = AppState.addEventListener('change', (nextAppState: string) => {
      if (nextAppState === 'active') {
        console.log('📱 App became active, checking for new notifications...');
        // Only refetch if user is authenticated
        if (isAuthenticated) {
          refetch();
        } else {
          console.log('📱 User not authenticated, skipping notification refetch');
        }
      }
    });

    return () => {
      subscription?.remove();
    };
  }, [refetch, isAuthenticated]);

  // Check for new delivered orders
  useEffect(() => {
    console.log('🔍 Checking for delivered notifications...', {
      hasData: !!notificationsData,
      notificationCount: notificationsData?.notifications?.length || 0,
      currentModalState: { showDeliveredModal, showReviewModal },
      isLoadingShownOrders,
      isAuthenticated
    });

    // Don't check if user is not authenticated
    if (!isAuthenticated) {
      console.log('🔐 User not authenticated, skipping notification check');
      return;
    }
    
    // Don't check until shown orders are loaded from storage
    if (isLoadingShownOrders) {
      console.log('⏳ Still loading previously shown orders from storage...');
      return;
    }
    
    if (!notificationsData?.notifications) {
      console.log('⚠️ No notifications data available yet');
      return;
    }

    const notifications = notificationsData.notifications;
    console.log('📬 Total notifications:', notifications.length);
    console.log('📦 Already shown orders:', Array.from(shownDeliveredOrdersRef.current));
    
    // Find delivered order notifications that haven't been shown yet
    const deliveredNotifications = notifications.filter((notification: any) => {
      if (!notification.data) {
        console.log('⚠️ Notification has no data:', notification);
        return false;
      }
      
      // Handle both nested and non-nested data structures
      // Backend might send: { data: { data: {...} } } or { data: {...} }
      const data = notification.data.data || notification.data;
      
      const isDelivered = data.status === 'delivered' && data.show_review_prompt === true;
      const notShown = !shownDeliveredOrdersRef.current.has(data.order_id?.toString());
      
      return isDelivered && notShown;
    });

    console.log('✅ Delivered notifications found:', deliveredNotifications.length);

    // Show modal for the most recent delivered order
    if (deliveredNotifications.length > 0) {
      const latestDelivered = deliveredNotifications[0];
      // Handle nested data structure
      const orderData = latestDelivered.data.data || latestDelivered.data;
      
      console.log('🎉 SHOWING Order delivered popup for:', orderData.order_number);
      console.log('📦 Order data:', orderData);
      
      // Mark as shown
      markAsShown(orderData.order_id?.toString());
      
      // Show the delivered modal
      setDeliveredOrderNumber(orderData.order_number || `Order #${orderData.order_id}`);
      setDeliveredOrderId(orderData.order_id || 0);
      setShowDeliveredModal(true);
      
      console.log('✅ Modal state set to visible!');
    } else {
      console.log('ℹ️ No new delivered orders to show popup for');
      console.log('📊 Already shown orders:', Array.from(shownDeliveredOrdersRef.current));
    }
  }, [notificationsData, isLoadingShownOrders, isAuthenticated]);

  const loadShownDeliveredOrders = async () => {
    try {
      setIsLoadingShownOrders(true);
      const stored = await AsyncStorage.getItem(SHOWN_DELIVERED_KEY);
      if (stored) {
        const orderIds = JSON.parse(stored);
        shownDeliveredOrdersRef.current = new Set(orderIds);
        console.log('📝 Loaded previously shown delivered orders:', orderIds);
      } else {
        console.log('📝 No previously shown delivered orders');
      }
    } catch (error) {
      console.error('Failed to load shown delivered orders:', error);
    } finally {
      setIsLoadingShownOrders(false);
      console.log('✅ Shown orders loaded, ready to check notifications');
    }
  };

  // Clear shown orders (for testing)
  const clearShownOrders = async () => {
    try {
      await AsyncStorage.removeItem(SHOWN_DELIVERED_KEY);
      shownDeliveredOrdersRef.current = new Set();
      console.log('🗑️ Cleared shown delivered orders cache');
    } catch (error) {
      console.error('Failed to clear shown orders:', error);
    }
  };

  // Expose clear function globally for testing
  useEffect(() => {
    (global as any).clearDeliveredPopupCache = clearShownOrders;
    console.log('💡 TIP: Run clearDeliveredPopupCache() in console to reset popup cache');
  }, []);

  const markAsShown = async (orderId: string) => {
    try {
      shownDeliveredOrdersRef.current.add(orderId);
      const orderIds = Array.from(shownDeliveredOrdersRef.current);
      await AsyncStorage.setItem(SHOWN_DELIVERED_KEY, JSON.stringify(orderIds));
    } catch (error) {
      console.error('Failed to save shown delivered orders:', error);
    }
  };

  const handleCloseDeliveredModal = () => {
    console.log('🚫 User closed delivered modal without reviewing - marking as shown');
    setShowDeliveredModal(false);
    // Order is already marked as shown in markAsShown() when modal was opened
  };

  const handleOpenReviewModal = () => {
    setShowDeliveredModal(false);
    setTimeout(() => {
      setShowReviewModal(true);
    }, 300);
  };

  const handleCloseReviewModal = () => {
    console.log('🚫 User closed review modal without submitting - this popup won\'t show again');
    setShowReviewModal(false);
    // Order is already marked as shown, so popup won't appear again
  };

  return {
    showDeliveredModal,
    deliveredOrderNumber,
    deliveredOrderId,
    showReviewModal,
    handleCloseDeliveredModal,
    handleOpenReviewModal,
    handleCloseReviewModal,
  };
}

