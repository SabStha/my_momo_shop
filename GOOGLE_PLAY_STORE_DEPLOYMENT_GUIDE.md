# 🚀 Google Play Store Deployment Guide for AmaKo Shop

## ✅ Pre-Deployment Checklist

### Current Status:
- ✅ **App Configuration**: Ready (app.json configured)
- ✅ **EAS Configuration**: Ready (eas.json configured)
- ✅ **Package Name**: `com.amako.shop`
- ✅ **Version**: 1.0.0
- ⚠️ **EAS Project ID**: Needs to be updated (currently placeholder)
- ⚠️ **Google Maps API Key**: Needs production key
- ⚠️ **Production API URL**: Needs configuration
- ⚠️ **App Icons**: Need verification

## 📋 What You Need Before Publishing

### 1. **Google Play Console Account**
- Cost: **$25 one-time registration fee**
- Sign up at: https://play.google.com/console
- Use your business email/Google account
- Complete identity verification (may take 48 hours)

### 2. **Expo Account** (for building)
- Free for building and submitting
- Sign up at: https://expo.dev
- Install EAS CLI: `npm install -g eas-cli`

### 3. **App Store Assets**
You need to prepare:
- **App Icon**: 512x512 PNG (already have: `icon.png`)
- **Feature Graphic**: 1024x500 PNG
- **Screenshots**: At least 2 screenshots
  - Phone: 16:9 or 9:16 ratio
  - Recommended: 1080x1920 or 1920x1080
- **App Description**: Short (80 chars) and Full (4000 chars)
- **Privacy Policy URL**: Required!
- **Content Rating**: Need to fill questionnaire

## 🔧 Step-by-Step Deployment Process

### **STEP 1: Prepare Your App**

#### 1.1 Update App Configuration

Edit `amako-shop/app.json`:

```json
{
  "expo": {
    "name": "AmaKo Momo Shop",
    "slug": "amako-shop",
    "version": "1.0.0",
    "android": {
      "package": "com.amako.shop",
      "versionCode": 1,
      "permissions": [
        "POST_NOTIFICATIONS",
        "ACCESS_FINE_LOCATION",
        "ACCESS_COARSE_LOCATION"
      ],
      "adaptiveIcon": {
        "foregroundImage": "./assets/adaptive-icon.png",
        "backgroundColor": "#ffffff"
      }
    }
  }
}
```

#### 1.2 Configure Production API

Create `amako-shop/.env.production`:

```env
API_URL=https://your-production-domain.com
GOOGLE_MAPS_API_KEY=your_production_google_maps_key
```

#### 1.3 Remove Development Settings

In `app.json`, change:
```json
"android": {
  "usesCleartextTraffic": false  // Change from true to false for production
}
```

### **STEP 2: Set Up EAS (Expo Application Services)**

#### 2.1 Install EAS CLI
```bash
npm install -g eas-cli
```

#### 2.2 Login to Expo
```bash
eas login
```

#### 2.3 Configure Your Project
```bash
cd amako-shop
eas build:configure
```

This will create/update your `eas.json` file.

#### 2.4 Link Project to EAS
```bash
eas init
```

This will give you a real project ID to replace in `app.json`.

### **STEP 3: Build Your App**

#### 3.1 Create Production Build (AAB format)
```bash
eas build --platform android --profile production
```

This will:
- Upload your code to Expo servers
- Build the Android App Bundle (.aab file)
- Take 10-20 minutes
- Give you a download link when done

#### 3.2 Download the AAB File
After build completes, download the `.aab` file from the provided link.

### **STEP 4: Create Google Play Console Listing**

#### 4.1 Create New App
1. Go to https://play.google.com/console
2. Click "Create app"
3. Fill in:
   - **App name**: "AmaKo Momo Shop"
   - **Default language**: English (United States)
   - **App or game**: App
   - **Free or paid**: Free
   - **Declarations**: Check all required boxes

#### 4.2 Complete Store Listing

**Main store listing** (required):
- **App name**: AmaKo Momo Shop
- **Short description** (80 chars max):
  ```
  Order delicious Nepali momos with fast delivery. Track orders in real-time!
  ```
- **Full description** (4000 chars max):
  ```
  AmaKo Momo Shop - Your Favorite Nepali Momo Delivery App
  
  🥟 Fresh, Authentic Momos
  Order from our extensive menu of steamed, fried, and chili momos. All made fresh with authentic Nepali recipes.
  
  🚀 Fast Delivery
  Get your momos delivered hot and fresh to your doorstep in 30-45 minutes.
  
  📍 Real-Time Tracking
  Track your order in real-time with GPS location updates.
  
  💳 Multiple Payment Options
  Pay with cash, eSewa, Khalti, or your Amako wallet credits.
  
  🎁 Rewards & Badges
  Earn loyalty points and badges with every order. Unlock exclusive rewards!
  
  ✨ Features:
  • Easy order placement
  • GPS-based branch selection
  • Live order tracking
  • Multiple payment methods
  • Loyalty rewards system
  • Special offers and discounts
  • Order history
  • Push notifications
  
  Download now and enjoy the best momos in town!
  ```
- **App icon**: Upload `icon.png` (512x512)
- **Feature graphic**: Create and upload (1024x500)
- **Screenshots**: Upload 2-8 screenshots
- **App category**: Food & Drink
- **Contact email**: your-email@example.com
- **Privacy Policy**: https://your-website.com/privacy-policy

#### 4.3 Set Up Privacy Policy

**IMPORTANT**: You MUST have a privacy policy URL.

