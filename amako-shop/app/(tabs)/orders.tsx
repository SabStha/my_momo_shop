import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  FlatList, 
  StyleSheet, 
  TouchableOpacity, 
  Alert,
  RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useFocusEffect } from 'expo-router';
import { useOrders, useCancelOrder, useRefreshOrders, useCreateOrder, useDebugStorage } from '../../src/state/orders';
import { Card, Button, Price } from '../../src/ui';
import { spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { Order, OrderStatus } from '../../src/state/orders';

// Order status configuration
const ORDER_STATUS_CONFIG = {
  pending: { label: 'Pending', color: colors.warning[500], icon: 'time-outline' },
  confirmed: { label: 'Confirmed', color: colors.info[500], icon: 'checkmark-circle-outline' },
  preparing: { label: 'Preparing', color: colors.primary[500], icon: 'restaurant-outline' },
  ready: { label: 'Ready', color: colors.success[500], icon: 'checkmark-circle' },
  delivered: { label: 'Delivered', color: colors.success[600], icon: 'checkmark-done-circle' },
  cancelled: { label: 'Cancelled', color: colors.error[500], icon: 'close-circle' },
} as const;

export default function OrdersScreen() {
  const orders = useOrders();
  const cancelOrder = useCancelOrder();
  const refreshOrders = useRefreshOrders();
  const createOrder = useCreateOrder();
  const debugStorage = useDebugStorage();
  const [selectedStatus, setSelectedStatus] = useState<OrderStatus | 'all'>('all');

  // Auto-refresh orders when screen mounts
  useEffect(() => {
    if (__DEV__) {
      console.log('📋 OrdersScreen: Component mounted, refreshing orders...');
    }
    refreshOrders();
  }, [refreshOrders]);

  // Refresh orders when screen comes into focus
  useFocusEffect(
    React.useCallback(() => {
      if (__DEV__) {
        console.log('📋 OrdersScreen: Screen focused, refreshing orders...');
      }
      refreshOrders();
    }, [refreshOrders])
  );

  // Debug logging
  if (__DEV__) {
    console.log('📋 OrdersScreen: Current orders:', orders);
    console.log('📋 OrdersScreen: Orders length:', orders.length);
    console.log('📋 OrdersScreen: Selected status:', selectedStatus);
  }

  // Filter orders by status
  const filteredOrders = selectedStatus === 'all' 
    ? orders 
    : orders.filter(order => order.status === selectedStatus);

  if (__DEV__) {
    console.log('📋 OrdersScreen: Filtered orders:', filteredOrders);
    console.log('📋 OrdersScreen: Filtered orders length:', filteredOrders.length);
  }

  // Handle order cancellation
  const handleCancelOrder = (order: Order) => {
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
          onPress: () => cancelOrder(order.id)
        }
      ]
    );
  };

  // Navigate to checkout
  const handleNewOrder = () => {
    router.push('/(tabs)/');
  };

  // Test function to create a sample order (for debugging)
  const handleCreateTestOrder = () => {
    if (__DEV__) {
      console.log('🧪 Creating test order...');
    }
    
    createOrder({
      items: [
        {
          itemId: 'test-item-1',
          name: 'Test Momo',
          unitBasePrice: { currency: 'NPR', amount: 150 },
          qty: 2,
          imageUrl: undefined,
        }
      ],
      subtotal: { currency: 'NPR', amount: 300 },
      deliveryFee: { currency: 'NPR', amount: 0 },
      tax: { currency: 'NPR', amount: 0 },
      total: { currency: 'NPR', amount: 300 },
      status: 'pending',
      paymentMethod: 'cash',
      deliveryAddress: 'Test Address',
      notes: 'Test order for debugging',
    });
    
    // Refresh orders after creating test order
    setTimeout(() => refreshOrders(), 100);
  };

  // Format date for display
  const formatDate = (date: Date) => {
    return new Date(date).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  // Render order status badge
  const renderStatusBadge = (status: OrderStatus) => {
    const config = ORDER_STATUS_CONFIG[status];
    return (
      <View style={[styles.statusBadge, { backgroundColor: config.color + '20' }]}>
        <Ionicons name={config.icon as any} size={16} color={config.color} />
        <Text style={[styles.statusText, { color: config.color }]}>
          {config.label}
        </Text>
      </View>
    );
  };

  // Render order item
  const renderOrderItem = ({ item }: { item: Order }) => (
    <Card style={styles.orderCard} padding="lg" radius="md" shadow="light">
      {/* Order Header */}
      <View style={styles.orderHeader}>
        <View style={styles.orderInfo}>
          <Text style={styles.orderId}>Order #{item.id.split('_')[1]}</Text>
          <Text style={styles.orderDate}>{formatDate(item.createdAt)}</Text>
        </View>
        {renderStatusBadge(item.status)}
      </View>

      {/* Order Items Summary */}
      <View style={styles.orderItems}>
        <Text style={styles.orderItemsTitle}>
          {item.items.length} {item.items.length === 1 ? 'item' : 'items'}
        </Text>
        {item.items.slice(0, 2).map((cartItem, index) => (
          <Text key={index} style={styles.orderItemText}>
            • {cartItem.name} x{cartItem.qty}
          </Text>
        ))}
        {item.items.length > 2 && (
          <Text style={styles.orderItemText}>
            • +{item.items.length - 2} more items
          </Text>
        )}
      </View>

      {/* Order Totals */}
      <View style={styles.orderTotals}>
        <View style={styles.totalRow}>
          <Text style={styles.totalLabel}>Total:</Text>
          <Price 
            value={item.total} 
            size="md" 
            weight="semibold" 
            color="primary"
          />
        </View>
        <Text style={styles.paymentMethod}>
          Payment: {item.paymentMethod === 'cash' ? 'Cash on Delivery' : 'eSewa'}
        </Text>
      </View>

      {/* Order Actions */}
      <View style={styles.orderActions}>
        {item.status === 'pending' && (
          <Button
            title="Cancel Order"
            onPress={() => handleCancelOrder(item)}
            variant="outline"
            size="sm"
            style={styles.cancelButton}
          />
        )}
        <Button
          title="View Details"
          onPress={() => {
            router.push(`/order/${item.id}`);
          }}
          variant="solid"
          size="sm"
          style={styles.detailsButton}
        />
      </View>
    </Card>
  );

  // Render empty state
  const renderEmptyState = () => (
    <View style={styles.emptyContainer}>
      <View style={styles.emptyIcon}>
        <Ionicons name="receipt-outline" size={80} color={colors.gray[300]} />
      </View>
      <Text style={styles.emptyTitle}>No orders yet</Text>
      <Text style={styles.emptyMessage}>
        Start ordering delicious momos and drinks to see your order history here!
      </Text>
      <View style={styles.emptyButtons}>
        <Button
          title="Browse Menu"
          onPress={handleNewOrder}
          variant="solid"
          size="lg"
          style={styles.browseButton}
        />
        {__DEV__ && (
          <Button
            title="Create Test Order"
            onPress={handleCreateTestOrder}
            variant="outline"
            size="md"
            style={styles.testButton}
          />
        )}
      </View>
    </View>
  );

  // Render status filter
  const renderStatusFilter = () => (
    <View style={styles.filterContainer}>
      <FlatList
        horizontal
        data={[
          { key: 'all', label: 'All', status: 'all' as const },
          ...Object.entries(ORDER_STATUS_CONFIG).map(([status, config]) => ({
            key: status,
            label: config.label,
            status: status as OrderStatus,
          }))
        ]}
        renderItem={({ item }) => (
          <TouchableOpacity
            style={[
              styles.filterChip,
              selectedStatus === item.status && styles.filterChipSelected
            ]}
            onPress={() => setSelectedStatus(item.status)}
          >
            <Text style={[
              styles.filterChipText,
              selectedStatus === item.status && styles.filterChipTextSelected
            ]}>
              {item.label}
            </Text>
          </TouchableOpacity>
        )}
        keyExtractor={(item) => item.key}
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.filterList}
      />
    </View>
  );

  if (orders.length === 0) {
    return (
      <View style={styles.container}>
        {renderEmptyState()}
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Your Orders</Text>
        <View style={styles.headerRight}>
          <Text style={styles.headerSubtitle}>
            {filteredOrders.length} {filteredOrders.length === 1 ? 'order' : 'orders'}
          </Text>
          <TouchableOpacity 
            style={styles.refreshButton} 
            onPress={refreshOrders}
            accessibilityRole="button"
            accessibilityLabel="Refresh orders"
          >
            <Ionicons name="refresh" size={20} color={colors.primary[600]} />
          </TouchableOpacity>
          {__DEV__ && (
            <TouchableOpacity 
              style={styles.debugButton} 
              onPress={debugStorage}
              accessibilityRole="button"
              accessibilityLabel="Debug storage"
            >
              <Ionicons name="bug" size={20} color={colors.warning[600]} />
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* Status Filter */}
      {renderStatusFilter()}

      {/* Orders List */}
      <FlatList
        data={filteredOrders}
        renderItem={renderOrderItem}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.listContainer}
        showsVerticalScrollIndicator={false}
        ListFooterComponent={<View style={styles.listFooter} />}
        refreshControl={
          <RefreshControl refreshing={false} onRefresh={refreshOrders} />
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.md,
  },
  header: {
    marginBottom: spacing.lg,
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.text.primary,
    marginBottom: spacing.xs,
  },
  headerSubtitle: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  refreshButton: {
    padding: spacing.xs,
  },
  debugButton: {
    padding: spacing.xs,
  },
  filterContainer: {
    marginBottom: spacing.lg,
  },
  filterList: {
    paddingHorizontal: spacing.xs,
  },
  filterChip: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
    backgroundColor: colors.gray[100],
    marginHorizontal: spacing.xs,
    borderWidth: 1,
    borderColor: colors.gray[200],
  },
  filterChipSelected: {
    backgroundColor: colors.primary[500],
    borderColor: colors.primary[500],
  },
  filterChipText: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
    fontWeight: fontWeights.medium,
  },
  filterChipTextSelected: {
    color: colors.white,
  },
  listContainer: {
    paddingBottom: spacing.lg,
  },
  listFooter: {
    height: spacing.md,
  },
  orderCard: {
    marginBottom: spacing.md,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: spacing.md,
  },
  orderInfo: {
    flex: 1,
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
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    gap: spacing.xs,
  },
  statusText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  orderItems: {
    marginBottom: spacing.md,
  },
  orderItemsTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.text.primary,
    marginBottom: spacing.xs,
  },
  orderItemText: {
    fontSize: fontSizes.sm,
    color: colors.text.secondary,
    marginBottom: spacing.xs,
  },
  orderTotals: {
    marginBottom: spacing.md,
    paddingTop: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  totalLabel: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
  },
  paymentMethod: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    fontStyle: 'italic',
  },
  orderActions: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  cancelButton: {
    flex: 1,
    borderColor: colors.error[500],
  },
  detailsButton: {
    flex: 1,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
  },
  emptyIcon: {
    marginBottom: spacing.lg,
  },
  emptyTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.text.primary,
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  emptyMessage: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
    textAlign: 'center',
    marginBottom: spacing.xl,
    lineHeight: 22,
  },
  browseButton: {
    minWidth: 160,
  },
  emptyButtons: {
    flexDirection: 'row',
    gap: spacing.md,
    marginTop: spacing.lg,
  },
  testButton: {
    minWidth: 160,
  },
});
