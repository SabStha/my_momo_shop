import { Platform } from 'react-native';
import NativeNotificationService, { DeliveryNotificationData } from './NativeNotificationService';

// Lazy import to avoid Expo Go errors
let Notifications: any = null;

const initNotifications = async () => {
  if (!Notifications) {
    try {
      Notifications = await import('expo-notifications');
      
      // Configure notification behavior for local notifications
      Notifications.setNotificationHandler({
        handleNotification: async () => ({
          shouldShowAlert: true,
          shouldPlaySound: false,
          shouldSetBadge: false,
          priority: Notifications.AndroidNotificationPriority?.HIGH || 'high',
        }),
      });
      
      console.log('📱 Notifications module loaded successfully');
    } catch (error) {
      console.warn('📱 Notifications not available in Expo Go:', error);
      return null;
    }
  }
  return Notifications;
};

class DeliveryNotificationService {
  private notificationId: string | null = null;
  private nativeService: NativeNotificationService;

  constructor() {
    this.nativeService = NativeNotificationService.getInstance();
  }

  async requestPermissions(): Promise<boolean> {
    try {
      const NotificationsModule = await initNotifications();
      if (!NotificationsModule) {
        console.log('📱 Notifications not available in current environment');
        return false;
      }

      const { status: existingStatus } = await NotificationsModule.getPermissionsAsync();
      let finalStatus = existingStatus;

      if (existingStatus !== 'granted') {
        const { status } = await NotificationsModule.requestPermissionsAsync();
        finalStatus = status;
      }

      if (finalStatus !== 'granted') {
        console.log('📱 Notification permissions not granted');
        return false;
      }

      // Create notification channel for Android
      if (Platform.OS === 'android') {
        await NotificationsModule.setNotificationChannelAsync('delivery-tracking', {
          name: 'Delivery Tracking',
          importance: NotificationsModule.AndroidImportance.HIGH,
          vibrationPattern: [0, 250, 250, 250],
          lightColor: '#3B82F6',
          lockscreenVisibility: NotificationsModule.AndroidNotificationVisibility.PUBLIC,
        });
      }

      console.log('📱 Notification permissions granted');
      return true;
    } catch (error) {
      console.error('📱 Error requesting permissions:', error);
      return false;
    }
  }

  async startLiveTracking(orderId: number, orderNumber: string) {
    try {
      const hasPermission = await this.requestPermissions();
      if (!hasPermission) return;

      const NotificationsModule = await initNotifications();
      if (!NotificationsModule) return;

      // Create ongoing notification with initial state
      this.notificationId = await NotificationsModule.scheduleNotificationAsync({
        content: {
          title: `🛵 Your Delivery is Starting!`,
          body: `🏪○○○○○○○○○○○○○○○🏠\n\n` +
                `📦 Order #${orderNumber}\n` +
                `⏱️ Calculating your delivery route...`,
          data: { orderId, type: 'delivery_tracking' },
          sticky: true,
          priority: NotificationsModule.AndroidNotificationPriority?.HIGH || 'high',
          categoryIdentifier: 'delivery',
          sound: false,
        },
        trigger: null, // Show immediately
      });

      console.log('📱 Live tracking notification created:', this.notificationId);
    } catch (error) {
      console.warn('📱 Failed to create notification:', error);
    }
  }

  async updateTrackingNotification(data: {
    orderId: number;
    orderNumber: string;
    distance?: string;
    duration?: string;
    eta?: string;
    status?: string;
    progressPercent?: number;
  }) {
    const { orderNumber, distance, duration, eta, status, progressPercent } = data;

    // Calculate progress bar position (0-100%)
    const calculateProgress = (distanceStr?: string): number => {
      if (!distanceStr) return 0;
      
      // Parse distance (e.g., "2.5 km" or "500 m")
      const distanceMatch = distanceStr.match(/(\d+\.?\d*)\s*(km|m)/);
      if (!distanceMatch) return 50;
      
      const value = parseFloat(distanceMatch[1]);
      const unit = distanceMatch[2];
      
      // Convert to meters
      const meters = unit === 'km' ? value * 1000 : value;
      
      // Assume average delivery is 5km max
      // Progress: 0% at 5km, 100% at 0m
      const maxDistance = 5000; // 5km
      const progress = Math.max(0, Math.min(100, ((maxDistance - meters) / maxDistance) * 100));
      
      return Math.round(progress);
    };
    
  // Create professional progress indicator
  const createProgressIndicator = (percent: number): string => {
    const progress = Math.round(percent);
    const filledBars = Math.round((progress / 100) * 12);
    const emptyBars = 12 - filledBars;
    
    let indicator = '';
    for (let i = 0; i < filledBars; i++) {
      indicator += '█';
    }
    for (let i = 0; i < emptyBars; i++) {
      indicator += '░';
    }
    
    return `${indicator} ${progress}%`;
  };

  // Create professional notification content
  const createNotificationContent = (status: string, orderNumber: string, distance?: string, duration?: string, eta?: string, progress?: number) => {
    const progressBar = progress ? createProgressIndicator(progress) : '';
    
    switch (status) {
      case 'out_for_delivery':
        return {
          title: 'Your order is on the way',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: `Driver is ${distance} away • ${duration} • Arrives ${eta}\n\n${progressBar}`
        };
      case 'preparing':
        return {
          title: 'Preparing your order',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: 'Your delicious momos are being freshly prepared with care. We\'ll notify you when it\'s ready for pickup!'
        };
      case 'ready':
        return {
          title: 'Order ready for pickup',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: 'Your order is ready! Our driver will be on their way shortly.'
        };
      case 'delivered':
        return {
          title: 'Order delivered!',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: 'Your order has arrived! Enjoy your meal and don\'t forget to rate your experience.'
        };
      default:
        return {
          title: 'Order update',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: `Status: ${status.replace('_', ' ').toUpperCase()}`
        };
    }
  };

    const progress = progressPercent ?? calculateProgress(distance);
    
    // Get professional notification content
    const notificationContent = createNotificationContent(
      status, 
      orderNumber, 
      distance, 
      duration, 
      eta, 
      progress
    );
    
    const { title, subtitle, body } = notificationContent;

    // Update the notification
    try {
      const NotificationsModule = await initNotifications();
      if (!NotificationsModule) return;

      await NotificationsModule.scheduleNotificationAsync({
        content: {
          title,
          subtitle,
          body,
          data: { orderId: data.orderId, type: 'delivery_tracking', progress },
          sticky: true,
          priority: NotificationsModule.AndroidNotificationPriority?.HIGH || 'high',
          categoryIdentifier: 'delivery',
          sound: false,
        },
        trigger: null,
      });

      console.log(`📱 Tracking notification updated: ${progress}% - ${distance || 'calculating...'}`);
    } catch (error) {
      console.warn('📱 Failed to update notification:', error);
    }
  }

