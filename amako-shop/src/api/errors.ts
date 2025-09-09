import { ApiError, NetworkError } from './types';

// Error messages for common scenarios
const ERROR_MESSAGES = {
  NETWORK_ERROR: 'Network connection error. Please check your internet connection.',
  TIMEOUT_ERROR: 'Request timed out. Please try again.',
  SERVER_ERROR: 'Server error. Please try again later.',
  UNAUTHORIZED: 'Unauthorized access. Please log in again.',
  FORBIDDEN: 'Access forbidden. You don\'t have permission for this action.',
  NOT_FOUND: 'Resource not found.',
  VALIDATION_ERROR: 'Validation error. Please check your input.',
  UNKNOWN_ERROR: 'An unexpected error occurred. Please try again.',
} as const;

// Normalize axios errors to our standard format
export function normalizeAxiosError(error: any): ApiError {
  if (error.response) {
    // Server responded with error status
    const { status, data } = error.response;
    
    let message: string = ERROR_MESSAGES.UNKNOWN_ERROR;
    let code: string | undefined;
    
    switch (status) {
      case 400:
        message = data?.message || ERROR_MESSAGES.VALIDATION_ERROR;
        code = 'VALIDATION_ERROR';
        break;
      case 401:
        message = ERROR_MESSAGES.UNAUTHORIZED;
        code = 'UNAUTHORIZED';
        break;
      case 403:
        message = ERROR_MESSAGES.FORBIDDEN;
        code = 'FORBIDDEN';
        break;
      case 404:
        message = ERROR_MESSAGES.NOT_FOUND;
        code = 'NOT_FOUND';
        break;
      case 500:
        message = ERROR_MESSAGES.SERVER_ERROR;
        code = 'SERVER_ERROR';
        break;
      default:
        message = data?.message || `Error ${status}`;
        code = `HTTP_${status}`;
    }
    
    return {
      message,
      code,
      status,
      details: data,
    };
  }
  
  if (error.request) {
    // Request was made but no response received
    let message = ERROR_MESSAGES.NETWORK_ERROR;
    let code = 'NETWORK_ERROR';
    let status = 0;
    
    if (error.code === 'ECONNABORTED') {
      message = 'Request timed out. Please check your connection and try again.';
      code = 'TIMEOUT';
      status = 408;
    } else if (error.code === 'ERR_NETWORK') {
      message = 'Network connection failed. Please check your internet connection.';
      code = 'CONNECTION_ERROR';
    } else if (error.message?.includes('ENOTFOUND')) {
      message = 'Unable to reach the server. Please check the API URL configuration.';
      code = 'SERVER_UNREACHABLE';
    }
    
    return {
      message,
      code,
      status,
      details: {
        originalError: error.message,
        errorCode: error.code,
        request: error.request,
        config: error.config,
        suggestion: 'Try checking your internet connection and API configuration.'
      },
    };
  }
  
  // Something else happened
  return {
    message: error.message || ERROR_MESSAGES.UNKNOWN_ERROR,
    code: 'UNKNOWN_ERROR',
    status: undefined,
  };
}

// Check if error is a network error
export function isNetworkError(error: any): boolean {
  return !error.response && error.request;
}

// Check if error is a timeout error
export function isTimeoutError(error: any): boolean {
  return error.code === 'ECONNABORTED';
}

// Check if error is a server error (5xx)
export function isServerError(error: any): boolean {
  return error.response?.status >= 500;
}

// Check if error is a client error (4xx)
export function isClientError(error: any): boolean {
  return error.response?.status >= 400 && error.response?.status < 500;
}

// Get user-friendly error message
export function getUserFriendlyMessage(error: ApiError): string {
  // You can customize this based on your app's needs
  return error.message;
}

// Log error for debugging (in development)
export function logError(error: ApiError, context?: string): void {
  if (__DEV__) {
    console.group(`API Error${context ? ` - ${context}` : ''}`);
    console.error('Error:', error);
    console.groupEnd();
  }
}
