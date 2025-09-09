import { client } from './client';

/**
 * Register device token with the backend
 * @param token - Expo push token
 * @param platform - Device platform (android|ios)
 */
export async function registerDevice(token: string, platform: 'android' | 'ios') {
  try {
    // POST /devices
    // body: { token, platform }
    await client.post('/devices', { token, platform });
    
    if (__DEV__) {
      console.log('🔔 Device API: Device registered successfully');
    }
  } catch (error) {
    // ignore errors (fail-soft), but log once
    if (__DEV__) {
      console.error('🔔 Device API: Failed to register device:', error);
    }
  }
}

/**
 * Test push notification by calling the test endpoint
 * This will trigger a local notification and in-app toast
 */
export async function testPushNotification() {
  try {
    // POST /notify/test
    await client.post('/notify/test');
    
    if (__DEV__) {
      console.log('🔔 Device API: Test push notification sent successfully');
    }
  } catch (error) {
    if (__DEV__) {
      console.error('🔔 Device API: Failed to send test push notification:', error);
    }
    throw error;
  }
}
