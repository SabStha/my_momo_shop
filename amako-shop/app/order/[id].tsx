import React from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity, 
  Alert 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { useOrder, useCancelOrder, useUpdateOrderStatus } from '../../src/state/orders';
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
  delivered: { label: 'Delivered', color: colors.success[600], icon: 'checkmark-done-circle' },
  cancelled: { label: 'Cancelled', color: colors.error[500], icon: 'close-circle' },
} as const;

export default function OrderDetailsScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const order = useOrder(id || '');
  const cancelOrder = useCancelOrder();
  const updateOrderStatus = useUpdateOrderStatus();

  if (__DEV__) {
    console.log('📋 OrderDetailsScreen: Rendering with ID:', id);
    console.log('📋 OrderDetailsScreen: Found order:', order);
  }

  if (!order) {
    if (__DEV__) {
      console.log('📋 OrderDetailsScreen: No order found for ID:', id);
    }
    return (
      <View style={styles.container}>
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle-outline" size={80} color={colors.error[500]} />
          <Text style={styles.errorTitle}>Order Not Found</Text>
          <Text style={styles.errorMessage}>
            The order you're looking for doesn't exist or has been removed.
          </Text>
          <Button
            title="Go Back"
            onPress={() => router.back()}
            variant="solid"
            size="lg"
            style={styles.errorButton}
          />
        </View>
      </View>
    );
  }

  // Handle order cancellation
  const handleCancelOrder = () => {
    if (order.status === 'delivered' || order.status === 'cancelled') {
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
            cancelOrder(order.id);
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
    const config = ORDER_STATUS_CONFIG[status];
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
      <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
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
        <View style={styles.headerSpacer} />
      </View>

      {/* Order Status Card */}
      <Card style={styles.statusCard} padding="lg" radius="md" shadow="light">
        <View style={styles.statusHeader}>
          <Text style={styles.statusTitle}>Order Status</Text>
          {renderStatusBadge(order.status)}
        </View>
        <Text style={styles.orderId}>Order #{order.id.split('_')[1]}</Text>
        <Text style={styles.orderDate}>Placed on {formatDate(order.createdAt)}</Text>
        {order.updatedAt !== order.createdAt && (
          <Text style={styles.orderUpdated}>Last updated: {formatDate(order.updatedAt)}</Text>
        )}
        
        {/* Order Progress Indicator */}
        <View style={styles.progressContainer}>
          {['pending', 'confirmed', 'preparing', 'ready', 'delivered'].map((status, index) => {
            const isCompleted = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'].indexOf(order.status) >= index;
            const isCurrent = order.status === status;
            const isCancelled = order.status === 'cancelled';
            
            return (
              <View key={status} style={styles.progressStep}>
                <View style={[
                  styles.progressDot,
                  isCompleted && !isCancelled && styles.progressDotCompleted,
                  isCurrent && styles.progressDotCurrent,
                  isCancelled && styles.progressDotCancelled
                ]}>
                  {isCompleted && !isCancelled && (
                    <Ionicons name="checkmark" size={16} color={colors.white} />
                  )}
                  {isCancelled && (
                    <Ionicons name="close" size={16} color={colors.white} />
                  )}
                </View>
                <Text style={[
                  styles.progressLabel,
                  isCompleted && !isCancelled && styles.progressLabelCompleted,
                  isCurrent && styles.progressLabelCurrent,
                  isCancelled && styles.progressLabelCancelled
                ]}>
                  {ORDER_STATUS_CONFIG[status as OrderStatus]?.label || status}
                </Text>
                {index < 4 && (
                  <View style={[
                    styles.progressLine,
                    isCompleted && !isCancelled && styles.progressLineCompleted,
                    isCancelled && styles.progressLineCancelled
                  ]} />
                )}
              </View>
            );
          })}
        </View>
      </Card>

      {/* Order Items Card */}
      <Card style={styles.itemsCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.sectionTitle}>Order Items</Text>
        <View style={styles.orderItems}>
          {order.items.map((item, index) => renderOrderItem(item, index))}
        </View>
      </Card>

      {/* Order Summary Card */}
      <Card style={styles.summaryCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.sectionTitle}>Order Summary</Text>
        
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Subtotal:</Text>
          <Price 
            value={order.subtotal} 
            size="md" 
            weight="medium"
          />
        </View>
        
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Delivery Fee:</Text>
          <Price 
            value={order.deliveryFee} 
            size="md" 
            weight="medium"
            color={order.deliveryFee.amount === 0 ? colors.success[600] : colors.gray[600]}
          />
        </View>
        
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Tax:</Text>
          <Price 
            value={order.tax} 
            size="md" 
            weight="medium"
          />
        </View>
        
        <View style={styles.divider} />
        
        <View style={styles.summaryRow}>
          <Text style={styles.totalLabel}>Total:</Text>
          <Price 
            value={order.total} 
            size="lg" 
            weight="bold" 
            color={colors.primary[600]}
          />
        </View>
      </Card>

      {/* Delivery Details Card */}
      <Card style={styles.deliveryCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.sectionTitle}>Delivery Details</Text>
        
        <View style={styles.detailRow}>
          <Ionicons name="location-outline" size={20} color={colors.gray[600]} />
          <View style={styles.detailContent}>
            <Text style={styles.detailLabel}>Delivery Address</Text>
            <Text style={styles.detailValue}>{order.deliveryAddress}</Text>
          </View>
        </View>
        
        <View style={styles.detailRow}>
          <Ionicons name="card-outline" size={20} color={colors.gray[600]} />
          <View style={styles.detailContent}>
            <Text style={styles.detailLabel}>Payment Method</Text>
            <Text style={styles.detailValue}>
              {order.paymentMethod === 'cash' ? 'Cash on Delivery' : 'eSewa'}
            </Text>
          </View>
        </View>
        
        <View style={styles.detailRow}>
          <Ionicons name="time-outline" size={20} color={colors.gray[600]} />
          <View style={styles.detailContent}>
            <Text style={styles.detailLabel}>Estimated Delivery</Text>
            <Text style={styles.detailValue}>
              {order.status === 'delivered' 
                ? 'Delivered' 
                : order.status === 'cancelled'
                ? 'Order Cancelled'
                : '25-35 minutes'
              }
            </Text>
          </View>
        </View>
        
        {order.notes && (
          <View style={styles.detailRow}>
            <Ionicons name="chatbubble-outline" size={20} color={colors.gray[600]} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Special Instructions</Text>
              <Text style={styles.detailValue}>{order.notes}</Text>
            </View>
          </View>
        )}
      </Card>

      {/* Order Actions */}
      <View style={styles.actionsContainer}>
        {order.status === 'pending' && (
          <Button
            title="Cancel Order"
            onPress={handleCancelOrder}
            variant="outline"
            size="lg"
            style={styles.cancelButton}
          />
        )}
        
        <Button
          title="Track Order"
          onPress={() => {
            Alert.alert('Order Tracking', 'Order tracking feature coming soon!');
          }}
          variant="solid"
          size="lg"
          style={styles.trackButton}
        />
        
        <Button
          title="Back to Orders"
          onPress={() => router.push('/(tabs)/profile')}
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
  headerSpacer: {
    width: 40,
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
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
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
    width: 30,
    height: 30,
    borderRadius: radius.full,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[300],
    zIndex: 1,
  },
  progressDotCompleted: {
    backgroundColor: colors.primary[500],
  },
  progressDotCurrent: {
    backgroundColor: colors.primary[500],
  },
  progressDotCancelled: {
    backgroundColor: colors.error[500],
  },
  progressLabel: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: colors.gray[600],
    marginTop: spacing.xs,
    textAlign: 'center',
  },
  progressLabelCompleted: {
    color: colors.primary[600],
  },
  progressLabelCurrent: {
    color: colors.primary[600],
  },
  progressLabelCancelled: {
    color: colors.error[600],
  },
  progressLine: {
    width: '100%',
    height: 1,
    backgroundColor: colors.gray[300],
    position: 'absolute',
    top: 15,
    zIndex: -1,
  },
  progressLineCompleted: {
    backgroundColor: colors.primary[500],
  },
  progressLineCancelled: {
    backgroundColor: colors.error[500],
  },
});
