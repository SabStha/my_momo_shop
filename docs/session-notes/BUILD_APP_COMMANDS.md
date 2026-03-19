# 📱 Mobile App Build Commands

Quick reference for building the Amako Shop mobile app using EAS Build.

---

## 🚀 **Quick Build Commands**

### **Build Android APK (for testing):**
```bash
cd amako-shop
eas build --platform android --profile preview --non-interactive
```

### **Build Android AAB (for Google Play Store):**
```bash
cd amako-shop
eas build --platform android --profile production --non-interactive
```

### **Build Both Platforms (Android + iOS):**
```bash
cd amako-shop
eas build --platform all --profile production --non-interactive
```

---

## 🔧 **Step-by-Step Process**

### **1. Navigate to the mobile app directory:**
```bash
cd amako-shop
```

### **2. Install dependencies (if needed):**
```bash
npm install
```

### **3. Choose your build:**

#### **For Testing (APK - installs directly):**
```bash
eas build --platform android --profile preview --non-interactive
```
- ✅ Generates an APK file
- ✅ Can be installed directly on Android devices
- ✅ Good for testing before Play Store submission
- ⏱️ Takes ~10-15 minutes

#### **For Production (AAB - Google Play Store):**
```bash
eas build --platform android --profile production --non-interactive
```
- ✅ Generates an AAB file (required by Google Play)
- ✅ Optimized and signed for Play Store
- ✅ Includes all optimizations
- ⏱️ Takes ~15-20 minutes

---

## 🌐 **Build in Background (Run and Forget)**

To run the build in the background and close your terminal:

### **Option 1: Using `nohup` (keeps running if you close terminal):**
```bash
cd amako-shop
nohup eas build --platform android --profile production --non-interactive > build.log 2>&1 &
```

Then you can:
- Close your terminal
- Check progress later: `tail -f amako-shop/build.log`

### **Option 2: Using `screen` (recommended for long builds):**
```bash
# Start a new screen session
screen -S app-build

# Inside the screen, run the build
cd amako-shop
eas build --platform android --profile production --non-interactive

# Detach from screen: Press Ctrl+A, then D

# Later, reattach to see progress:
screen -r app-build
```

### **Option 3: Using `tmux` (if installed):**
```bash
# Start a new tmux session
tmux new -s app-build

# Inside tmux, run the build
cd amako-shop
eas build --platform android --profile production --non-interactive

# Detach: Press Ctrl+B, then D

# Reattach later:
tmux attach -t app-build
```

---

## 📊 **Check Build Status**

### **From your computer:**
```bash
cd amako-shop
eas build:list
```

### **Or visit:**
https://expo.dev/accounts/YOUR_ACCOUNT/projects/amako-shop/builds

---

## 🎯 **Recommended Build Flow**

### **For Daily Testing:**
```bash
cd amako-shop
eas build --platform android --profile preview --non-interactive
```

### **For Play Store Submission:**
```bash
cd amako-shop
eas build --platform android --profile production --non-interactive
```

### **For iOS App Store:**
```bash
cd amako-shop
eas build --platform ios --profile production --non-interactive
```

---

## ⚡ **One-Command Builds (Copy-Paste)**

### **Quick Test Build (Background):**
```bash
cd amako-shop && nohup eas build --platform android --profile preview --non-interactive > build-$(date +%Y%m%d-%H%M%S).log 2>&1 & echo "Build started! Check logs with: tail -f amako-shop/build-*.log"
```

### **Production Build (Background):**
```bash
cd amako-shop && nohup eas build --platform android --profile production --non-interactive > build-production-$(date +%Y%m%d-%H%M%S).log 2>&1 & echo "Production build started! Check logs with: tail -f amako-shop/build-production-*.log"
```

### **Full Build Both Platforms (Background):**
```bash
cd amako-shop && nohup eas build --platform all --profile production --non-interactive > build-all-$(date +%Y%m%d-%H%M%S).log 2>&1 & echo "Building both platforms! Check logs with: tail -f amako-shop/build-all-*.log"
```

---

## 📱 **After Build Completes**

### **1. Download the APK/AAB:**
The build output will give you a download URL, or you can:
```bash
cd amako-shop
eas build:list
```

### **2. Install APK on device (for testing):**
- Download the APK from the URL
- Transfer to your Android device
- Install it (enable "Install from Unknown Sources")

### **3. Submit to Google Play Store (for AAB):**
- Download the AAB file
- Go to Google Play Console
- Upload the AAB under "Release" → "Production" → "Create new release"

---

## 🔍 **Troubleshooting**

### **Problem: Build fails with "No Android credentials found"**
```bash
cd amako-shop
eas credentials
```
Follow the prompts to set up Android keystore.

### **Problem: Build takes too long**
- This is normal! Builds typically take 15-20 minutes
- Use background build commands above
- You'll get an email when it's done

### **Problem: Want to cancel a build**
1. Visit https://expo.dev
2. Go to your project builds
3. Click "Cancel" on the running build

---

## 📋 **Build Profiles**

Your `eas.json` has these profiles:

### **`preview`** - For testing:
- Builds APK
- Faster build
- Can install directly on device

### **`production`** - For Play Store:
- Builds AAB
- Fully optimized
- Ready for Play Store submission
- Includes all release optimizations

---

## 🎉 **Quick Start (Recommended)**

For most cases, just run this:

```bash
cd amako-shop
nohup eas build --platform android --profile production --non-interactive > build.log 2>&1 & 
echo "Build started in background! Monitor with: tail -f amako-shop/build.log"
```

Then:
1. Wait for email notification (or check `tail -f amako-shop/build.log`)
2. Download the AAB when ready
3. Upload to Google Play Store

Done! 🚀

