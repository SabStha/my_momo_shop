// Default configuration constants
export const DEFAULT_CONFIG = {
  // API Configuration
  API_URLS: {
    LOCALHOST: 'http://localhost:8000/api',
    ANDROID_EMULATOR: 'http://10.0.2.2:8000/api',
    PRODUCTION: 'https://your-production-api.com/api',
  },
  
  // Timeouts
  TIMEOUTS: {
    API_REQUEST: 15000,
    TOKEN_REFRESH: 5000,
    RETRY_DELAY: 1000,
  },
  
  // Retry Configuration
  RETRY: {
    MAX_ATTEMPTS: 3,
    BACKOFF_MULTIPLIER: 2,
  },
  
  // Cache Configuration
  CACHE: {
    QUERY_STALE_TIME: 5 * 60 * 1000, // 5 minutes
    QUERY_GC_TIME: 10 * 60 * 1000,   // 10 minutes
  },
} as const;

// Environment detection
export const ENV = {
  IS_DEVELOPMENT: __DEV__,
  IS_PRODUCTION: !__DEV__,
  IS_ANDROID: false, // Will be set dynamically
  IS_IOS: false,     // Will be set dynamically
  IS_WEB: false,     // Will be set dynamically
} as const;

// Feature flags
export const FEATURES = {
  DEBUG_LOGGING: __DEV__,
  ERROR_BOUNDARIES: true,
  RETRY_MECHANISM: true,
  TOKEN_REFRESH: true,
} as const;
