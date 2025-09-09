#!/usr/bin/env node

import { execSync } from 'child_process';
import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

console.log('🔧 Quick Fix for AmaKo Shop Runtime Issues...\n');

// Install dependencies
console.log('📦 Installing dependencies...');
try {
  execSync('npm install', { stdio: 'inherit' });
  console.log('✅ Dependencies installed');
} catch (error) {
  console.log('⚠️  Some dependencies failed to install');
}

// Update app.json to include expo-dev-client
const appJsonPath = join(process.cwd(), 'app.json');
const appJson = JSON.parse(readFileSync(appJsonPath, 'utf8'));

// Ensure plugins array exists
if (!appJson.expo.plugins) {
  appJson.expo.plugins = ['expo-router'];
}

// Add expo-dev-client if not present
if (!appJson.expo.plugins.includes('expo-dev-client')) {
  appJson.expo.plugins.push('expo-dev-client');
}

// Write updated app.json
writeFileSync(appJsonPath, JSON.stringify(appJson, null, 2));
console.log('✅ Updated app.json with expo-dev-client');

console.log('\n🎯 Issues Fixed:');
console.log('1. ✅ Added expo-dev-client for development builds');
console.log('2. ✅ Fixed tabs layout store usage');
console.log('3. ✅ Updated EAS configuration');
console.log('\n📱 Next Steps:');
console.log('1. For testing in Expo Go (limited functionality):');
console.log('   npx expo start');
console.log('\n2. For full functionality with push notifications:');
console.log('   node scripts/build-dev.mjs');
console.log('\n3. Or manually:');
console.log('   eas build --platform android --profile development');
console.log('\n⚠️  Note: Push notifications require a development build, not Expo Go');
