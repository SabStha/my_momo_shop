// Lazy import to avoid bundling issues
let notifee: any = null;
let AndroidImportance: any = null;
let AndroidVisibility: any = null;
let AndroidStyle: any = null;
let AndroidColor: any = null;

const initNotifee = async () => {
  if (!notifee) {
    try {
      const notifeeModule = await import('@notifee/react-native');
      notifee = notifeeModule.default;
      AndroidImportance = notifeeModule.AndroidImportance;
      AndroidVisibility = notifeeModule.AndroidVisibility;
      AndroidStyle = notifeeModule.AndroidStyle;
      AndroidColor = notifeeModule.AndroidColor;
    } catch (error) {
      console.warn('Notifee not available, falling back to expo notifications');
      return null;
    }
  }
  return notifee;
};

export interface DeliveryNotificationData {
  orderId: string;
  orderNumber: string;
  status: string;
  driverName?: string;
  driverPhone?: string;
  distance?: string;
  duration?: string;
  eta?: string;
  progress?: number;
  restaurantName?: string;
}

export class NativeNotificationService {
  private static instance: NativeNotificationService;
  private notificationId = 'delivery_tracking';
  private isInitialized = false;

  static getInstance(): NativeNotificationService {
    if (!NativeNotificationService.instance) {
      NativeNotificationService.instance = new NativeNotificationService();
    }
    return NativeNotificationService.instance;
  }

  async initialize(): Promise<void> {
    if (this.isInitialized) return;

    try {
      const notifeeModule = await initNotifee();
      if (!notifeeModule) {
        console.log('📱 Notifee not available, using expo notifications');
        this.isInitialized = true;
        return;
      }

      // Request notification permissions
      await notifeeModule.requestPermission();
      
      // Create notification channel for delivery tracking
      await notifeeModule.createChannel({
        id: 'delivery_tracking',
        name: 'Delivery Tracking',
        description: 'Live delivery updates and tracking',
        importance: AndroidImportance?.HIGH || 4,
        visibility: AndroidVisibility?.PUBLIC || 1,
        sound: 'default',
        vibration: true,
        vibrationPattern: [300, 500],
        lights: true,
        lightColor: AndroidColor?.RED || '#FF0000',
      });

      // Create channel for delivery actions
      await notifeeModule.createChannel({
        id: 'delivery_actions',
        name: 'Delivery Actions',
        description: 'Call driver, message driver actions',
        importance: AndroidImportance?.DEFAULT || 3,
        visibility: AndroidVisibility?.PUBLIC || 1,
      });

      this.isInitialized = true;
      console.log('🔔 Native notification service initialized');
    } catch (error) {
      console.error('❌ Failed to initialize native notifications:', error);
      this.isInitialized = true; // Mark as initialized to prevent retries
    }
  }

  async showDeliveryNotification(data: DeliveryNotificationData): Promise<void> {
    try {
      await this.initialize();

      const notifeeModule = await initNotifee();
      if (!notifeeModule) {
        console.log('📱 Using expo notifications instead of native');
        return;
      }

      const { title, body, subtitle } = this.createNotificationContent(data);
      
      // Create action buttons
      const actions = this.createActionButtons(data);

      // Create notification with BigText style for timeline
      const notification = {
        id: this.notificationId,
        title,
        body,
        subtitle,
        data: {
          orderId: data.orderId,
          type: 'delivery_tracking',
          progress: data.progress || 0,
        },
        android: {
          channelId: 'delivery_tracking',
          importance: AndroidImportance?.HIGH || 4,
          visibility: AndroidVisibility?.PUBLIC || 1,
          style: {
            type: AndroidStyle?.BIGTEXT || 'bigtext',
            text: this.createTimelineText(data),
          },
          actions,
          ongoing: true, // Makes notification persistent
          autoCancel: false, // Prevents auto-dismiss
          showTimestamp: true,
          timestamp: Date.now(),
          color: '#FF6B35', // Amako Momo brand color
          smallIcon: 'ic_notification',
          largeIcon: 'ic_delivery',
          progress: {
            max: 100,
            current: data.progress || 0,
            indeterminate: false,
          },
        },
      };

      await notifeeModule.displayNotification(notification);
      console.log(`📱 Native notification displayed: ${data.progress || 0}% - ${data.distance || 'calculating...'}`);
    } catch (error) {
      console.error('❌ Failed to show native notification:', error);
    }
  }

  private createNotificationContent(data: DeliveryNotificationData) {
    const { status, orderNumber, distance, duration, eta, progress } = data;

    switch (status) {
      case 'out_for_delivery':
        return {
          title: 'Your order is on the way',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: `Driver is ${distance} away • ${duration} • Arrives ${eta}`,
        };
      case 'preparing':
        return {
          title: 'Preparing your order',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: 'Your delicious momos are being freshly prepared with care.',
        };
      case 'ready':
        return {
          title: 'Order ready for pickup',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: 'Your order is ready! Our driver will be on their way shortly.',
        };
      case 'delivered':
        return {
          title: 'Order delivered!',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: 'Your order has arrived! Enjoy your meal.',
        };
      default:
        return {
          title: 'Order update',
          subtitle: `Order #${orderNumber} • Amako Momo`,
          body: `Status: ${status.replace('_', ' ').toUpperCase()}`,
        };
    }
  }

