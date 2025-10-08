import { client } from './client';
import { AuthToken } from '../session/token';

export interface LoginCredentials {
  emailOrPhone: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  emailOrPhone: string;
  password: string;
  password_confirmation: string;
}

export interface ChangePasswordCredentials {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}

export interface LoginResponse {
  token: string;
  user: {
    id: string;
    name: string;
    email?: string;
    phone?: string;
  };
}

export interface UserProfile {
  id: string;
  name: string;
  email?: string;
  phone?: string;
  city?: string;
  ward_number?: string;
  area_locality?: string;
  building_name?: string;
  detailed_directions?: string;
  created_at: string;
  updated_at: string;
}

/**
 * Authenticate user with email/phone and password
 */
export async function login(credentials: LoginCredentials): Promise<LoginResponse> {
  if (__DEV__) {
    console.log('🔐 Login: Making request to:', client.defaults.baseURL + '/login');
    console.log('🔐 Login: Credentials:', { emailOrPhone: credentials.emailOrPhone, password: '[HIDDEN]' });
  }
  
  // Transform emailOrPhone to email for Laravel API
  const requestData = {
    email: credentials.emailOrPhone,
    password: credentials.password,
  };
  
  if (__DEV__) {
    console.log('🔐 Login: Sending data:', { email: requestData.email, password: '[HIDDEN]' });
  }
  
  const response = await client.post('/login', requestData);
  
  if (__DEV__) {
    console.log('🔐 Login API Response:', JSON.stringify(response.data, null, 2));
  }
  
  // Handle Laravel API response format with success wrapper
  if (response.data.success) {
    const result = {
      token: response.data.token,
      user: response.data.user
    };
    
    if (__DEV__) {
      console.log('🔐 Login API Parsed Result:', JSON.stringify(result, null, 2));
    }
    
    return result;
  }
  
  // Fallback to direct response (for backward compatibility)
  if (__DEV__) {
    console.log('🔐 Login API Fallback to direct response:', JSON.stringify(response.data, null, 2));
  }
  
  return response.data;
}

/**
 * Register a new user
 */
export async function register(credentials: RegisterCredentials): Promise<LoginResponse> {
  // Send emailOrPhone as-is to match Laravel API expectations
  const requestData = {
    name: credentials.name,
    emailOrPhone: credentials.emailOrPhone,
    password: credentials.password,
    password_confirmation: credentials.password_confirmation,
  };
  
  if (__DEV__) {
    console.log('🔐 Register: Sending data:', { 
      name: requestData.name, 
      emailOrPhone: requestData.emailOrPhone, 
      password: '[HIDDEN]',
      password_confirmation: '[HIDDEN]'
    });
  }
  
  const response = await client.post('/auth/register', requestData);
  
  if (__DEV__) {
    console.log('🔐 Register API Response:', JSON.stringify(response.data, null, 2));
  }
  
  // Handle Laravel API response format with success wrapper
  if (response.data.success) {
    return {
      token: response.data.token,
      user: response.data.user
    };
  }
  
  // Fallback to direct response (for backward compatibility)
  return response.data;
}

/**
 * Get current user profile
 */
export async function getProfile(): Promise<UserProfile> {
  const response = await client.get('/me');
  return response.data;
}

/**
 * Logout current user (revoke token)
 */
export async function logout(): Promise<void> {
  console.log('🔐 Auth: Starting logout API call');
  try {
    const response = await client.post('/auth/logout');
    console.log('🔐 Auth: Logout API response:', response.data);
  } catch (error) {
    console.error('🔐 Auth: Logout API error:', error);
    throw error;
  }
}

/**
 * Change user password
 */
export async function changePassword(credentials: ChangePasswordCredentials): Promise<{ success: boolean; message: string }> {
  console.log('🔐 Auth: Starting change password API call');
  try {
    const response = await client.post('/auth/change-password', credentials);
    console.log('🔐 Auth: Change password API response:', response.data);
    return response.data;
  } catch (error) {
    console.error('🔐 Auth: Change password API error:', error);
    throw error;
  }
}

/**
 * Refresh user profile from server
 */
export async function refreshProfile(): Promise<UserProfile> {
  const response = await client.get('/me');
  return response.data;
}

/**
 * Upload profile picture
 */
export async function uploadProfilePicture(imageUri: string): Promise<{ success: boolean; message: string; profile_picture_url?: string }> {
  console.log('📸 Auth: Starting profile picture upload');
  
  try {
    // Create form data
    const formData = new FormData();
    
    // Extract filename from URI
    const uriParts = imageUri.split('/');
    const filename = uriParts[uriParts.length - 1];
    
    // Add image to form data
    formData.append('profile_picture', {
      uri: imageUri,
      name: filename || 'profile.jpg',
      type: 'image/jpeg',
    } as any);
    
    console.log('📸 Auth: Uploading image:', filename);
    
    const response = await client.post('/profile/update-picture', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    
    console.log('📸 Auth: Profile picture upload response:', response.data);
    return response.data;
  } catch (error) {
    console.error('📸 Auth: Profile picture upload error:', error);
    throw error;
  }
}
