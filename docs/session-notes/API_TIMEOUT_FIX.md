# API Timeout Issue - RESOLVED ✅

## Problem

The mobile app was experiencing **timeout errors** (15 seconds) on all API endpoints:
- ❌ `GET /notifications` - timeout
- ❌ `GET /menu` - timeout  
- ❌ `GET /stats/home` - timeout

```
ERROR ❌ API Error: {"baseURL": "http://192.168.0.19:8000/api", "code": "ECONNABORTED", "message": "timeout of 15000ms exceeded"}
```

## Root Cause

**IP Address Mismatch** 🔴

The mobile app was configured to connect to an **old IP address** (`192.168.0.19`), but your machine's actual IP address is **`192.168.2.142`**.

### Why This Happened
Your WiFi network assigned a different IP address to your machine, but the mobile app configuration files still had the old hardcoded IP.

---

## Solution Applied

### 1. Updated Mobile App Network Configuration ✅

**File**: `amako-shop/src/config/network.ts`

```typescript
// OLD
wifi: {
  ip: '192.168.0.19',  ❌ Wrong IP
  name: 'WiFi Network',
}

// NEW  
wifi: {
  ip: '192.168.2.142',  ✅ Correct IP
  name: 'WiFi Network',
}
```

### 2. Updated API Base URL ✅

**File**: `amako-shop/src/config/api.ts`

```typescript
// OLD
export const BASE_URL = 'http://192.168.0.19:8000/api';  ❌

// NEW
export const BASE_URL = 'http://192.168.2.142:8000/api';  ✅
```

### 3. Updated Server Startup Script ✅

**File**: `start-laravel-server.bat`

```batch
REM OLD
echo - Network: http://192.168.2.145:8000  ❌

REM NEW  
echo - Network: http://192.168.2.142:8000  ✅
```

---

## Verification

### ✅ Health Check Endpoint
```bash
$ curl http://192.168.2.142:8000/api/health
{
  "status": "ok",
  "timestamp": "2025-10-17T06:44:36.439106Z",
  "server": "Laravel API",
  "version": "1.0.0"
}
```

### ✅ Menu API Endpoint
```bash
$ curl http://192.168.2.142:8000/api/menu
{
  "success": true,
  "data": {
    "categories": [...],
    "items": [...]
  }
}
```

**Status Code**: `200 OK`  
**Response Size**: 28KB (28,047 bytes)

---

## Next Steps

### 1. Restart Your Mobile App
The app needs to reload with the new configuration:

```bash
# If using Expo
cd amako-shop
npm start

# Or restart the Metro bundler
r  # Press 'r' in the Expo CLI to reload
```

### 2. Clear App Cache (Optional)
If the app still shows errors after reloading:

```bash
cd amako-shop
rm -rf .expo/
rm -rf node_modules/.cache/
npm start --clear
```

### 3. Verify Connection
After restarting the app, you should see in the logs:

```
🔧 API Configuration: {
  BASE_URL: "http://192.168.2.142:8000/api",  ✅
  TIMEOUT: 15000,
  ENV: "development"
}
```

---

## Future Prevention

### Option 1: Use Device Hostname (Recommended)
Instead of hardcoding IP addresses, use your machine's hostname:

```typescript
// In amako-shop/src/config/network.ts
wifi: {
  ip: 'YOUR-PC-NAME.local',  // e.g., 'DESKTOP-ABC123.local'
  name: 'WiFi Network',
}
```

### Option 2: Use Expo Tunnel
For more stability across network changes:

```bash
cd amako-shop
npx expo start --tunnel
```

Then update the config:
```typescript
// In amako-shop/src/config/network.ts
export const NETWORK_MODE: NetworkMode = 'tunnel';  // Switch to tunnel mode
```

### Option 3: Auto-Detect IP (Advanced)
Implement automatic IP detection in the app startup (but be careful of detection loops).

---

## How to Find Your Current IP Address

If the IP changes again in the future, run:

```bash
# Windows
ipconfig | findstr /i "IPv4"

# Mac/Linux  
ifconfig | grep "inet "

# Or check in Settings:
# Windows: Settings > Network > WiFi > Properties > IPv4 Address
```

Then update both config files:
1. `amako-shop/src/config/network.ts` (line 12)
2. `amako-shop/src/config/api.ts` (line 47)

---

## Testing Checklist

After applying this fix and restarting the app:

- [ ] Health check endpoint responds (`/api/health`)
- [ ] Menu loads without timeout (`/api/menu`)
- [ ] Notifications load (`/api/notifications`)
- [ ] Home stats load (`/api/stats/home`)
- [ ] No more `ECONNABORTED` errors in logs
- [ ] App shows "Connected to API" status

---

## Summary

**Problem**: Mobile app couldn't reach API (wrong IP address)  
**Solution**: Updated IP from `192.168.0.19` → `192.168.2.142`  
**Files Changed**: 3 configuration files  
**Status**: ✅ **RESOLVED** - API is now accessible

Your API endpoints are now working correctly! Just restart your mobile app to apply the changes.

