# 🗺️ How to Test the Live Tracking Map

## **✅ Setup Complete:**
- Google Maps API Key added: `AIzaSyCgas0A0JVwVLZefRXJ-e4qpkam1TdEf2A`
- Test mode enabled (shows demo map when no real driver data)
- Dev server restarted

---

## **📱 To See the Map Right Now:**

### **Step 1: Change an Order Status**
On your server, run:
```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan tinker
```

Then:
```php
$order = \App\Models\Order::latest()->first();
$order->status = 'out_for_delivery';
$order->save();
exit
```

### **Step 2: View in Mobile App**
1. **Refresh your mobile app** (shake device → "Reload")
2. Go to **Orders**
3. Click on that order
4. Click **"Track Delivery"** button
5. You should see:
   - 🟠 Orange banner: **"⚠️ TEST MODE: Demo Map"**
   - 🗺️ **Google Map** with:
     - 🚴 Pink driver marker (moving randomly)
     - 🏠 Red home marker (delivery address)

---

## **🔍 What to Check:**

### **✅ Map Appears:**
- You see a Google Map (not blank/error)
- Map shows Kathmandu area
- Driver and delivery markers visible

### **✅ API Key Works:**
- No "Google Maps API Key" error
- No watermark saying "For development purposes only"
- Map loads smoothly

### **❌ If Map Doesn't Show:**

#### **Option 1: react-native-maps needs native build**
```bash
# Build development APK with native modules
cd C:\Users\user\my_momo_shop\amako-shop
npx eas-cli build --platform android --profile preview
```

#### **Option 2: Enable Google Maps API in Console**
1. Go to: https://console.cloud.google.com/
2. Enable these APIs:
   - Maps SDK for Android
   - Maps SDK for iOS
3. Wait 2-3 minutes for activation

---

## **🚀 For Production:**

When building final APK:
```bash
npx eas-cli build --platform android --profile production --non-interactive
```

The map will work if you have:
- ✅ Google Maps API enabled
- ✅ API key in `app.json`
- ✅ Order status = `'out_for_delivery'`
- ✅ Backend sends driver tracking data

---

## **🧪 Test Mode Details:**

**Current behavior:**
- If order is "out_for_delivery" BUT no real driver location
- Shows demo map with random driver position
- Only in development mode (`__DEV__`)
- Will be removed in production builds

**To remove test mode:**
Delete lines 294-311 in `amako-shop/app/order-tracking/[id].tsx`

---

## **📊 Check Map Loading:**

Look for these logs in your terminal/app:
```
🗺️ OrderTrackingScreen: ID param: order_1
🗺️ OrderTrackingScreen: Numeric ID: 1
```

If you see errors about "react-native-maps", you need a native build (not Expo Go).

