# 📍 Mobile Live Delivery Tracking - Setup Guide

## ✨ What's Been Added

Your mobile app now has **real-time delivery tracking** with live maps! Customers can see exactly where their delivery driver is on a map with automatic updates every 5 seconds.

### Features Implemented:

1. **🗺️ Live Map View**
   - Google Maps integration
   - Real-time driver location updates
   - Route history visualization
   - User's current location display

2. **📱 Beautiful Tracking Screen**
   - Order status with live indicator
   - Driver information card
   - Delivery address display
   - Tracking history timeline
   - Pull-to-refresh functionality

3. **🔄 Auto-Refresh**
   - Location updates every 5 seconds when order is out for delivery
   - Automatic map centering on driver location
   - Background updates while screen is active

4. **👤 Driver Info**
   - Driver name and contact
   - Current location marker
   - Delivery route path

---

## 🚀 Setup Instructions

### Step 1: Install Dependencies

Navigate to the mobile app directory and install the new package:

```bash
cd amako-shop
npm install
```

Or if using yarn:

```bash
cd amako-shop
yarn install
```

### Step 2: Get Google Maps API Keys

You need Google Maps API keys for both Android and iOS (even if only testing on one platform).

#### For Android:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable these APIs:
   - Maps SDK for Android
   - Maps SDK for iOS (for iOS)
4. Go to **Credentials** → **Create Credentials** → **API Key**
5. Restrict the key:
   - **Android apps**: Add your package name `com.amako.shop` and SHA-1 certificate
   - **iOS apps**: Add your bundle identifier `com.amako.shop`

#### Get SHA-1 Certificate (Android):

For development (debug):
```bash
cd amako-shop/android
keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android
```

Copy the SHA-1 fingerprint and add it to your Google Cloud Console API key restrictions.

### Step 3: Add API Keys to App Configuration

Edit `amako-shop/app.json` and replace the placeholder API keys:

```json
{
  "expo": {
    "ios": {
      "config": {
        "googleMapsApiKey": "YOUR_ACTUAL_IOS_KEY_HERE"
      }
    },
    "android": {
      "config": {
        "googleMaps": {
          "apiKey": "YOUR_ACTUAL_ANDROID_KEY_HERE"
        }
      }
    }
  }
}
```

### Step 4: Rebuild Your App

After adding the API keys, you need to rebuild the native app:

```bash
cd amako-shop

# For Android
npx expo prebuild --clean
npx expo run:android

# For iOS (Mac only)
npx expo prebuild --clean
npx expo run:ios
```

**Note:** If you're using Expo Go, maps won't work. You must build a development build.

---

## 🎯 How to Use

### For Customers (Mobile App):

1. **Place an Order** → Complete checkout
2. **View Order Details** → Go to Orders tab → Tap on your order
3. **Track Delivery** → When status is "Out for Delivery", tap **"📍 Track Delivery"** button
4. **Live Tracking Screen Opens:**
   - See driver's real-time location on map
   - View driver information
   - Check delivery address
   - See tracking history
   - Pull down to manually refresh
   - Auto-updates every 5 seconds

### For Testing:

1. **Create a test order** through the mobile app
2. **On web browser** → Go to `http://localhost:8000/admin/orders` (Payment Manager)
3. **Confirm order** → Click "Confirm Order"
4. **Mark as ready** → Click "Mark as Ready" button
5. **Switch to delivery driver** → Go to `http://localhost:8000/delivery`
6. **Accept delivery** → Click "Accept Delivery" on the order
7. **Back to mobile app** → Order status changes to "Out for Delivery"
8. **Open tracking** → Tap "📍 Track Delivery"
9. **See live location** → Driver's location appears on map
10. **Driver location updates automatically** every 10 seconds from the driver's dashboard

---

## 📁 New Files Created

### Mobile App:
- `amako-shop/app/order-tracking/[id].tsx` - Main tracking screen component

### Modified Files:
- `amako-shop/app/order/[id].tsx` - Added "Track Delivery" button
- `amako-shop/app/_layout.tsx` - Added tracking route
- `amako-shop/package.json` - Added react-native-maps
- `amako-shop/app.json` - Added Google Maps config

---

## 🔧 API Endpoint Used

The tracking screen uses this endpoint to fetch driver location:

```
GET /api/orders/{orderId}/tracking
```

