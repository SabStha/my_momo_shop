# AmaKo Shop - Troubleshooting Guide

## 🚨 **Current Issues & Solutions**

### **Issue 1: Expo Go Limitations (SDK 53)**
**Problem**: Push notifications don't work in Expo Go with SDK 53
**Solution**: Use a development build instead

### **Issue 2: SessionProvider Context Error**
**Problem**: Components trying to use stores outside SessionProvider
**Solution**: ✅ Fixed - Moved store usage to component level

### **Issue 3: Missing Dependencies**
**Problem**: expo-dev-client not installed
**Solution**: ✅ Fixed - Added to package.json

## 🔧 **Quick Fixes Applied**

1. ✅ **Fixed tabs layout** - Removed store usage from layout level
2. ✅ **Added expo-dev-client** - Required for development builds
3. ✅ **Updated app.json** - Proper plugin configuration
4. ✅ **Updated EAS config** - Development build profiles

## 📱 **Testing Options**

### **Option 1: Expo Go (Limited Functionality)**
```bash
npx expo start
```
- ✅ Basic app functionality works
- ❌ Push notifications won't work
- ❌ Some native features limited
- 🔍 Good for UI testing only

### **Option 2: Development Build (Full Functionality)**
```bash
# Build development APK
node scripts/build-dev.mjs

# Or manually:
eas build --platform android --profile development
```
- ✅ Push notifications work
- ✅ All native features available
- ✅ Production-like environment
- ⏱️ Takes 10-20 minutes to build

## 🚀 **Development Build Process**

### **Step 1: Install EAS CLI**
```bash
npm install -g @expo/eas-cli
```

### **Step 2: Login to Expo**
```bash
eas login
```

### **Step 3: Build Development APK**
```bash
node scripts/build-dev.mjs
```

### **Step 4: Install & Test**
1. Download APK from EAS dashboard
2. Install on Android device
3. Run: `npx expo start --dev-client`
4. Scan QR code with development build

## 🔍 **Testing Push Notifications**

### **In Expo Go (Limited)**
- Test Push button will show API call
- No actual push notifications
- Good for testing API integration

### **In Development Build (Full)**
- Test Push button triggers real notifications
- Local notifications work
- In-app toast displays
- Background/foreground handling works

## 🛠️ **Backend Requirements**

### **Required Endpoints**
```php
// Laravel routes needed:
POST /api/devices - Register device token
POST /api/notify/test - Send test notification
POST /api/notify/order - Send order status updates
```

### **Test Endpoint Example**
```php
Route::post('/api/notify/test', function (Request $request) {
    // Send test push notification to authenticated user
    // This will trigger the Test Push button functionality
});
```

## 📊 **Current Status**

| Feature | Expo Go | Development Build | Production |
|---------|---------|-------------------|------------|
| Basic App | ✅ | ✅ | ✅ |
| Push Notifications | ❌ | ✅ | ✅ |
| Native Features | ⚠️ | ✅ | ✅ |
| Testing Speed | 🚀 | 🐌 | 🐌 |

## 🎯 **Recommended Testing Strategy**

### **Phase 1: UI Testing (Expo Go)**
- Test all screens and navigation
- Verify cart functionality
- Check form validations
- Test responsive design

### **Phase 2: Full Testing (Development Build)**
- Test push notifications
- Verify device registration
- Test order flow
- Check native integrations

### **Phase 3: Production Testing**
- EAS build with production profile
- Test on multiple devices
- Performance testing
- Play Store submission

## 🔧 **Common Issues & Solutions**

### **Issue: "Property 'radius' doesn't exist"**
**Solution**: ✅ Fixed - radius is properly defined in tokens.ts

### **Issue: "useSession must be used within SessionProvider"**
**Solution**: ✅ Fixed - SessionProvider wraps all components properly

### **Issue: "Invalid prop 'style' supplied to React.Fragment"**
**Solution**: ✅ Fixed - Removed invalid style props from fragments

### **Issue: Reanimated plugin warnings**
**Solution**: ✅ Fixed - Using correct `react-native-worklets/plugin`

## 📱 **Device Setup**

### **Android Requirements**
- Android 13+ for POST_NOTIFICATIONS permission
- USB debugging enabled
- Development build installed

### **iOS Requirements**
- iOS 12+ (minimum)
- Development certificate
- Device registered in Apple Developer account

## 🚀 **Next Steps**

1. **For immediate testing**: Use Expo Go for UI testing
2. **For push notifications**: Build development build
3. **For production**: Complete backend integration
4. **For Play Store**: Follow submission checklist

## 📞 **Support**

If you encounter issues:
1. Check this troubleshooting guide
2. Review console logs for specific errors
3. Ensure all dependencies are installed
4. Verify EAS project configuration

---

**Status**: ✅ Critical Issues Fixed  
**Next**: Development Build for Full Testing  
**Target**: Push Notification System Working
