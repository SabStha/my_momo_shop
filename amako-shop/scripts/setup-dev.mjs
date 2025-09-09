#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const rootDir = path.join(__dirname, '..');

console.log('🚀 Setting up Amako Shop development environment...\n');

// Check if .env file exists
const envPath = path.join(rootDir, '.env');
if (fs.existsSync(envPath)) {
  console.log('✅ .env file already exists');
} else {
  console.log('📝 Creating .env file...');
  const envContent = `# API Configuration
EXPO_PUBLIC_API_URL=http://localhost:8000/api

# App Environment
EXPO_PUBLIC_APP_ENV=development

# Note: For Android emulator, the app will automatically use 10.0.2.2:8000
# For iOS simulator or web, it will use localhost:8000
# You can override this by setting EXPO_PUBLIC_API_URL to a specific value
`;
  
  fs.writeFileSync(envPath, envContent);
  console.log('✅ .env file created successfully');
}

// Check if node_modules exists
const nodeModulesPath = path.join(rootDir, 'node_modules');
if (fs.existsSync(nodeModulesPath)) {
  console.log('✅ Dependencies already installed');
} else {
  console.log('📦 Installing dependencies...');
  console.log('   Run: npm install');
}

// Check if Laravel backend is accessible
console.log('\n🔍 Checking backend connectivity...');
console.log('   Make sure your Laravel backend is running on port 8000');
console.log('   Run: php artisan serve');

console.log('\n📱 Development Commands:');
console.log('   npm start          - Start development server');
console.log('   npm run android    - Run on Android');
console.log('   npm run ios        - Run on iOS');
console.log('   npm run web        - Run on web');

console.log('\n🔧 Configuration:');
console.log('   - Android Emulator: http://10.0.2.2:8000/api');
console.log('   - iOS Simulator:    http://localhost:8000/api');
console.log('   - Web:              http://localhost:8000/api');

console.log('\n✅ Setup complete! Happy coding! 🎉');
