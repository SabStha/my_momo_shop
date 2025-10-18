import React, { useEffect, useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  TouchableOpacity,
  Dimensions,
  ScrollView,
  Linking,
  Alert,
  Animated,
  Platform,
  StatusBar,
} from 'react-native';
import { Ionicons, MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui';
import { useBackendOrder } from '../../src/hooks/useOrders';
import * as Location from 'expo-location';
import { client as apiClient } from '../../src/api/client';
import LiveTrackingMap from '../../src/components/tracking/LiveTrackingMap';
import { useSession } from '../../src/session/SessionProvider';
import DeliveryNotificationService from '../../src/services/DeliveryNotificationService';
import { AppState } from 'react-native';

const { width, height } = Dimensions.get('window');

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
  latitude?: string;
  longitude?: string;
}

export default function OrderTrackingScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const backendOrderId = id?.toString().replace('order_', '');
  const numericOrderId = parseInt(backendOrderId || '0');
  const { token } = useSession();
  
  // Removed excessive logging for production performance
  
  const { data: order, isLoading: orderLoading, error: orderError } = useBackendOrder(numericOrderId);
  const [tracking, setTracking] = useState<TrackingPoint[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const trackingIntervalRef = useRef<NodeJS.Timeout | null>(null);
  
  // Real driver location state (must be declared before early returns)
  const [realDriverLocation, setRealDriverLocation] = useState<{
    latitude: number;
    longitude: number;
  } | null>(null);
  
  // Real navigation info from Google Directions API
  const [navigationInfo, setNavigationInfo] = useState<{
    distance: string;
    duration: string;
    eta: string;
  } | null>(null);

  // Animations
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const appState = useRef(AppState.currentState);
  const [appStateVisible, setAppStateVisible] = useState(appState.current);
  const notificationIntervalRef = useRef<any>(null);

  useEffect(() => {
    // Pulse animation for live indicator
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1.3,
          duration: 1000,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
        }),
      ])
    ).start();

    // Fade in animation
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 500,
      useNativeDriver: true,
    }).start();
  }, []);

  // Fetch tracking data
  const fetchTracking = async () => {
    if (!numericOrderId || numericOrderId === 0) {
      console.error('Invalid order ID');
      setIsLoading(false);
      return;
    }
    
    try {
      const response = await apiClient.get(`/orders/${numericOrderId}/tracking`);
      const data = response.data;
      
      if (data?.success && data?.tracking) {
        setTracking(data.tracking);
      }
    } catch (error: any) {
      console.error('Failed to fetch tracking:', error);
      // Don't show error to user - tracking might not exist yet
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    if (numericOrderId) {
    fetchTracking();
      
      // Poll for updates if order is active
      if (order?.status === 'out_for_delivery' || order?.status === 'preparing') {
        trackingIntervalRef.current = setInterval(fetchTracking, 10000); // Every 10 seconds
      }
    }

      return () => {
        if (trackingIntervalRef.current) {
          clearInterval(trackingIntervalRef.current);
        }
      };
  }, [numericOrderId, order?.status]);

  // Poll for real driver location updates (must be before early returns)
  useEffect(() => {
    if (!numericOrderId) return;

    const fetchDriverLocation = async () => {
      try {
        const response = await fetch(`https://amakomomo.com/api/driver/location/${numericOrderId}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        });

        if (response.ok) {
          const data = await response.json();
          if (data.success && data.data) {
            setRealDriverLocation({
              latitude: parseFloat(data.data.latitude),
              longitude: parseFloat(data.data.longitude),
            });
            console.log('✅ Real driver location received:', data.data);
          } else {
            console.log('⚠️ No real driver location available, using tracking data');
          }
        } else {
          console.log('⚠️ Driver location API not available, using tracking data');
        }
      } catch (error) {
        console.error('❌ Failed to fetch driver location:', error);
      }
    };

    // Fetch immediately
    fetchDriverLocation();

    // Set up polling every 10 seconds
    const interval = setInterval(fetchDriverLocation, 10000);

    return () => clearInterval(interval);
  }, [numericOrderId, token]);

  // Handle app state changes and background notifications
  useEffect(() => {
    const subscription = AppState.addEventListener('change', async (nextAppState) => {
      if (
        appState.current.match(/inactive|background/) &&
        nextAppState === 'active'
      ) {
        // App came to foreground - stop notifications
        console.log('📱 App came to foreground - stopping background notifications');
        if (notificationIntervalRef.current) {
          clearInterval(notificationIntervalRef.current);
          notificationIntervalRef.current = null;
        }
        await DeliveryNotificationService.stopLiveTracking();
      } else if (nextAppState.match(/inactive|background/)) {
        // App went to background - start notifications
        console.log('📱 App went to background - starting live notifications');
        
        if (order?.status === 'out_for_delivery' && order?.order_number) {
          // Start live tracking notification
          await DeliveryNotificationService.startLiveTracking(
            numericOrderId,
            order.order_number
          );
          
          // Update notification every 30 seconds with latest tracking info
          notificationIntervalRef.current = setInterval(async () => {
            if (navigationInfo) {
              await DeliveryNotificationService.updateTrackingNotification({
                orderId: numericOrderId,
                orderNumber: order.order_number || `Order ${numericOrderId}`,
                distance: navigationInfo.distance,
                duration: navigationInfo.duration,
                eta: navigationInfo.eta,
                status: order.status,
              });
            }
          }, 30000); // Update every 30 seconds
        }
      }

      appState.current = nextAppState;
      setAppStateVisible(appState.current);
    });

    return () => {
      subscription.remove();
      if (notificationIntervalRef.current) {
        clearInterval(notificationIntervalRef.current);
      }
    };
  }, [order?.status, order?.order_number, navigationInfo, numericOrderId]);

  // Test function to simulate driver movement for 3D camera testing
  const simulateDriverMovement = () => {
    if (!driverLocation) {
      console.log('❌ No driver location available for simulation');
      return;
    }
    
    console.log('🚗 Starting driver movement simulation...');
    
    // Simulate driver moving in a circle
    const centerLat = driverLocation.latitude;
    const centerLng = driverLocation.longitude;
    const radius = 0.001; // Small radius for testing
    
    let angle = 0;
    const interval = setInterval(() => {
      const newLat = centerLat + radius * Math.cos(angle);
      const newLng = centerLng + radius * Math.sin(angle);
      
      console.log('🚗 Simulating driver move to:', { latitude: newLat, longitude: newLng });
      
      setRealDriverLocation({
        latitude: newLat,
        longitude: newLng,
      });
      
      angle += 0.1; // Move in circle
      if (angle > 2 * Math.PI) {
        console.log('🚗 Driver simulation complete');
        clearInterval(interval);
      }
    }, 2000); // Move every 2 seconds
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await fetchTracking();
    setRefreshing(false);
  };

  const getStatusColor = (status: string) => {
    const statusColors: Record<string, string> = {
      pending: '#F59E0B',
      confirmed: '#10B981',
      preparing: '#3B82F6',
      ready: '#8B5CF6',
      out_for_delivery: '#EC4899',
      delivered: '#059669',
      cancelled: '#EF4444',
    };
    return statusColors[status] || '#6B7280';
  };

  const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
      pending: 'Order Received',
      confirmed: 'Confirmed',
      preparing: 'Being Prepared',
      ready: 'Ready for Pickup',
      out_for_delivery: 'On the Way!',
      delivered: 'Delivered',
      cancelled: 'Cancelled',
    };
    return labels[status] || status;
  };

  const getStatusIcon = (status: string) => {
    const icons: Record<string, string> = {
      pending: 'time-outline',
      confirmed: 'checkmark-circle',
      preparing: 'flame',
      ready: 'checkmark-done-circle',
      out_for_delivery: 'bicycle',
      delivered: 'trophy',
      cancelled: 'close-circle',
    };
    return icons[status] || 'help-circle';
  };

  if (orderLoading || isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#A43E2D" />
        <Text style={styles.loadingText}>Loading order details...</Text>
      </View>
    );
  }

  if (orderError || !order) {
    return (
      <View style={styles.errorContainer}>
        <Ionicons name="alert-circle" size={64} color="#EF4444" />
        <Text style={styles.errorTitle}>Order Not Found</Text>
        <Text style={styles.errorText}>
          {orderError ? 'Failed to load order details.' : 'We couldn\'t find this order.'}
        </Text>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const latestTracking = tracking && tracking.length > 0 ? tracking[tracking.length - 1] : null;
  const driverInfo = latestTracking?.driver;
  
  // Parse delivery address - it might be a JSON string
  let deliveryAddress: DeliveryAddress | null = null;
  if (order?.delivery_address) {
    if (typeof order.delivery_address === 'string') {
      try {
        deliveryAddress = JSON.parse(order.delivery_address);
      } catch (e) {
        console.error('Failed to parse delivery address:', e);
      }
    } else {
      deliveryAddress = order.delivery_address as DeliveryAddress;
    }
  }

  // Removed excessive logging - only log errors

  // Get driver location from tracking
  const driverLocation = latestTracking ? {
    latitude: parseFloat(latestTracking.latitude),
    longitude: parseFloat(latestTracking.longitude),
  } : null;

  // Use real driver location if available, fallback to tracking data
  const currentDriverLocation = realDriverLocation || driverLocation;

  // Removed excessive driver location logging

  // Get delivery location from address coordinates or geocode
  const deliveryLocation = deliveryAddress ? (() => {
    // If address has coordinates, use them
    if (deliveryAddress.latitude && deliveryAddress.longitude) {
      return {
        latitude: parseFloat(deliveryAddress.latitude),
        longitude: parseFloat(deliveryAddress.longitude),
      };
    }
    
    // If city is Fukuoka, use Fukuoka coordinates
    if (deliveryAddress.city?.toLowerCase().includes('fukuoka')) {
      return {
        latitude: 33.5904, // Fukuoka, Japan
        longitude: 130.4017,
      };
    }
    
    // Default to Kathmandu for other cities
    return {
      latitude: 27.7172, // Kathmandu, Nepal
      longitude: 85.324,
    };
  })() : null;

  // Generate route coordinates (simulate path from driver to delivery)
  const generateRouteCoordinates = () => {
    if (!currentDriverLocation || !deliveryLocation) return [];
    
    // If both locations are the same, return empty array (no route needed)
    if (currentDriverLocation.latitude === deliveryLocation.latitude && 
        currentDriverLocation.longitude === deliveryLocation.longitude) {
      return [];
    }
    
    // Generate intermediate points for a realistic route
    const steps = 8; // Number of intermediate points
    const route: Array<{latitude: number, longitude: number}> = [];
    
    for (let i = 0; i <= steps; i++) {
      const ratio = i / steps;
      const lat = currentDriverLocation.latitude + (deliveryLocation.latitude - currentDriverLocation.latitude) * ratio;
      const lng = currentDriverLocation.longitude + (deliveryLocation.longitude - currentDriverLocation.longitude) * ratio;
      
      // Add some realistic road-like curves
      const curve = Math.sin(ratio * Math.PI) * 0.001; // Small curve
      route.push({
        latitude: lat + curve,
        longitude: lng + curve * 0.5
      });
    }
    
    return route;
  };

  const routeCoordinates = generateRouteCoordinates();

  // Use real ETA from navigation info, or fallback to mock
  const eta = navigationInfo?.duration || (order?.status === 'out_for_delivery' ? '15-20 minutes' : order?.status === 'preparing' ? '10-15 minutes' : null);

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#152039" />
      {/* Header with Solid Color (matches top nav) */}
      <View style={styles.header}>
        <View style={styles.headerTop}>
        <TouchableOpacity
            style={styles.backBtn}
          onPress={() => router.back()}
        >
            <Ionicons name="arrow-back" size={24} color="#FFF" />
        </TouchableOpacity>
          <View style={styles.headerCenter}>
            <Text style={styles.headerTitle}>Track Your Order</Text>
            <Text style={styles.headerSubtitle}>#{order?.order_number || order?.id || 'N/A'}</Text>
        </View>
        <TouchableOpacity
          style={styles.refreshBtn}
          onPress={handleRefresh}
          disabled={refreshing}
        >
          {refreshing ? (
            <ActivityIndicator size="small" color="#FFF" />
          ) : (
            <Ionicons name="refresh" size={24} color="#FFF" />
          )}
        </TouchableOpacity>
      </View>

        {/* Live Status Indicator */}
        {order?.status === 'out_for_delivery' && (
          <View style={styles.liveIndicator}>
            <Animated.View style={[styles.liveDot, { transform: [{ scale: pulseAnim }] }]} />
            <Text style={styles.liveText}>LIVE TRACKING</Text>
        </View>
      )}
      </View>

      <ScrollView
        style={styles.content}
        showsVerticalScrollIndicator={false}
      >
        <Animated.View style={{ opacity: fadeAnim }}>
          {/* Live Map - Show if driver location available */}
          {currentDriverLocation && order?.status === 'out_for_delivery' && (
            <>
              <LiveTrackingMap
                driverLocation={currentDriverLocation}
                deliveryLocation={deliveryLocation}
                routeCoordinates={routeCoordinates}
                onRouteInfoUpdate={(info) => setNavigationInfo(info)}
              />
              
              {/* Live Navigation Info Card */}
              {navigationInfo && (
                <View style={styles.navigationCard}>
                  <View style={styles.navigationHeader}>
                    <Ionicons name="navigate" size={24} color="#3B82F6" />
                    <Text style={styles.navigationTitle}>Live Navigation</Text>
                  </View>
                  
                  <View style={styles.navigationStats}>
                    <View style={styles.navigationStat}>
                      <Ionicons name="location" size={20} color="#6B7280" />
                      <Text style={styles.navigationLabel}>Distance</Text>
                      <Text style={styles.navigationValue}>{navigationInfo.distance}</Text>
                    </View>
                    
                    <View style={styles.navigationDivider} />
                    
                    <View style={styles.navigationStat}>
                      <Ionicons name="time" size={20} color="#6B7280" />
                      <Text style={styles.navigationLabel}>Duration</Text>
                      <Text style={styles.navigationValue}>{navigationInfo.duration}</Text>
                    </View>
                    
                    <View style={styles.navigationDivider} />
                    
                    <View style={styles.navigationStat}>
                      <Ionicons name="alarm" size={20} color="#6B7280" />
                      <Text style={styles.navigationLabel}>Arrives at</Text>
                      <Text style={styles.navigationValue}>{navigationInfo.eta}</Text>
                    </View>
                  </View>
                  
                  <View style={styles.navigationFooter}>
                    <Ionicons name="checkmark-circle" size={16} color="#10B981" />
                    <Text style={styles.navigationFooterText}>Using real-time traffic data</Text>
                  </View>
                </View>
              )}
            </>
          )}
          
          {/* TEST MODE: Show map with demo data if no real tracking (REMOVE AFTER TESTING) */}
          {!driverLocation && order?.status === 'out_for_delivery' && __DEV__ && (
            <>
              <View style={styles.testModeBanner}>
                <Text style={styles.testModeText}>⚠️ TEST MODE: Demo Map (No real driver data)</Text>
              </View>
              <LiveTrackingMap
                driverLocation={{
                  latitude: 33.5904 + (Math.random() - 0.5) * 0.005, // Random within ~500m of Fukuoka
                  longitude: 130.4017 + (Math.random() - 0.5) * 0.005,
                }}
                deliveryLocation={{
                  latitude: 33.5904, // Fukuoka center
                  longitude: 130.4017,
                }}
                routeCoordinates={[
                  { latitude: 33.5904 + 0.003, longitude: 130.4017 + 0.003 },
                  { latitude: 33.5904 + 0.002, longitude: 130.4017 + 0.002 },
                  { latitude: 33.5904 + 0.001, longitude: 130.4017 + 0.001 },
                  { latitude: 33.5904, longitude: 130.4017 },
                ]}
              />
            </>
          )}

          {/* Order Timeline */}
          <View style={styles.timelineCard}>
            <Text style={styles.sectionTitle}>Order Progress</Text>
            <View style={styles.timeline}>
              {[
                { key: 'pending', label: 'Order Placed', icon: 'receipt' },
                { key: 'confirmed', label: 'Confirmed', icon: 'checkmark-circle' },
                { key: 'preparing', label: 'Preparing', icon: 'flame' },
                { key: 'out_for_delivery', label: 'On the Way', icon: 'bicycle' },
                { key: 'delivered', label: 'Delivered', icon: 'trophy' },
              ].map((step, index) => {
                const statusArray = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'];
                const currentStatus = order?.status || 'pending';
                const isCompleted = statusArray.indexOf(currentStatus) >= index;
                const isCurrent = statusArray[index] === currentStatus;
                
                return (
                  <View key={step.key} style={styles.timelineStep}>
                    <View style={styles.timelineLeft}>
                      <View style={[
                        styles.timelineDot,
                        isCompleted && styles.timelineDotCompleted,
                        isCurrent && styles.timelineDotCurrent,
                      ]}>
                        {isCompleted && (
                          <Ionicons name="checkmark" size={12} color="#FFF" />
                        )}
              </View>
                      {index < 4 && (
                        <View style={[
                          styles.timelineLine,
                          isCompleted && styles.timelineLineCompleted,
                        ]} />
                      )}
                </View>
                    <View style={styles.timelineRight}>
                      <View style={styles.timelineContent}>
                        <Ionicons 
                          name={step.icon as any} 
                          size={20} 
                          color={isCompleted ? colors.primary[500] : colors.gray[400]} 
                        />
                        <Text style={[
                          styles.timelineLabel,
                          isCompleted && styles.timelineLabelCompleted,
                          isCurrent && styles.timelineLabelCurrent,
                        ]}>
                          {step.label}
                </Text>
              </View>
            </View>
                  </View>
                );
              })}
            </View>
          </View>

          {/* Driver Info Card */}
        {driverInfo && (
            <View style={styles.driverCard}>
              <View style={styles.driverHeader}>
              <View style={styles.driverAvatar}>
                  <Text style={styles.driverInitial}>
                    {driverInfo.name.charAt(0).toUpperCase()}
                  </Text>
              </View>
                <View style={styles.driverInfo}>
                  <Text style={styles.driverLabel}>Your Delivery Partner</Text>
                <Text style={styles.driverName}>{driverInfo.name}</Text>
                  <View style={styles.driverRating}>
                    {[...Array(5)].map((_, i) => (
                      <Ionicons key={i} name="star" size={14} color="#FFA500" />
                    ))}
                    <Text style={styles.ratingText}>5.0</Text>
              </View>
            </View>
              </View>
            {driverInfo.phone && (
                <TouchableOpacity 
                  style={styles.callButton}
                  onPress={() => Linking.openURL(`tel:${driverInfo.phone}`)}
                >
                  <Ionicons name="call" size={20} color="#FFF" />
                  <Text style={styles.callButtonText}>Call Driver</Text>
                </TouchableOpacity>
            )}
          </View>
        )}

          {/* Order Summary Card */}
          <View style={styles.summaryCard}>
            <Text style={styles.sectionTitle}>Order Summary</Text>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Total Amount</Text>
              <Text style={styles.summaryValue}>NPR {order?.total_amount || order?.total || '0'}</Text>
                  </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Payment Method</Text>
              <Text style={styles.summaryValue}>{order?.payment_method || 'N/A'}</Text>
                </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Order Time</Text>
              <Text style={styles.summaryValue}>
                {order?.created_at ? new Date(order.created_at).toLocaleString() : 'N/A'}
              </Text>
            </View>
          </View>
          
          {/* Need Help Card */}
          <View style={styles.helpCard}>
            <MCI name="help-circle" size={32} color={colors.primary[500]} />
            <Text style={styles.helpTitle}>Need Help?</Text>
            <Text style={styles.helpText}>
              Contact our support team for any assistance
              </Text>
            <TouchableOpacity
              style={styles.helpButton}
              onPress={() => Linking.openURL('tel:+9771234567890')}
            >
              <Text style={styles.helpButtonText}>Call Support</Text>
            </TouchableOpacity>
            </View>
        </Animated.View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFF',
  },
  loadingText: {
    marginTop: spacing.md,
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.xl,
  },
  errorTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
    marginTop: spacing.md,
  },
  errorText: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    marginTop: spacing.sm,
    textAlign: 'center',
  },
  backButton: {
    marginTop: spacing.lg,
    backgroundColor: colors.primary[500],
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  backButtonText: {
    color: '#FFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold as any,
  },
  header: {
    paddingTop: Platform.OS === 'ios' ? 50 : 40,
    paddingBottom: spacing.md,
    paddingHorizontal: spacing.lg,
    backgroundColor: '#152039', // Dark blue to match top nav
    minHeight: Platform.OS === 'ios' ? 100 : 80,
  },
  headerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    minHeight: 50,
  },
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerCenter: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.md,
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold as any,
    color: '#FFF',
    textAlign: 'center',
    marginBottom: 2,
  },
  headerSubtitle: {
    fontSize: fontSizes.sm,
    color: 'rgba(255,255,255,0.9)',
    marginTop: 2,
  },
  refreshBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  liveIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: spacing.md,
    gap: 8,
  },
  liveDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#10B981',
  },
  liveText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold as any,
    color: '#FFF',
    letterSpacing: 1,
  },
  content: {
    flex: 1,
    padding: spacing.lg,
  },
  mainStatusCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.xl,
    alignItems: 'center',
    marginBottom: spacing.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  statusIconContainer: {
    width: 80,
    height: 80,
    borderRadius: 40,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  statusTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
    textAlign: 'center',
  },
  etaBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: colors.primary[500],
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
    marginTop: spacing.md,
  },
  etaText: {
    color: '#FFF',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold as any,
  },
  timelineCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
    marginBottom: spacing.md,
  },
  timeline: {
    marginTop: spacing.sm,
  },
  timelineStep: {
    flexDirection: 'row',
    marginBottom: spacing.xs,
  },
  timelineLeft: {
    alignItems: 'center',
    marginRight: spacing.md,
  },
  timelineDot: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: colors.gray[200],
    justifyContent: 'center',
    alignItems: 'center',
  },
  timelineDotCompleted: {
    backgroundColor: colors.primary[500],
  },
  timelineDotCurrent: {
    backgroundColor: colors.primary[500],
    borderWidth: 3,
    borderColor: colors.primary[200],
  },
  timelineLine: {
    width: 2,
    flex: 1,
    backgroundColor: colors.gray[200],
    minHeight: 30,
  },
  timelineLineCompleted: {
    backgroundColor: colors.primary[500],
  },
  timelineRight: {
    flex: 1,
    justifyContent: 'center',
  },
  timelineContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  timelineLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[500],
  },
  timelineLabelCompleted: {
    color: colors.gray[700],
    fontWeight: fontWeights.medium as any,
  },
  timelineLabelCurrent: {
    color: colors.primary[500],
    fontWeight: fontWeights.bold as any,
  },
  locationCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
    borderWidth: 2,
    borderColor: '#FDE047',
  },
  locationHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: spacing.md,
  },
  locationTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold as any,
    color: '#111827',
    flex: 1,
  },
  livePulse: {
    position: 'relative',
    width: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  pulseRing: {
    position: 'absolute',
    width: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: '#10B981',
    opacity: 0.3,
  },
  pulseDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#10B981',
  },
  coordinatesBox: {
    backgroundColor: '#F3F4F6',
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  coordinatesLabel: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  coordinatesText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium as any,
    color: '#111827',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  openMapsButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: '#3B82F6',
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  openMapsButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold as any,
  },
  driverCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  driverHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  driverAvatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: colors.primary[500],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  driverInitial: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold as any,
    color: '#FFF',
  },
  driverInfo: {
    flex: 1,
  },
  driverLabel: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    textTransform: 'uppercase',
  },
  driverName: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
    marginTop: 2,
  },
  driverRating: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
    marginTop: 4,
  },
  ratingText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginLeft: 4,
  },
  callButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: colors.primary[500],
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  callButtonText: {
    color: '#FFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold as any,
  },
  addressCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: spacing.md,
  },
  addressTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
  },
  addressContent: {
    gap: 6,
  },
  addressText: {
    fontSize: fontSizes.md,
    color: colors.gray[700],
    lineHeight: 22,
  },
  addressDirections: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    fontStyle: 'italic',
    marginTop: 4,
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
  },
  summaryCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[100],
  },
  summaryLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  summaryValue: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold as any,
    color: colors.gray[900],
  },
  helpCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.lg,
    padding: spacing.xl,
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  helpTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
    marginTop: spacing.sm,
  },
  helpText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    marginTop: spacing.xs,
  },
  helpButton: {
    backgroundColor: colors.primary[500],
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
    marginTop: spacing.md,
  },
  helpButtonText: {
    color: '#FFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold as any,
  },
  testModeBanner: {
    backgroundColor: '#FFA500',
    padding: 12,
    borderRadius: 8,
    marginBottom: 12,
    alignItems: 'center',
  },
  testModeText: {
    color: '#FFF',
    fontSize: 14,
    fontWeight: '600',
  },
  navigationCard: {
    backgroundColor: '#FFF',
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.md,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
    borderLeftWidth: 3,
    borderLeftColor: '#3B82F6',
  },
  navigationHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: spacing.sm,
  },
  navigationTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
  },
  navigationStats: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.sm,
  },
  navigationStat: {
    flex: 1,
    alignItems: 'center',
    gap: 2,
  },
  navigationDivider: {
    width: 1,
    height: 28,
    backgroundColor: colors.gray[200],
  },
  navigationLabel: {
    fontSize: 10,
    color: colors.gray[500],
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  navigationValue: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold as any,
    color: colors.gray[900],
  },
  navigationFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: spacing.xs,
    paddingTop: spacing.xs,
    borderTopWidth: 1,
    borderTopColor: colors.gray[100],
  },
  navigationFooterText: {
    fontSize: 10,
    color: colors.gray[600],
    fontStyle: 'italic',
  },
  testButton: {
    backgroundColor: '#3B82F6',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    marginHorizontal: spacing.lg,
    marginVertical: spacing.sm,
    borderRadius: 12,
    gap: spacing.sm,
  },
  testButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold as any,
  },
});
