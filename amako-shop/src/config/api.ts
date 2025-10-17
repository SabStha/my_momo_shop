import { Platform } from 'react-native';
import Constants from 'expo-constants';
import { getCurrentNetworkIP } from './network';

// Detect if running on emulator/simulator vs physical device
const isEmulator = () => {
  if (Platform.OS === 'android') {
    // Check if running on Android emulator
    return Constants.isDevice === false || 
           Constants.deviceName?.includes('emulator') ||
           Constants.deviceName?.includes('simulator');
  } else if (Platform.OS === 'ios') {
    // Check if running on iOS simulator
    return Constants.isDevice === false || 
           Constants.deviceName?.includes('simulator');
  }
  return false;
};

// Dynamic BASE_URL that auto-detects network
export const getBaseURL = async (): Promise<string> => {
  if (!__DEV__) {
    // Production API URL
    return 'https://amakomomo.com/api';
  }

  // Get the current network IP from network config (async)
  const networkIP = await getCurrentNetworkIP();
  
  if (Platform.OS === 'android') {
    // Check if running on emulator vs physical device
    if (isEmulator()) {
      return 'http://10.0.2.2:8000/api'; // Android emulator host IP
    } else {
      return `http://${networkIP}:8000/api`; // Physical Android device - use network IP
    }
  } else if (Platform.OS === 'ios') {
    return isEmulator()
      ? 'http://localhost:8000/api' // iOS simulator
      : `http://${networkIP}:8000/api`; // Physical iOS device - use network IP
  }
  
  // Web or other platforms - use network IP
  return `http://${networkIP}:8000/api`;
};

// Fallback BASE_URL for synchronous usage
export const BASE_URL = 'http://192.168.2.142:8000/api'; // Your actual WiFi IP (Home Network)

export const API_BASE_URL = BASE_URL;

export const API_CONFIG = {
  BASE_URL,
  API_BASE_URL,
  TIMEOUT: 15000,
  ENV: __DEV__ ? 'development' : 'production',
} as const;
