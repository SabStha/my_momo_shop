# API Layer Documentation

## Overview
This project includes a robust API layer built with Axios, React Query, and TypeScript. It provides error handling, authentication, retry mechanisms, and a comprehensive set of hooks for data fetching and mutations.

## Architecture

### Core Components
- **Axios Client**: Configured with interceptors for auth and error handling
- **Error Normalization**: Consistent error handling across all API calls
- **React Query Integration**: Efficient data fetching with caching and synchronization
- **TypeScript Types**: Full type safety for all API operations

## File Structure
```
src/api/
├── types.ts          # TypeScript interfaces and types
├── errors.ts         # Error normalization and utilities
├── client.ts         # Axios instance and interceptors
├── services.ts       # API service functions
├── hooks.ts          # React Query hooks
└── index.ts          # Main exports
```

## Features

### 🔐 Authentication
- Automatic token injection via interceptors
- Token refresh handling for expired tokens
- Secure token storage with AsyncStorage

### 🚨 Error Handling
- Normalized error responses
- User-friendly error messages
- Automatic retry for network/server errors
- Development logging

### 🔄 Retry Mechanism
- Smart retry logic (don't retry 4xx errors)
- Exponential backoff
- Configurable retry attempts and delays

### 📱 React Query Integration
- Automatic caching and synchronization
- Background refetching
- Optimistic updates
- Query invalidation

## Usage Examples

### Basic Health Check
```tsx
import { useHealth } from '../src/api';

function HealthStatus() {
  const { data, isLoading, isError, error, refetch } = useHealth();

  if (isLoading) return <Text>Checking...</Text>;
  if (isError) return <Text>Error: {error.message}</Text>;
  
  return (
    <View>
      <Text>Status: {data?.status}</Text>
      <Text>Version: {data?.version}</Text>
    </View>
  );
}
```

### Authentication
```tsx
import { useLogin, useLogout } from '../src/api';

function LoginForm() {
  const login = useLogin();
  
  const handleLogin = async (email: string, password: string) => {
    try {
      const result = await login.mutateAsync({ email, password });
      // Handle successful login
    } catch (error) {
      // Handle error
    }
  };
}
```

### Data Fetching with Parameters
```tsx
import { useMenuItems } from '../src/api';

function MenuList() {
  const { data, isLoading } = useMenuItems({ 
    category: 'drinks',
    search: 'coffee' 
  });
  
  // Component logic
}
```

### Mutations with Cache Updates
```tsx
import { useAddToCart } from '../src/api';

function AddToCartButton({ itemId }) {
  const addToCart = useAddToCart();
  
  const handleAdd = async () => {
    await addToCart.mutateAsync({ itemId, quantity: 1 });
    // Cart cache is automatically updated
  };
}
```

## Configuration

### Environment Variables
```bash
EXPO_PUBLIC_API_URL=https://api.amako-shop.com
```

### API Client Settings
```typescript
const API_CONFIG = {
  BASE_URL: 'https://api.amako-shop.com',
  TIMEOUT: 10000,        // 10 seconds
  RETRY_ATTEMPTS: 3,     // Retry failed requests
  RETRY_DELAY: 1000,     // 1 second initial delay
};
```

### React Query Settings
```typescript
// Global defaults
staleTime: 5 * 60 * 1000,    // 5 minutes
gcTime: 10 * 60 * 1000,      // 10 minutes
retry: Smart retry logic
refetchOnWindowFocus: false
refetchOnReconnect: true
```

## Error Handling

### Error Types
- **Network Errors**: Connection issues, timeouts
- **Server Errors**: 5xx status codes
- **Client Errors**: 4xx status codes
- **Validation Errors**: 400 with details

### Error Normalization
All errors are normalized to a consistent format:
```typescript
interface ApiError {
  message: string;      // User-friendly message
  code?: string;        // Error code for handling
  status?: number;      // HTTP status code
  details?: any;        // Additional error details
}
```

## Available Services

### Health Service
- `check()`: GET /health

### Auth Service
- `login(email, password)`: POST /auth/login
- `refreshToken(token)`: POST /auth/refresh
- `logout()`: POST /auth/logout

### User Service
- `getProfile()`: GET /user/profile
- `updateProfile(data)`: PUT /user/profile

### Menu Service
- `getItems(params)`: GET /menu/items
- `getCategories()`: GET /menu/categories

### Cart Service
- `getItems()`: GET /cart/items
- `addItem(itemId, quantity)`: POST /cart/items
- `updateQuantity(itemId, quantity)`: PUT /cart/items/:id
- `removeItem(itemId)`: DELETE /cart/items/:id
- `clear()`: DELETE /cart/items

### Order Service
- `getOrders(params)`: GET /orders
- `getOrder(orderId)`: GET /orders/:id
- `createOrder(data)`: POST /orders
- `cancelOrder(orderId)`: POST /orders/:id/cancel

## Available Hooks

### Query Hooks
- `useHealth()`: Health check status
- `useUserProfile()`: User profile data
- `useMenuItems(params)`: Menu items with filtering
- `useMenuCategories()`: Menu categories
- `useCartItems()`: Shopping cart contents
- `useOrders(params)`: User orders
- `useOrder(orderId)`: Specific order details

### Mutation Hooks
- `useLogin()`: User authentication
- `useLogout()`: User logout
- `useUpdateProfile()`: Profile updates
- `useAddToCart()`: Add items to cart
- `useUpdateCartItem()`: Update cart quantities
- `useRemoveFromCart()`: Remove cart items
- `useClearCart()`: Clear entire cart
- `useCreateOrder()`: Create new orders
- `useCancelOrder()`: Cancel existing orders

## Best Practices

### 1. Use Hooks for Data Fetching
```tsx
// ✅ Good - Use React Query hooks
const { data, isLoading } = useMenuItems();

// ❌ Bad - Direct API calls
const [data, setData] = useState();
useEffect(() => {
  menuService.getItems().then(setData);
}, []);
```

### 2. Handle Loading and Error States
```tsx
function MyComponent() {
  const { data, isLoading, isError, error } = useMyData();
  
  if (isLoading) return <LoadingSpinner />;
  if (isError) return <ErrorMessage error={error} />;
  
  return <DataDisplay data={data} />;
}
```

### 3. Use Mutations for Data Changes
```tsx
function UpdateButton() {
  const updateMutation = useUpdateData();
  
  const handleUpdate = () => {
    updateMutation.mutate(newData, {
      onSuccess: () => {
        // Handle success
      },
      onError: (error) => {
        // Handle error
      },
    });
  };
}
```

### 4. Leverage Query Invalidation
```tsx
// Queries are automatically invalidated after mutations
const addItem = useAddToCart();
// Cart queries will automatically refresh after adding item
```

## Development Tools

### React Query DevTools
Available in development mode for debugging:
- Query cache inspection
- Request/response monitoring
- Cache invalidation testing

### Debug Logging
In development mode:
- Request logging with emojis
- Response status logging
- Error logging with context

## Testing

### Mock API Responses
```typescript
// Mock health check response
const mockHealthResponse = {
  status: 'healthy' as const,
  timestamp: new Date().toISOString(),
  version: '1.0.0',
  uptime: 3600,
};
```

### Testing Hooks
```typescript
import { renderHook, waitFor } from '@testing-library/react-native';
import { useHealth } from '../src/api';

test('useHealth returns data', async () => {
  const { result } = renderHook(() => useHealth());
  
  await waitFor(() => {
    expect(result.current.data).toBeDefined();
  });
});
```

## Troubleshooting

### Common Issues

1. **Network Errors**: Check internet connection and API URL
2. **Authentication Errors**: Verify token storage and refresh logic
3. **Cache Issues**: Use query invalidation or manual cache updates
4. **Type Errors**: Ensure all API responses match TypeScript interfaces

### Debug Mode
Enable detailed logging in development:
```typescript
if (__DEV__) {
  console.log('API Request:', config);
  console.log('API Response:', response);
}
```

## Future Enhancements

- [ ] Offline support with React Query persistence
- [ ] Request/response compression
- [ ] Advanced caching strategies
- [ ] API rate limiting
- [ ] Request queuing for offline scenarios
