# 🚀 Quick Start: Deploy to Google Play Store

## TL;DR - Fastest Way to Publish

### Prerequisites (Do Once)
1. **Create Google Play Console account**: https://play.google.com/console ($25 fee)
2. **Create Expo account**: https://expo.dev (Free)
3. **Install EAS CLI**: `npm install -g eas-cli`

### Deploy Steps (Do This Now)

```bash
# 1. Navigate to your app
cd C:\Users\user\my_momo_shop\amako-shop

# 2. Install dependencies (if not done)
npm install

# 3. Login to Expo
eas login

# 4. Initialize EAS project
eas init

# 5. Build production APK/AAB
eas build --platform android --profile production

# 6. Wait for build to complete (10-20 minutes)
# You'll get a download link when done

# 7. Download the .aab file

# 8. Upload to Google Play Console
# Go to: https://play.google.com/console
# Create app > Upload .aab file
```

## ⚠️ Things You MUST Do First

### 1. Create Privacy Policy
Your app WILL be rejected without a privacy policy.

**Quick Solution**: 
- Use generator: https://app-privacy-policy-generator.nisrulz.com/
- Or create a simple page on your website

**Minimal Privacy Policy Content**:
```
Privacy Policy for AmaKo Momo Shop

Data We Collect:
- Name, email, phone number
- Delivery address
- Order history
- GPS location (for delivery)

How We Use It:
- Process and deliver orders
- Send order updates
- Improve our service

Data Protection:
- Industry-standard encryption
- Secure servers
- No data sharing with third parties

Your Rights:
- Request data deletion
- Access your data
Contact: your-email@example.com

Last updated: [Today's Date]
```

### 2. Prepare Required Assets

You need these EXACT sizes:

**App Icon** ✅ (You have this)
- File: `assets/icon.png`
- Size: 512x512 pixels
- Format: PNG, 32-bit

**Feature Graphic** ❌ (You need to create)
- Size: 1024x500 pixels
- Format: PNG or JPEG
- No transparency

**Screenshots** ❌ (You need to create)
- Minimum: 2 screenshots
- Maximum: 8 screenshots
- Size: 1080x1920 (portrait) or 1920x1080 (landscape)
- Format: PNG or JPEG

### 3. Production Configuration

**Update API URL** in `amako-shop/app.json`:
```json
"extra": {
  "apiUrl": "https://your-actual-domain.com"
}
```

**For Production Security**, change in `app.json`:
```json
"android": {
  "usesCleartextTraffic": false  // Change to false
}
```

## 📸 How to Create Screenshots

### Easy Method:
1. Open your app on an Android phone
2. Navigate to these screens and screenshot:
   - **Home/Menu** screen
   - **Cart** screen
   - **Checkout** screen
   - **Order tracking** screen
   - **Profile** screen

3. Transfer photos to computer
4. Resize to 1080x1920 if needed

### Using Online Tool:
- Use Canva (free): https://www.canva.com
- Create design > Custom size: 1080x1920
- Upload your screenshots
- Add text overlays (optional)
- Download as PNG

## 🎨 How to Create Feature Graphic

Use Canva or Photoshop:

1. **Size**: 1024x500 pixels
2. **Content**: 
   - Your app logo/mascot
   - App name: "AmaKo Momo Shop"
   - Tagline: "Fresh Momos, Fast Delivery"
   - Eye-catching colors

**Template Idea**:
```
[Mascot/Logo] | AmaKo Momo Shop
               Fresh Momos Delivered Fast!
```

## ⏱️ Timeline

| Task | Time Required |
|------|---------------|
| Create Google Play account | 30 min + 48h verification |
| Prepare assets (screenshots, etc.) | 1-2 hours |
| Create privacy policy | 30 min |
| First EAS build | 20-30 min |
| Fill Google Play listing | 1 hour |
| Google review | 1-7 days |
| **Total** | **~2-3 hours work + waiting** |

## 💵 Costs

| Item | Cost |
|------|------|
| Google Play Developer Account | $25 (one-time) |
| Expo EAS Builds | Free |
| Privacy Policy hosting | Free (use your website) |
| **Total** | **$25** |

## ✅ Production Readiness Check

Run this checklist:

- [ ] **App builds successfully** 
  ```bash
  cd amako-shop && npm install && npm start
  ```
  
- [ ] **All features tested**
  - [ ] Login/Register works
  - [ ] Browse menu works
  - [ ] Add to cart works
  - [ ] Checkout works
  - [ ] Payment methods work
  - [ ] Order tracking works
  - [ ] Notifications work
  - [ ] GPS location works

- [ ] **Production config**
  - [ ] API URL updated to production
  - [ ] `usesCleartextTraffic: false`
  - [ ] Google Maps API key (production)
  - [ ] Remove all `console.log` (optional but recommended)

- [ ] **Assets prepared**
  - [ ] App icon (512x512) ✅
  - [ ] Feature graphic (1024x500)
  - [ ] Screenshots (2-8 images)
  - [ ] Privacy policy URL

- [ ] **Store listing prepared**
  - [ ] App name: "AmaKo Momo Shop"
  - [ ] Short description (80 chars)
  - [ ] Full description (up to 4000 chars)
  - [ ] Category: Food & Drink
  - [ ] Contact email
  - [ ] Content rating completed

## 🐛 Troubleshooting

### Build fails with "Missing credentials"
```bash
# Re-login to Expo
eas logout
eas login
eas build --platform android --profile production
```

### "Invalid package name"
- Check `app.json` > `android` > `package`
- Must be: `com.amako.shop` (no spaces, lowercase)
- Must be unique (not used by another app)

### "Privacy policy required"
- You MUST add a privacy policy URL
- Cannot skip this step
- Must be accessible (not 404)

### App rejected for "Incomplete content rating"
- Complete the questionnaire in Google Play Console
- Go to: Content rating > Start questionnaire
- Answer all questions honestly

## 📞 Need Help?

If stuck:
1. Check full guide: `GOOGLE_PLAY_STORE_DEPLOYMENT_GUIDE.md`
2. Expo docs: https://docs.expo.dev
3. Google Play help: https://support.google.com/googleplay/android-developer

## 🎯 Next Steps

After your app is published:

1. **Monitor** crashes and reviews daily
2. **Respond** to user reviews
3. **Update** regularly (fix bugs, add features)
4. **Promote** on social media
5. **Gather** user feedback

Good luck! 🚀


