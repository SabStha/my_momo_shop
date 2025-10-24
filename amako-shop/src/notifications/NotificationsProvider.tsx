import React, { createContext, useContext, useEffect, useRef, useState } from 'react';
import { Platform } from 'react-native';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { router } from 'expo-router';
import { useSession } from '../session/SessionProvider';
import { client } from '../api/client';
import Constants from 'expo-constants';

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
  const { isAuthenticated, user } = useSession();

  useEffect(() => {
    // Register for push notifications
    registerForPushNotificationsAsync().then(token => {
      if (token) {
        console.log('🔔 Expo Push Token:', token);
        setExpoPushToken(token);
      }
    });

    // Listen for incoming notifications while app is foregrounded
    notificationListener.current = Notifications.addNotificationReceivedListener(notification => {
      console.log('🔔 Notification received:', notification);
      setNotification(notification);
    });

    // Listen for notification taps
    responseListener.current = Notifications.addNotificationResponseReceivedListener(response => {
      console.log('🔔 Notification tapped:', response);
      handleNotificationTap(response.notification);
    });

    return () => {
      if (notificationListener.current) {
        Notifications.removeNotificationSubscription(notificationListener.current);
      }
      if (responseListener.current) {
        Notifications.removeNotificationSubscription(responseListener.current);
      }
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

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('default', {
      name: 'default',
      importance: Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#FF231F7C',
      sound: 'default',
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
async function registerDeviceWithBackend(token: string) {
  try {
    console.log('🔔 Registering device token with backend...');
    
    const response = await client.post('/devices', {
      token,
      platform: Platform.OS,
    });

    if (response.data.success) {
      console.log('🔔 Device registered successfully');
    }
  } catch (error: any) {
    console.error('🔔 Failed to register device:', error.message);
  }
}

// Handle notification tap navigation
function handleNotificationTap(notification: Notifications.Notification) {
  const data = notification.request.content.data;
  
  console.log('🔔 Handling notification tap, data:', data);
  
  // Navigate based on notification type
  if (data.action === 'view_offer' || data.offer_code) {
    // Offer notification - go to notifications to claim it
    router.push('/(tabs)/notifications');
  } else if (data.orderId || data.order_id) {
    // Order notification - go to order tracking
    const orderId = data.orderId || data.order_id;
    router.push(`/order/${orderId}`);
  } else if (data.navigation) {
    // Custom navigation path
    router.push(data.navigation as any);
  } else {
    // Default - go to notifications
    router.push('/(tabs)/notifications');
  }
}

