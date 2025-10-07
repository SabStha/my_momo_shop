import { Platform } from 'react-native';

// Common IP ranges for different network types
const NETWORK_IP_RANGES = {
  home: [
    '192.168.1.',   // Common home router range
    '192.168.0.',   // Common home router range
    '192.168.56.',  // Your current home network
  ],
  school: [
    '10.0.',        // Common school/enterprise range
    '172.16.',      // Common school/enterprise range
    '192.168.10.',  // Some school networks
    '192.168.20.',  // Some school networks
  ],
  office: [
    '10.0.',
    '172.16.',
    '192.168.100.',
  ]
};

// Function to detect network type based on device IP (if available)
export const detectNetworkType = (): 'home' | 'school' | 'office' | 'unknown' => {
  // This is a simplified detection - in a real app you might use
  // react-native-network-info or similar to get actual device IP
  return 'home'; // Default to home for now
};

// Function to get the most likely IP for the current network
export const getNetworkIP = (): string => {
  const networkType = detectNetworkType();
  
  switch (networkType) {
    case 'home':
      // Return your most common home IP
      return '192.168.56.1';
    case 'school':
      // Return a common school network IP
      return '192.168.10.1';
    case 'office':
      // Return a common office network IP
      return '10.0.0.1';
    default:
      // Fallback to your current IP
      return '192.168.56.1';
  }
};

// Function to get all possible IPs for the current network type
export const getPossibleIPs = (): string[] => {
  const networkType = detectNetworkType();
  const baseIPs = NETWORK_IP_RANGES[networkType] || NETWORK_IP_RANGES.home;
  
  // Generate common IPs for this network type
  const possibleIPs: string[] = [];
  
  baseIPs.forEach(baseIP => {
    // Add common router IPs
    possibleIPs.push(baseIP + '1');
    possibleIPs.push(baseIP + '100');
    possibleIPs.push(baseIP + '254');
  });
  
  return possibleIPs;
};

// Function to test if an IP is reachable (simplified)
export const testIPReachability = async (ip: string): Promise<boolean> => {
  try {
    const response = await fetch(`http://${ip}:8000/api/health`, {
      method: 'GET',
      timeout: 3000, // 3 second timeout
    });
    return response.ok;
  } catch (error) {
    return false;
  }
};

// Function to find the working IP automatically
export const findWorkingIP = async (): Promise<string | null> => {
  const possibleIPs = getPossibleIPs();
  
  // Test each IP to find one that works
  for (const ip of possibleIPs) {
    const isReachable = await testIPReachability(ip);
    if (isReachable) {
      return ip;
    }
  }
  
  return null;
};
