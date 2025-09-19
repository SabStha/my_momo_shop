import { Platform } from 'react-native';
import Constants from 'expo-constants';

const LAN_IP = '192.168.0.19'; // dev: set to your PC LAN IP for physical device testing

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

export const BASE_URL = (() => {
  if (!__DEV__) {
    return 'https://api.example.com/api'; // TODO: prod base
  }

  if (Platform.OS === 'android') {
    return isEmulator() 
      ? 'http://10.0.2.2:8000/api' // Android emulator
      : `http://${LAN_IP}:8000/api`; // Physical Android device
  } else if (Platform.OS === 'ios') {
    return isEmulator()
      ? 'http://localhost:8000/api' // iOS simulator
      : `http://${LAN_IP}:8000/api`; // Physical iOS device
  }
  
  // Web or other platforms
  return `http://${LAN_IP}:8000/api`;
})();

export const API_CONFIG = {
  BASE_URL,
  TIMEOUT: 15000,
  ENV: __DEV__ ? 'development' : 'production',
} as const;
