import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  Alert,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { DriverLocationTracker } from './DriverLocationTracker';

export const DriverApp: React.FC = () => {
  const [driverId, setDriverId] = useState('driver_001');
  const [orderId, setOrderId] = useState('');
  const [isTracking, setIsTracking] = useState(false);

  const startTracking = () => {
    if (!orderId.trim()) {
      Alert.alert('Error', 'Please enter an order ID');
      return;
    }
    setIsTracking(true);
  };

  const stopTracking = () => {
    setIsTracking(false);
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>🚚 Driver App</Text>
        <Text style={styles.subtitle}>GPS Location Tracker</Text>
      </View>

      <View style={styles.formCard}>
        <Text style={styles.formTitle}>Driver Information</Text>
        
        <View style={styles.inputGroup}>
          <Text style={styles.inputLabel}>Driver ID</Text>
          <TextInput
            style={styles.input}
            value={driverId}
            onChangeText={setDriverId}
            placeholder="Enter driver ID"
            editable={!isTracking}
          />
        </View>

        <View style={styles.inputGroup}>
          <Text style={styles.inputLabel}>Order ID</Text>
          <TextInput
            style={styles.input}
            value={orderId}
            onChangeText={setOrderId}
            placeholder="Enter order ID"
            editable={!isTracking}
          />
        </View>

        <View style={styles.buttonContainer}>
          {!isTracking ? (
            <TouchableOpacity
              style={[styles.button, styles.startButton]}
              onPress={startTracking}
            >
              <Ionicons name="play" size={20} color="#FFF" />
              <Text style={styles.buttonText}>Start Tracking</Text>
            </TouchableOpacity>
          ) : (
            <TouchableOpacity
              style={[styles.button, styles.stopButton]}
              onPress={stopTracking}
            >
              <Ionicons name="stop" size={20} color="#FFF" />
              <Text style={styles.buttonText}>Stop Tracking</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>

      {isTracking && (
        <DriverLocationTracker
          driverId={driverId}
          orderId={orderId}
          isActive={isTracking}
        />
      )}

      <View style={styles.infoCard}>
        <Text style={styles.infoTitle}>📱 How to Use</Text>
        <View style={styles.infoList}>
          <Text style={styles.infoItem}>1. Enter your driver ID and order ID</Text>
          <Text style={styles.infoItem}>2. Tap "Start Tracking" to begin GPS tracking</Text>
          <Text style={styles.infoItem}>3. Your location will be sent to the server every 5 seconds</Text>
          <Text style={styles.infoItem}>4. Customers can see your live location on their map</Text>
          <Text style={styles.infoItem}>5. Tap "Stop Tracking" when delivery is complete</Text>
        </View>
      </View>

      <View style={styles.statusCard}>
        <Text style={styles.statusTitle}>🔒 Privacy & Permissions</Text>
        <Text style={styles.statusText}>
          This app requires location permissions to track your delivery route. 
          Your location data is only shared with customers for active orders.
        </Text>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  header: {
    backgroundColor: '#A43E2D',
    padding: 24,
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFF',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
    color: 'rgba(255,255,255,0.9)',
  },
  formCard: {
    backgroundColor: '#FFF',
    margin: 16,
    padding: 20,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  formTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  inputGroup: {
    marginBottom: 16,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#555',
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#FAFAFA',
  },
  buttonContainer: {
    marginTop: 8,
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 16,
    borderRadius: 8,
    gap: 8,
  },
  startButton: {
    backgroundColor: '#10B981',
  },
  stopButton: {
    backgroundColor: '#EF4444',
  },
  buttonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  infoCard: {
    backgroundColor: '#FFF',
    margin: 16,
    padding: 20,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  infoTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  infoList: {
    gap: 8,
  },
  infoItem: {
    fontSize: 14,
    color: '#666',
    lineHeight: 20,
  },
  statusCard: {
    backgroundColor: '#EFF6FF',
    margin: 16,
    padding: 16,
    borderRadius: 12,
    borderLeftWidth: 4,
    borderLeftColor: '#3B82F6',
  },
  statusTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1E40AF',
    marginBottom: 8,
  },
  statusText: {
    fontSize: 14,
    color: '#374151',
    lineHeight: 20,
  },
});
