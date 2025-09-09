import React from 'react';
import { View, Text, Image, TouchableOpacity, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { CartLine } from '../state/cart';
import { Card, QuantityStepper, Price } from '../ui';
import { spacing, radius, fontSizes, fontWeights, colors } from '../ui';
import { addMoney, multiplyMoney } from '../utils/price';

interface CartItemProps {
  item: CartLine;
  onUpdateQuantity: (quantity: number) => void;
  onRemove: () => void;
}

export function CartItem({ item, onUpdateQuantity, onRemove }: CartItemProps) {
  // Calculate unit price: base + add-ons
  let unitPrice = item.unitBasePrice;
  if (item.addOns && item.addOns.length > 0) {
    const addOnsTotal = item.addOns.reduce((sum, addon) => 
      addMoney(sum, addon.price), { currency: 'NPR' as const, amount: 0 }
    );
    unitPrice = addMoney(unitPrice, addOnsTotal);
  }

  // Calculate total price for this item
  const totalPrice = multiplyMoney(unitPrice, item.qty);

  return (
    <Card style={styles.container} padding="md" radius="md" shadow="light">
      {/* Image */}
      <View style={styles.imageContainer}>
        {item.imageUrl ? (
          <Image
            source={{ uri: item.imageUrl }}
            style={styles.image}
            resizeMode="cover"
            accessibilityLabel={`Image of ${item.name}`}
          />
        ) : (
          <View style={styles.placeholderImage}>
            <Text style={styles.placeholderText}>🍽️</Text>
          </View>
        )}
      </View>

      {/* Content */}
      <View style={styles.content}>
        {/* Item Name */}
        <Text style={styles.name} numberOfLines={2}>
          {item.name}
        </Text>

        {/* Variant */}
        {item.variantName && (
          <Text style={styles.variant}>
            {item.variantName}
          </Text>
        )}

        {/* Add-ons */}
        {item.addOns && item.addOns.length > 0 && (
          <View style={styles.addOnsContainer}>
            {item.addOns.map((addon, index) => (
              <Text key={addon.id} style={styles.addOn}>
                + {addon.name}
              </Text>
            ))}
          </View>
        )}

        {/* Price and Quantity Row */}
        <View style={styles.bottomRow}>
          <View style={styles.priceContainer}>
            <Text style={styles.unitPrice}>
              Rs. {unitPrice.amount} each
            </Text>
            <Price 
              value={totalPrice} 
              size="md" 
              weight="semibold" 
              color="primary"
            />
          </View>

          <View style={styles.quantityContainer}>
            <QuantityStepper
              value={item.qty}
              onValueChange={onUpdateQuantity}
              min={1}
              max={99}
              size="sm"
            />
          </View>
        </View>
      </View>

      {/* Remove Button */}
      <TouchableOpacity
        style={styles.removeButton}
        onPress={onRemove}
        accessibilityRole="button"
        accessibilityLabel="Remove item from cart"
        accessibilityHint="Double tap to remove this item"
      >
        <Ionicons name="trash-outline" size={20} color={colors.error[500]} />
      </TouchableOpacity>
    </Card>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    marginBottom: spacing.md,
    position: 'relative',
  },
  imageContainer: {
    width: 80,
    height: 80,
    borderRadius: radius.sm,
    overflow: 'hidden',
    marginRight: spacing.md,
  },
  image: {
    width: '100%',
    height: '100%',
  },
  placeholderImage: {
    width: '100%',
    height: '100%',
    backgroundColor: colors.gray[100],
    justifyContent: 'center',
    alignItems: 'center',
  },
  placeholderText: {
    fontSize: 24,
  },
  content: {
    flex: 1,
    justifyContent: 'space-between',
  },
  name: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
    lineHeight: fontSizes.md * 1.2,
  },
  variant: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    fontWeight: fontWeights.medium,
    marginBottom: spacing.xs,
  },
  addOnsContainer: {
    marginBottom: spacing.sm,
  },
  addOn: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    marginBottom: 2,
  },
  bottomRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
  },
  priceContainer: {
    flex: 1,
  },
  unitPrice: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
    marginBottom: spacing.xs,
  },
  quantityContainer: {
    marginLeft: spacing.md,
  },
  removeButton: {
    position: 'absolute',
    top: spacing.xs,
    right: spacing.xs,
    padding: spacing.xs,
    borderRadius: radius.sm,
    backgroundColor: colors.error[50],
    borderWidth: 1,
    borderColor: colors.error[200],
  },
});
