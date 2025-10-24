# 🔍 **Expo Debug Testing Guide - Login Flow**

## 🎯 **What to Test**

I've added comprehensive debug logging to track every step of the login process. This will help us identify exactly where any issues occur.

## 📱 **How to Test in Expo**

### **Step 1: Start Expo with Debug Logs**
```bash
npx expo start --tunnel
```

### **Step 2: Watch the Console Logs**
When you test the login, you'll see detailed logs with these prefixes:

- 🚀 **[LOGIN DEBUG]** - Login process steps
- 🔄 **[SESSION DEBUG]** - Session management
- 🛒 **[CART DEBUG]** - Cart synchronization
- 🛡️ **[ROUTE DEBUG]** - Navigation and routing
- 🌐 **[API DEBUG]** - API requests and responses

## 🔍 **What to Look For**

### **✅ Expected Login Flow:**
```
🚀 [LOGIN DEBUG] ===== LOGIN SUCCESS START =====
🚀 [LOGIN DEBUG] Step 1: Starting token storage...
🚀 [LOGIN DEBUG] Step 1: ✅ Token stored successfully
🚀 [LOGIN DEBUG] Step 2: Resetting 401 counter...
🚀 [LOGIN DEBUG] Step 2: ✅ 401 counter reset
🚀 [LOGIN DEBUG] Step 3: Waiting for token propagation (1000ms)...
🚀 [LOGIN DEBUG] Step 3: ✅ Token propagation delay complete
🚀 [LOGIN DEBUG] Step 4: Invalidating profile queries...
🚀 [LOGIN DEBUG] Step 4: ✅ Profile queries invalidated
🚀 [LOGIN DEBUG] Step 5: Clearing login flag...
🚀 [LOGIN DEBUG] Step 5: ✅ Login flag cleared
🚀 [LOGIN DEBUG] Step 6: Preparing navigation (100ms delay)...
🚀 [LOGIN DEBUG] Step 6: Attempting navigation...
🚀 [LOGIN DEBUG] Step 6: ✅ Navigation successful - replaced with /(tabs)
🚀 [LOGIN DEBUG] ===== LOGIN SUCCESS END =====
```

### **🔄 Expected Session Flow:**
```
🔄 [SESSION DEBUG] ===== SETTING AUTH TOKEN START =====
🔄 [SESSION DEBUG] Step 1: Storing token in secure storage...
🔄 [SESSION DEBUG] Step 1: ✅ Token stored in secure storage
🔄 [SESSION DEBUG] Step 2: Updating session state...
🔄 [SESSION DEBUG] Step 2: ✅ Session state updated
🔄 [SESSION DEBUG] Step 3: Scheduling cart sync (1000ms delay)...
🔄 [SESSION DEBUG] Step 3: Starting delayed cart sync...
🔄 [SESSION DEBUG] Step 3: ✅ Cart sync completed successfully
🔄 [SESSION DEBUG] ===== SETTING AUTH TOKEN END =====
```

### **🛒 Expected Cart Sync Flow:**
```
🛒 [CART DEBUG] ===== LOADING FROM SERVER START =====
🛒 [CART DEBUG] Step 1: Setting sync in progress...
🛒 [CART DEBUG] Step 1: ✅ Sync in progress set
🛒 [CART DEBUG] Step 2: Making API call to /cart...
🛒 [CART DEBUG] Step 2: ✅ API call successful
🛒 [CART DEBUG] Step 3: Processing server items...
🛒 [CART DEBUG] Step 4: Updating cart state...
🛒 [CART DEBUG] Step 4: ✅ Cart state updated successfully
🛒 [CART DEBUG] ===== LOADING FROM SERVER END =====
```

### **🛡️ Expected Route Guard Flow:**
```
🛡️ [ROUTE DEBUG] ===== REDIRECTING AUTHENTICATED USER =====
🛡️ [ROUTE DEBUG] From: auth screens
🛡️ [ROUTE DEBUG] To: /(tabs)/home
🛡️ [ROUTE DEBUG] Step 1: Set redirecting to true
🛡️ [ROUTE DEBUG] Step 2: Attempting router.replace...
🛡️ [ROUTE DEBUG] Step 2: ✅ Navigation successful
🛡️ [ROUTE DEBUG] Step 3: Clearing redirecting state...
🛡️ [ROUTE DEBUG] Step 3: ✅ Redirect complete
```

### **🌐 Expected API Flow:**
```
🌐 [API DEBUG] ===== API REQUEST START =====
🌐 [API DEBUG] Step 1: Retrieving token...
🌐 [API DEBUG] Step 1: ✅ Token found, adding to headers
🌐 [API DEBUG] ===== API REQUEST END =====
🌐 [API DEBUG] ===== API RESPONSE SUCCESS =====
🌐 [API DEBUG] ===== API RESPONSE SUCCESS END =====
```

## ⚠️ **Red Flags to Watch For**

### **❌ Critical Errors:**
- `🚀 [LOGIN DEBUG] ❌ CRITICAL ERROR in post-login flow`
- `🔄 [SESSION DEBUG] ❌ Cart sync failed during login`
- `🛒 [CART DEBUG] ❌ Cart load error`
- `🛡️ [ROUTE DEBUG] ❌ Navigation failed`
- `🌐 [API DEBUG] ❌ Multiple 401 errors detected`

### **⚠️ Warning Signs:**
- `🚀 [LOGIN DEBUG] Step 4: ⚠️ Profile invalidation failed`
- `🛒 [CART DEBUG] ⚠️ 401 error - token may not be ready yet`
- `🌐 [API DEBUG] ⚠️ 401 during login, ignoring`

## 🧪 **Testing Steps**

### **1. Test Login Process**
1. Open the app in Expo
2. Navigate to login screen
3. Enter your credentials
4. Tap "Sign In"
5. **Watch the console logs carefully**

### **2. Check for Issues**
Look for any of these patterns:

**✅ Good Pattern:**
- All steps complete with ✅
- No ❌ errors
- Smooth navigation to home screen

**❌ Bad Pattern:**
- Any step fails with ❌
- Multiple 401 errors
- Navigation failures
- App crashes or freezes

### **3. Document Issues**
If you see any ❌ errors, note:
- Which step failed
- What the error message says
- When it happens (immediately or after delay)

## 📊 **Debug Information to Collect**

If issues occur, please share:

1. **Console Logs** - Copy the entire login flow logs
2. **Error Messages** - Any ❌ error messages
3. **Timing** - When exactly the issue occurs
4. **Behavior** - What you see on screen vs. what should happen

## 🎯 **Expected Results**

After the fixes, you should see:

✅ **Smooth Login Flow** - All steps complete successfully  
✅ **No 401 Errors** - Token propagation works correctly  
✅ **Successful Navigation** - App navigates to home screen  
✅ **Cart Sync Works** - Cart loads without errors  
✅ **No Crashes** - App remains stable throughout  

## 🚀 **Next Steps**

1. **Test in Expo first** - Use this guide to identify any issues
2. **Fix any problems** - Address issues found in testing
3. **Build APK** - Only after successful Expo testing
4. **Test APK** - Verify the fixes work in production build

---

**Remember**: The detailed logs will show us exactly where any problems occur, making it much easier to fix them before building! 🎉

