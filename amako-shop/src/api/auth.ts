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
  created_at: string;
  updated_at: string;
}

/**
 * Authenticate user with email/phone and password
 */
export async function login(credentials: LoginCredentials): Promise<LoginResponse> {
  const response = await client.post('/auth/login', credentials);
  return response.data;
}

/**
 * Register a new user
 */
export async function register(credentials: RegisterCredentials): Promise<LoginResponse> {
  const response = await client.post('/auth/register', credentials);
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
  await client.post('/auth/logout');
}

/**
 * Refresh user profile from server
 */
export async function refreshProfile(): Promise<UserProfile> {
  const response = await client.get('/me');
  return response.data;
}
