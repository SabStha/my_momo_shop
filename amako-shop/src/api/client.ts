import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { normalizeAxiosError, logError } from './errors';
import { ApiResponse, ApiError } from './types';
import { getToken } from '../session/token';
import { emitUnauthorized } from '../utils/events';
import { ENV_CONFIG } from '../config/environment';
import { getBaseURL } from '../config/api';

// API Configuration
const API_CONFIG = {
  BASE_URL: ENV_CONFIG.API_URL,
  TIMEOUT: ENV_CONFIG.API_TIMEOUT,
  RETRY_ATTEMPTS: ENV_CONFIG.RETRY_ATTEMPTS,
  RETRY_DELAY: ENV_CONFIG.RETRY_DELAY,
  VERBOSE_LOGGING: false, // Set to true for detailed API logs
} as const;

// Log API configuration in development
if (__DEV__) {
  console.log('🔧 API Configuration:', {
    BASE_URL: API_CONFIG.BASE_URL,
    TIMEOUT: API_CONFIG.TIMEOUT,
    ENV: ENV_CONFIG.ENV
  });
}

// Create axios instance with dynamic base URL
const apiClient: AxiosInstance = axios.create({
  baseURL: API_CONFIG.BASE_URL, // Fallback base URL
  timeout: API_CONFIG.TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add request interceptor for debugging (only if verbose logging enabled)
apiClient.interceptors.request.use(
  (config) => {
    if (__DEV__ && API_CONFIG.VERBOSE_LOGGING) {
      console.log('🚀 API Request:', config.method?.toUpperCase(), config.url);
    }
    return config;
  },
  (error) => {
    if (__DEV__ && API_CONFIG.VERBOSE_LOGGING) {
      console.error('❌ Request Error:', error);
    }
    return Promise.reject(error);
  }
);

// Add response interceptor for better error handling
apiClient.interceptors.response.use(
  (response) => {
    if (__DEV__ && API_CONFIG.VERBOSE_LOGGING) {
      console.log('✅ API Response:', response.status, response.config.url);
    }
    return response;
  },
  (error) => {
    if (__DEV__) {
      console.error('❌ API Error:', {
        message: error.message,
        code: error.code,
        status: error.response?.status,
        url: error.config?.url,
        baseURL: error.config?.baseURL,
      });
    }
    
    // Provide more specific error messages
    if (error.code === 'ERR_NETWORK') {
      error.message = 'Network connection failed. Please check your internet connection.';
    } else if (error.code === 'ECONNREFUSED') {
      error.message = 'Connection refused. Please check if the server is running.';
    } else if (error.response?.status === 404) {
      error.message = 'API endpoint not found. Please check the server configuration.';
    }
    
    return Promise.reject(error);
  }
);

// Function to update base URL dynamically
export const updateBaseURL = async (): Promise<void> => {
  try {
    const newBaseURL = await getBaseURL();
    apiClient.defaults.baseURL = newBaseURL;
    if (__DEV__) {
      console.log('🔄 Updated API base URL to:', newBaseURL);
    }
  } catch (error) {
    console.warn('Failed to update base URL:', error);
  }
};

// Initialize base URL on app start
if (__DEV__) {
  updateBaseURL();
}

// Request interceptor
apiClient.interceptors.request.use(
  async (config: any) => {
    // Add auth token if available
    try {
      const tokenData = await getToken();

      if (tokenData?.token && config.headers) {
        config.headers.Authorization = `Bearer ${tokenData.token}`;
      }
    } catch (error) {
      // Token retrieval failed, continue without auth
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Track login state to suppress 401 handling during token propagation
let isLoggingIn = false;

// No-op kept for callers — counter logic removed (see 401 handler below)
export const reset401Counter = () => {};

// Function to mark login in progress (prevents premature logout during token propagation)
export const setLoggingIn = (value: boolean) => {
  isLoggingIn = value;
  if (__DEV__) {
    console.log('🔐 Login in progress:', value);
  }
};

// Response interceptor
apiClient.interceptors.response.use(
  (response: AxiosResponse<ApiResponse>) => {
    return response;
  },
  async (error) => {
    // Normalize error
    const normalizedError = normalizeAxiosError(error);

    // Log error for debugging
    logError(normalizedError, `API Call: ${error.config?.method?.toUpperCase()} ${error.config?.url}`);

    // Handle 401 unauthorized errors
    if (normalizedError.status === 401) {
      // Skip 401 handling while login request itself is in flight
      if (isLoggingIn) {
        return Promise.reject(normalizedError);
      }

      const url = error.config?.url || '';

      // Non-critical endpoints: a 401 here should silently fail, not log the user out.
      const nonCriticalEndpoints = ['/notifications', '/reviews', '/stats/home', '/store/info', '/home/benefits'];
      const isNonCritical = nonCriticalEndpoints.some(ep => url.includes(ep));
      if (isNonCritical) {
        return Promise.reject(normalizedError);
      }

      // Core authenticated endpoints — if these 401, the token is definitively invalid.
      // Logout immediately without waiting for a count threshold, because the counter
      // resets on every successful request (e.g. /menu) so the threshold is never reached.
      const coreEndpoints = ['/cart', '/me', '/user', '/profile', '/orders'];
      const isCoreEndpoint = coreEndpoints.some(e => url.includes(e));

      if (isCoreEndpoint) {
        emitUnauthorized();
      }
    }

    return Promise.reject(normalizedError);
  }
);

// Retry mechanism for failed requests
export const retryRequest = async <T>(
  requestFn: () => Promise<T>,
  retryCount: number = API_CONFIG.RETRY_ATTEMPTS,
  delay: number = API_CONFIG.RETRY_DELAY
): Promise<T> => {
  try {
    return await requestFn();
  } catch (error) {
    if (retryCount > 0 && shouldRetry(error)) {
      await new Promise(resolve => setTimeout(resolve, delay));
      return retryRequest(requestFn, retryCount - 1, delay * 2);
    }
    throw error;
  }
};

// Determine if a request should be retried
function shouldRetry(error: any): boolean {
  // Don't retry client errors (4xx) except for specific cases
  if (error.status >= 400 && error.status < 500) {
    return error.status === 408 || error.status === 429; // Timeout or rate limit
  }
  
  // Retry server errors (5xx) and network errors
  return error.status >= 500 || error.status === 0;
}

// Helper function to create request config
export const createRequestConfig = (
  config: Partial<AxiosRequestConfig> = {}
): AxiosRequestConfig => ({
  ...config,
  timeout: config.timeout || API_CONFIG.TIMEOUT,
});

// Export configuration for use in other modules
export { API_CONFIG, apiClient as client };
