// Quick fix for network detection loop
// This script will update your configuration to use a static IP

const fs = require('fs');
const path = require('path');

console.log('🔧 Fixing network detection loop...');

// Update network.ts
const networkConfigPath = path.join(__dirname, 'src/config/network.ts');
let networkConfig = fs.readFileSync(networkConfigPath, 'utf8');

// Change to use wifi mode with correct IP
networkConfig = networkConfig.replace(
  "export const NETWORK_MODE: NetworkMode = 'auto';",
  "export const NETWORK_MODE: NetworkMode = 'wifi';"
);

networkConfig = networkConfig.replace(
  "ip: '192.168.0.19', // Your current WiFi IP",
  "ip: '192.168.2.145', // Your actual WiFi IP"
);

fs.writeFileSync(networkConfigPath, networkConfig);
console.log('✅ Updated network.ts');

// Update api.ts
const apiConfigPath = path.join(__dirname, 'src/config/api.ts');
let apiConfig = fs.readFileSync(apiConfigPath, 'utf8');

// Change BASE_URL to use correct IP
apiConfig = apiConfig.replace(
  "export const BASE_URL = 'http://192.168.0.19:8000/api'; // Fallback IP",
  "export const BASE_URL = 'http://192.168.2.145:8000/api'; // Your actual WiFi IP"
);

fs.writeFileSync(apiConfigPath, apiConfig);
console.log('✅ Updated api.ts');

console.log('');
console.log('🎯 Network configuration fixed!');
console.log('📱 Your app will now use: http://192.168.2.145:8000/api');
console.log('');
console.log('📋 Make sure your Laravel server is running:');
console.log('   php artisan serve --host=0.0.0.0 --port=8000');
console.log('');
console.log('🔄 Reload your app to apply the changes.');
