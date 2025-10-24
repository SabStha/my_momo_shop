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
    console.log('🌐 [API DEBUG] ===== API REQUEST START =====');
    console.log('🌐 [API DEBUG] Method:', config.method?.toUpperCase());
    console.log('🌐 [API DEBUG] URL:', config.url);
    console.log('🌐 [API DEBUG] Base URL:', config.baseURL);
    
    // Add auth token if available
    try {
      console.log('🌐 [API DEBUG] Step 1: Retrieving token...');
      const tokenData = await getToken();
      
      if (tokenData?.token && config.headers) {
        console.log('🌐 [API DEBUG] Step 1: ✅ Token found, adding to headers');
        console.log('🌐 [API DEBUG] Token length:', tokenData.token.length);
        config.headers.Authorization = `Bearer ${tokenData.token}`;
        console.log('🌐 [API DEBUG] Authorization header set');
      } else {
        console.log('🌐 [API DEBUG] Step 1: ⚠️ No token available, making unauthenticated request');
      }
    } catch (error) {
      // Token retrieval failed, continue without auth
      console.warn('🌐 [API DEBUG] Step 1: ❌ Failed to retrieve auth token:', error);
    }

    console.log('🌐 [API DEBUG] ===== API REQUEST END =====');
    return config;
  },
  (error) => {
    console.error('🌐 [API DEBUG] ❌ Request interceptor error:', error);
    return Promise.reject(error);
  }
);

// Track 401 errors to detect token expiration
let recent401Count = 0;
let last401Reset = Date.now();
let isLoggingIn = false; // Track if user is currently logging in

// Function to reset 401 counter (called on successful login)
export const reset401Counter = () => {
  recent401Count = 0;
  last401Reset = Date.now();
  if (__DEV__) {
    console.log('🔐 401 counter reset');
  }
};

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
    console.log('🌐 [API DEBUG] ===== API RESPONSE SUCCESS =====');
    console.log('🌐 [API DEBUG] Status:', response.status);
    console.log('🌐 [API DEBUG] URL:', response.config?.url);
    console.log('🌐 [API DEBUG] Method:', response.config?.method?.toUpperCase());
    
    // Reset 401 counter on successful requests
    recent401Count = 0;
    console.log('🌐 [API DEBUG] 401 counter reset due to successful request');
    
    console.log('🌐 [API DEBUG] ===== API RESPONSE SUCCESS END =====');
    return response;
  },
  async (error) => {
    console.log('🌐 [API DEBUG] ===== API RESPONSE ERROR =====');
    console.log('🌐 [API DEBUG] Error status:', error.response?.status);
    console.log('🌐 [API DEBUG] Error message:', error.message);
    console.log('🌐 [API DEBUG] URL:', error.config?.url);
    console.log('🌐 [API DEBUG] Method:', error.config?.method?.toUpperCase());
    
    // Normalize error
    const normalizedError = normalizeAxiosError(error);
    console.log('🌐 [API DEBUG] Normalized error:', {
      status: normalizedError.status,
      message: normalizedError.message,
      code: normalizedError.code
    });
    
    // Log error for debugging
    logError(normalizedError, `API Call: ${error.config?.method?.toUpperCase()} ${error.config?.url}`);
    
    // Handle 401 unauthorized errors more gracefully
    if (normalizedError.status === 401) {
      console.log('🌐 [API DEBUG] ===== HANDLING 401 ERROR =====');
      console.log('🌐 [API DEBUG] Is logging in:', isLoggingIn);
      console.log('🌐 [API DEBUG] Recent 401 count:', recent401Count);
      console.log('🌐 [API DEBUG] Time since last 401:', Date.now() - last401Reset);
      
      // If user is currently logging in, don't count 401s yet (token still propagating)
      if (isLoggingIn) {
        console.warn('🌐 [API DEBUG] ⚠️ 401 during login, ignoring (token propagating):', error.config?.url);
        return Promise.reject(normalizedError);
      }
      
      // Reset counter if it's been more than 10 seconds since last 401 (increased from 5s)
      if (Date.now() - last401Reset > 10000) {
        console.log('🌐 [API DEBUG] Resetting 401 counter (10+ seconds since last 401)');
        recent401Count = 0;
      }
      
      recent401Count++;
      last401Reset = Date.now();
      console.log('🌐 [API DEBUG] Updated 401 count:', recent401Count);
      
      const url = error.config?.url || '';
      const sensitiveEndpoints = ['/user', '/me', '/profile'];
      const isSensitiveEndpoint = sensitiveEndpoints.some(endpoint => url.includes(endpoint));
      
      console.log('🌐 [API DEBUG] URL:', url);
      console.log('🌐 [API DEBUG] Is sensitive endpoint:', isSensitiveEndpoint);
      console.log('🌐 [API DEBUG] Threshold check:', recent401Count >= 5);
      
      // Increased threshold from 3 to 5 to prevent premature logout
      // OR if it's a sensitive endpoint, logout immediately
      if (recent401Count >= 5 || isSensitiveEndpoint) {
        console.error('🌐 [API DEBUG] ❌ Multiple 401 errors detected or sensitive endpoint failed - token expired, logging out');
        emitUnauthorized();
      } else {
        // For other endpoints, just log the error but don't log out yet
        console.warn(`🌐 [API DEBUG] ⚠️ API 401 error #${recent401Count} on non-sensitive endpoint:`, url, '- not logging out user yet');
      }
    }
    
    console.log('🌐 [API DEBUG] ===== API RESPONSE ERROR END =====');
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
