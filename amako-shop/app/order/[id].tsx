import React from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity, 
  Alert,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { useOrder, useCancelOrder, useUpdateOrderStatus } from '../../src/state/orders';
import { useBackendOrder } from '../../src/hooks/useOrders';
import { Card, Button, Price } from '../../src/ui';
import { spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { OrderStatus } from '../../src/state/orders';
import { ScreenWithBottomNav } from '../../src/components';

// Order status configuration
const ORDER_STATUS_CONFIG = {
  pending: { label: 'Pending', color: colors.warning[500], icon: 'time-outline' },
  confirmed: { label: 'Confirmed', color: colors.info[500], icon: 'checkmark-circle-outline' },
  preparing: { label: 'Preparing', color: colors.primary[500], icon: 'restaurant-outline' },
  ready: { label: 'Ready', color: colors.success[500], icon: 'checkmark-circle' },
  out_for_delivery: { label: 'Out for Delivery', color: colors.info[600], icon: 'car-outline' },
  delivered: { label: 'Delivered', color: colors.success[600], icon: 'checkmark-done-circle' },
  cancelled: { label: 'Cancelled', color: colors.error[500], icon: 'close-circle' },
} as const;

export default function OrderDetailsScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  
  // Extract backend order ID from local storage ID format (order_123 -> 123)
  const backendOrderId = id?.toString().replace('order_', '');
  const numericOrderId = parseInt(backendOrderId || '0');
  
  // Validate order ID - timestamps are typically 13 digits, real order IDs are much smaller
  // This catches cases where Date.now() was used as a fallback order ID
  const isInvalidOrderId = numericOrderId > 10000000; // Reasonable upper bound for order IDs
  
  if (__DEV__) {
    console.log('📋 OrderDetailsScreen: Rendering with ID:', id);
    console.log('📋 OrderDetailsScreen: Backend ID:', numericOrderId);
    console.log('📋 OrderDetailsScreen: Is invalid ID (timestamp?):', isInvalidOrderId);
  }
  
  // Try to fetch from backend first (for real-time status) - but only if ID is valid
  const { data: backendOrder, isLoading, error, refetch } = useBackendOrder(isInvalidOrderId ? 0 : numericOrderId);
  
  // Fallback to local storage if backend order not available
  const localOrder = useOrder(id || '');
  
  // Use backend order if available (has real-time status), otherwise use local
  const order = backendOrder || localOrder;
  
  // Type assertion to handle both local and backend order types
  const orderData = order as any;
  
  const cancelOrder = useCancelOrder();
  const updateOrderStatus = useUpdateOrderStatus();
  
  const [refreshing, setRefreshing] = React.useState(false);

  const handleRefresh = async () => {
    setRefreshing(true);
    await refetch();
    setRefreshing(false);
  };

  if (__DEV__) {
    console.log('📋 OrderDetailsScreen: Backend order:', backendOrder);
    console.log('📋 OrderDetailsScreen: Local order:', localOrder);
    console.log('📋 OrderDetailsScreen: Using order:', order);
  }
  
  // Handle invalid order IDs early
  if (isInvalidOrderId) {
    console.warn('⚠️ Invalid order ID detected (looks like a timestamp):', numericOrderId);
    return (
      <ScreenWithBottomNav>
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle-outline" size={80} color={colors.error[500]} />
          <Text style={styles.errorTitle}>Invalid Order ID</Text>
          <Text style={styles.errorMessage}>
            This order was not properly created. Please check your orders list or try placing a new order.
          </Text>
          <Button
            title="View My Orders"
            onPress={() => router.replace('/orders')}
            variant="solid"
            size="lg"
            style={styles.errorButton}
          />
          <Button
            title="Go Back"
            onPress={() => router.back()}
            variant="outline"
            size="md"
            style={styles.secondaryErrorButton}
          />
        </View>
      </ScreenWithBottomNav>
    );
  }
  
  if (isLoading) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary[600]} />
          <Text style={styles.loadingText}>Loading order details...</Text>
        </View>
      </ScreenWithBottomNav>
    );
  }

  if (!order) {
    if (__DEV__) {
      console.log('📋 OrderDetailsScreen: No order found for ID:', id);
      if (error) {
        console.error('📋 OrderDetailsScreen: Error fetching order:', error);
      }
    }
    return (
      <ScreenWithBottomNav>
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle-outline" size={80} color={colors.error[500]} />
          <Text style={styles.errorTitle}>Order Not Found</Text>
          <Text style={styles.errorMessage}>
            The order you're looking for doesn't exist or has been removed.
          </Text>
          <Button
            title="View My Orders"
            onPress={() => router.replace('/orders')}
            variant="solid"
            size="lg"
            style={styles.errorButton}
          />
          <Button
            title="Go Back"
            onPress={() => router.back()}
            variant="outline"
            size="md"
            style={styles.secondaryErrorButton}
          />
        </View>
      </ScreenWithBottomNav>
    );
  }

  // Handle order cancellation
  const handleCancelOrder = () => {
    if (orderData.status === 'delivered' || orderData.status === 'cancelled') {
      Alert.alert('Cannot Cancel', 'This order cannot be cancelled.');
      return;
    }

    Alert.alert(
      'Cancel Order',
      'Are you sure you want to cancel this order?',
      [
        { text: 'No', style: 'cancel' },
        { 
          text: 'Yes, Cancel', 
          style: 'destructive',
          onPress: () => {
            cancelOrder(orderData.id);
            Alert.alert('Order Cancelled', 'Your order has been cancelled successfully.');
          }
        }
      ]
    );
  };

  // Format date for display
  const formatDate = (date: Date) => {
    return new Date(date).toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  // Render order status badge
  const renderStatusBadge = (status: OrderStatus) => {
    const config = ORDER_STATUS_CONFIG[status] || {
      label: status?.charAt(0).toUpperCase() + status?.slice(1).replace(/_/g, ' ') || 'Unknown',
      color: colors.gray[500],
      icon: 'help-circle-outline'
    };
    return (
      <View style={[styles.statusBadge, { backgroundColor: config.color + '20' }]}>
        <Ionicons name={config.icon as any} size={20} color={config.color} />
        <Text style={[styles.statusText, { color: config.color }]}>
          {config.label}
        </Text>
      </View>
    );
  };

  // Render order item
  const renderOrderItem = (item: any, index: number) => (
    <View key={`${item.itemId}-${item.variantId || 'base'}-${index}`} style={styles.orderItem}>
      <View style={styles.orderItemLeft}>
        <Text style={styles.orderItemName} numberOfLines={2}>
          {item.name}
        </Text>
        {item.variantName && (
          <Text style={styles.orderItemVariant}>
            {item.variantName}
          </Text>
        )}
        {item.addOns && item.addOns.length > 0 && (
          <Text style={styles.orderItemAddOns}>
            + {item.addOns.map((a: any) => a.name).join(', ')}
          </Text>
        )}
      </View>
      <View style={styles.orderItemRight}>
        <Text style={styles.orderItemQuantity}>
          Qty: {item.qty}
        </Text>
        <Price 
          value={item.unitBasePrice} 
          size="sm" 
          weight="medium"
        />
      </View>
    </View>
  );

  return (
    <ScreenWithBottomNav>
      <ScrollView 
        style={styles.container} 
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            colors={[colors.primary[600]]}
            tintColor={colors.primary[600]}
          />
        }
      >
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
          accessibilityRole="button"
          accessibilityLabel="Go back"
        >
          <Ionicons name="arrow-back" size={24} color={colors.gray[700]} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Order Details</Text>
        <View style={styles.headerIcon}>
          <Ionicons name="receipt-outline" size={24} color={colors.primary[600]} />
        </View>
      </View>

      {/* Order Status Card */}
      <Card style={styles.statusCard} padding="lg" radius="md" shadow="light">
        <View style={styles.statusHeader}>
          <Text style={styles.statusTitle}>Order Status</Text>
          {renderStatusBadge(orderData.status)}
        </View>
        <Text style={styles.orderId}>{orderData.order_number || `Order #${orderData.id}`}</Text>
        <Text style={styles.orderDate}>Placed on {formatDate(orderData.created_at || orderData.createdAt)}</Text>
        {(orderData.updated_at || orderData.updatedAt) !== (orderData.created_at || orderData.createdAt) && (
          <Text style={styles.orderUpdated}>Last updated: {formatDate(orderData.updated_at || orderData.updatedAt)}</Text>
        )}
        
        {/* Order Progress Indicator */}
        <View style={styles.progressContainer}>
          {['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'].map((status, index) => {
            const statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'];
            const currentIndex = statusOrder.indexOf(orderData.status);
            const isCompleted = currentIndex > index;
            const isCurrent = orderData.status === status;
            const isCancelled = orderData.status === 'cancelled';
            const isActive = isCurrent || isCompleted;
            
            return (
              <View key={status} style={styles.progressStep}>
                <View style={[
                  styles.progressDot,
                  isCompleted && !isCancelled && styles.progressDotCompleted,
                  isCurrent && !isCancelled && styles.progressDotCurrent,
                  isCancelled && styles.progressDotCancelled,
                  !isActive && !isCancelled && styles.progressDotPending
                ]}>
                  {isCompleted && !isCancelled && (
                    <Ionicons name="checkmark" size={16} color={colors.white} />
                  )}
                  {isCurrent && !isCancelled && (
                    <View style={styles.currentPulse} />
                  )}
                  {isCancelled && (
                    <Ionicons name="close" size={16} color={colors.white} />
                  )}
                </View>
                <Text style={[
                  styles.progressLabel,
                  isCompleted && !isCancelled && styles.progressLabelCompleted,
                  isCurrent && !isCancelled && styles.progressLabelCurrent,
                  isCancelled && styles.progressLabelCancelled,
                  !isActive && !isCancelled && styles.progressLabelPending
                ]}>
                  {ORDER_STATUS_CONFIG[status as OrderStatus]?.label || status}
                </Text>
                {index < 5 && (
                  <View style={[
                    styles.progressLine,
                    isCompleted && !isCancelled && styles.progressLineCompleted,
                    isCancelled && styles.progressLineCancelled,
                    !isActive && !isCancelled && styles.progressLinePending
                  ]} />
                )}
              </View>
            );
          })}
        </View>
      </Card>

      {/* Order Items Card - Only show if items are available */}
      {orderData.items && orderData.items.length > 0 && (
        <Card style={styles.itemsCard} padding="lg" radius="md" shadow="light">
          <Text style={styles.sectionTitle}>Order Items</Text>
          <View style={styles.orderItems}>
            {orderData.items.map((item: any, index: number) => renderOrderItem(item, index))}
          </View>
        </Card>
      )}

      {/* Order Summary Card */}
      <Card style={styles.summaryCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.sectionTitle}>📦 Order Summary</Text>
        
        {/* Order Items Breakdown */}
        {orderData.items && orderData.items.length > 0 && (
          <View style={styles.itemsBreakdown}>
            {orderData.items.map((item: any, index: number) => (
              <View key={index} style={styles.breakdownRow}>
                <Text style={styles.breakdownItemName}>
                  {item.qty}x {item.name}
                </Text>
                <Text style={styles.breakdownItemPrice}>
                  Rs. {(item.unitBasePrice * item.qty).toFixed(2)}
                </Text>
              </View>
            ))}
            <View style={styles.breakdownDivider} />
          </View>
        )}
        
        {/* Subtotal */}
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Subtotal:</Text>
          <Text style={styles.summaryValue}>
            Rs. {(orderData.total || orderData.total_amount || 0).toFixed(2)}
          </Text>
        </View>
        
        {/* Tax (if available) */}
        {orderData.tax_amount && orderData.tax_amount > 0 && (
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Tax:</Text>
            <Text style={styles.summaryValue}>
              Rs. {orderData.tax_amount.toFixed(2)}
            </Text>
          </View>
        )}
        
        {/* Delivery Fee (if available) */}
        {orderData.delivery_fee && orderData.delivery_fee > 0 && (
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Delivery Fee:</Text>
            <Text style={styles.summaryValue}>
              Rs. {orderData.delivery_fee.toFixed(2)}
            </Text>
          </View>
        )}
        
        <View style={styles.summaryDivider} />
        
        {/* Grand Total */}
        <View style={styles.summaryRow}>
          <Text style={styles.totalLabel}>Total Amount:</Text>
          <Text style={styles.totalValue}>
            Rs. {(orderData.grand_total || orderData.total || orderData.total_amount || 0).toFixed(2)}
          </Text>
        </View>
        
        {/* Payment Status */}
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Payment Status:</Text>
          <View style={styles.paymentStatusContainer}>
            <View style={[
              styles.paymentStatusDot,
              { backgroundColor: orderData.payment_status === 'paid' ? colors.success[500] : colors.warning[500] }
            ]} />
            <Text style={[
              styles.summaryValue,
              { color: orderData.payment_status === 'paid' ? colors.success[600] : colors.warning[600] }
            ]}>
              {(orderData.payment_status || 'pending').toUpperCase()}
            </Text>
          </View>
        </View>
        
        {/* Payment Method */}
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Payment Method:</Text>
          <Text style={styles.summaryValue}>
            {(() => {
              const method = orderData.payment_method || orderData.paymentMethod || 'cash';
              switch(method) {
                case 'cash': return 'Cash on Delivery';
                case 'esewa': return 'eSewa';
                case 'khalti': return 'Khalti';
                case 'amako_credits': return 'Amako Credits';
                default: return method.charAt(0).toUpperCase() + method.slice(1);
              }
            })()}
          </Text>
        </View>
      </Card>

      {/* Delivery Details Card */}
      <Card style={styles.deliveryCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.sectionTitle}>Delivery Details</Text>
        
        <View style={styles.detailRow}>
          <Ionicons name="location-outline" size={20} color={colors.gray[600]} />
          <View style={styles.detailContent}>
            <Text style={styles.detailLabel}>Delivery Address</Text>
            <Text style={styles.detailValue}>
              {(() => {
                // Handle delivery address - it might be a string or an object
                const addr = orderData.delivery_address || orderData.deliveryAddress;
                
                if (!addr) {
                  return 'No address provided';
                }
                
                // If it's a string, try to parse it as JSON first, then display
                if (typeof addr === 'string') {
                  try {
                    // Try to parse if it looks like JSON
                    if (addr.startsWith('{') && addr.endsWith('}')) {
                      const parsedAddr = JSON.parse(addr);
                      return formatAddress(parsedAddr);
                    }
                    // Otherwise display as-is
                    return addr;
                  } catch (e) {
                    // If parsing fails, display as-is
                    return addr;
                  }
                }
                
                // If it's already an object, format it properly
                return formatAddress(addr);
                
                function formatAddress(addressObj: any) {
                  const parts = [];
                  
                  // Building name first (if available)
                  if (addressObj.building_name) {
                    parts.push(addressObj.building_name);
                  }
                  
                  // Area/locality
                  if (addressObj.area_locality) {
                    parts.push(addressObj.area_locality);
                  }
                  
                  // Ward and city
                  if (addressObj.ward_number && addressObj.city) {
                    parts.push(`Ward ${addressObj.ward_number}, ${addressObj.city}`);
                  } else if (addressObj.city) {
                    parts.push(addressObj.city);
                  } else if (addressObj.ward_number) {
                    parts.push(`Ward ${addressObj.ward_number}`);
                  }
                  
                  // If we have parts, join them with line breaks for better readability
                  if (parts.length > 0) {
                    const formattedAddress = parts.join('\n');
                    
                    // Add detailed directions if available
                    if (addressObj.detailed_directions) {
                      return formattedAddress + '\n\nDirections: ' + addressObj.detailed_directions;
                    }
                    
                    return formattedAddress;
                  }
                  
                  return 'Address details not available';
                }
              })()}
            </Text>
          </View>
        </View>
        
        {(orderData.name || orderData.customer_name) && (
          <View style={styles.detailRow}>
            <Ionicons name="person-outline" size={20} color={colors.gray[600]} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Customer Name</Text>
              <Text style={styles.detailValue}>{orderData.name || orderData.customer_name}</Text>
            </View>
          </View>
        )}
        
        {(orderData.phone || orderData.customer_phone) && (
          <View style={styles.detailRow}>
            <Ionicons name="call-outline" size={20} color={colors.gray[600]} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Contact Number</Text>
              <Text style={styles.detailValue}>{orderData.phone || orderData.customer_phone}</Text>
            </View>
          </View>
        )}
        
        <View style={styles.detailRow}>
          <Ionicons name="card-outline" size={20} color={colors.gray[600]} />
          <View style={styles.detailContent}>
            <Text style={styles.detailLabel}>Payment Method</Text>
            <Text style={styles.detailValue}>
              {(() => {
                const method = orderData.payment_method || orderData.paymentMethod || 'cash';
                switch(method) {
                  case 'cash': return 'Cash on Delivery';
                  case 'esewa': return 'eSewa';
                  case 'khalti': return 'Khalti';
                  case 'amako_credits': return 'Amako Credits';
                  default: return method.toUpperCase();
                }
              })()}
            </Text>
          </View>
        </View>
        
        <View style={styles.detailRow}>
          <Ionicons name="time-outline" size={20} color={colors.gray[600]} />
          <View style={styles.detailContent}>
            <Text style={styles.detailLabel}>Estimated Delivery</Text>
            <Text style={styles.detailValue}>
              {orderData.status === 'delivered' 
                ? 'Delivered' 
                : orderData.status === 'cancelled'
                ? 'Order Cancelled'
                : '25-35 minutes'
              }
            </Text>
          </View>
        </View>
        
        {(orderData.notes || orderData.special_instructions) && (
          <View style={styles.detailRow}>
            <Ionicons name="chatbubble-outline" size={20} color={colors.gray[600]} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Special Instructions</Text>
              <Text style={styles.detailValue}>{orderData.notes || orderData.special_instructions}</Text>
            </View>
          </View>
        )}
      </Card>

      {/* Order Actions */}
      <View style={styles.actionsContainer}>
        {orderData.status === 'pending' && (
          <Button
            title="Cancel Order"
            onPress={handleCancelOrder}
            variant="outline"
            size="lg"
            style={styles.cancelButton}
          />
        )}
        
        {(orderData.status === 'out_for_delivery' || orderData.status === 'delivered') && (
          <Button
            title="📍 Track Delivery"
            onPress={() => router.push(`/order-tracking/${id}`)}
            variant="solid"
            size="lg"
            style={styles.trackButton}
          />
        )}
        
        <Button
          title="Back to Orders"
          onPress={() => router.push('/orders')}
          variant="outline"
          size="md"
          style={styles.backToOrdersButton}
        />
      </View>

      {/* Footer Spacing */}
      <View style={styles.footer} />
      </ScrollView>
    </ScreenWithBottomNav>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.md,
    paddingBottom: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  backButton: {
    padding: spacing.sm,
    borderRadius: radius.sm,
    backgroundColor: colors.gray[100],
  },
  headerIcon: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    flex: 1,
  },
  statusCard: {
    margin: spacing.lg,
    marginBottom: spacing.md,
  },
  statusHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  statusTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
    gap: spacing.sm,
  },
  statusText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  orderId: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.text.primary,
    marginBottom: spacing.xs,
  },
  orderDate: {
    fontSize: fontSizes.sm,
    color: colors.text.secondary,
    marginBottom: spacing.xs,
  },
  orderUpdated: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    fontStyle: 'italic',
  },
  itemsCard: {
    marginHorizontal: spacing.lg,
    marginBottom: spacing.md,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginBottom: spacing.md,
  },
  orderItems: {
    gap: spacing.md,
  },
  orderItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingVertical: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  orderItemLeft: {
    flex: 1,
    marginRight: spacing.md,
  },
  orderItemName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[800],
    marginBottom: spacing.xs,
  },
  orderItemVariant: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    marginBottom: spacing.xs,
  },
  orderItemAddOns: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    fontStyle: 'italic',
  },
  orderItemRight: {
    alignItems: 'flex-end',
  },
  orderItemQuantity: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  summaryCard: {
    marginHorizontal: spacing.lg,
    marginBottom: spacing.md,
  },
  itemsBreakdown: {
    marginBottom: spacing.md,
  },
  breakdownRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  breakdownItemName: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
    flex: 1,
    marginRight: spacing.sm,
  },
  breakdownItemPrice: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[800],
  },
  breakdownDivider: {
    height: 1,
    backgroundColor: colors.gray[200],
    marginVertical: spacing.sm,
  },
  summaryDivider: {
    height: 2,
    backgroundColor: colors.gray[300],
    marginVertical: spacing.md,
    borderRadius: 1,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  paymentStatusContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  paymentStatusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  summaryLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  totalLabel: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
  },
  totalValue: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.primary[600],
  },
  summaryValue: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  divider: {
    height: 1,
    backgroundColor: colors.gray[200],
    marginVertical: spacing.md,
  },
  deliveryCard: {
    marginHorizontal: spacing.lg,
    marginBottom: spacing.md,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: spacing.md,
  },
  detailContent: {
    flex: 1,
    marginLeft: spacing.sm,
  },
  detailLabel: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  detailValue: {
    fontSize: fontSizes.md,
    color: colors.gray[800],
    lineHeight: 20,
  },
  actionsContainer: {
    margin: spacing.lg,
    gap: spacing.md,
  },
  cancelButton: {
    borderColor: colors.error[500],
  },
  trackButton: {
    width: '100%',
  },
  backToOrdersButton: {
    width: '100%',
  },
  footer: {
    height: spacing.xl,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  errorTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.error[600],
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  errorMessage: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.xl,
    lineHeight: 22,
  },
  errorButton: {
    minWidth: 160,
  },
  secondaryErrorButton: {
    minWidth: 160,
    marginTop: 8,
  },
  progressContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: spacing.md,
    paddingHorizontal: spacing.sm,
  },
  progressStep: {
    alignItems: 'center',
    flex: 1,
  },
  progressDot: {
    width: 32,
    height: 32,
    borderRadius: radius.full,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[300],
    zIndex: 1,
    borderWidth: 3,
    borderColor: colors.white,
    shadowColor: colors.gray[900],
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  progressDotCompleted: {
    backgroundColor: colors.success[500],
    borderColor: colors.success[600],
  },
  progressDotCurrent: {
    backgroundColor: colors.primary[500],
    borderColor: colors.primary[600],
    shadowColor: colors.primary[500],
    shadowOpacity: 0.3,
  },
  progressDotPending: {
    backgroundColor: colors.gray[200],
    borderColor: colors.gray[300],
  },
  progressDotCancelled: {
    backgroundColor: colors.error[500],
    borderColor: colors.error[600],
  },
  currentPulse: {
    width: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: colors.white,
    shadowColor: colors.white,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.8,
    shadowRadius: 4,
    elevation: 2,
  },
  progressLabel: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: colors.gray[600],
    marginTop: spacing.xs,
    textAlign: 'center',
    lineHeight: 14,
  },
  progressLabelCompleted: {
    color: colors.success[600],
    fontWeight: fontWeights.semibold,
  },
  progressLabelCurrent: {
    color: colors.primary[600],
    fontWeight: fontWeights.bold,
  },
  progressLabelPending: {
    color: colors.gray[500],
    fontWeight: fontWeights.normal,
  },
  progressLabelCancelled: {
    color: colors.error[600],
    fontWeight: fontWeights.semibold,
  },
  progressLine: {
    width: '100%',
    height: 3,
    backgroundColor: colors.gray[300],
    position: 'absolute',
    top: 16,
    zIndex: -1,
    borderRadius: 2,
  },
  progressLineCompleted: {
    backgroundColor: colors.success[500],
  },
  progressLinePending: {
    backgroundColor: colors.gray[200],
  },
  progressLineCancelled: {
    backgroundColor: colors.error[500],
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.xl,
  },
  loadingText: {
    marginTop: spacing.md,
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
});