  private createTimelineText(data: DeliveryNotificationData): string {
    const { status, progress, distance, duration, eta, driverName } = data;
    
    let timeline = '';
    
    if (status === 'out_for_delivery' && progress !== undefined) {
      // Create visual progress timeline
      const progressBars = Math.round((progress / 100) * 20);
      const emptyBars = 20 - progressBars;
      
      timeline = `🏪${'█'.repeat(progressBars)}${'░'.repeat(emptyBars)}🏠 ${progress}%\n\n`;
      timeline += `📍 Distance: ${distance || 'Calculating...'}\n`;
      timeline += `⏱️ Time: ${duration || 'Calculating...'}\n`;
      timeline += `🕐 ETA: ${eta || 'Calculating...'}\n`;
      if (driverName) {
        timeline += `👨‍💼 Driver: ${driverName}\n`;
      }
      timeline += `\n🛵 Live tracking active`;
    } else if (status === 'preparing') {
      timeline = `👨‍🍳 Your delicious momos are being freshly prepared!\n\n`;
      timeline += `⏰ Estimated prep time: 15-20 minutes\n`;
      timeline += `🔥 Cooking with care and attention to detail\n`;
      timeline += `\n🍜 We'll notify you when it's ready!`;
    } else if (status === 'ready') {
      timeline = `✅ Your order is ready for pickup!\n\n`;
      timeline += `🚚 Driver will be assigned shortly\n`;
      timeline += `📱 You'll get a notification when driver is on the way\n`;
      timeline += `\n🎉 Almost there!`;
    } else if (status === 'delivered') {
      timeline = `🎉 Your order has arrived!\n\n`;
      timeline += `🍽️ Enjoy your delicious meal\n`;
      timeline += `⭐ Don't forget to rate your experience\n`;
      timeline += `\n🙏 Thank you for choosing Amako Momo!`;
    }

    return timeline;
  }

  private createActionButtons(data: DeliveryNotificationData) {
    const actions = [];

    // Add call driver button if driver phone is available
    if (data.driverPhone && data.status === 'out_for_delivery') {
      actions.push({
        title: '📞 Call Driver',
        pressAction: {
          id: 'call_driver',
          launchActivity: 'default',
        },
        input: {
          allowFreeFormInput: false,
        },
      });
    }

    // Add message driver button
    if (data.driverName && data.status === 'out_for_delivery') {
      actions.push({
        title: '💬 Message',
        pressAction: {
          id: 'message_driver',
          launchActivity: 'default',
        },
        input: {
          allowFreeFormInput: true,
          placeholder: 'Type your message...',
        },
      });
    }

    // Add track order button
    actions.push({
      title: '📍 Track Order',
      pressAction: {
        id: 'track_order',
        launchActivity: 'default',
      },
    });

    return actions;
  }

  async updateProgress(progress: number, data: DeliveryNotificationData): Promise<void> {
    try {
      const notifeeModule = await initNotifee();
      if (!notifeeModule) {
        console.log('📱 Notifee not available for progress update');
        return;
      }

      await notifeeModule.displayNotification({
        id: this.notificationId,
        android: {
          channelId: 'delivery_tracking',
          progress: {
            max: 100,
            current: progress,
            indeterminate: false,
          },
        },
      });
    } catch (error) {
      console.error('❌ Failed to update progress:', error);
    }
  }

  async dismissNotification(): Promise<void> {
    try {
      const notifeeModule = await initNotifee();
      if (!notifeeModule) {
        console.log('📱 Notifee not available for dismiss');
        return;
      }

      await notifeeModule.cancelNotification(this.notificationId);
      console.log('📱 Delivery notification dismissed');
    } catch (error) {
      console.error('❌ Failed to dismiss notification:', error);
    }
  }

  async showGroupedNotification(data: DeliveryNotificationData): Promise<void> {
    try {
      await this.initialize();

      const notifeeModule = await initNotifee();
      if (!notifeeModule) {
        console.log('📱 Notifee not available for grouped notifications');
        return;
      }

      // Create summary notification
      const summaryNotification = {
        id: 'delivery_summary',
        title: 'Your delivery updates',
        body: `${data.orderNumber} • ${data.status.replace('_', ' ').toUpperCase()}`,
        android: {
          channelId: 'delivery_tracking',
          groupSummary: true,
          groupId: 'delivery_group',
          style: {
            type: AndroidStyle?.INBOX || 'inbox',
            lines: [
              `Order #${data.orderNumber} - ${data.status}`,
              `Progress: ${data.progress || 0}%`,
              `Driver: ${data.driverName || 'Assigning...'}`,
            ],
          },
        },
      };

      // Create child notification
      const childNotification = {
        id: this.notificationId,
        title: 'Live tracking',
        body: this.createTimelineText(data),
        android: {
          channelId: 'delivery_tracking',
          groupId: 'delivery_group',
          groupAlertBehavior: 'children',
        },
      };

      await notifeeModule.displayNotification(summaryNotification);
      await notifeeModule.displayNotification(childNotification);
    } catch (error) {
      console.error('❌ Failed to show grouped notification:', error);
    }
  }
}

export default NativeNotificationService;
