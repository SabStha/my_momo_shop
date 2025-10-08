# Registration 500 Server Error - Fix Summary

## Problem

When users tried to register, they received a 500 Server Error:
```
ERROR Registration failed: {
  "code": "SERVER_ERROR", 
  "details": {"message": "Server Error"}, 
  "message": "Server error. Please try again later.", 
  "status": 500
}
```

## Root Cause

The registration endpoint in Laravel was attempting to assign a 'user' role to newly registered users, but **the 'user' role did not exist in the database**, causing an exception that resulted in a 500 error.

### What Was Happening:

1. User fills registration form and submits
2. Backend validates the data ✅
3. Backend creates the user successfully ✅
4. Backend tries to assign 'user' role: `$user->assignRole('user')` ❌
5. Role doesn't exist → Exception thrown
6. No error handling → 500 error returned to app

## Solutions Implemented

### 1. Created the 'user' Role

**Script**: `check_and_create_user_role.php`

Ran script to:
- ✅ Check if 'user' role exists
- ✅ Create the 'user' role in the database
- ✅ Assign the role to 2 existing users who didn't have roles

**Result**:
```
✅ 'user' role created successfully (ID: 1)
✅ Assigned 'user' role to 2 existing users
```

### 2. Added Error Handling to Registration Endpoint

**File**: `routes/api.php` (lines 90-174)

**Changes Made**:

1. **Wrapped everything in try-catch**:
   ```php
   try {
       // Registration logic
   } catch (\Illuminate\Validation\ValidationException $e) {
       // Handle validation errors
   } catch (\Exception $e) {
       // Handle general errors
   }
   ```

2. **Added comprehensive logging**:
   ```php
   \Log::info('Registration attempt', ['name' => $request->name]);
   \Log::info('User created successfully', ['user_id' => $user->id]);
   \Log::error('Registration failed', ['error' => $e->getMessage()]);
   ```

3. **Made role assignment safe**:
   ```php
   // Assign default role (user) - only if role exists
   try {
       if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
           $user->assignRole('user');
           \Log::info('User role assigned');
       } else {
           \Log::warning('User role does not exist, skipping role assignment');
       }
   } catch (\Exception $roleError) {
       \Log::error('Failed to assign role');
       // Continue without role - don't fail registration
   }
   ```

4. **Better error responses**:
   ```php
   return response()->json([
       'success' => false,
       'message' => 'Registration failed: ' . $e->getMessage()
   ], 500);
   ```

## Benefits of These Changes

### Before:
- ❌ Cryptic 500 error with no details
- ❌ No logs to debug the issue
- ❌ Registration completely failed
- ❌ No graceful degradation

### After:
- ✅ Detailed error messages returned to app
- ✅ Comprehensive logging for debugging
- ✅ Registration succeeds even if role assignment fails
- ✅ Graceful handling of missing roles
- ✅ Easier to diagnose future issues

## Testing

### Before Fix:
```bash
# Try to register
POST /auth/register
{
  "name": "Test User",
  "emailOrPhone": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

# Response: 500 Server Error ❌
```

### After Fix:
```bash
# Try to register
POST /auth/register
{
  "name": "Test User",
  "emailOrPhone": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

# Response: 201 Created ✅
{
  "success": true,
  "message": "User registered successfully",
  "token": "...",
  "user": { ... }
}
```

## How to Test

1. **Clear app data** (to test fresh registration):
   ```
   Settings > Apps > Expo Go > Storage > Clear Data
   ```

2. **Restart mobile app**:
   ```bash
   cd amako-shop
   npx expo start --clear
   ```

3. **Try registration**:
   - Open app → Sign Up
   - Fill all fields:
     - Name: "Test User"
     - Email: "newuser@example.com"
     - Password: "password123"
     - Confirm: "password123"
   - Click "Sign Up"
   - Should succeed ✅

## Logs to Check

If registration still fails, check Laravel logs:

**PowerShell**:
```powershell
Get-Content storage/logs/laravel.log -Tail 100
```

**Look for**:
- `Registration attempt` - Shows registration started
- `User created successfully` - User was created
- `User role assigned` - Role was assigned
- `Registration successful` - Complete success
- `Registration failed` - If there's an error, it will show details

## Related Scripts

### check_and_create_user_role.php
Run this script anytime to:
- Check if 'user' role exists
- Create it if missing
- Assign it to users without roles

**Usage**:
```bash
php check_and_create_user_role.php
```

## Database State

After running the fix script:

### Roles Table:
| ID | Name | Guard Name |
|----|------|------------|
| 1  | user | web        |

### Users with Roles:
- All existing users now have 'user' role
- New registrations will automatically get 'user' role

## Prevention

To prevent similar issues in the future:

1. **Always seed default roles**:
   ```php
   // database/seeders/RolesSeeder.php
   Role::firstOrCreate(['name' => 'user']);
   Role::firstOrCreate(['name' => 'admin']);
   ```

2. **Add error handling** to all API endpoints

3. **Add comprehensive logging** for debugging

4. **Test error cases**, not just happy paths

5. **Use graceful degradation** - don't fail completely if a non-critical step fails

## Files Modified

1. **`routes/api.php`**
   - Added try-catch error handling
   - Added comprehensive logging
   - Made role assignment safe
   - Better error responses

2. **`check_and_create_user_role.php`** (new file)
   - Script to check and create 'user' role
   - Assigns roles to users without them

## Impact

✅ **Fixed**: Registration 500 error resolved
✅ **Fixed**: 'user' role now exists in database
✅ **Fixed**: Existing users now have 'user' role
✅ **Improved**: Better error handling for all registration errors
✅ **Improved**: Comprehensive logging for debugging
✅ **Enhanced**: Graceful degradation if role assignment fails

---

**Date Fixed**: October 8, 2025
**Issue**: 500 Server Error on registration
**Root Cause**: Missing 'user' role in database
**Status**: ✅ Resolved

