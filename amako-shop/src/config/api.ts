const DEV_URL  = 'http://192.168.0.2:8000/api';
const PROD_URL = 'https://amakomomo.com/api';

export const BASE_URL = __DEV__ ? DEV_URL : PROD_URL;
export const API_BASE_URL = BASE_URL;

// Async wrapper kept for callers that use await getBaseURL()
export const getBaseURL = async (): Promise<string> => BASE_URL;

export const API_CONFIG = {
  BASE_URL,
  API_BASE_URL,
  TIMEOUT: 15000,
  ENV: __DEV__ ? 'development' : 'production',
} as const;
