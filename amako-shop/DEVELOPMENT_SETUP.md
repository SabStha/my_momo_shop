# Development Setup Guide

## Prerequisites

- Node.js 18+ 
- npm or yarn
- Expo CLI (`npm install -g @expo/cli`)
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)

## Installation

1. **Clone and install dependencies:**
   ```bash
   cd amako-shop
   npm install
   ```

2. **Environment Configuration:**
   
   Create a `.env` file in the root directory:
   ```bash
   # API Configuration
   EXPO_PUBLIC_API_URL=http://localhost:8000/api
   
   # App Environment
   EXPO_PUBLIC_APP_ENV=development
   ```

   **Important Notes:**
   - For Android emulator: The app automatically uses `http://10.0.2.2:8000/api`
   - For iOS simulator: The app uses `http://localhost:8000/api`
   - For web: The app uses `http://localhost:8000/api`

3. **Backend Setup:**
   
   Ensure your Laravel backend is running on port 8000:
   ```bash
   cd ../
   php artisan serve
   ```

## Running the App

### Development Mode
```bash
npm start
```

### Platform Specific
```bash
# Android
npm run android

# iOS
npm run ios

# Web
npm run web
```

## Configuration

### API URLs
The app automatically detects the platform and uses the appropriate API URL:

- **Android Emulator**: `http://10.0.2.2:8000/api`
- **iOS Simulator**: `http://localhost:8000/api`
- **Web**: `http://localhost:8000/api`
- **Production**: Set `EXPO_PUBLIC_API_URL` environment variable

### Authentication
The app uses secure token storage with Expo SecureStore:

- Tokens are automatically stored after login
- Automatic token refresh and validation
- Secure logout with token revocation

## Troubleshooting

### Common Issues

1. **API Connection Errors:**
   - Ensure Laravel backend is running on port 8000
   - Check firewall settings
   - For Android: Verify emulator network configuration

2. **Authentication Errors:**
   - Clear app storage: `expo start --clear`
   - Check backend authentication routes
   - Verify CORS configuration in Laravel

3. **Build Errors:**
   - Clear cache: `expo start --clear`
   - Delete node_modules and reinstall: `rm -rf node_modules && npm install`
   - Check Expo SDK version compatibility

4. **Maximum Update Depth Error:**
   - This has been fixed in the RouteGuard component
   - If it persists, restart the development server

### Debug Mode
Enable debug logging by setting:
```bash
EXPO_PUBLIC_APP_ENV=development
```

### Network Debugging
The app logs all API requests and responses in development mode. Check the console for:
- 🔧 API Configuration
- 🚀 API Request
- ✅ API Response
- ❌ API Error

## Project Structure

```
amako-shop/
├── app/                    # Expo Router screens
├── src/
│   ├── api/              # API client and hooks
│   ├── components/       # Reusable components
│   ├── config/          # Environment configuration
│   ├── session/         # Authentication management
│   ├── state/           # State management (Zustand)
│   ├── types/           # TypeScript type definitions
│   ├── ui/              # UI components and tokens
│   └── utils/           # Utility functions
├── assets/              # Images and static files
└── config/              # Babel and TypeScript config
```

## Development Workflow

1. **Start development server:** `npm start`
2. **Make changes** to your code
3. **Hot reload** will automatically update the app
4. **Test on different platforms** using the Expo Go app or simulators
5. **Debug** using React Native Debugger or Chrome DevTools

## Backend Integration

The app expects a Laravel backend with the following endpoints:

- `POST /api/auth/login` - User authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/me` - User profile

Ensure your Laravel backend has proper CORS configuration and authentication middleware.

## Performance Tips

- Use React Query for efficient data fetching
- Implement proper error boundaries
- Use React.memo for expensive components
- Optimize images and assets
- Enable Hermes engine for better performance

## Deployment

For production deployment:

1. Set production environment variables
2. Build the app: `expo build:android` or `expo build:ios`
3. Configure production API endpoints
4. Test thoroughly on real devices

## Support

If you encounter issues:

1. Check this documentation
2. Review the console logs
3. Check Expo documentation: https://docs.expo.dev/
4. Review React Native documentation: https://reactnative.dev/
