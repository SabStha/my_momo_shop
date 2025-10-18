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
  // TEMPORARY: Use production server for ALL environments during testing
  return 'https://amakomomo.com/api';
  
  // Original network detection code (commented out for testing)
  /*
  if (!__DEV__) {
    return 'https://amakomomo.com/api';
  }

  const networkIP = await getCurrentNetworkIP();
  
  if (Platform.OS === 'android') {
    if (isEmulator()) {
      return 'http://10.0.2.2:8000/api';
    } else {
      return `http://${networkIP}:8000/api`;
    }
  } else if (Platform.OS === 'ios') {
    return isEmulator()
      ? 'http://localhost:8000/api'
      : `http://${networkIP}:8000/api`;
  }
  
  return `http://${networkIP}:8000/api`;
  */
};

// Fallback BASE_URL for synchronous usage
// TEMPORARY: Use production for Expo testing
export const BASE_URL = 'https://amakomomo.com/api'; // Production server for testing

export const API_BASE_URL = BASE_URL;

export const API_CONFIG = {
  BASE_URL,
  API_BASE_URL,
  TIMEOUT: 15000,
  ENV: __DEV__ ? 'development' : 'production',
} as const;
