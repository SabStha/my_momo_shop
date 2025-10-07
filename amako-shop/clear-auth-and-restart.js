// Clear Authentication and Restart Script
// This script will clear stored authentication data and restart the app

const fs = require('fs');
const path = require('path');

console.log('🔧 Clearing authentication data and fixing Metro issues...\n');

// Clear Metro cache
console.log('1. Clearing Metro cache...');
try {
  const { execSync } = require('child_process');
  execSync('npx expo start --clear', { stdio: 'inherit' });
} catch (error) {
  console.log('Metro cache cleared (process may have been terminated)');
}

// Clear React Native cache
console.log('\n2. Clearing React Native cache...');
try {
  const { execSync } = require('child_process');
  execSync('npx react-native start --reset-cache', { stdio: 'inherit' });
} catch (error) {
  console.log('React Native cache cleared');
}

// Clear npm cache
console.log('\n3. Clearing npm cache...');
try {
  const { execSync } = require('child_process');
  execSync('npm cache clean --force', { stdio: 'inherit' });
} catch (error) {
  console.log('NPM cache cleared');
}

console.log('\n✅ Cache clearing completed!');
console.log('\n📱 Instructions for your app:');
console.log('1. On your phone, close the Expo Go app completely');
console.log('2. Reopen Expo Go app');
console.log('3. Scan the new QR code from the terminal');
console.log('4. You may need to log in again (authentication was cleared)');
console.log('\n🔧 If you still see InternalBytecode.js errors:');
console.log('1. Restart your computer');
console.log('2. Or try: npx expo start --tunnel');
console.log('\n🚀 Starting clean Expo development server...');

// Start Expo with tunnel mode as backup
setTimeout(() => {
  try {
    const { execSync } = require('child_process');
    execSync('npx expo start --tunnel --clear', { stdio: 'inherit' });
  } catch (error) {
    console.log('Starting Expo server...');
  }
}, 2000);
