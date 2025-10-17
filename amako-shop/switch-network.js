#!/usr/bin/env node

// Simple script to switch between home and school networks
const fs = require('fs');
const path = require('path');

const networkConfigPath = path.join(__dirname, 'src', 'config', 'network.ts');

const networks = {
  home: {
    ip: '192.168.0.19',
    name: 'Home Network'
  },
  school: {
    ip: '192.168.2.142', // Your school/work IP
    name: 'School Network'
  }
};

function switchNetwork(networkType) {
  if (!networks[networkType]) {
    console.error(`❌ Unknown network type: ${networkType}`);
    console.log('Available networks:', Object.keys(networks).join(', '));
    process.exit(1);
  }

  try {
    // Read the current network config
    let content = fs.readFileSync(networkConfigPath, 'utf8');
    
    // Update the NETWORK_MODE
    content = content.replace(
      /export const NETWORK_MODE: NetworkMode = '[^']*';/,
      `export const NETWORK_MODE: NetworkMode = '${networkType}';`
    );
    
    // Update the IP in the config
    const network = networks[networkType];
    content = content.replace(
      new RegExp(`ip: '[^']*',\\s*// ${networkType} network`),
      `ip: '${network.ip}', // ${networkType} network`
    );
    
    // Write back to file
    fs.writeFileSync(networkConfigPath, content);
    
    console.log(`✅ Switched to ${network.name}`);
    console.log(`📍 IP Address: ${network.ip}`);
    console.log('🔄 Please reload your app to apply changes');
    
  } catch (error) {
    console.error('❌ Error switching network:', error.message);
    process.exit(1);
  }
}

// Get network type from command line arguments
const networkType = process.argv[2];

if (!networkType) {
  console.log('🔧 Network Switcher for Amako Shop');
  console.log('');
  console.log('Usage: node switch-network.js <network-type>');
  console.log('');
  console.log('Available networks:');
  Object.entries(networks).forEach(([key, config]) => {
    console.log(`  ${key}: ${config.name} (${config.ip})`);
  });
  console.log('');
  console.log('Examples:');
  console.log('  node switch-network.js home');
  console.log('  node switch-network.js school');
  process.exit(0);
}

switchNetwork(networkType);
