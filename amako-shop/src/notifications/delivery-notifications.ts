import * as Notifications from 'expo-notifications';
import * as Linking from 'expo-linking';
import { Platform } from 'react-native';

// ==========================================
// 🔔 PROFESSIONAL DELIVERY NOTIFICATIONS
// ==========================================

// 1) Global handler: how notifications behave in foreground
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: false, // Silent by default
    shouldSetBadge: false,
  }),
});

// 2) ANDROID CHANNELS: Controls heads-up vs silent
export async function ensureNotificationChannels() {
  if (Platform.OS === 'android') {
    // HIGH PRIORITY: Heads-up notifications for important updates
    await Notifications.setNotificationChannelAsync('orders-high', {
      name: 'Orders (important)',
      importance: Notifications.AndroidImportance.HIGH, // Heads-up
      sound: null,
      showBadge: true,
      enableVibrate: true,
      lockscreenVisibility: Notifications.AndroidNotificationVisibility.PUBLIC,
      description: 'Important order updates like delivery started, arriving',
    });

    // SILENT: Tray-only notifications for less critical updates
    await Notifications.setNotificationChannelAsync('orders-silent', {
      name: 'Orders (silent)',
      importance: Notifications.AndroidImportance.MIN, // Tray only
      sound: null,
      showBadge: false,
      enableVibrate: false,
      description: 'Silent order updates like cooking, accepted',
    });

    console.log('🔔 Notification channels created successfully');
  }
}

// 3) iOS CATEGORIES: Action buttons setup
export async function setupIOSCategories() {
  if (Platform.OS === 'ios') {
    await Notifications.setNotificationCategoryAsync('ORDER_ACTIONS', [
      { 
        identifier: 'TRACK', 
        buttonTitle: 'Track', 
        options: { opensAppToForeground: true } 
      },
      { 
        identifier: 'CALL', 
        buttonTitle: 'Call rider',
        options: { opensAppToForeground: false }
      },
      { 
        identifier: 'HELP', 
        buttonTitle: 'Help',
        options: { opensAppToForeground: true }
      },
    ]);

    await Notifications.setNotificationCategoryAsync('ORDER_DELIVERED', [
      { 
        identifier: 'RATE', 
        buttonTitle: 'Rate now', 
        options: { opensAppToForeground: true } 
      },
    ]);

    console.log('🍎 iOS notification categories set up');
  }
}

// 4) UPSERT ORDER NOTIFICATION: Updates same thread
export interface OrderNotificationOptions {
  etaMin?: [number, number];
  riderName?: string;
  riderPhone?: string;
  percent?: number;
  headsUp?: boolean;
  orderNumber?: string;
}

export type OrderPhase = 
  | 'accepted' 
  | 'confirmed'
  | 'preparing'
  | 'cooking' 
  | 'ready'
  | 'picked_up' 
  | 'out_for_delivery'
  | 'arriving' 
  | 'delivered';

