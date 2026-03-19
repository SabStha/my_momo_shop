import React, { createContext, useContext, useEffect, useRef, useState } from 'react';
import { Platform, AppState, AppStateStatus } from 'react-native';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { router } from 'expo-router';
import { useSession } from '../session/SessionProvider';
import { client } from '../api/client';
import Constants from 'expo-constants';
import { initializeDeliveryNotifications } from './delivery-notifications';
import { setupOrderNotificationListener } from '../services/OrderNotificationHandler';
import { imagePreloader } from '../services/ImagePreloader';

// Configure notification behavior
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

interface NotificationsContextType {
  expoPushToken: string | null;
  notification: Notifications.Notification | null;
}

const NotificationsContext = createContext<NotificationsContextType>({
  expoPushToken: null,
  notification: null,
});

export const useNotifications = () => useContext(NotificationsContext);

export function NotificationsProvider({ children }: { children: React.ReactNode }) {
  const [expoPushToken, setExpoPushToken] = useState<string | null>(null);
  const [notification, setNotification] = useState<Notifications.Notification | null>(null);
  const notificationListener = useRef<Notifications.Subscription>();
  const responseListener = useRef<Notifications.Subscription>();
  const orderNotificationListener = useRef<Notifications.Subscription>();
  const { isAuthenticated, user } = useSession();

  useEffect(() => {
    console.log('🔔 [INIT] ===== INITIALIZING NOTIFICATIONS =====');
    
    // Initialize delivery notification system (channels, categories, handlers)
    initializeDeliveryNotifications().catch(error => {
      console.error('🔔 [INIT] ❌ Failed to initialize delivery notifications:', error);
    });
    
    // Setup order notification handler for enhanced notifications
    try {
      orderNotificationListener.current = setupOrderNotificationListener();
      console.log('🔔 [INIT] ✅ Order notification handler setup complete');
    } catch (error) {
      console.error('🔔 [INIT] ❌ Failed to setup order notification handler:', error);
    }
    
    // Register for push notifications
    registerForPushNotificationsAsync()
      .then(token => {
        if (token) {
          console.log('🔔 [INIT] ✅ Expo Push Token obtained:', token);
          setExpoPushToken(token);
        } else {
          console.log('🔔 [INIT] ⚠️ No push token obtained (may be in Expo Go)');
        }
      })
      .catch(error => {
        console.error('🔔 [INIT] ❌ Failed to get push token:', error);
      });

    // Listen for incoming notifications while app is foregrounded
    try {
      notificationListener.current = Notifications.addNotificationReceivedListener(notification => {
        console.log('🔔 [RECEIVED] ===== NOTIFICATION RECEIVED =====');
        console.log('🔔 [RECEIVED] Title:', notification.request.content.title);
        console.log('🔔 [RECEIVED] Body:', notification.request.content.body);
        console.log('🔔 [RECEIVED] Data:', notification.request.content.data);
        setNotification(notification);
      });
      console.log('🔔 [INIT] ✅ Notification received listener added');
    } catch (error) {
      console.error('🔔 [INIT] ❌ Failed to add notification listener:', error);
    }

    // Listen for notification taps
    try {
      responseListener.current = Notifications.addNotificationResponseReceivedListener(response => {
        console.log('🔔 [TAPPED] ===== NOTIFICATION TAPPED =====');
        console.log('🔔 [TAPPED] Notification:', response.notification.request.content);
        handleNotificationTap(response.notification);
      });
      console.log('🔔 [INIT] ✅ Notification response listener added');
    } catch (error) {
      console.error('🔔 [INIT] ❌ Failed to add response listener:', error);
    }

    // Clear old notifications when app comes to foreground (Fix for Issue #2)
    const appStateSubscription = AppState.addEventListener('change', async (nextAppState: AppStateStatus) => {
      if (nextAppState === 'active') {
        console.log('🔔 [APP RESUME] App became active, clearing old notifications...');
        try {
          await Notifications.dismissAllNotificationsAsync();
          console.log('🔔 [APP RESUME] ✅ Cleared all old notifications');
        } catch (error) {
          console.error('🔔 [APP RESUME] ❌ Failed to clear notifications:', error);
        }
      }
    });

    // Start image preloading in background (non-blocking)
    imagePreloader.preloadAllImages().catch(error => {
      console.error('🖼️ [PRELOADER] Background preloading failed:', error);
    });

    console.log('🔔 [INIT] ===== INITIALIZATION COMPLETE =====');

    return () => {
      console.log('🔔 [CLEANUP] Removing notification listeners');
      if (notificationListener.current) {
        notificationListener.current.remove();
      }
      if (responseListener.current) {
        responseListener.current.remove();
      }
      if (orderNotificationListener.current) {
        orderNotificationListener.current.remove();
      }
      appStateSubscription.remove();
    };
  }, []);

  // Register device token with backend when authenticated
  useEffect(() => {
    if (expoPushToken && isAuthenticated && user) {
      registerDeviceWithBackend(expoPushToken);
    }
  }, [expoPushToken, isAuthenticated, user]);

  return (
    <NotificationsContext.Provider value={{ expoPushToken, notification }}>
      {children}
    </NotificationsContext.Provider>
  );
}

