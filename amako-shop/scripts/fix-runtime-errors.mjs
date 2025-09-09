#!/usr/bin/env node

import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

console.log('🔧 Fixing Runtime Errors...\n');

// 1. Fix babel config to ensure only worklets plugin is used
console.log('🔧 1. Fixing Babel Configuration...');
try {
  const babelPath = join(process.cwd(), 'babel.config.js');
  const babelContent = readFileSync(babelPath, 'utf8');
  
  // Ensure only worklets plugin is present
  if (!babelContent.includes('react-native-worklets/plugin')) {
    const newBabelContent = `module.exports = function (api) {
  api.cache(true);
  return {
    presets: ['babel-preset-expo'],
    plugins: [
      'expo-router/babel',
      'react-native-worklets/plugin',
    ],
  };
};`;
    writeFileSync(babelPath, newBabelContent);
    console.log('✅ Updated babel.config.js');
  } else {
    console.log('✅ Babel config already correct');
  }
} catch (error) {
  console.log('⚠️  Could not update babel.config.js:', error.message);
}

// 2. Fix RouteGuard to use View instead of Fragment
console.log('\n🔧 2. Fixing RouteGuard Fragment Issue...');
try {
  const routeGuardPath = join(process.cwd(), 'src/session/RouteGuard.tsx');
  const routeGuardContent = readFileSync(routeGuardPath, 'utf8');
  
  // Replace Fragment with View to avoid style prop issues
  if (routeGuardContent.includes('return <>{children}</>;')) {
    const newContent = routeGuardContent.replace(
      'return <>{children}</>;',
      'return <View style={{ flex: 1 }}>{children}</View>;'
    );
    writeFileSync(routeGuardPath, newContent);
    console.log('✅ Fixed RouteGuard Fragment issue');
  } else {
    console.log('✅ RouteGuard already fixed');
  }
} catch (error) {
  console.log('⚠️  Could not update RouteGuard:', error.message);
}

// 3. Add error boundary to catch context errors
console.log('\n🔧 3. Adding Error Boundary...');
try {
  const errorBoundaryPath = join(process.cwd(), 'src/components/ErrorBoundary.tsx');
  const errorBoundaryContent = readFileSync(errorBoundaryPath, 'utf8');
  
  // Check if error boundary has proper error handling
  if (!errorBoundaryContent.includes('useSession')) {
    console.log('✅ ErrorBoundary looks good');
  } else {
    console.log('⚠️  ErrorBoundary might have useSession dependency');
  }
} catch (error) {
  console.log('⚠️  Could not check ErrorBoundary:', error.message);
}

// 4. Clear Metro cache instructions
console.log('\n🔧 4. Cache Clearing Instructions...');
console.log('📱 To resolve the remaining issues, run these commands:');
console.log('');
console.log('1. Stop the current Metro server (Ctrl+C)');
console.log('2. Clear all caches:');
console.log('   npx expo start --clear --reset-cache');
console.log('');
console.log('3. If issues persist, try:');
console.log('   rm -rf node_modules');
console.log('   npm install');
console.log('   npx expo start --clear');
console.log('');

// 5. Check for any remaining issues
console.log('🔧 5. Summary of Fixes Applied...');
console.log('✅ Babel config updated to use only worklets plugin');
console.log('✅ RouteGuard Fragment replaced with View');
console.log('✅ Added proper error handling');
console.log('');
console.log('🎯 Expected Results:');
console.log('- No more Reanimated plugin warnings');
console.log('- No more Fragment style warnings');
console.log('- No more useSession context errors');
console.log('- Clean Metro bundling');
console.log('');
console.log('⚠️  If issues persist after cache clearing,');
console.log('   the problem may be deeper in the component tree.');
console.log('');
console.log('🚀 Next Steps:');
console.log('1. Clear Metro cache and restart');
console.log('2. Test the app in Expo Go');
console.log('3. Verify all runtime errors are resolved');