export async function upsertOrderNotification(
  orderId: string | number,
  phase: OrderPhase,
  opts: OrderNotificationOptions = {}
) {
  // Format ETA string cleanly (like "ETA 18-22 min")
  const etaStr = opts.etaMin 
    ? ` — ETA ${opts.etaMin[0]}-${opts.etaMin[1]} min` 
    : '';
  
  // Title by phase - clean and professional (matching image style)
  const titleByPhase: Record<OrderPhase, string> = {
    accepted: '✅ Order received',
    confirmed: '✅ Order confirmed',
    preparing: '👨‍🍳 Preparing your order',
    cooking: '👨‍🍳 Preparing your order',
    ready: '📦 Order ready',
    picked_up: `🛵 Delivery started${etaStr}`,
    out_for_delivery: `🛵 On the way${etaStr}`,
    arriving: `📍 Arriving soon${etaStr}`,
    delivered: '✅ Delivered! Enjoy your momos',
  };

  // Body - format like "Rider Suman picked up • ORD-68F69EC"
  const bodyParts: string[] = [];
  
  // Add rider info for delivery phases
  if (opts.riderName && ['picked_up', 'out_for_delivery', 'arriving'].includes(phase)) {
    const action = phase === 'picked_up' ? 'picked up' : 
                   phase === 'arriving' ? 'arriving' : 'on the way';
    bodyParts.push(`Rider ${opts.riderName} ${action}`);
  } else if (phase === 'ready' && opts.riderName) {
    bodyParts.push(`Rider ${opts.riderName} will pick up soon`);
  } else if (phase === 'preparing' || phase === 'cooking') {
    bodyParts.push('Fresh momos being made');
  } else if (phase === 'delivered') {
    bodyParts.push('Rate your experience');
  }
  
  // Always add order number at the end
  const orderNum = opts.orderNumber || `ORD-${orderId}`;
  bodyParts.push(orderNum);
  
  const body = bodyParts.join(' • ');

  // Data for deep linking
  const data = { 
    orderId: String(orderId), 
    phase,
    riderPhone: opts.riderPhone,
    action: 'order_update',
    navigation: {
      screen: 'order-tracking',
      params: { id: String(orderId) }
    }
  };

  // Determine if this should be heads-up (important statuses)
  const isHeadsUp = opts.headsUp ?? ['picked_up', 'out_for_delivery', 'arriving', 'delivered'].includes(phase);
  
  // Progress percentage for visual indicator
  const progressByPhase: Record<OrderPhase, number> = {
    accepted: 10,
    confirmed: 20,
    preparing: 40,
    cooking: 40,
    ready: 60,
    picked_up: 70,
    out_for_delivery: 85,
    arriving: 95,
    delivered: 100,
  };
  const progress = opts.percent ?? progressByPhase[phase];

  console.log(`🔔 [DELIVERY NOTIFICATION] Order ${orderNum} - ${phase} - ${isHeadsUp ? 'HEADS-UP' : 'SILENT'}${etaStr}`);

  try {
    await Notifications.scheduleNotificationAsync({
      identifier: `order:${orderId}`, // Same ID = updates in place (threading)
      content: {
        title: titleByPhase[phase],
        body,
        data,
        
        // iOS interruption & relevance
        ...(Platform.OS === 'ios' && {
          interruptionLevel: isHeadsUp ? 'active' : 'passive',
          relevanceScore: isHeadsUp ? 0.9 : 0.3,
          threadIdentifier: `order:${orderId}`, // Group notifications by order
        }),
        
        // Android specifics - matches image style
        ...(Platform.OS === 'android' && {
          android: {
            channelId: isHeadsUp ? 'orders-high' : 'orders-silent',
            groupId: 'amako-orders',
            groupSummary: false,
            color: '#FF6B35', // Brand orange
            priority: isHeadsUp ? 'max' : 'default',
            
            // Progress bar (like in the image)
            ...(progress && progress < 100 && {
              progress: {
                max: 100,
                current: progress,
                indeterminate: false,
              }
            }),
            
            // Action buttons (clean, like in image: Track | Call rider | Help)
            actions: phase === 'delivered' 
              ? [{ 
                  identifier: 'RATE', 
                  title: 'Rate now',
                  buttonType: 'default'
                }]
              : [
                  { 
                    identifier: 'TRACK', 
                    title: 'Track',
                    buttonType: 'default'
                  },
                  ...(opts.riderPhone ? [{ 
                    identifier: 'CALL', 
                    title: 'Call rider',
                    buttonType: 'default'
                  }] : []),
                  { 
                    identifier: 'HELP', 
                    title: 'Help',
                    buttonType: 'default'
                  },
                ],
          },
        }),
        
        // iOS category for actions
        categoryIdentifier: phase === 'delivered' ? 'ORDER_DELIVERED' : 'ORDER_ACTIONS',
      },
      trigger: null, // Deliver immediately (upsert)
    });

    console.log(`✅ [DELIVERY NOTIFICATION] Notification sent: ${titleByPhase[phase]}`);
  } catch (error) {
    console.error('❌ [DELIVERY NOTIFICATION] Failed to send:', error);
  }
}

// 5) HANDLE ACTION TAPS: Deep links
export function setupNotificationResponseHandler() {
  Notifications.addNotificationResponseReceivedListener((response) => {
    const action = response.actionIdentifier;
    const data = response.notification.request.content.data;
    const orderId = data?.orderId;
    const riderPhone = data?.riderPhone;

    console.log(`🔔 [NOTIFICATION ACTION] ${action} for order ${orderId}`);

    if (!orderId) return;

    switch (action) {
      case 'TRACK':
        // Deep link to order tracking screen
        Linking.openURL(Linking.createURL(`/order-tracking/${orderId}`));
        break;

      case 'CALL':
        // Call rider directly
        if (riderPhone) {
          Linking.openURL(`tel:${riderPhone}`);
        }
        break;

      case 'HELP':
        // Open support/help screen
        Linking.openURL(Linking.createURL(`/help?orderId=${orderId}`));
        break;

      case 'RATE':
        // Open order detail with review modal
        Linking.openURL(Linking.createURL(`/order/${orderId}?review=true`));
        break;

      case Notifications.DEFAULT_ACTION_IDENTIFIER:
        // Notification tap (no specific action button)
        // Navigate based on phase
        const phase = data?.phase;
        if (phase === 'delivered') {
          Linking.openURL(Linking.createURL(`/order/${orderId}`));
        } else {
          Linking.openURL(Linking.createURL(`/order-tracking/${orderId}`));
        }
        break;
    }
  });

  console.log('🔔 [NOTIFICATION HANDLER] Response handler registered');
}

// 6) HELPER: Clear old order notifications
export async function clearOrderNotification(orderId: string | number) {
  try {
    await Notifications.dismissNotificationAsync(`order:${orderId}`);
    console.log(`🗑️ [NOTIFICATION] Cleared notification for order ${orderId}`);
  } catch (error) {
    console.error('❌ [NOTIFICATION] Failed to clear:', error);
  }
}

// 7) HELPER: Clear all notifications
export async function clearAllNotifications() {
  try {
    await Notifications.dismissAllNotificationsAsync();
    console.log('🗑️ [NOTIFICATION] Cleared all notifications');
  } catch (error) {
    console.error('❌ [NOTIFICATION] Failed to clear all:', error);
  }
}

// 8) INITIALIZE: Call this on app start
export async function initializeDeliveryNotifications() {
  console.log('🔔 [NOTIFICATION] Initializing delivery notification system...');
  
  await ensureNotificationChannels();
  await setupIOSCategories();
  setupNotificationResponseHandler();
  
  console.log('✅ [NOTIFICATION] Delivery notification system ready!');
}