Create a simple one at: https://app-privacy-policy-generator.nisrulz.com/

Or create a page on your website with:
- What data you collect
- How you use it
- How you protect it
- User rights

Example minimal privacy policy for your website:
```
/privacy-policy

We collect:
- Name, email, phone, delivery address
- Order history
- Location data (for delivery)

We use this data to:
- Process and deliver orders
- Send order updates
- Improve our service

We protect your data with industry-standard security.
You can request data deletion by contacting us.
```

#### 4.4 Content Rating
1. Go to "Content rating" section
2. Fill out questionnaire (takes 5 minutes)
3. Common answers for food delivery app:
   - No violence
   - No sexual content
   - No drugs/alcohol
   - No gambling
   - No user interaction (unless you have chat/forums)

#### 4.5 Target Audience
- **Target age group**: 13+ or 18+ (you decide)
- **App appeals to children**: No (unless targeting kids)

#### 4.6 App Access
- **All functionality available**: Yes
- **Restricted access**: No (unless you have special restrictions)

### **STEP 5: Upload Your App**

#### 5.1 Create Production Release
1. Go to "Production" > "Create release"
2. Upload your `.aab` file
3. Fill in:
   - **Release name**: Version 1.0.0
   - **Release notes**:
     ```
     Initial release of AmaKo Momo Shop!
     
     Features:
     - Browse and order delicious momos
     - Real-time order tracking
     - Multiple payment options
     - Loyalty rewards system
     - GPS-based delivery
     ```

#### 5.2 Countries/Regions
- Select Nepal (and other countries if applicable)

#### 5.3 Review and Roll Out
1. Review all information
2. Click "Start rollout to Production"
3. Confirm rollout

### **STEP 6: Wait for Review**

Google Play review typically takes:
- **First submission**: 1-7 days
- **Updates**: Usually within 24 hours

You'll receive email notifications about:
- App under review
- App approved/rejected
- App published

## 📱 After Publishing

### Monitor Your App
1. **Google Play Console** > **Dashboard**
   - View installs
   - Check ratings/reviews
   - Monitor crashes

2. **Set up alerts** for:
   - Crash reports
   - ANR (App Not Responding)
   - Low ratings

### Updates

To publish updates:

1. Increment version in `app.json`:
```json
{
  "version": "1.0.1",  // Changed from 1.0.0
  "android": {
    "versionCode": 2   // Changed from 1
  }
}
```

2. Build new version:
```bash
eas build --platform android --profile production
```

3. Upload to Google Play Console > Production > Create release

## 🚨 Common Issues & Solutions

### Issue 1: Build Fails
**Solution**: Check logs, usually missing dependencies
```bash
npm install
eas build --platform android --profile production
```

### Issue 2: App Rejected for Privacy Policy
**Solution**: Add valid privacy policy URL

### Issue 3: Icon/Screenshot Requirements
**Solution**: Use exact dimensions:
- Icon: 512x512 PNG
- Feature graphic: 1024x500 PNG
- Screenshots: 1080x1920 or 1920x1080

### Issue 4: Cleartext Traffic Error
**Solution**: Set `usesCleartextTraffic: false` in production

### Issue 5: API Connection Issues
**Solution**: Ensure production API URL is HTTPS, not HTTP

## 📸 Creating Screenshots

### Method 1: Use Emulator
1. Start Android emulator
2. Open your app
3. Navigate to key screens
4. Press `Ctrl+S` to screenshot
5. Resize to 1080x1920

### Method 2: Use Real Device
1. Open app on phone
2. Take screenshots (Power + Volume Down)
3. Transfer to computer
4. Resize if needed

**Recommended screens to capture**:
- Home/Menu screen
- Cart screen
- Checkout screen
- Order tracking screen
- Profile/Rewards screen

## 💰 Costs

- **Google Play Developer Account**: $25 (one-time)
- **Expo EAS Build**: Free for unlimited builds
- **App Store Listing**: Free
- **Domain/Hosting for Privacy Policy**: ~$10-50/year

## 🎯 Optimization Tips

### Before Publishing:
1. **Test thoroughly** on real devices
2. **Optimize images** and assets
3. **Remove console.logs** from production code
4. **Test offline functionality**
5. **Check all permissions** are necessary
6. **Enable ProGuard** for code obfuscation (optional)

### After Publishing:
1. **Monitor crash reports** daily
2. **Respond to user reviews** within 24-48h
3. **Plan regular updates** (monthly/quarterly)
4. **A/B test** app store listing
5. **Promote** on social media

## 📞 Support

If you encounter issues:
- **Expo Documentation**: https://docs.expo.dev
- **Google Play Console Help**: https://support.google.com/googleplay/android-developer
- **Expo Discord**: https://chat.expo.dev
- **Stack Overflow**: Tag with `expo`, `react-native`, `google-play`

## ✅ Final Checklist

Before submitting:
- [ ] App builds successfully
- [ ] All features work on real device
- [ ] Privacy policy URL is live
- [ ] Icons and screenshots prepared
- [ ] App description written
- [ ] Content rating completed
- [ ] Production API configured
- [ ] Google Maps API key configured
- [ ] Tested payment methods
- [ ] Tested notifications
- [ ] Tested location services
- [ ] Removed all test/debug code
- [ ] Set `usesCleartextTraffic: false`
- [ ] Version numbers correct
- [ ] Package name correct (`com.amako.shop`)

## 🎉 Ready to Publish!

Once all the above is complete, your app is ready for the Google Play Store!

**Good luck with your app launch! 🚀**


