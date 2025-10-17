# 🧪 Beta Testing Setup Guide

## Overview

I've created a complete beta testing system for your AmaKo Momo Shop app. This allows you to distribute your app to testers before launching on Google Play Store.

## ✅ What I've Created

1. **Beta Testing Landing Page** - `resources/views/beta-testing.blade.php`
   - Professional design
   - Access code protection
   - Installation instructions
   - Feedback collection
   - Privacy policy link

2. **Route** - Accessible at: `http://your-domain.com/beta`

3. **Access Code System** - Password-protected downloads

## 🚀 How to Set Up Beta Testing

### Step 1: Build Your APK

You have **3 options** for building:

#### **Option A: Use EAS Build (Recommended)** ⭐

```bash
# 1. Install EAS CLI (if not installed)
npm install -g eas-cli

# 2. Navigate to your app
cd C:\Users\user\my_momo_shop\amako-shop

# 3. Login to Expo
eas login

# 4. Initialize project (first time only)
eas init

# 5. Build preview APK
eas build --platform android --profile preview
```

**This will:**
- Build a signed APK on Expo servers
- Take 10-20 minutes
- Give you a download link
- APK is ready for distribution

#### **Option B: Build Locally with Expo**

```bash
cd amako-shop

# Build APK locally
npx expo export:android

# Or use Expo prebuild + Android Studio
npx expo prebuild
# Then open in Android Studio and build
```

#### **Option C: Use Gradle Directly**

```bash
cd amako-shop/android
./gradlew assembleRelease

# APK will be in: android/app/build/outputs/apk/release/
```

### Step 2: Upload APK to Your Server

1. **Create downloads folder:**
   ```bash
   mkdir public/downloads
   ```

2. **Copy APK file:**
   ```bash
   # Copy the built APK to public folder
   cp amako-shop-beta.apk public/downloads/
   ```

3. **Verify it's accessible:**
   ```
   http://localhost:8000/downloads/amako-shop-beta.apk
   ```

### Step 3: Configure Access Codes

Edit `resources/views/beta-testing.blade.php` to set your access codes:

```javascript
// Line ~287: Change these codes
const validCodes = [
    'AMAKO2025',    // Change this
    'BETA2025',     // Change this
    'MOMOTEST',     // Change this
    'TESTAMAKO',    // Change this
    'BETAUSER'      // Change this
];
```

**Tips for access codes:**
- Make them memorable
- Mix letters and numbers
- 8-12 characters
- Share only with trusted testers

### Step 4: Share Beta Link

Give testers this link:
```
http://your-domain.com/beta
```

Or for local testing:
```
http://localhost:8000/beta
```

**Include in your message:**
- The beta page URL
- An access code
- Instructions to enable unknown sources
- Your contact for feedback

### Step 5: Collect Feedback

Testers can send feedback via:
- **Email:** beta@amakoshop.com (update to your real email)
- **Feedback form:** Create a Google Form (optional)
- **WhatsApp:** Share your number with testers

## 📧 Sample Email to Beta Testers

```
Subject: 🥟 You're Invited - AmaKo Momo Shop Beta!

Hi [Name],

You've been selected to test the new AmaKo Momo Shop mobile app!

🔗 Beta Page: http://your-domain.com/beta
🔐 Access Code: AMAKO2025

📱 How to Install:
1. Visit the beta page on your Android phone
2. Enter the access code
3. Follow the installation instructions
4. Download and install the app

💬 We Need Your Feedback:
- Report bugs or crashes
- Suggest improvements
- Let us know what you love!

Email feedback to: beta@amakoshop.com

Thank you for helping us improve AmaKo! 🙏

Best regards,
AmaKo Team
```

## 🎯 Testing Checklist for Testers

Create a simple checklist for your testers:

### Core Features to Test:
- [ ] Register new account
- [ ] Login with existing account
- [ ] Browse menu
- [ ] Add items to cart
- [ ] Update cart quantities
- [ ] Remove items from cart
- [ ] Enter delivery address
- [ ] Select branch
- [ ] Choose payment method
- [ ] Place an order
- [ ] Track order in real-time
- [ ] View order history
- [ ] View profile
- [ ] Earn loyalty points
- [ ] Check notifications
- [ ] Use help center

### What to Report:
- Crashes or freezes
- Buttons that don't work
- Confusing UI/UX
- Slow loading times
- Payment issues
- GPS/location problems
- Notification issues
- Any unexpected behavior

## 📊 Track Beta Testing

### Create a Simple Tracker

Use Google Sheets or Excel:

| Tester Name | Access Code | Install Date | Feedback Received | Issues Found | Status |
|-------------|-------------|--------------|-------------------|--------------|--------|
| John Doe | AMAKO2025 | 2025-10-16 | Yes | 2 bugs | Active |
| Jane Smith | BETA2025 | 2025-10-17 | Pending | - | Testing |

### Analytics to Track:

- Number of downloads
- Installation success rate
- Common issues reported
- Feature usage
- Crash reports
- User feedback summary

## 🔒 Security Best Practices

### Protect Your Beta:

1. **Use Access Codes** ✅ (Already implemented)
   - Don't share codes publicly
   - Give unique codes to each tester (optional)
   - Rotate codes weekly