  async stopLiveTracking() {
    try {
      if (this.notificationId) {
        const NotificationsModule = await initNotifications();
        if (!NotificationsModule) return;

        await NotificationsModule.dismissNotificationAsync(this.notificationId);
        this.notificationId = null;
        console.log('📱 Live tracking notification dismissed');
      }
    } catch (error) {
      console.warn('📱 Failed to dismiss notification:', error);
    }
  }

  async sendDeliveryStatusNotification(
    orderNumber: string,
    status: string,
    message?: string
  ) {
    try {
      const hasPermission = await this.requestPermissions();
      if (!hasPermission) return;

      const NotificationsModule = await initNotifications();
      if (!NotificationsModule) return;

      const statusIcons: Record<string, string> = {
        confirmed: '✅',
        preparing: '👨‍🍳',
        ready: '🎉',
        out_for_delivery: '🛵',
        delivered: '🏠',
      };

      const statusMessages: Record<string, string> = {
        confirmed: 'Your order has been confirmed!',
        preparing: 'Your momos are being prepared!',
        ready: 'Your order is ready for pickup!',
        out_for_delivery: 'Your delivery is on the way!',
        delivered: 'Your order has been delivered!',
      };

      await NotificationsModule.scheduleNotificationAsync({
        content: {
          title: `${statusIcons[status] || '📦'} Order #${orderNumber}`,
          body: message || statusMessages[status] || 'Order status updated',
          data: { orderNumber, status, type: 'status_update' },
          priority: NotificationsModule.AndroidNotificationPriority?.HIGH || 'high',
        },
        trigger: null,
      });

      console.log('📱 Status notification sent:', status);
    } catch (error) {
      console.warn('📱 Failed to send status notification:', error);
    }
  }

  // Native notification method using notifee
  async updateNativeNotification(data: DeliveryNotificationData): Promise<void> {
    try {
      await this.nativeService.showDeliveryNotification(data);
      console.log('📱 Native notification updated');
    } catch (error) {
      console.warn('⚠️ Native notifications not available, using expo notifications:', error);
      // Fallback to expo notifications
      await this.updateNativeNotification({
        orderId: data.orderId,
        orderNumber: data.orderNumber,
        status: data.status,
        distance: data.distance,
        duration: data.duration,
        eta: data.eta,
        progressPercent: data.progress,
      });
    }
  }

  // Update progress with native notifications
  async updateNativeProgress(progress: number, data: DeliveryNotificationData): Promise<void> {
    try {
      await this.nativeService.updateProgress(progress, data);
      console.log(`📱 Native progress updated: ${progress}%`);
    } catch (error) {
      console.warn('⚠️ Native progress update failed, using expo notifications:', error);
      // Fallback to expo notifications
      await this.updateNativeNotification({
        orderId: data.orderId,
        orderNumber: data.orderNumber,
        status: data.status,
        distance: data.distance,
        duration: data.duration,
        eta: data.eta,
        progressPercent: progress,
      });
    }
  }

  // Show grouped notifications
  async showGroupedNotification(data: DeliveryNotificationData): Promise<void> {
    try {
      await this.nativeService.showGroupedNotification(data);
      console.log('📱 Grouped notification shown');
    } catch (error) {
      console.warn('⚠️ Grouped notifications not available, using expo notifications:', error);
      // Fallback to expo notifications
      await this.updateNativeNotification({
        orderId: data.orderId,
        orderNumber: data.orderNumber,
        status: data.status,
        distance: data.distance,
        duration: data.duration,
        eta: data.eta,
        progressPercent: data.progress,
      });
    }
  }
}

export default new DeliveryNotificationService();

