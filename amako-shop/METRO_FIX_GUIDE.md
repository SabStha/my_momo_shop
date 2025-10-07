# Metro Bundler Syntax Error Fix Guide

## Problem
You're encountering: `SyntaxError: Unexpected token 'this'` in Metro's Server.js

This is typically caused by:
1. Node.js version incompatibility (v22 is too new)
2. Corrupted Metro bundler installation
3. Cached build artifacts

## Solution Steps

### Option 1: Use Node.js LTS Version (RECOMMENDED)

Expo SDK 54 works best with Node.js 18.x or 20.x LTS versions.

1. **Install NVM for Windows** (if not already installed):
   - Download from: https://github.com/coreybutler/nvm-windows/releases
   - Install the latest `nvm-setup.exe`

2. **Install Node.js 20 LTS**:
   ```powershell
   nvm install 20
   nvm use 20
   node --version  # Should show v20.x.x
   ```

3. **Clean and Reinstall**:
   ```powershell
   cd amako-shop
   Remove-Item -Recurse -Force node_modules
   Remove-Item -Force package-lock.json
   npm cache clean --force
   npm install
   npm run start:tunnel
   ```

### Option 2: Force Clean Reinstall (If staying on Node v22)

1. **Run the automated fix script**:
   ```powershell
   cd amako-shop
   .\fix-metro.bat
   ```

2. **OR manually execute these steps**:
   ```powershell
   # Clear all caches
   npm cache clean --force
   Remove-Item -Recurse -Force node_modules -ErrorAction SilentlyContinue
   Remove-Item -Force package-lock.json -ErrorAction SilentlyContinue
   Remove-Item -Recurse -Force .expo -ErrorAction SilentlyContinue
   Remove-Item -Recurse -Force node_modules\.cache -ErrorAction SilentlyContinue
   
   # Reinstall
   npm install
   
   # Try starting
   npm run start:tunnel
   ```

### Option 3: Update Expo CLI

If the above doesn't work, update Expo CLI globally:

```powershell
npm install -g expo-cli@latest
npm install -g @expo/cli@latest
```

Then repeat the clean reinstall steps from Option 2.

### Option 4: Check for File Corruption

The error shows "rawait" instead of "await" which suggests file corruption. If reinstalling doesn't fix it:

1. **Check your antivirus** - It might be corrupting files during installation
2. **Run disk check**: `chkdsk /f`
3. **Try installing in a different directory** with a shorter path (Windows path length issues)

## Verification

After fixing, verify it works:
```powershell
cd amako-shop
npm run start:tunnel
```

You should see:
```
Starting project at C:\Users\user\my_momo_shop\amako-shop
Starting Metro Bundler
› Metro waiting on exp://...
› Scan the QR code above to open the app
```

## Recommended Node.js Versions for Expo

- **Node.js 18.x LTS** ✅ (Best compatibility)
- **Node.js 20.x LTS** ✅ (Recommended)
- **Node.js 21.x** ⚠️ (May work but not guaranteed)
- **Node.js 22.x** ❌ (Too new, compatibility issues)

## Additional Resources

- Expo Documentation: https://docs.expo.dev/
- Node Version Manager (NVM): https://github.com/coreybutler/nvm-windows
- Expo Discord: https://chat.expo.dev/

