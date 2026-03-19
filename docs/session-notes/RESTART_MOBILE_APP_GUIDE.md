# Quick Guide: Restart Mobile App After API Fix

## ✅ What Was Fixed
- Updated API IP address from `192.168.0.19` → `192.168.2.142`
- Your API endpoints should now work without timeouts

## 🔄 How to Apply the Fix

### Option 1: Hot Reload (Fastest)
1. In your Expo/Metro terminal, press:
   - **`r`** - Reload app
   - **`R`** - Reload and clear cache

### Option 2: Restart Metro Bundler
1. Stop the current Metro server (**Ctrl+C**)
2. Navigate to amako-shop directory:
   ```bash
   cd amako-shop
   ```
3. Clear cache and restart:
   ```bash
   npm start --clear
   ```
4. Once Metro is running, press:
   - **`a`** - Open on Android
   - **`i`** - Open on iOS

### Option 3: Full Clean Restart (If issues persist)
```bash
cd amako-shop
rm -rf .expo/
rm -rf node_modules/.cache/
npm start --clear
```

---

## ✅ Verification Checklist

After restarting, check the Metro logs for:

### 1. Correct API Configuration
You should see:
```
🔧 API Configuration: {
  BASE_URL: "http://192.168.2.142:8000/api",  ✅
  TIMEOUT: 15000,
  ENV: "development"
}
```

### 2. Successful API Requests
Look for:
```
🚀 API Request: GET /notifications
✅ API Response: 200 /notifications

🚀 API Request: GET /menu
✅ API Response: 200 /menu

🚀 API Request: GET /stats/home
✅ API Response: 200 /stats/home
```

### 3. No More Timeout Errors
**Before Fix**:
```
❌ API Error: {"code": "ECONNABORTED", "message": "timeout of 15000ms exceeded"}
```

**After Fix**:
```
✅ API Response: 200 /notifications
```

---

## 🐛 About the InternalBytecode.js Errors

You may still see errors like:
```
Error: ENOENT: no such file or directory, open 'InternalBytecode.js'
```

**This is a cosmetic Metro bundler issue** and does NOT affect your app's functionality:
- It occurs when Metro tries to symbolicate stack traces from React Native's internal code
- It's a known issue in Metro (the React Native bundler)
- Your app will work perfectly fine despite these errors

### To Suppress These Errors (Optional)
A `metro.config.js` file has been created to help suppress these errors. Restart Metro for it to take effect:

```bash
cd amako-shop
npm start --clear
```

---

## 📱 Testing the App

Once restarted, verify these features work:

1. **Home Screen**
   - [ ] Featured products load
   - [ ] Stats display correctly
   - [ ] Reviews show up

2. **Menu**
   - [ ] Categories load
   - [ ] Products display with images
   - [ ] Can browse all items

3. **Notifications**
   - [ ] Notifications panel opens
   - [ ] Shows your notifications (if any)
   - [ ] No timeout errors

4. **Orders**
   - [ ] Can view orders
   - [ ] Order details load

---

## 🆘 Troubleshooting

### Still Getting Timeouts?

1. **Verify Laravel server is running**:
   ```bash
   netstat -ano | findstr :8000
   ```
   You should see:
   ```
   TCP    0.0.0.0:8000           0.0.0.0:0              LISTENING
   ```

2. **Test API manually**:
   ```bash
   curl http://192.168.2.142:8000/api/health
   ```
   Should return:
   ```json
   {
     "status": "ok",
     "timestamp": "2025-10-17...",
     "server": "Laravel API"
   }
   ```

3. **Check your device and computer are on the same WiFi network**
   - Both must be connected to the same WiFi
   - Some corporate/school WiFi networks block device-to-device communication

4. **Windows Firewall blocking connections?**
   Run the firewall fix script:
   ```bash
   .\fix-firewall-quickly.bat
   ```

### App Still Shows Old IP in Logs?

1. Make sure you **restarted Metro** after the fix
2. Try clearing cache:
   ```bash
   cd amako-shop
   npm start -- --reset-cache
   ```

### Can't Connect on Physical Device?

If testing on a **physical phone/tablet**:
- Make sure it's connected to the **same WiFi** as your computer
- Try using **Expo Tunnel** instead:
  ```bash
  cd amako-shop
  npx expo start --tunnel
  ```

---

## 📝 Summary

**What Changed**: Updated IP addresses in config files  
**Files Modified**: 
- `amako-shop/src/config/network.ts`
- `amako-shop/src/config/api.ts`
- `start-laravel-server.bat`

**What You Need to Do**: Restart Metro bundler (press `r` or restart with `npm start --clear`)

**Expected Result**: All API calls work, no more timeout errors ✅