// Request notification permissions and get Expo push token
async function registerForPushNotificationsAsync() {
  let token;

  const isExpoGo = Constants.appOwnership === 'expo';
  if (isExpoGo) {
    console.log('🔔 Running in Expo Go - skipping push token registration');
    return null;
  }

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('default', {
      name: 'Amako Momo',
      importance: Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#FF6B35',
      sound: 'default',
      description: 'Amako Momo notifications',
    });
  }

  if (Device.isDevice) {
    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;
    
    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }
    
    if (finalStatus !== 'granted') {
      console.log('🔔 Notification permissions denied');
      return null;
    }
    
    try {
      const projectId = Constants.expoConfig?.extra?.eas?.projectId;
      
      if (!projectId) {
        console.warn('🔔 EAS Project ID not found in app.json');
        return null;
      }

      token = (await Notifications.getExpoPushTokenAsync({
        projectId,
      })).data;
      
      console.log('🔔 Push token obtained:', token);
    } catch (e) {
      console.error('🔔 Error getting push token:', e);
      return null;
    }
  } else {
    console.log('🔔 Must use physical device for push notifications');
  }

  return token;
}

// Register device token with backend
async function registerDeviceWithBackend(token: string, retryCount = 0) {
  const MAX_RETRIES = 3;
  
  try {
    console.log(`🔔 [DEVICE REG] ===== REGISTERING DEVICE (Attempt ${retryCount + 1}) =====`);
    console.log('🔔 [DEVICE REG] Token:', token.substring(0, 20) + '...');
    console.log('🔔 [DEVICE REG] Platform:', Platform.OS);
    
    const response = await client.post('/devices', {
      token,
      platform: Platform.OS,
    });

    if (response.data.success) {
      console.log('🔔 [DEVICE REG] ✅ Device registered successfully');
      console.log('🔔 [DEVICE REG] Device ID:', response.data.device_id);
      console.log('🔔 [DEVICE REG] ===== REGISTRATION COMPLETE =====');
    } else {
      console.warn('🔔 [DEVICE REG] ⚠️ Registration returned non-success:', response.data);
    }
  } catch (error: any) {
    console.error('🔔 [DEVICE REG] ❌ Registration failed:', error.message);
    console.error('🔔 [DEVICE REG] Error status:', error.status);
    console.error('🔔 [DEVICE REG] Error details:', error.response?.data);
    
    // Retry on network errors
    if (retryCount < MAX_RETRIES && (error.code === 'NETWORK_ERROR' || error.status === 503)) {
      const retryDelay = (retryCount + 1) * 2000; // 2s, 4s, 6s
      console.log(`🔔 [DEVICE REG] 🔄 Retrying in ${retryDelay/1000}s...`);
      setTimeout(() => {
        registerDeviceWithBackend(token, retryCount + 1);
      }, retryDelay);
    } else {
      console.log('🔔 [DEVICE REG] ===== REGISTRATION FAILED (No more retries) =====');
    }
  }
}

// Handle notification tap navigation
function handleNotificationTap(notification: Notifications.Notification) {
  const data = notification.request.content.data;
  
  console.log('🔔 [NOTIFICATION TAP] ===== HANDLING NOTIFICATION TAP =====');
  console.log('🔔 [NOTIFICATION TAP] Notification data:', data);
  console.log('🔔 [NOTIFICATION TAP] Action:', data.action);
  
  try {
    // Navigate based on notification type and action
    if (data.action === 'view_offer' || data.offer_code) {
      // Offer notification - go to notifications to claim it
      console.log('🔔 [NOTIFICATION TAP] Navigating to notifications for offer');
      router.push('/(tabs)/notifications');
    } else if (data.action === 'view_order' || data.orderId || data.order_id) {
      // Order notification - go to specific order
      const orderId = data.orderId || data.order_id;
      console.log('🔔 [NOTIFICATION TAP] Navigating to order:', orderId);
      router.push(`/order/${orderId}` as any);
    } else if (data.navigation) {
      // Custom navigation path
      console.log('🔔 [NOTIFICATION TAP] Navigating to custom path:', data.navigation);
      router.push(data.navigation as any);
    } else {
      // Default - go to notifications
      console.log('🔔 [NOTIFICATION TAP] Default navigation to notifications tab');
      router.push('/(tabs)/notifications');
    }
    
    console.log('🔔 [NOTIFICATION TAP] ===== NAVIGATION SUCCESSFUL =====');
  } catch (error) {
    console.error('🔔 [NOTIFICATION TAP] ===== NAVIGATION FAILED =====');
    console.error('🔔 [NOTIFICATION TAP] Error:', error);
    // Fallback to notifications tab
    router.push('/(tabs)/notifications');
  }
}

