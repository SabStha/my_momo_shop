#!/usr/bin/env node

import { execSync } from 'child_process';
import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

console.log('🚀 Building AmaKo Shop Development Build...\n');

// Check if EAS CLI is installed
try {
  execSync('eas --version', { stdio: 'pipe' });
  console.log('✅ EAS CLI is installed');
} catch (error) {
  console.log('❌ EAS CLI not found. Installing...');
  try {
    execSync('npm install -g @expo/eas-cli', { stdio: 'inherit' });
    console.log('✅ EAS CLI installed successfully');
  } catch (installError) {
    console.error('❌ Failed to install EAS CLI:', installError.message);
    process.exit(1);
  }
}

// Check if user is logged in
try {
  execSync('eas whoami', { stdio: 'pipe' });
  console.log('✅ Logged in to Expo');
} catch (error) {
  console.log('❌ Not logged in to Expo. Please login first:');
  console.log('   eas login');
  process.exit(1);
}

// Update app.json with development build configuration
const appJsonPath = join(process.cwd(), 'app.json');
const appJson = JSON.parse(readFileSync(appJsonPath, 'utf8'));

// Ensure development build configuration
if (!appJson.expo.plugins) {
  appJson.expo.plugins = [];
}

if (!appJson.expo.plugins.includes('expo-dev-client')) {
  appJson.expo.plugins.push('expo-dev-client');
}

// Write updated app.json
writeFileSync(appJsonPath, JSON.stringify(appJson, null, 2));
console.log('✅ Updated app.json with development build configuration');

// Install expo-dev-client if not already installed
try {
  execSync('npm install expo-dev-client', { stdio: 'inherit' });
  console.log('✅ expo-dev-client installed');
} catch (error) {
  console.log('⚠️  expo-dev-client installation failed, continuing...');
}

console.log('\n🔨 Building development APK...');
console.log('This may take 10-20 minutes...\n');

try {
  // Build development APK
  execSync('eas build --platform android --profile development', { 
    stdio: 'inherit',
    cwd: process.cwd()
  });
  
  console.log('\n✅ Development build completed successfully!');
  console.log('\n📱 Next steps:');
  console.log('1. Download the APK from the EAS dashboard');
  console.log('2. Install it on your Android device');
  console.log('3. Run: npx expo start --dev-client');
  console.log('4. Scan the QR code with your development build');
  console.log('\n🎉 Push notifications will now work properly!');
  
} catch (error) {
  console.error('\n❌ Build failed:', error.message);
  console.log('\n🔧 Troubleshooting:');
  console.log('1. Check your EAS project ID in app.json');
  console.log('2. Ensure you have sufficient EAS credits');
  console.log('3. Check the EAS dashboard for detailed error logs');
  process.exit(1);
}
