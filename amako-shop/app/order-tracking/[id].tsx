import React, { useEffect, useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  TouchableOpacity,
  Dimensions,
  ScrollView,
  RefreshControl,
  Linking,
  Alert,
} from 'react-native';
// import MapView, { Marker, Polyline, PROVIDER_GOOGLE } from 'react-native-maps';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui';
import { useBackendOrder } from '../../src/hooks/useOrders';
import * as Location from 'expo-location';
import { client as apiClient } from '../../src/api/client';

const { width, height } = Dimensions.get('window');
const ASPECT_RATIO = width / height;
const LATITUDE_DELTA = 0.02;
const LONGITUDE_DELTA = LATITUDE_DELTA * ASPECT_RATIO;

interface TrackingPoint {
  id: number;
  order_id: number;
  driver_id: number;
  status: string;
  latitude: string;
  longitude: string;
  photo_url: string | null;
  notes: string | null;
  created_at: string;
  driver: {
    id: number;
    name: string;
    email: string;
    phone?: string;
  };
}

interface DeliveryAddress {
  area_locality?: string;
  ward_number?: string;
  city?: string;
  building_name?: string;
  detailed_directions?: string;
}

export default function OrderTrackingScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const backendOrderId = id?.toString().replace('order_', '');
  const numericOrderId = parseInt(backendOrderId || '0');
  
  if (__DEV__) {
    console.log('🗺️ OrderTrackingScreen: Rendering with ID:', id);
    console.log('🗺️ OrderTrackingScreen: Numeric ID:', numericOrderId);
  }
  
  const { data: order, isLoading: orderLoading } = useBackendOrder(numericOrderId);
  const [tracking, setTracking] = useState<TrackingPoint[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [userLocation, setUserLocation] = useState<Location.LocationObject | null>(null);
  // const mapRef = useRef<MapView>(null);
  const trackingIntervalRef = useRef<NodeJS.Timeout | null>(null);

  // Get user's current location
  useEffect(() => {
    (async () => {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status === 'granted') {
        const location = await Location.getCurrentPositionAsync({});
        setUserLocation(location);
      }
    })();
  }, []);

  // Fetch tracking data
  const fetchTracking = async () => {
    try {
      const response = await apiClient.get(`/orders/${numericOrderId}/tracking`);
      const data = response.data;
      
      if (data.success && data.tracking) {
        setTracking(data.tracking);
        
        // Auto-focus on latest driver location
        // if (data.tracking.length > 0 && mapRef.current) {
        //   const latestPoint = data.tracking[data.tracking.length - 1];
        //   mapRef.current.animateToRegion({
        //     latitude: parseFloat(latestPoint.latitude),
        //     longitude: parseFloat(latestPoint.longitude),
        //     latitudeDelta: LATITUDE_DELTA,
        //     longitudeDelta: LONGITUDE_DELTA,
        //   }, 1000);
        // }
      }
    } catch (error) {
      console.error('Error fetching tracking:', error);
      if (__DEV__) {
        console.log('Tracking fetch failed for order:', numericOrderId);
        console.log('Error details:', JSON.stringify(error, null, 2));
      }
    } finally {
      setIsLoading(false);
    }
  };

  // Initial fetch
  useEffect(() => {
    fetchTracking();
  }, [numericOrderId]);

  // Auto-refresh every 5 seconds for active deliveries
  useEffect(() => {
    if (order?.status === 'out_for_delivery') {
      trackingIntervalRef.current = setInterval(() => {
        fetchTracking();
      }, 5000);

      return () => {
        if (trackingIntervalRef.current) {
          clearInterval(trackingIntervalRef.current);
        }
      };
    }
  }, [order?.status, numericOrderId]);

  const handleRefresh = async () => {
    setRefreshing(true);
    await fetchTracking();
    setRefreshing(false);
  };

  const getDeliveryAddress = (): DeliveryAddress | null => {
    if (!order?.delivery_address) return null;
    
    if (typeof order.delivery_address === 'string') {
      try {
        return JSON.parse(order.delivery_address);
      } catch {
        return null;
      }
    }
    
    return order.delivery_address as DeliveryAddress;
  };

  const getLatestLocation = () => {
    if (tracking.length === 0) return null;
    const latest = tracking[tracking.length - 1];
    return {
      latitude: parseFloat(latest.latitude),
      longitude: parseFloat(latest.longitude),
    };
  };

  const getDriverInfo = () => {
    if (tracking.length === 0) return null;
    return tracking[tracking.length - 1].driver;
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending':
        return colors.warning[500];
      case 'confirmed':
      case 'preparing':
        return colors.info[500];
      case 'ready':
        return colors.success[400];
      case 'out_for_delivery':
        return colors.primary[500];
      case 'delivered':
        return colors.success[600];
      default:
        return colors.gray[500];
    }
  };

  const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
      pending: 'Pending',
      confirmed: 'Confirmed',
      preparing: 'Preparing',
      ready: 'Ready for Pickup',
      out_for_delivery: 'Out for Delivery',
      delivered: 'Delivered',
    };
    return labels[status] || status;
  };

  // Calculate distance between two coordinates (Haversine formula)
  const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number): number => {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
      Math.sin(dLat/2) * Math.sin(dLat/2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
  };

  // Calculate ETA based on distance
  const calculateETA = (): { minutes: number; distance: number } | null => {
    const driverLocation = getLatestLocation();
    if (!driverLocation || !userLocation) return null;

    const distance = calculateDistance(
      driverLocation.latitude,
      driverLocation.longitude,
      userLocation.coords.latitude,
      userLocation.coords.longitude
    );

    // Average speed in city: 20 km/h
    const averageSpeed = 20;
    const timeInHours = distance / averageSpeed;
    const timeInMinutes = Math.ceil(timeInHours * 60);

    return { minutes: timeInMinutes, distance };
  };

  // Open phone dialer
  const callDriver = () => {
    const driver = getDriverInfo();
    if (driver?.phone) {
      Linking.openURL(`tel:${driver.phone}`);
    } else {
      Alert.alert('Not Available', 'Driver phone number is not available');
    }
  };

  // Open messaging app
  const messageDriver = () => {
    const driver = getDriverInfo();
    if (driver?.phone) {
      Linking.openURL(`sms:${driver.phone}`);
    } else {
      Alert.alert('Not Available', 'Driver phone number is not available');
    }
  };

  // Open address in Google Maps
  const openInMaps = () => {
    const address = getDeliveryAddress();
    if (address) {
      const query = encodeURIComponent(
        `${address.building_name || ''} ${address.area_locality || ''} ${address.city || ''}`.trim()
      );
      Linking.openURL(`https://www.google.com/maps/search/?api=1&query=${query}`);
    }
  };

  if (orderLoading || isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={colors.primary[500]} />
        <Text style={styles.loadingText}>Loading tracking...</Text>
      </View>
    );
  }

  if (!order) {
    return (
      <View style={styles.errorContainer}>
        <Ionicons name="alert-circle" size={64} color={colors.error[500]} />
        <Text style={styles.errorText}>Order not found</Text>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const driverLocation = getLatestLocation();
  const driverInfo = getDriverInfo();
  const address = getDeliveryAddress();
  const eta = calculateETA();

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.headerBackButton}
          onPress={() => router.back()}
        >
          <Ionicons name="arrow-back" size={24} color={colors.gray[900]} />
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>Track Order</Text>
          <Text style={styles.headerSubtitle}>#{order.order_number}</Text>
        </View>
        <TouchableOpacity
          style={styles.refreshButton}
          onPress={handleRefresh}
        >
          <Ionicons name="refresh" size={24} color={colors.gray[900]} />
        </TouchableOpacity>
      </View>

      {/* Location Info Card (Map temporarily disabled - requires development build) */}
      {driverLocation ? (
        <View style={styles.mapPlaceholder}>
          <View style={styles.locationCard}>
            <Ionicons name="location" size={48} color={colors.primary[500]} />
            <Text style={styles.locationTitle}>Driver Location</Text>
            <Text style={styles.locationCoords}>
              📍 {driverLocation.latitude.toFixed(6)}, {driverLocation.longitude.toFixed(6)}
            </Text>
            <Text style={styles.locationNote}>
              🗺️ Map view requires development build
            </Text>
            {driverInfo && (
              <Text style={styles.driverNameInMap}>
                Driver: {driverInfo.name}
              </Text>
            )}
          </View>
        </View>
      ) : (
        <View style={styles.noMapContainer}>
          <Ionicons name="map-outline" size={64} color={colors.gray[400]} />
          <Text style={styles.noMapText}>
            {order.status === 'out_for_delivery'
              ? 'Waiting for driver location...'
              : 'Driver has not started delivery yet'}
          </Text>
        </View>
      )}

      {/* Status & Info Card */}
      <ScrollView
        style={styles.infoContainer}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
        }
      >
        {/* Order Status */}
        <View style={styles.card}>
          <View style={styles.statusContainer}>
            <View
              style={[
                styles.statusBadge,
                { backgroundColor: getStatusColor(order.status) },
              ]}
            >
              <Text style={styles.statusText}>
                {getStatusLabel(order.status)}
              </Text>
            </View>
            {order.status === 'out_for_delivery' && (
              <View style={styles.pulseContainer}>
                <View style={styles.pulse} />
                <Text style={styles.liveText}>LIVE</Text>
              </View>
            )}
          </View>
        </View>

        {/* ETA Display */}
        {eta && order.status === 'out_for_delivery' && (
          <View style={styles.card}>
            <View style={styles.etaContainer}>
              <View style={styles.etaIcon}>
                <Ionicons name="time" size={32} color={colors.primary[500]} />
              </View>
              <View style={styles.etaContent}>
                <Text style={styles.etaLabel}>Estimated Arrival</Text>
                <View style={styles.etaTimeContainer}>
                  <Text style={styles.etaMinutes}>{eta.minutes}</Text>
                  <Text style={styles.etaUnit}>mins</Text>
                </View>
                <Text style={styles.etaDistance}>
                  🚗 {eta.distance.toFixed(1)} km away
                </Text>
              </View>
            </View>
          </View>
        )}

        {/* Driver Info */}
        {driverInfo && (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Driver Information</Text>
            <View style={styles.driverInfo}>
              <View style={styles.driverAvatar}>
                <Ionicons name="person" size={32} color={colors.primary[500]} />
              </View>
              <View style={styles.driverDetails}>
                <Text style={styles.driverName}>{driverInfo.name}</Text>
                {driverInfo.phone && (
                  <Text style={styles.driverPhone}>{driverInfo.phone}</Text>
                )}
              </View>
            </View>
            
            {/* Contact Buttons */}
            {driverInfo.phone && (
              <View style={styles.contactButtons}>
                <TouchableOpacity 
                  style={[styles.contactButton, styles.callButton]}
                  onPress={callDriver}
                >
                  <Ionicons name="call" size={20} color="white" />
                  <Text style={styles.contactButtonText}>Call</Text>
                </TouchableOpacity>
                
                <TouchableOpacity 
                  style={[styles.contactButton, styles.messageButton]}
                  onPress={messageDriver}
                >
                  <Ionicons name="chatbubble" size={20} color="white" />
                  <Text style={styles.contactButtonText}>Message</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        )}

        {/* Delivery Address */}
        {address && (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>📍 Delivery Address</Text>
            <View style={styles.improvedAddressContainer}>
              {address.building_name && (
                <Text style={styles.buildingName}>{address.building_name}</Text>
              )}
              {address.area_locality && (
                <Text style={styles.addressLine}>{address.area_locality}</Text>
              )}
              {(address.ward_number || address.city) && (
                <Text style={styles.addressLine}>
                  {address.ward_number && `Ward ${address.ward_number}`}
                  {address.ward_number && address.city && ', '}
                  {address.city}
                </Text>
              )}
              
              {address.detailed_directions && (
                <View style={styles.directionsContainer}>
                  <Ionicons name="navigate" size={16} color={colors.info[600]} />
                  <Text style={styles.addressDirections}>
                    {address.detailed_directions}
                  </Text>
                </View>
              )}
              
              <TouchableOpacity 
                style={styles.mapsButton}
                onPress={openInMaps}
              >
                <Ionicons name="map" size={18} color="white" />
                <Text style={styles.mapsButtonText}>Open in Google Maps</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {/* Order Summary */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>📦 Order Summary</Text>
          
          {/* Order Items */}
          {order.items && order.items.length > 0 && (
            <View style={styles.orderItemsContainer}>
              {order.items.map((item: any, index: number) => (
                <View key={index} style={styles.orderItem}>
                  <View style={styles.orderItemInfo}>
                    <Text style={styles.orderItemQuantity}>{item.quantity}x</Text>
                    <Text style={styles.orderItemName}>{item.item_name || item.name}</Text>
                  </View>
                  <Text style={styles.orderItemPrice}>
                    Rs. {parseFloat(item.subtotal || item.price * item.quantity).toFixed(2)}
                  </Text>
                </View>
              ))}
            </View>
          )}
          
          {/* Order Total */}
          <View style={styles.orderTotalContainer}>
            <View style={styles.orderTotalRow}>
              <Text style={styles.orderTotalLabel}>Subtotal:</Text>
              <Text style={styles.orderTotalValue}>
                Rs. {parseFloat(order.total_amount || 0).toFixed(2)}
              </Text>
            </View>
            {order.tax_amount && (
              <View style={styles.orderTotalRow}>
                <Text style={styles.orderTotalLabel}>Tax:</Text>
                <Text style={styles.orderTotalValue}>
                  Rs. {parseFloat(order.tax_amount).toFixed(2)}
                </Text>
              </View>
            )}
            <View style={[styles.orderTotalRow, styles.grandTotalRow]}>
              <Text style={styles.grandTotalLabel}>Total:</Text>
              <Text style={styles.grandTotalValue}>
                Rs. {parseFloat(order.grand_total || order.total_amount || 0).toFixed(2)}
              </Text>
            </View>
          </View>
          
          {/* Payment Method */}
          {order.payment_method && (
            <View style={styles.paymentMethodContainer}>
              <Ionicons 
                name={order.payment_method === 'cash' ? 'cash' : 'card'} 
                size={16} 
                color={colors.gray[600]} 
              />
              <Text style={styles.paymentMethodText}>
                Payment: {order.payment_method.charAt(0).toUpperCase() + order.payment_method.slice(1)}
              </Text>
            </View>
          )}
        </View>

        {/* Tracking History */}
        {tracking.length > 0 && (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Tracking History</Text>
            {tracking.map((point, index) => (
              <View key={point.id} style={styles.trackingItem}>
                <View style={styles.trackingDot} />
                {index < tracking.length - 1 && (
                  <View style={styles.trackingLine} />
                )}
                <View style={styles.trackingContent}>
                  <Text style={styles.trackingStatus}>
                    {point.status === 'accepted'
                      ? 'Driver accepted order'
                      : point.status === 'delivered'
                      ? 'Order delivered'
                      : 'Location updated'}
                  </Text>
                  <Text style={styles.trackingTime}>
                    {new Date(point.created_at).toLocaleTimeString()}
                  </Text>
                </View>
              </View>
            ))}
          </View>
        )}

        <View style={{ height: 40 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.background,
  },
  loadingText: {
    marginTop: spacing[4],
    fontSize: fontSizes.base,
    color: colors.gray[600],
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.background,
    padding: spacing[6],
  },
  errorText: {
    marginTop: spacing[4],
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.error[500],
  },
  backButton: {
    marginTop: spacing[6],
    paddingHorizontal: spacing[6],
    paddingVertical: spacing[3],
    backgroundColor: colors.primary[500],
    borderRadius: radius.lg,
  },
  backButtonText: {
    color: 'white',
    fontSize: fontSizes.base,
    fontWeight: fontWeights.semibold,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing[4],
    paddingTop: spacing[12],
    paddingBottom: spacing[4],
    backgroundColor: 'white',
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  headerBackButton: {
    padding: spacing[2],
  },
  headerContent: {
    flex: 1,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  headerSubtitle: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginTop: spacing[1],
  },
  refreshButton: {
    padding: spacing[2],
  },
  map: {
    width: '100%',
    height: height * 0.4,
  },
  mapPlaceholder: {
    width: '100%',
    height: height * 0.4,
    backgroundColor: colors.primary[50],
    justifyContent: 'center',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  locationCard: {
    backgroundColor: 'white',
    padding: spacing[6],
    borderRadius: radius.xl,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    maxWidth: '90%',
  },
  locationTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginTop: spacing[3],
  },
  locationCoords: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginTop: spacing[2],
    fontFamily: 'monospace',
  },
  locationNote: {
    fontSize: fontSizes.xs,
    color: colors.info[600],
    marginTop: spacing[3],
    textAlign: 'center',
  },
  driverNameInMap: {
    fontSize: fontSizes.base,
    fontWeight: fontWeights.semibold,
    color: colors.primary[600],
    marginTop: spacing[2],
  },
  noMapContainer: {
    width: '100%',
    height: height * 0.4,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[100],
  },
  noMapText: {
    marginTop: spacing[4],
    fontSize: fontSizes.base,
    color: colors.gray[600],
    textAlign: 'center',
  },
  driverMarker: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: colors.primary[500],
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: 'white',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  infoContainer: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  card: {
    backgroundColor: 'white',
    marginHorizontal: spacing[4],
    marginTop: spacing[4],
    padding: spacing[4],
    borderRadius: radius.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  statusContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  statusBadge: {
    paddingHorizontal: spacing[4],
    paddingVertical: spacing[2],
    borderRadius: radius.full,
  },
  statusText: {
    color: 'white',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
  },
  pulseContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  pulse: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.error[500],
    marginRight: spacing[2],
  },
  liveText: {
    color: colors.error[500],
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
  },
  cardTitle: {
    fontSize: fontSizes.base,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing[3],
  },
  driverInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  driverAvatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: colors.primary[50],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing[3],
  },
  driverDetails: {
    flex: 1,
  },
  driverName: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
  },
  driverPhone: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginTop: spacing[1],
  },
  addressContainer: {
    flexDirection: 'row',
  },
  addressIcon: {
    marginRight: spacing[2],
    marginTop: spacing[1],
  },
  addressText: {
    flex: 1,
  },
  addressLine: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
    marginBottom: spacing[1],
  },
  addressDirections: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    fontStyle: 'italic',
    marginTop: spacing[2],
  },
  trackingItem: {
    flexDirection: 'row',
    marginBottom: spacing[4],
    position: 'relative',
  },
  trackingDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: colors.primary[500],
    marginRight: spacing[3],
    marginTop: spacing[1],
  },
  trackingLine: {
    position: 'absolute',
    left: 5.5,
    top: 16,
    bottom: -16,
    width: 1,
    backgroundColor: colors.gray[300],
  },
  trackingContent: {
    flex: 1,
  },
  trackingStatus: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
  },
  trackingTime: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    marginTop: spacing[1],
  },
  // ETA Styles
  etaContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.primary[50],
    padding: spacing[4],
    borderRadius: radius.lg,
  },
  etaIcon: {
    marginRight: spacing[4],
  },
  etaContent: {
    flex: 1,
  },
  etaLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing[1],
  },
  etaTimeContainer: {
    flexDirection: 'row',
    alignItems: 'baseline',
  },
  etaMinutes: {
    fontSize: fontSizes['3xl'],
    fontWeight: fontWeights.bold,
    color: colors.primary[600],
    marginRight: spacing[2],
  },
  etaUnit: {
    fontSize: fontSizes.base,
    color: colors.gray[600],
  },
  etaDistance: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginTop: spacing[1],
  },
  // Contact Button Styles
  contactButtons: {
    flexDirection: 'row',
    marginTop: spacing[4],
    gap: spacing[3],
  },
  contactButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing[3],
    paddingHorizontal: spacing[4],
    borderRadius: radius.lg,
    gap: spacing[2],
  },
  callButton: {
    backgroundColor: colors.success[600],
  },
  messageButton: {
    backgroundColor: colors.primary[600],
  },
  contactButtonText: {
    color: 'white',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
  },
  // Improved Address Styles
  improvedAddressContainer: {
    backgroundColor: colors.blue[50],
    padding: spacing[4],
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.blue[200],
  },
  buildingName: {
    fontSize: fontSizes.base,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing[2],
  },
  directionsContainer: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginTop: spacing[3],
    paddingTop: spacing[3],
    borderTopWidth: 1,
    borderTopColor: colors.blue[200],
    gap: spacing[2],
  },
  mapsButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.primary[600],
    paddingVertical: spacing[3],
    paddingHorizontal: spacing[4],
    borderRadius: radius.lg,
    marginTop: spacing[3],
    gap: spacing[2],
  },
  mapsButtonText: {
    color: 'white',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
  },
  // Order Summary Styles
  orderItemsContainer: {
    marginTop: spacing[2],
  },
  orderItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing[3],
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[100],
  },
  orderItemInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  orderItemQuantity: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.primary[600],
    marginRight: spacing[2],
    minWidth: 30,
  },
  orderItemName: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
    flex: 1,
  },
  orderItemPrice: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[900],
  },
  orderTotalContainer: {
    marginTop: spacing[4],
    paddingTop: spacing[4],
    borderTopWidth: 2,
    borderTopColor: colors.gray[200],
  },
  orderTotalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: spacing[2],
  },
  orderTotalLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  orderTotalValue: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  grandTotalRow: {
    marginTop: spacing[2],
    paddingTop: spacing[2],
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
  },
  grandTotalLabel: {
    fontSize: fontSizes.base,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  grandTotalValue: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.primary[600],
  },
  paymentMethodContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: spacing[3],
    paddingTop: spacing[3],
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
    gap: spacing[2],
  },
  paymentMethodText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
});

