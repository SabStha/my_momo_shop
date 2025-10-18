import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Alert, Platform } from 'react-native';
import * as Location from 'expo-location';
import { apiClient } from '../../api/client';

interface DriverLocationTrackerProps {
  driverId: string;
  orderId: string;
  isActive: boolean;
}

interface LocationData {
  latitude: number;
  longitude: number;
  accuracy: number;
  timestamp: number;
}

export const DriverLocationTracker: React.FC<DriverLocationTrackerProps> = ({
  driverId,
  orderId,
  isActive
}) => {
  const [location, setLocation] = useState<LocationData | null>(null);
  const [isTracking, setIsTracking] = useState(false);
  const [permissionStatus, setPermissionStatus] = useState<string>('unknown');
  const [error, setError] = useState<string | null>(null);
  
  const trackingInterval = useRef<NodeJS.Timeout | null>(null);
  const lastSentLocation = useRef<LocationData | null>(null);

  // Request location permissions
  useEffect(() => {
    requestLocationPermission();
  }, []);

  // Start/stop tracking based on isActive prop
  useEffect(() => {
    if (isActive && permissionStatus === 'granted') {
      startTracking();
    } else {
      stopTracking();
    }
    
    return () => stopTracking();
  }, [isActive, permissionStatus]);

  const requestLocationPermission = async () => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      setPermissionStatus(status);
      
      if (status !== 'granted') {
        setError('Location permission is required for delivery tracking');
        return;
      }

      // Also request background permission for continuous tracking
      if (Platform.OS === 'ios') {
        const backgroundStatus = await Location.requestBackgroundPermissionsAsync();
        if (backgroundStatus.status !== 'granted') {
          console.warn('Background location permission not granted');
        }
      }
    } catch (err) {
      setError('Failed to request location permission');
      console.error('Permission error:', err);
    }
  };

  const startTracking = () => {
    if (isTracking) return;
    
    setIsTracking(true);
    setError(null);
    
    // Get initial location
    getCurrentLocation();
    
    // Set up interval for continuous tracking
    trackingInterval.current = setInterval(() => {
      getCurrentLocation();
    }, 5000); // Update every 5 seconds
  };

  const stopTracking = () => {
    if (trackingInterval.current) {
      clearInterval(trackingInterval.current);
      trackingInterval.current = null;
    }
    setIsTracking(false);
  };

  const getCurrentLocation = async () => {
    try {
      const locationResult = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.High,
        maximumAge: 10000, // Accept location up to 10 seconds old
      });

      const newLocation: LocationData = {
        latitude: locationResult.coords.latitude,
        longitude: locationResult.coords.longitude,
        accuracy: locationResult.coords.accuracy || 0,
        timestamp: Date.now(),
      };

      setLocation(newLocation);

      // Only send if location has changed significantly (more than 10 meters)
      if (shouldSendLocation(newLocation)) {
        await sendLocationToServer(newLocation);
        lastSentLocation.current = newLocation;
      }
    } catch (err) {
      console.error('Location error:', err);
      setError('Failed to get current location');
    }
  };

  const shouldSendLocation = (newLocation: LocationData): boolean => {
    if (!lastSentLocation.current) return true;
    
    // Calculate distance between last sent location and new location
    const distance = calculateDistance(
      lastSentLocation.current.latitude,
      lastSentLocation.current.longitude,
      newLocation.latitude,
      newLocation.longitude
    );
    
    // Send if moved more than 10 meters
    return distance > 10;
  };

  const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number): number => {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI/180;
    const φ2 = lat2 * Math.PI/180;
    const Δφ = (lat2-lat1) * Math.PI/180;
    const Δλ = (lon2-lon1) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c;
  };

  const sendLocationToServer = async (locationData: LocationData) => {
    try {
      const response = await apiClient.post('/driver/location', {
        driver_id: driverId,
        order_id: orderId,
        latitude: locationData.latitude,
        longitude: locationData.longitude,
        accuracy: locationData.accuracy,
        timestamp: locationData.timestamp,
      });

      console.log('✅ Location sent to server:', response.data);
    } catch (err) {
      console.error('❌ Failed to send location:', err);
      setError('Failed to send location to server');
    }
  };

  if (permissionStatus !== 'granted') {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>
          Location permission required for tracking
        </Text>
        <Text style={styles.subText}>
          Please enable location access in your device settings
        </Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>{error}</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.statusText}>
        {isTracking ? '🟢 Tracking Active' : '🔴 Tracking Stopped'}
      </Text>
      
      {location && (
        <View style={styles.locationInfo}>
          <Text style={styles.coordsText}>
            📍 {location.latitude.toFixed(6)}, {location.longitude.toFixed(6)}
          </Text>
          <Text style={styles.accuracyText}>
            Accuracy: {location.accuracy.toFixed(0)}m
          </Text>
        </View>
      )}
      
      <Text style={styles.instructionText}>
        {isTracking 
          ? 'Your location is being tracked for delivery' 
          : 'Start delivery to begin location tracking'
        }
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 16,
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    margin: 16,
  },
  statusText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2c3e50',
    textAlign: 'center',
    marginBottom: 8,
  },
  locationInfo: {
    backgroundColor: '#e8f5e8',
    padding: 12,
    borderRadius: 6,
    marginBottom: 8,
  },
  coordsText: {
    fontSize: 14,
    color: '#27ae60',
    fontWeight: '500',
  },
  accuracyText: {
    fontSize: 12,
    color: '#7f8c8d',
    marginTop: 4,
  },
  instructionText: {
    fontSize: 12,
    color: '#7f8c8d',
    textAlign: 'center',
    fontStyle: 'italic',
  },
  errorText: {
    fontSize: 14,
    color: '#e74c3c',
    textAlign: 'center',
    marginBottom: 8,
  },
  subText: {
    fontSize: 12,
    color: '#7f8c8d',
    textAlign: 'center',
  },
});
