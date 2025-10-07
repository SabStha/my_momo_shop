// Test API Connection Script
const http = require('http');

console.log('🔍 Testing API Connection...\n');

const testURLs = [
  'http://192.168.2.145:8000/api/health',
  'http://192.168.2.145:8000/api',
  'http://192.168.2.145:8000/',
];

async function testConnection(url) {
  return new Promise((resolve) => {
    const req = http.get(url, (res) => {
      console.log(`✅ ${url} - Status: ${res.statusCode}`);
      resolve({ url, status: res.statusCode, success: true });
    });
    
    req.on('error', (error) => {
      console.log(`❌ ${url} - Error: ${error.message}`);
      resolve({ url, status: 0, success: false, error: error.message });
    });
    
    req.setTimeout(5000, () => {
      console.log(`⏰ ${url} - Timeout`);
      req.destroy();
      resolve({ url, status: 0, success: false, error: 'timeout' });
    });
  });
}

async function runTests() {
  console.log('📱 Your Laravel server should be running on: http://0.0.0.0:8000');
  console.log('📱 From your phone, it should be accessible at: http://192.168.2.145:8000\n');
  
  for (const url of testURLs) {
    await testConnection(url);
  }
  
  console.log('\n🎯 Expected Results:');
  console.log('✅ At least one URL should be accessible (Status 200 or 404)');
  console.log('❌ If all fail, check your Laravel server is running');
  console.log('\n📋 Make sure your Laravel server is running:');
  console.log('   php artisan serve --host=0.0.0.0 --port=8000');
}

runTests();
