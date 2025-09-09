import { Platform } from 'react-native';
import { DEFAULT_CONFIG } from './constants';
import { BASE_URL } from './api';

export const ENV_CONFIG = {
  // API Configuration
  API_URL: BASE_URL,
  
  // App Environment
  ENV: process.env.EXPO_PUBLIC_APP_ENV || 'development',
  
  // Timeouts
  API_TIMEOUT: DEFAULT_CONFIG.TIMEOUTS.API_REQUEST,
  
  // Retry Configuration
  RETRY_ATTEMPTS: DEFAULT_CONFIG.RETRY.MAX_ATTEMPTS,
  RETRY_DELAY: DEFAULT_CONFIG.TIMEOUTS.RETRY_DELAY,
} as const;

// Helper function to check if we're in development
export const isDevelopment = __DEV__;

// Helper function to check if we're on Android
export const isAndroid = Platform.OS === 'android';

// Helper function to check if we're on iOS
export const isIOS = Platform.OS === 'ios';

// Helper function to check if we're on web
export const isWeb = Platform.OS === 'web';

// Platform-specific API URL getter
export const getApiUrl = (): string => {
  return ENV_CONFIG.API_URL;
};

// Debug information
export const getDebugInfo = () => ({
  platform: Platform.OS,
  version: Platform.Version,
  isDevelopment: __DEV__,
  apiUrl: ENV_CONFIG.API_URL,
  environment: ENV_CONFIG.ENV,
});
