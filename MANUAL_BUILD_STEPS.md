# 🔧 Manual Build Steps - Android APK

## 🚨 Issue: Interactive Prompts Not Working

The automated build failed because EAS needs to generate Android credentials (keystore) and requires interactive input that our terminal environment can't handle.

## ✅ Solution: Manual Build

**You need to run these commands manually in your terminal:**

### Step 1: Open Command Prompt or PowerShell
```cmd
# Navigate to your project
cd C:\Users\user\my_momo_shop\amako-shop
```

### Step 2: Start the Build
```cmd
eas build --platform android --profile preview
```

### Step 3: Answer the Prompts

When prompted, you'll see:
```
Generate a new Android Keystore?
> Yes
```

**Answer: `Yes`** (press Enter)

### Step 4: Wait for Build

The build will then start and take **15-20 minutes**.

You'll see output like:
```
✓ Using remote Android credentials (Expo server)
✓ Generated a new Keystore
✓ Starting build process...
```

---

## 🔄 Alternative: Use Expo Web Dashboard

If terminal commands still don't work, you can trigger the build from the web:

### Option 1: Expo Dashboard
1. Go to: https://expo.dev/accounts/sabstha98/projects/amako-shop
2. Click **"Builds"** in the sidebar
3. Click **"Create a build"**
4. Select **Android** platform
5. Select **preview** profile
6. Click **"Create build"**

### Option 2: EAS Web
1. Go to: https://expo.dev/builds
2. Select your project: `amako-shop`
3. Click **"New build"**
4. Choose **Android** → **preview**
5. Click **"Create build"**

---

## 📱 What Happens Next

### During Build (15-20 minutes):
- EAS compiles your app
- Generates signed APK
- Uploads to their servers

### When Complete:
- You'll get an email notification
- Download link will be provided
- APK will be ready for testing

### Build Output:
```
✓ Build finished successfully!
📱 Install app: https://expo.dev/artifacts/eas/xxxxx.apk
```

---

## 📥 Download and Setup

### Step 1: Download APK
Click the download link from email or Expo dashboard.

### Step 2: Copy to Server
```powershell
# Copy downloaded APK to your server
Copy-Item "C:\Users\user\Downloads\*.apk" "C:\Users\user\my_momo_shop\public\downloads\amako-shop-beta.apk"
```

### Step 3: Test Beta Page
```
http://localhost:8000/beta
```
- Enter code: `AMAKO2025`
- Should download the APK

---

## 🎯 Why This Happened

**The Issue:**
- EAS needs to generate Android signing credentials (keystore)
- This requires interactive user input: "Generate new keystore? (Yes/No)"
- Our automated terminal can't handle interactive prompts
- This is a one-time setup - future builds won't need this

**The Solution:**
- Run the command manually in your terminal
- Answer the keystore prompt
- Build will proceed normally

---

## 🚀 Quick Commands

**Copy and paste these commands:**

```cmd
cd C:\Users\user\my_momo_shop\amako-shop
eas build --platform android --profile preview
```

**When prompted:**
```
Generate a new Android Keystore?
> Yes
```

**Then wait 15-20 minutes for the build to complete!**

---

## 📞 Need Help?

If you still have issues:

1. **Check EAS login:**
   ```cmd
   eas whoami
   ```

2. **Check project status:**
   ```cmd
   eas project:info
   ```

3. **View build logs:**
   ```cmd
   eas build:list
   ```

4. **Use web dashboard instead:**
   - Go to https://expo.dev/builds
   - Create build from there

---

**🎉 Once the build completes, you'll have your beta APK ready for testing!**

