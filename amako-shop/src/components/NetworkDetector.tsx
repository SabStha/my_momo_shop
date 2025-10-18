import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, Alert, TouchableOpacity } from 'react-native';
import { updateBaseURL } from '../api/client';
import { detectNetworkIP } from '../config/network';

// 🎛️ TOGGLE: Set to false to completely hide the network debug icon
const SHOW_NETWORK_DEBUG = true;

interface NetworkDetectorProps {
  children: React.ReactNode;
}

export const NetworkDetector: React.FC<NetworkDetectorProps> = ({ children }) => {
  const [isDetecting, setIsDetecting] = useState(true);
  const [detectedIP, setDetectedIP] = useState<string>('');
  const [error, setError] = useState<string>('');

  useEffect(() => {
    detectAndUpdateNetwork();
  }, []);

  const detectAndUpdateNetwork = async () => {
    try {
      setIsDetecting(true);
      setError('');
      
      console.log('🔍 NetworkDetector: Starting network detection...');
      
      // Detect the best network IP
      const ip = await detectNetworkIP();
      setDetectedIP(ip);
      
      // Update the API client base URL
      await updateBaseURL();
      
      console.log('✅ NetworkDetector: Network detection complete');
      setIsDetecting(false);
    } catch (err) {
      console.error('❌ NetworkDetector: Network detection failed:', err);
      setError('Failed to detect network. Using fallback configuration.');
      setIsDetecting(false);
    }
  };

  const retryDetection = () => {
    detectAndUpdateNetwork();
  };

  const showNetworkOptions = () => {
    Alert.alert(
      'Network Configuration',
      `Current IP: ${detectedIP}\n\nChoose network option:`,
      [
        { text: 'Auto Detect', onPress: detectAndUpdateNetwork },
        { text: 'WiFi (192.168.2.142)', onPress: () => setManualIP('192.168.2.142') },
        { text: 'VirtualBox (192.168.56.1)', onPress: () => setManualIP('192.168.56.1') },
        { text: 'Cancel', style: 'cancel' }
      ]
    );
  };

  const setManualIP = async (ip: string) => {
    try {
      setIsDetecting(true);
      setError('');
      
      console.log(`🔧 NetworkDetector: Setting manual IP to ${ip}`);
      
      // Manually set the base URL
      const { client: apiClient } = await import('../api/client');
      apiClient.defaults.baseURL = `http://${ip}:8000/api`;
      
      setDetectedIP(ip);
      console.log('✅ NetworkDetector: Manual IP set successfully');
      setIsDetecting(false);
    } catch (err) {
      console.error('❌ NetworkDetector: Manual IP setting failed:', err);
      setError('Failed to set manual IP');
      setIsDetecting(false);
    }
  };

  if (isDetecting) {
    return (
      <View style={styles.container}>
        <Text style={styles.title}>🔍 Detecting Network...</Text>
        <Text style={styles.subtitle}>Finding the best connection for your device</Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorTitle}>⚠️ Network Detection Failed</Text>
        <Text style={styles.errorText}>{error}</Text>
        <Text style={styles.retryText} onPress={retryDetection}>
          Tap to retry
        </Text>
      </View>
    );
  }

  return (
    <>
      {children}
      {__DEV__ && SHOW_NETWORK_DEBUG && (
        <TouchableOpacity 
          style={styles.debugIcon} 
          onPress={showNetworkOptions}
          activeOpacity={0.7}
        >
          <Text style={styles.debugIconText}>🌐</Text>
        </TouchableOpacity>
      )}
    </>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 10,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
  },
  errorTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#d32f2f',
    marginBottom: 10,
    textAlign: 'center',
  },
  errorText: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
    marginBottom: 20,
  },
  retryText: {
    fontSize: 16,
    color: '#1976d2',
    textAlign: 'center',
    textDecorationLine: 'underline',
  },
  debugIcon: {
    position: 'absolute',
    bottom: 100,
    right: 10,
    backgroundColor: 'rgba(0,0,0,0.6)',
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  debugIconText: {
    fontSize: 20,
  },
});
