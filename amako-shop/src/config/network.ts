// Fixed Network Configuration - No Auto-Detection Loop
import { Platform } from 'react-native';

export type NetworkMode = 'wifi' | 'tunnel';

// Use static WiFi configuration to avoid detection loops
export const NETWORK_MODE: NetworkMode = 'wifi';

// Simple network configurations
export const NETWORK_CONFIGS = {
  wifi: {
    ip: '192.168.2.142', // Your actual WiFi IP (Home Network)
    name: 'WiFi Network',
    description: 'Your current WiFi network'
  },
  tunnel: {
    ip: '1rt7vr4-sabstha98-8081.exp.direct', // Expo tunnel URL
    name: 'Expo Tunnel',
    description: 'Expo development tunnel'
  }
};

// Simple function to get current network IP (no auto-detection)
export const getCurrentNetworkIP = async (): Promise<string> => {
  return NETWORK_CONFIGS[NETWORK_MODE].ip;
};

// Function to get current network info
export const getCurrentNetworkInfo = () => {
  return NETWORK_CONFIGS[NETWORK_MODE];
};

// Simple network detection without loops
export const detectNetworkIP = async (): Promise<string> => {
  return NETWORK_CONFIGS[NETWORK_MODE].ip;
};
