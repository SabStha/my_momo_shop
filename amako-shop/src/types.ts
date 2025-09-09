// Money type for Nepalese Rupees
export interface Money {
  currency: "NPR";
  amount: number; // amount in rupees
}

// Add-on item for menu items
export interface AddOn {
  id: string;
  name: string;
  price: Money;
}

// Variant option for menu items
export interface Variant {
  id: string;
  name: string;
  priceDiff: Money; // price difference from base price
}

// Menu category
export interface Category {
  id: string;
  name: string;
}

// Menu item with variants and add-ons
export interface MenuItem {
  id: string;
  name: string;
  desc?: string;
  imageUrl?: string;
  basePrice: Money;
  variants?: Variant[];
  addOns?: AddOn[];
  categoryId: string;
  isAvailable: boolean;
}

// Cart line item with variants and add-ons
export interface CartLine {
  itemId: string;
  name: string;
  unitBasePrice: Money;
  variantId?: string;
  variantName?: string;
  addOns?: { id: string; name: string; price: Money }[];
  qty: number;
  imageUrl?: string;
}

// API error response
export interface ApiError {
  message: string;
  code?: string;
  status?: number;
  details?: any;
}

// Network error type
export interface NetworkError extends ApiError {
  status: 0;
  code: 'NETWORK_ERROR';
}

// Common response wrapper
export interface ApiResponse<T = any> {
  data: T;
  message?: string;
  success: boolean;
}

// Pagination response
export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

// Common request parameters
export interface PaginationParams {
  page?: number;
  limit?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export interface SearchParams extends PaginationParams {
  query?: string;
  filters?: Record<string, any>;
}
