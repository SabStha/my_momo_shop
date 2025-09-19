#!/usr/bin/env node

/**
 * Find Wi-Fi IP Address Script
 * Helps users find their actual Wi-Fi IP for LAN mode development
 */

const { exec } = require('child_process');
const os = require('os');

function findWiFiIP() {
  const platform = os.platform();
  
  console.log('🔍 Finding your Wi-Fi IP address...\n');
  
  if (platform === 'win32') {
    // Windows
    exec('ipconfig', (error, stdout, stderr) => {
      if (error) {
        console.error('❌ Error running ipconfig:', error.message);
        return;
      }
      
      const lines = stdout.split('\n');
      let inWiFiSection = false;
      
      for (const line of lines) {
        if (line.includes('Wireless LAN adapter Wi-Fi') || line.includes('Wireless LAN adapter WLAN')) {
          inWiFiSection = true;
          continue;
        }
        
        if (inWiFiSection && line.includes('IPv4 Address')) {
          const ipMatch = line.match(/(\d+\.\d+\.\d+\.\d+)/);
          if (ipMatch) {
            const ip = ipMatch[1];
            console.log('✅ Found Wi-Fi IP:', ip);
            console.log('\n📝 To use LAN mode:');
            console.log(`1. Update package.json scripts`);
            console.log(`2. Replace <REPLACE_WITH_WIFI_IP> with: ${ip}`);
            console.log(`3. Run: npm run start:lan`);
            console.log('\n💡 Or use tunnel mode (recommended): npm run start:tunnel');
            return;
          }
        }
        
        if (inWiFiSection && line.trim() === '') {
          inWiFiSection = false;
        }
      }
      
      console.log('❌ Could not find Wi-Fi IP address');
      console.log('💡 Try running: ipconfig | findstr "IPv4"');
    });
    
  } else if (platform === 'darwin') {
    // macOS
    exec('ifconfig en0 | grep "inet "', (error, stdout, stderr) => {
      if (error) {
        console.error('❌ Error running ifconfig:', error.message);
        return;
      }
      
      const ipMatch = stdout.match(/(\d+\.\d+\.\d+\.\d+)/);
      if (ipMatch) {
        const ip = ipMatch[1];
        console.log('✅ Found Wi-Fi IP:', ip);
        console.log('\n📝 To use LAN mode:');
        console.log(`1. Update package.json scripts`);
        console.log(`2. Replace <REPLACE_WITH_WIFI_IP> with: ${ip}`);
        console.log(`3. Run: npm run start:lan`);
        console.log('\n💡 Or use tunnel mode (recommended): npm run start:tunnel');
      } else {
        console.log('❌ Could not find Wi-Fi IP address');
        console.log('💡 Try running: ifconfig | grep "inet "');
      }
    });
    
  } else {
    // Linux
    exec('ip route get 1.1.1.1 | awk \'{print $7}\'', (error, stdout, stderr) => {
      if (error) {
        console.error('❌ Error running ip route:', error.message);
        return;
      }
      
      const ip = stdout.trim();
      if (ip && /^\d+\.\d+\.\d+\.\d+$/.test(ip)) {
        console.log('✅ Found Wi-Fi IP:', ip);
        console.log('\n📝 To use LAN mode:');
        console.log(`1. Update package.json scripts`);
        console.log(`2. Replace <REPLACE_WITH_WIFI_IP> with: ${ip}`);
        console.log(`3. Run: npm run start:lan`);
        console.log('\n💡 Or use tunnel mode (recommended): npm run start:tunnel');
      } else {
        console.log('❌ Could not find Wi-Fi IP address');
        console.log('💡 Try running: ip addr show | grep "inet "');
      }
    });
  }
}

// Check for VirtualBox Host-Only adapter warning
function checkVirtualBox() {
  const networkInterfaces = os.networkInterfaces();
  
  for (const [name, interfaces] of Object.entries(networkInterfaces)) {
    if (name.toLowerCase().includes('virtualbox') || name.toLowerCase().includes('vbox')) {
      console.log('⚠️  Warning: VirtualBox network adapter detected:', name);
      console.log('   This may interfere with LAN mode. Consider disabling it.');
      console.log('   Use tunnel mode instead: npm run start:tunnel\n');
      break;
    }
  }
}

// Main execution
console.log('🌐 AmaKo Shop - Wi-Fi IP Finder\n');
checkVirtualBox();
findWiFiIP();