2. **Limit Distribution**
   - Start with 5-10 trusted users
   - Expand to 20-50 after fixes
   - Don't share download link publicly

3. **Monitor Downloads**
   - Track who downloads
   - Disable codes after testing period
   - Remove APK from server when done

4. **Add Analytics** (Optional)
   ```php
   // Log access in routes/api.php
   Route::post('/beta-access-log', function(Request $request) {
       \Log::info('Beta access', [
           'code' => $request->access_code,
           'ip' => $request->ip(),
           'time' => now()
       ]);
       return response()->json(['logged' => true]);
   });
   ```

## 📱 Alternative: Use Firebase App Distribution

Instead of self-hosting, you can use **Firebase App Distribution** (free):

### Advantages:
- ✅ Automatic updates
- ✅ Built-in crash reporting
- ✅ Tester management
- ✅ Email invitations
- ✅ No server hosting needed

### Setup:
1. Go to https://console.firebase.google.com
2. Create project
3. Add Android app
4. Install Firebase CLI
5. Upload APK: `firebase appdistribution:distribute app.apk`

## 🐛 Bug Tracking

### Simple Bug Report Form

Create a Google Form with:

1. **Tester Information:**
   - Name
   - Email
   - Phone model
   - Android version

2. **Issue Details:**
   - Type: Bug / Suggestion / UI Issue / Performance
   - Severity: Critical / High / Medium / Low
   - Description
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Screenshots (optional)

3. **Context:**
   - What were you trying to do?
   - When did it happen?
   - Does it happen every time?

### Issue Tracking:

Use:
- **Google Sheets** (free, simple)
- **Trello** (free, kanban board)
- **GitHub Issues** (free, developer-friendly)
- **Notion** (free, database)

## 📈 Success Metrics

Track these during beta:

- **Install rate:** How many testers actually install?
- **Active users:** How many use it daily?
- **Crash rate:** Crashes per user session
- **Feature usage:** Which features are used most?
- **Feedback quality:** Detailed vs vague reports
- **Bug severity:** Critical vs minor issues

**Goal:** < 1% crash rate, 80%+ feature completion

## ⏱️ Beta Testing Timeline

### Recommended Schedule:

**Week 1:** Internal testing
- 5-10 trusted users
- Team members, friends, family
- Focus on critical bugs

**Week 2:** Closed beta
- 20-30 selected users
- Regular customers
- Fix major issues

**Week 3:** Open beta
- 50-100 users
- General public with access code
- Fine-tune UI/UX

**Week 4:** Final testing
- Polish remaining issues
- Prepare for Play Store submission

## 🔄 Updating the Beta

When you fix bugs and want to release a new version:

1. **Update version** in `app.json`:
   ```json
   {
     "version": "1.0.1-beta",
     "android": {
       "versionCode": 2
     }
   }
   ```

2. **Build new APK:**
   ```bash
   eas build --platform android --profile preview
   ```

3. **Upload to server:**
   ```bash
   cp new-beta.apk public/downloads/amako-shop-beta.apk
   ```

4. **Notify testers:**
   ```
   Subject: 🔄 AmaKo Beta Update Available!
   
   A new beta version is available with bug fixes!
   Download: http://your-domain.com/beta
   
   What's new:
   - Fixed cart sync issue
   - Improved order tracking
   - Performance improvements
   ```

## 📋 Beta Testing Completion Criteria

Before launching to Play Store:

- [ ] Less than 1% crash rate
- [ ] All critical bugs fixed
- [ ] All major features working
- [ ] Payment processing verified
- [ ] GPS/location services working
- [ ] Notifications working
- [ ] UI/UX polished
- [ ] Performance optimized
- [ ] Privacy policy reviewed
- [ ] Legal compliance verified
- [ ] Positive tester feedback (80%+)
- [ ] All feedback addressed or documented

## 🎉 Launch Checklist

When beta testing is complete:

1. **Increment version** to 1.0.0 (remove -beta)
2. **Build production AAB** for Play Store
3. **Create Play Store listing**
4. **Upload production build**
5. **Submit for review**
6. **Disable beta page** or redirect to Play Store
7. **Thank your beta testers!**

## 🆘 Common Issues

### Issue: APK won't install
**Solution:**
- Check Android version (need 6.0+)
- Enable unknown sources
- Clear old version if exists
- Check storage space

### Issue: App crashes on start
**Solution:**
- Check device logs
- Test on different devices
- Check API connectivity
- Review crash reports

### Issue: No one downloads
**Solution:**
- Make access codes easier
- Provide direct APK link
- Create video tutorial
- Offer incentives (free momo!)

## 📞 Support Your Beta Testers

Be responsive:
- Reply to emails within 24 hours
- Fix critical bugs ASAP
- Keep testers updated on progress
- Thank them for their time
- Consider rewards (discounts, free items)

## ✨ You're Ready for Beta!

Your beta testing page is now live at:
```
http://localhost:8000/beta (for testing)
http://your-domain.com/beta (for production)
```

**Default access codes:**
- AMAKO2025
- BETA2025
- MOMOTEST
- TESTAMAKO
- BETAUSER

**Next steps:**
1. Build your APK
2. Upload to `public/downloads/`
3. Test the beta page
4. Share with testers
5. Collect feedback
6. Fix bugs
7. Iterate!

Good luck with your beta testing! 🚀


