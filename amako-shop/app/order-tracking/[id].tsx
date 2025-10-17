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
  Animated,
} from 'react-native';
import { Ionicons, MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui';
import { useBackendOrder } from '../../src/hooks/useOrders';
import * as Location from 'expo-location';
import { client as apiClient } from '../../src/api/client';

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
}

export default function OrderTrackingScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const backendOrderId = id?.toString().replace('order_', '');
  const numericOrderId = parseInt(backendOrderId || '0');
  
  const { data: order, isLoading: orderLoading } = useBackendOrder(numericOrderId);
  const [tracking, setTracking] = useState<TrackingPoint[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const trackingIntervalRef = useRef<NodeJS.Timeout | null>(null);
  
  // Animations
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const fadeAnim = useRef(new Animated.Value(0)).current;

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
    try {
      const response = await apiClient.get(`/orders/${numericOrderId}/tracking`);
      const data = response.data;
      
      if (data.success && data.tracking) {
        setTracking(data.tracking);
      }
    } catch (error) {
      console.error('Failed to fetch tracking:', error);
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
        <ActivityIndicator size="large" color={colors.primary[500]} />
        <Text style={styles.loadingText}>Loading order details...</Text>
      </View>
    );
  }

  if (!order) {
    return (
      <View style={styles.errorContainer}>
        <Ionicons name="alert-circle" size={64} color={colors.error[500]} />
        <Text style={styles.errorTitle}>Order Not Found</Text>
        <Text style={styles.errorText}>We couldn't find this order.</Text>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const latestTracking = tracking[tracking.length - 1];
  const driverInfo = latestTracking?.driver;
  const deliveryAddress = order.delivery_address as DeliveryAddress | null;

  // Calculate ETA (mock for now - can be real calculation later)
  const getETA = () => {
    if (order.status === 'out_for_delivery') {
      return '15-20 minutes';
    } else if (order.status === 'preparing') {
      return '10-15 minutes';
    }
    return null;
  };

  const eta = getETA();

  return (
    <View style={styles.container}>
      {/* Header with Gradient */}
      <LinearGradient
        colors={['#A43E2D', '#8B1A3A']}
        style={styles.header}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <View style={styles.headerTop}>
          <TouchableOpacity
            style={styles.backBtn}
            onPress={() => router.back()}
          >
            <Ionicons name="arrow-back" size={24} color="#FFF" />
          </TouchableOpacity>
          <View style={styles.headerCenter}>
            <Text style={styles.headerTitle}>Track Your Order</Text>
            <Text style={styles.headerSubtitle}>#{order.order_number}</Text>
          </View>
          <TouchableOpacity
            style={styles.refreshBtn}
            onPress={handleRefresh}
          >
            <Ionicons name="refresh" size={24} color="#FFF" />
          </TouchableOpacity>
        </View>

        {/* Live Status Indicator */}
        {order.status === 'out_for_delivery' && (
          <View style={styles.liveIndicator}>
            <Animated.View style={[styles.liveDot, { transform: [{ scale: pulseAnim }] }]} />
            <Text style={styles.liveText}>LIVE TRACKING</Text>
          </View>
        )}
      </LinearGradient>

      <ScrollView
        style={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
        }
      >
        <Animated.View style={{ opacity: fadeAnim }}>
          {/* Main Status Card */}
          <View style={styles.mainStatusCard}>
            <View style={[styles.statusIconContainer, { backgroundColor: getStatusColor(order.status) + '20' }]}>
              <Ionicons 
                name={getStatusIcon(order.status) as any} 
                size={48} 
                color={getStatusColor(order.status)} 
              />
            </View>
            <Text style={styles.statusTitle}>{getStatusLabel(order.status)}</Text>
            {eta && (
              <View style={styles.etaBadge}>
                <Ionicons name="time-outline" size={16} color="#FFF" />
                <Text style={styles.etaText}>{eta}</Text>
              </View>
            )}
          </View>

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
                const isCompleted = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'].indexOf(order.status) >= index;
                const isCurrent = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'][index] === order.status;
                
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

          {/* Delivery Address Card */}
          {deliveryAddress && (
            <View style={styles.addressCard}>
              <View style={styles.addressHeader}>
                <Ionicons name="location" size={24} color={colors.primary[500]} />
                <Text style={styles.addressTitle}>Delivery Address</Text>
              </View>
              <View style={styles.addressContent}>
                {deliveryAddress.building_name && (
                  <Text style={styles.addressText}>🏢 {deliveryAddress.building_name}</Text>
                )}
                {deliveryAddress.area_locality && (
                  <Text style={styles.addressText}>📍 {deliveryAddress.area_locality}</Text>
                )}
                {deliveryAddress.city && (
                  <Text style={styles.addressText}>🏙️ {deliveryAddress.city}</Text>
                )}
                {deliveryAddress.detailed_directions && (
                  <Text style={styles.addressDirections}>
                    🧭 {deliveryAddress.detailed_directions}
                  </Text>
                )}
              </View>
            </View>
          )}

          {/* Order Summary Card */}
          <View style={styles.summaryCard}>
            <Text style={styles.sectionTitle}>Order Summary</Text>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Total Amount</Text>
              <Text style={styles.summaryValue}>NPR {order.total_amount}</Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Payment Method</Text>
              <Text style={styles.summaryValue}>{order.payment_method}</Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Order Time</Text>
              <Text style={styles.summaryValue}>
                {new Date(order.created_at).toLocaleString()}
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
    paddingTop: 50,
    paddingBottom: spacing.lg,
    paddingHorizontal: spacing.lg,
  },
  headerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
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
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold as any,
    color: '#FFF',
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
    ...styles.shadow,
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
  shadow: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
});
