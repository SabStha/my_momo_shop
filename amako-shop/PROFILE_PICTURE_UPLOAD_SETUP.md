# Profile Picture Upload Feature - Setup Guide

## What Was Added ✅

Added functionality to upload/change profile pictures in the Profile screen!

### Features:
- 📸 **Take Photo** - Capture new photo with camera
- 🖼️ **Choose from Gallery** - Select existing photo
- ✏️ **Easy Access** - Tap camera icon on profile picture
- ⏳ **Upload Progress** - Visual feedback during upload
- 🔄 **Auto Refresh** - Profile updates immediately after upload

---

## 🚀 Step 1: Install Required Package

You need to install `expo-image-picker` to enable image selection.

### Run this command:

```bash
cd amako-shop
npx expo install expo-image-picker
```

**This will:**
- Install expo-image-picker
- Configure it for your Expo project
- Add necessary permissions

---

## 🔧 Step 2: Setup Laravel Backend

You need to create an endpoint in your Laravel backend to handle the profile picture upload.

### Create Controller Method

Add this to your `UserController.php` or `ProfileController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Update user's profile picture
     */
    public function updateProfilePicture(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        try {
            $user = Auth::user();
            
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                // Extract path from URL if it's a full URL
                $oldPath = str_replace(url('storage') . '/', '', $user->profile_picture);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            
            // Update user record
            $user->profile_picture = url('storage/' . $path);
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'profile_picture_url' => $user->profile_picture,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Profile picture upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

### Add Route

Add this to your `routes/api.php`:

```php
use App\Http\Controllers\ProfileController;

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // ... your other routes ...
    
    Route::post('/profile/update-picture', [ProfileController::class, 'updateProfilePicture']);
});
```

### Create Storage Directory

Make sure the profile-pictures directory exists:

```bash
mkdir -p storage/app/public/profile-pictures
```

### Create Symbolic Link (if not already done)

```bash
php artisan storage:link
```

---

## 📱 Step 3: Test the Feature

1. **Stop and restart your servers:**
   ```bash
   # Laravel
   php -S 0.0.0.0:8000 -t public
   
   # Expo (in amako-shop directory)
   npm run start:tunnel
   ```

2. **Open the app on your phone**

3. **Go to Profile tab**

4. **Tap the camera icon** on the profile picture

5. **Choose an option:**
   - 📸 Take Photo - Opens camera
   - 🖼️ Choose from Gallery - Opens photo library

6. **Select/Take a photo**

7. **Photo will upload and update automatically!** ✨

---

## 🎨 How It Works

### Frontend (React Native):
1. User taps camera icon on profile picture
2. Shows alert with options (Camera or Gallery)
3. Opens image picker
4. User selects/takes photo
5. Photo is uploaded to Laravel API
6. Profile refreshes to show new picture

### Backend (Laravel):
1. Receives image file via multipart/form-data
2. Validates file (type, size)
3. Deletes old profile picture (if exists)
4. Stores new image in `storage/app/public/profile-pictures/`
5. Updates user record with new URL
6. Returns success response

---

## 🔐 Permissions

The app automatically requests permissions for:
- 📷 **Camera** - When taking photo
- 🖼️ **Photo Library** - When choosing from gallery

These permissions are handled automatically by expo-image-picker!

---

## 🎯 Features Included

✅ Camera capture
✅ Gallery selection  
✅ Image cropping (1:1 aspect ratio)
✅ Image compression (0.8 quality)
✅ Upload progress indicator
✅ Auto-refresh after upload
✅ Error handling
✅ Permission requests
✅ Loading states

---

## 🐛 Troubleshooting

### Issue: "expo-image-picker not found"
**Solution:** Run `npx expo install expo-image-picker`

### Issue: "Permission denied"
**Solution:** Check app permissions in device settings

### Issue: "Upload failed - 401 Unauthorized"
**Solution:** Make sure you're logged in and token is valid

### Issue: "Upload failed - 500 Server Error"
**Solution:** Check Laravel logs: `tail -f storage/logs/laravel.log`

### Issue: Image not showing after upload
**Solution:** 
- Check if `storage:link` is created
- Verify `storage/app/public/profile-pictures` exists
- Check file permissions: `chmod -R 775 storage`

---

## 📝 Summary

### What You Need to Do:

1. ✅ Install expo-image-picker: `npx expo install expo-image-picker`
2. ✅ Add Laravel controller method (see above)
3. ✅ Add route to `api.php`
4. ✅ Create storage directory
5. ✅ Test the feature!

### Already Done For You:

✅ React Native UI with camera button
✅ Image picker integration
✅ Upload API call
✅ Error handling
✅ Loading states
✅ Auto-refresh
✅ Beautiful UI

---

**Now users can easily update their profile pictures!** 📸✨

