# 🧪 Beta Testing - Quick Start

## 🎯 Goal

Distribute your AmaKo Momo Shop app to testers before Google Play Store launch.

## ⚡ Quick Setup (3 Steps)

### Step 1: Build the APK (Choose One Method)

#### **Method A: Use the Build Script** ⭐ EASIEST

```bash
cd amako-shop
build-beta.bat
```

This script will:
- Check if EAS CLI is installed
- Log you into Expo
- Build the APK automatically
- Give you a download link

**Time:** 20-30 minutes (mostly waiting)

---

#### **Method B: Manual EAS Build**

```bash
# Install EAS CLI (if needed)
npm install -g eas-cli

# Navigate to app
cd C:\Users\user\my_momo_shop\amako-shop

# Login to Expo
eas login

# Initialize (first time only)
eas init

# Build APK
eas build --platform android --profile preview
```

**Time:** 20-30 minutes (mostly waiting)

---

#### **Method C: Quick Local Build** (Advanced)

```bash
cd amako-shop

# Install dependencies
npm install

# Export for Android
npx expo export:android

# Or use prebuild
npx expo prebuild --platform android
# Then open in Android Studio
```

**Time:** 30-60 minutes (requires Android Studio)

---

### Step 2: Host the APK

```bash
# 1. Create downloads folder (if not exists)
mkdir public\downloads

# 2. Copy your built APK
# Download the APK from EAS build link, then:
copy Downloads\amako-shop-*.apk public\downloads\amako-shop-beta.apk

# 3. Verify it's accessible
# Open browser: http://localhost:8000/downloads/amako-shop-beta.apk
```

---

### Step 3: Share with Testers

**Your beta page is ready at:**
```
Local: http://localhost:8000/beta
Production: http://your-domain.com/beta
```

**Default access codes:**
- `AMAKO2025`
- `BETA2025`
- `MOMOTEST`
- `TESTAMAKO`
- `BETAUSER`

**Share this message:**
```
🥟 AmaKo Beta Testing Invitation

Beta Page: http://your-domain.com/beta
Access Code: AMAKO2025

Download the app and let us know what you think!
Feedback: beta@amakoshop.com
```

---

## 📋 What's Already Set Up

✅ **Beta Landing Page** - Beautiful, professional design  
✅ **Access Code Protection** - Password-gated downloads  
✅ **Installation Guide** - Step-by-step "Unknown Sources" instructions  
✅ **Feature Showcase** - Shows what's in the beta  
✅ **Feedback System** - Email and form links  
✅ **Privacy Policy Link** - Legal compliance  
✅ **FAQ Section** - Answers common questions  
✅ **Mobile Responsive** - Works on phones  

---

## 🎨 Customize Your Beta Page

### Change Access Codes:

Edit `resources/views/beta-testing.blade.php` (line ~287):

```javascript
const validCodes = [
    'YOUR_CODE_1',
    'YOUR_CODE_2',
    'YOUR_CODE_3'
];
```

### Update Contact Email:

Find and replace:
- `beta@amakoshop.com` → your real email

### Change APK File Name:

If your APK has a different name:

```html
<!-- Find this line (~177): -->
href="{{ asset('downloads/amako-shop-beta.apk') }}"

<!-- Change to: -->
href="{{ asset('downloads/your-file-name.apk') }}"
```

---

## 🧪 Testing the Beta Page

### Local Testing:

1. **Start Laravel server:**
   ```bash
   php artisan serve
   ```

2. **Visit beta page:**
   ```
   http://localhost:8000/beta
   ```

3. **Test access code:**
   - Enter: `AMAKO2025`
   - Should show download button
   - Click download (may show 404 if APK not uploaded yet)

4. **Verify on mobile:**
   - Open page on Android phone
   - Test responsive design
   - Try downloading APK

---

## 📱 Tester Instructions

### Send This to Your Testers:

```markdown
# How to Install AmaKo Beta

## Step 1: Get Access
Visit: http://your-domain.com/beta
Enter code: AMAKO2025

## Step 2: Enable Unknown Sources
1. Go to Settings on your phone
2. Tap "Apps" or "Security"
3. Find "Install unknown apps"
4. Select your browser (Chrome/Firefox)
5. Enable "Allow from this source"

## Step 3: Download & Install
1. Click "Download" on beta page
2. Open downloaded file
3. Tap "Install"
4. Open the app!

## Step 4: Test & Report
Try all features and report:
- Bugs or crashes
- Confusing UI
- Suggestions

Email: beta@amakoshop.com

Thank you! 🙏
```

---

## 🔍 Monitoring & Analytics

### Track Downloads (Optional):

Add to `routes/web.php`:

```php
Route::get('/downloads/amako-shop-beta.apk', function() {
    $filePath = public_path('downloads/amako-shop-beta.apk');
    
    if (!file_exists($filePath)) {
        abort(404, 'APK not found');
    }
    
    // Log download
    \Log::info('Beta APK downloaded', [
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'time' => now()
    ]);
    
    return response()->download($filePath);
})->name('beta.download');
```

### View Download Logs:

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Or on Windows
type storage\logs\laravel.log
```

---

## 🎯 Beta Testing Goals

### What to Validate:

1. **Core Functionality** (Must Work)
   - [ ] User registration/login
   - [ ] Browse menu
   - [ ] Add to cart
   - [ ] Checkout flow
   - [ ] Payment processing
   - [ ] Order tracking
   - [ ] Notifications

2. **User Experience** (Should Be Good)
   - [ ] Intuitive navigation
   - [ ] Fast loading times
   - [ ] Smooth animations
   - [ ] Clear error messages
   - [ ] Helpful feedback

3. **Edge Cases** (Should Handle Gracefully)
   - [ ] No internet connection
   - [ ] GPS disabled
   - [ ] Payment failures
   - [ ] Invalid inputs
   - [ ] Empty states

---

## ⚠️ Important Notes

### Security:
- ⚠️ Beta APK is **not as secure** as Play Store version
- ⚠️ Only share with **trusted testers**
- ⚠️ Don't share access codes publicly
- ⚠️ Remove APK when beta ends

### Limitations:
- ⚠️ Testers need to manually enable unknown sources
- ⚠️ No automatic updates (must download new APK)
- ⚠️ No Play Store protection scanning
- ⚠️ Some users may be hesitant to install

### Advantages:
- ✅ Test before Play Store submission
- ✅ Get early feedback
- ✅ Find bugs before public launch
- ✅ Improve app quality
- ✅ Build excitement and anticipation

---

## 📊 Sample Feedback Template

Send this form to testers:

```
🧪 AmaKo Beta Feedback Form

Name: _______________
Email: _______________
Phone Model: _______________
Android Version: _______________

Rate the app (1-5 stars):
Overall Experience: ⭐ ⭐ ⭐ ⭐ ⭐

What did you like most?
_________________________________

What needs improvement?
_________________________________

Did you encounter any bugs?
□ Yes  □ No
If yes, describe:
_________________________________

Would you recommend this app?
□ Yes  □ No  □ Maybe

Additional comments:
_________________________________

Thank you! 🙏
```

---

## 🚀 Ready to Start!

**Your beta testing system is ready!**

**To begin:**

1. **Run:** `amako-shop\build-beta.bat`
2. **Wait:** 20 minutes for build
3. **Download:** APK from EAS link
4. **Upload:** To `public/downloads/`
5. **Share:** `http://your-domain.com/beta` with testers
6. **Collect:** Feedback and fix bugs!

**Questions?** Check the detailed guide: `BETA_TESTING_SETUP_GUIDE.md`

Good luck with your beta launch! 🎉







