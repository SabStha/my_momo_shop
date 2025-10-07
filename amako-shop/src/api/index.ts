// Core API exports
export * from './client';
export * from './errors';
export * from './types';

// Services
export * from './services';

// Hooks (excluding auth to avoid conflicts)
export { 
  useHealth,
  useUserProfile,
  useUpdateProfile,
  useCartItems,
  useAddToCart,
  useUpdateCartItem,
  useRemoveFromCart,
  useClearCart,
  useOrders,
  useOrder,
  useCreateOrder,
  useCancelOrder,
  useIsAnyLoading,
  useHasAnyError,
} from './hooks';

// Menu service and hooks
export * from './menu';
export * from './menu-hooks';

// Auth service and hooks
export * from './auth';
export * from './auth-hooks';

// Device management
export * from './devices';

// Loyalty system
export * from './loyalty';

// Reviews system
export * from './reviews';
export * from './reviews-hooks';