**Response:**
```json
{
  "success": true,
  "tracking": [
    {
      "id": 1,
      "order_id": 123,
      "driver_id": 5,
      "status": "accepted",
      "latitude": "27.7172",
      "longitude": "85.3240",
      "created_at": "2025-10-16 18:30:00",
      "driver": {
        "id": 5,
        "name": "Ram Delivery",
        "email": "ram@example.com",
        "phone": "+977-9841234567"
      }
    },
    {
      "id": 2,
      "order_id": 123,
      "driver_id": 5,
      "status": "location_update",
      "latitude": "27.7180",
      "longitude": "85.3245",
      "created_at": "2025-10-16 18:30:10",
      "driver": {...}
    }
  ]
}
```

---

## 🎨 UI Features

### Map View:
- **Driver Marker**: Blue circle with bicycle icon
- **Route Line**: Blue line showing path traveled
- **User Location**: Red marker (your location)
- **Auto-centering**: Map follows driver automatically

### Status Indicators:
- **Green badge**: Delivered
- **Blue badge**: Out for Delivery
- **Orange badge**: Preparing
- **Red "LIVE" indicator**: Shows when actively tracking

### Cards:
- **Order Status Card**: Current order status
- **Driver Info Card**: Driver name, phone, avatar
- **Delivery Address Card**: Full address with directions
- **Tracking History Card**: Timeline of all location updates

---

## 🐛 Troubleshooting

### Map Not Showing:

1. **Check API Key**: Make sure you added valid Google Maps API keys
2. **Enable APIs**: Verify Maps SDK is enabled in Google Cloud Console
3. **Rebuild App**: Run `npx expo prebuild --clean` and rebuild
4. **Check Permissions**: Location permissions must be granted on device
5. **Not Using Expo Go**: Maps require development build, won't work in Expo Go

### No Driver Location:

1. **Check Order Status**: Tracking only works when status is "out_for_delivery"
2. **Driver Must Accept**: Driver must click "Accept Delivery" on web dashboard
3. **Check API Endpoint**: Visit `http://localhost:8000/api/orders/{orderId}/tracking` in browser
4. **Driver Location Updates**: Driver's location is sent every 10 seconds from delivery dashboard

### Location Permission Denied:

1. **iOS**: Settings → Your App → Location → "While Using the App"
2. **Android**: Settings → Apps → Your App → Permissions → Location → Allow

### Map Shows Gray Screen:

- Usually means invalid API key or API not enabled
- Check Google Cloud Console credentials
- Make sure billing is enabled on Google Cloud (required for Maps)

---

## 🌟 Future Enhancements (Optional)

Possible improvements you could add:

1. **ETA Calculation**: Show estimated time to delivery
2. **Directions**: Navigate to delivery location
3. **Chat with Driver**: In-app messaging
4. **Push Notifications**: Alert when driver is nearby
5. **Offline Mode**: Cache last known location
6. **Multiple Stops**: Show if driver has other deliveries
7. **Driver Photo**: Display driver's profile picture
8. **Call Driver**: Direct call button
9. **Distance Display**: Show distance to destination
10. **Speed Display**: Show driver's current speed

---

## 📊 Technical Details

### Performance:
- **Polling Interval**: 5 seconds (configurable)
- **Map Updates**: Smooth animations with 1-second duration
- **Memory**: Efficient - only active when screen is visible
- **Network**: Minimal data usage (~1KB per request)

### Security:
- API key restricted to your app package/bundle
- Location data only accessible for user's own orders
- Driver identity verified on backend

### Compatibility:
- **iOS**: 13.0+
- **Android**: 5.0+ (API level 21+)
- **Expo SDK**: 54+
- **React Native**: 0.81+

---

## ✅ Testing Checklist

- [ ] Install dependencies (`npm install`)
- [ ] Add Google Maps API keys to `app.json`
- [ ] Rebuild app (`npx expo prebuild --clean`)
- [ ] Run app on device/emulator
- [ ] Grant location permissions
- [ ] Create test order
- [ ] Mark order as ready (web)
- [ ] Accept delivery (web delivery dashboard)
- [ ] Open tracking in mobile app
- [ ] Verify map loads
- [ ] Verify driver location shows
- [ ] Verify auto-refresh works
- [ ] Test pull-to-refresh
- [ ] Check all info cards display correctly

---

## 📞 Support

If you encounter issues:

1. Check the console logs in the mobile app
2. Check the Laravel logs: `storage/logs/laravel.log`
3. Verify the API endpoint works in browser
4. Check Google Cloud Console for API usage/errors
5. Make sure delivery driver dashboard has no errors

---

## 🎉 You're All Set!

Your customers can now track their deliveries in real-time with a beautiful, professional tracking interface!

**Next Steps:**
1. Install dependencies
2. Add Google Maps API keys
3. Rebuild the app
4. Test with a real delivery

Enjoy your new live tracking feature! 🚀📍




