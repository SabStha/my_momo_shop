import * as Notifications from 'expo-notifications';
import { upsertOrderNotification, OrderPhase } from '../notifications/delivery-notifications';

/**
 * Handles incoming order notifications from backend
 * Converts them to beautiful native notifications
 */

interface IncomingNotificationData {
  orderId?: string;
  order_id?: string;
  order_number?: string;
  code?: string;
  status?: string;
  rider_name?: string;
  rider_phone?: string;
  eta_min?: number;
  eta_max?: number;
  progress?: number;
  action?: string;
  navigation?: string;
}

/**
 * Map backend status to notification phase
 */
function mapStatusToPhase(status: string): OrderPhase {
  const statusMap: Record<string, OrderPhase> = {
    'pending': 'accepted',
    'confirmed': 'confirmed',
    'processing': 'preparing',
    'preparing': 'preparing',
    'cooking': 'cooking',
    'ready': 'ready',
    'picked_up': 'picked_up',
    'out_for_delivery': 'out_for_delivery',
    'arriving': 'arriving',
    'delivered': 'delivered',
  };
  
  return statusMap[status] || 'confirmed';
}

/**
 * Handle incoming push notification and create native notification
 */
export async function handleIncomingOrderNotification(
  notificationData: IncomingNotificationData
) {
  try {
    // Extract order ID
    const orderId = notificationData.orderId || notificationData.order_id;
    if (!orderId) {
      console.warn('⚠️ [ORDER NOTIFICATION] No order ID in notification data');
      return;
    }

    // Extract status and convert to phase
    const status = notificationData.status;
    if (!status) {
      console.warn('⚠️ [ORDER NOTIFICATION] No status in notification data');
      return;
    }
    
    const phase = mapStatusToPhase(status);
    
    // Extract order number
    const orderNumber = notificationData.order_number || notificationData.code;
    
    // Prepare notification options
    const options = {
      orderNumber: orderNumber,
      riderName: notificationData.rider_name,
      riderPhone: notificationData.rider_phone,
      percent: notificationData.progress,
      // ETA array format: [min, max]
      ...(notificationData.eta_min && notificationData.eta_max && {
        etaMin: [notificationData.eta_min, notificationData.eta_max] as [number, number]
      }),
    };

    console.log('🔔 [ORDER NOTIFICATION] Creating notification:', {
      orderId,
      phase,
      orderNumber,
      hasRider: !!notificationData.rider_name,
      hasETA: !!(notificationData.eta_min && notificationData.eta_max)
    });

    // Create the beautiful notification
    await upsertOrderNotification(orderId, phase, options);
    
    return true;
  } catch (error) {
    console.error('❌ [ORDER NOTIFICATION] Failed to handle notification:', error);
    return false;
  }
}

/**
 * Setup listener for incoming push notifications
 * Intercepts them and creates beautiful native notifications
 */
export function setupOrderNotificationListener() {
  // Listen to notifications received while app is foregrounded
  const subscription = Notifications.addNotificationReceivedListener(async (notification) => {
    const data = notification.request.content.data as IncomingNotificationData;
    
    // Check if this is an order notification
    if (data.orderId || data.order_id) {
      console.log('🔔 [ORDER NOTIFICATION] Received order notification, processing...');
      
      // Dismiss the basic notification from Expo
      await Notifications.dismissNotificationAsync(notification.request.identifier);
      
      // Create our beautiful notification
      await handleIncomingOrderNotification(data);
    }
  });

  console.log('✅ [ORDER NOTIFICATION] Listener setup complete');
  
  return subscription;
}

/**
 * Process notification data when app is opened from notification
 */
export function extractOrderFromNotification(
  notification: Notifications.Notification
): { orderId: string; phase: OrderPhase } | null {
  try {
    const data = notification.request.content.data as IncomingNotificationData;
    const orderId = data.orderId || data.order_id;
    const status = data.status;
    
    if (orderId && status) {
      return {
        orderId,
        phase: mapStatusToPhase(status)
      };
    }
    
    return null;
  } catch (error) {
    console.error('❌ [ORDER NOTIFICATION] Failed to extract order data:', error);
    return null;
  }
}

