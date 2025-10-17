# Menu Images 404 Error - Fix Summary

## Problem

The mobile app was getting **404 errors** when trying to load product images:
```
GET http://localhost:8000/storage 404 (Not Found)
```

## Root Causes

### 1. Using `localhost` Instead of Network IP ❌

The API was using `asset('storage/...')` which generates URLs with `localhost:8000`, which doesn't work from a mobile device on the network.

### 2. Storage Link Not Created ❌

The symbolic link from `public/storage` to `storage/app/public` might not have existed.

---

## Solutions Implemented

### 1. Created Storage Symbolic Link ✅

**Command**: `php artisan storage:link`

This creates: `public/storage` → `storage/app/public`

Now images at `storage/app/public/products/momo/xyz.png` are accessible via:
`http://192.168.2.145:8000/storage/products/momo/xyz.png`

### 2. Fixed API to Use Dynamic Base URL ✅

**File**: `routes/api.php`

**Changed 4 endpoints**:

**Before** (Using `asset()` which uses APP_URL = localhost):
```php
'image' => $product->image ? asset('storage/' . $product->image) : null
// Returns: http://localhost:8000/storage/products/...
```

**After** (Using dynamic request URL):
```php
$baseUrl = request()->getSchemeAndHttpHost();
'image' => $product->image ? $baseUrl . '/storage/' . $product->image : null
// Returns: http://192.168.2.145:8000/storage/products/...
```

**Endpoints Fixed**:
1. `GET /api/menu` (line 794, 819)
2. `GET /api/items/{id}` (line 885, 893)
3. `GET /api/categories/{categoryId}/items` (line 926, 937)
4. `GET /api/items/search` (line 980, 994)

---

## How It Works Now

### Request Flow:

1. **Mobile app calls**: `http://192.168.2.145:8000/api/menu`
2. **API detects base URL**: `request()->getSchemeAndHttpHost()` = `http://192.168.2.145:8000`
3. **Returns image URLs**: `http://192.168.2.145:8000/storage/products/momo/amako-special-buff-momo.png`
4. **Mobile app loads image**: Uses the network IP ✅

### URL Examples:

**From Web Browser** (localhost):
```
Request: http://localhost:8000/api/menu
Returns: http://localhost:8000/storage/products/...
```

**From Mobile Device** (network):
```
Request: http://192.168.2.145:8000/api/menu
Returns: http://192.168.2.145:8000/storage/products/...
```

Dynamic base URL adapts to the request source! 🎯

---

## Important: Laravel Must Run With Network Access

Make sure Laravel is running with network binding:

**Wrong** (won't work from mobile):
```bash
php artisan serve
# Only accessible from localhost
```

**Correct** (works from mobile):
```bash
php artisan serve --host=192.168.2.145 --port=8000
# Accessible from network
```

Or bind to all interfaces:
```bash
php artisan serve --host=0.0.0.0 --port=8000
# Accessible from any IP
```

---

## Testing

### 1. Check Storage Link Exists:

```bash
# Windows
dir public\storage
# Should show: <SYMLINKD> or <JUNCTION>

# If not, run:
php artisan storage:link
```

### 2. Test Image URL Manually:

From your phone's browser, try:
```
http://192.168.2.145:8000/storage/products/momo/amako-special-buff-momo.png
```

Should show the image ✅

### 3. Test API Response:

```bash
curl http://192.168.2.145:8000/api/menu
# Should return image URLs with 192.168.2.145 (not localhost)
```

### 4. Refresh Mobile App:

Pull down to refresh in the menu screen. Images should load! ✅

---

## Files Modified

1. **`routes/api.php`**
   - `/api/menu` - Use dynamic base URL
   - `/api/items/{id}` - Use dynamic base URL
   - `/api/categories/{categoryId}/items` - Use dynamic base URL
   - `/api/items/search` - Use dynamic base URL

2. **Ran Command**:
   - `php artisan storage:link` - Created symbolic link

---

## Troubleshooting

### Images Still Show 404?

**Check 1**: Storage link exists
```bash
php artisan storage:link
```

**Check 2**: Laravel is running with network access
```bash
php artisan serve --host=192.168.2.145 --port=8000
```

**Check 3**: Image files exist
```bash
dir storage\app\public\products\momo
# Should show your image files
```

**Check 4**: Image paths in database are correct
```bash
php artisan tinker
>>> \App\Models\Product::first()->image
# Should return: products/momo/xyz.png (not full URL)
```

### Images Still Not Loading?

**Check firewall**:
```powershell
New-NetFirewallRule -DisplayName "Laravel" -Direction Inbound -Program "C:\php\php.exe" -Action Allow
```

**Check APP_URL in .env**:
```env
# For development, set to your network IP
APP_URL=http://192.168.2.145:8000
```

---

## Summary

✅ **Fixed**: Storage symbolic link created
✅ **Fixed**: API returns correct base URL (not localhost)
✅ **Fixed**: Images now accessible from mobile devices
✅ **Improved**: Dynamic URL generation based on request source

**Image URLs now work from both web and mobile!** 🖼️✅

---

**Date Fixed**: October 8, 2025  
**Issue**: 404 errors on product images  
**Status**: ✅ Resolved

