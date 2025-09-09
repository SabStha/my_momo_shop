#!/usr/bin/env node

import { execSync } from 'child_process';
import { rmSync, existsSync } from 'fs';
import { join } from 'path';

console.log('🧹 Clearing All Caches...\n');

const projectRoot = process.cwd();

// 1. Stop any running Metro processes
console.log('🔧 1. Stopping Metro processes...');
try {
  execSync('taskkill /f /im node.exe', { stdio: 'ignore' });
  console.log('✅ Stopped Node processes');
} catch (error) {
  console.log('⚠️  No Node processes to stop');
}

// 2. Clear Expo caches
console.log('\n🔧 2. Clearing Expo caches...');
const expoCacheDirs = [
  '.expo',
  '.expo-shared',
  'node_modules/.cache',
  'node_modules/.expo'
];

expoCacheDirs.forEach(dir => {
  const fullPath = join(projectRoot, dir);
  if (existsSync(fullPath)) {
    try {
      rmSync(fullPath, { recursive: true, force: true });
      console.log(`✅ Cleared ${dir}`);
    } catch (error) {
      console.log(`⚠️  Could not clear ${dir}: ${error.message}`);
    }
  }
});

// 3. Clear Metro caches
console.log('\n🔧 3. Clearing Metro caches...');
const metroCacheDirs = [
  '.metro-cache',
  'metro-cache',
  'node_modules/metro-cache'
];

metroCacheDirs.forEach(dir => {
  const fullPath = join(projectRoot, dir);
  if (existsSync(fullPath)) {
    try {
      rmSync(fullPath, { recursive: true, force: true });
      console.log(`✅ Cleared ${dir}`);
    } catch (error) {
      console.log(`⚠️  Could not clear ${dir}: ${error.message}`);
    }
  }
});

// 4. Clear React Native caches
console.log('\n🔧 4. Clearing React Native caches...');
const rnCacheDirs = [
  'android/app/build',
  'android/build',
  'ios/build',
  'ios/Pods'
];

rnCacheDirs.forEach(dir => {
  const fullPath = join(projectRoot, dir);
  if (existsSync(fullPath)) {
    try {
      rmSync(fullPath, { recursive: true, force: true });
      console.log(`✅ Cleared ${dir}`);
    } catch (error) {
      console.log(`⚠️  Could not clear ${dir}: ${error.message}`);
    }
  }
});

// 5. Clear package manager caches
console.log('\n🔧 5. Clearing package manager caches...');
try {
  execSync('npm cache clean --force', { stdio: 'inherit' });
  console.log('✅ Cleared npm cache');
} catch (error) {
  console.log('⚠️  Could not clear npm cache');
}

// 6. Instructions for next steps
console.log('\n🎯 Cache Clearing Complete!');
console.log('\n📱 Next Steps:');
console.log('1. Reinstall dependencies:');
console.log('   npm install');
console.log('');
console.log('2. Start Expo with clean cache:');
console.log('   npx expo start --clear --reset-cache');
console.log('');
console.log('3. If issues persist, try:');
console.log('   npx expo doctor');
console.log('   npx expo install --fix');
console.log('');
console.log('⚠️  The persistent errors should now be resolved!');
console.log('   Metro will rebuild everything from scratch.');
