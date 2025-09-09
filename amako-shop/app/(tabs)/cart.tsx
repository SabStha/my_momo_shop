import React from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { useCartStore } from '../../src/state/cart';
import { useOrderStore } from '../../src/state/orders';
import { CartItem } from '../../src/components';
import { Button, Card, Price, spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { formatPrice, sumMoney, multiplyMoney, addMoney } from '../../src/utils/price';
import { CartLine } from '../../src/types';

export default function CartScreen() {
  const items = useCartStore(state => state.items);
  const subtotal = useCartStore(state => state.subtotal);
  const isEmpty = useCartStore(state => state.isEmpty);
  const updateQuantity = useCartStore(state => state.updateQuantity);
  const removeItem = useCartStore(state => state.removeItem);
  const clearCart = useCartStore(state => state.clearCart);

  // Handle quantity update
  const handleUpdateQuantity = (item: CartLine, newQuantity: number) => {
    updateQuantity(
      item.itemId, 
      item.variantId, 
      item.addOns?.map(a => a.id) || [], 
      newQuantity
    );
  };

  // Handle item removal
  const handleRemoveItem = (item: CartLine) => {
    removeItem(
      item.itemId, 
      item.variantId, 
      item.addOns?.map(a => a.id) || []
    );
  };

  // Handle clear cart with confirmation
  const handleClearCart = () => {
    Alert.alert(
      'Clear Cart',
      'Are you sure you want to remove all items from your cart?',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Clear All', 
          style: 'destructive',
          onPress: clearCart
        }
      ]
    );
  };

  // Handle checkout navigation
  const handleCheckout = () => {
    router.push('/checkout');
  };

  // Navigate to menu
  const handleBrowseMenu = () => {
    router.push('/(tabs)/');
  };

  // Render empty cart state
  const renderEmptyCart = () => (
    <View style={styles.emptyContainer}>
      <View style={styles.emptyIcon}>
        <Ionicons name="cart-outline" size={80} color={colors.gray[300]} />
      </View>
      <Text style={styles.emptyTitle}>Your cart is empty</Text>
      <Text style={styles.emptyMessage}>
        Start adding delicious momos and drinks to your cart!
      </Text>
              <Button
          title="Browse Menu"
          onPress={handleBrowseMenu}
          variant="solid"
          size="lg"
          style={styles.browseButton}
        />
    </View>
  );

  // Render cart item
  const renderCartItem = ({ item }: { item: CartLine }) => (
    <CartItem
      item={item}
      onUpdateQuantity={(quantity) => handleUpdateQuantity(item, quantity)}
      onRemove={() => handleRemoveItem(item)}
    />
  );

  // Render cart totals
  const renderCartTotals = () => (
    <Card style={styles.totalsCard} padding="lg" radius="md" shadow="light">
      <Text style={styles.totalsTitle}>Order Summary</Text>
      
      {/* Subtotal */}
      <View style={styles.totalRow}>
        <Text style={styles.totalLabel}>Subtotal</Text>
        <Price value={subtotal} size="md" weight="semibold" color="primary" />
      </View>

      {/* Tax/Charges (Placeholder) */}
      <View style={styles.totalRow}>
        <Text style={styles.totalLabel}>Tax & Charges</Text>
        <Text style={styles.totalPlaceholder}>TBD</Text>
      </View>

      {/* Grand Total */}
      <View style={styles.totalRow}>
        <Text style={styles.totalLabel}>Grand Total</Text>
        <Price value={subtotal} size="lg" weight="bold" color="primary" />
      </View>

      {/* Action Buttons */}
      <View style={styles.actionButtons}>
        <Button
          title="Clear Cart"
          onPress={handleClearCart}
          variant="outline"
          size="md"
          style={styles.clearButton}
        />
        <Button
          title="Checkout"
          onPress={handleCheckout}
          variant="solid"
          size="md"
          style={styles.checkoutButton}
        />
      </View>
    </Card>
  );

  if (isEmpty) {
    return renderEmptyCart();
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Your Cart</Text>
        <Text style={styles.headerSubtitle}>
          {items.length} {items.length === 1 ? 'item' : 'items'}
        </Text>
      </View>

      {/* Cart Items */}
      <FlatList
        data={items}
        renderItem={renderCartItem}
        keyExtractor={(item) => {
          const addOnsKey = item.addOns?.map(a => a.id).sort().join(',') || '';
          return `${item.itemId}:${item.variantId || 'base'}:${addOnsKey}`;
        }}
        contentContainerStyle={styles.listContainer}
        showsVerticalScrollIndicator={false}
        ListFooterComponent={<View style={styles.listFooter} />}
      />

      {/* Cart Totals */}
      {renderCartTotals()}
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
  listContainer: {
    paddingBottom: spacing.lg,
  },
  listFooter: {
    height: spacing.md,
  },
  totalsCard: {
    marginBottom: spacing.lg,
  },
  totalsTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.text.primary,
    marginBottom: spacing.lg,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  totalLabel: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
  },
  totalPlaceholder: {
    fontSize: fontSizes.md,
    color: colors.gray[400],
    fontStyle: 'italic',
  },
  actionButtons: {
    flexDirection: 'row',
    gap: spacing.md,
    marginTop: spacing.lg,
  },
  clearButton: {
    flex: 1,
  },
  checkoutButton: {
    flex: 2,
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
});
