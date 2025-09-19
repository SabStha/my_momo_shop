import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights } from '../../ui/tokens';
import { useCartStore } from '../../state/cart';
import { useNotificationStore } from '../../state/notifications';

interface TopBarProps {
  onCartPress?: () => void;
  onNotificationPress?: () => void;
}

export default function TopBar({ onCartPress, onNotificationPress }: TopBarProps) {
  const insets = useSafeAreaInsets();
  const cartCount = useCartStore((state) => state.items.length);
  const hasUnreadNotifications = useNotificationStore((state) => state.hasUnread);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <View style={styles.content}>
        {/* Left side - Logo/Title */}
        <View style={styles.leftSection}>
          <Text style={styles.logo}>AmaKo</Text>
        </View>

        {/* Right side - Cart and Notifications */}
        <View style={styles.rightSection}>
          {/* Cart Icon */}
          <TouchableOpacity 
            style={styles.iconButton} 
            onPress={onCartPress}
            accessibilityLabel="Shopping Cart"
          >
            <MCI 
              name="cart-variant" 
              size={24} 
              color={colors.text.primary} 
            />
            {cartCount > 0 && (
              <View style={styles.badge}>
                <Text style={styles.badgeText}>
                  {cartCount > 99 ? '99+' : cartCount}
                </Text>
              </View>
            )}
          </TouchableOpacity>

          {/* Notification Bell */}
          <TouchableOpacity 
            style={styles.iconButton} 
            onPress={onNotificationPress}
            accessibilityLabel="Notifications"
          >
            <MCI 
              name={hasUnreadNotifications ? 'bell' : 'bell-outline'} 
              size={24} 
              color={colors.text.primary} 
            />
            {hasUnreadNotifications && (
              <View style={styles.notificationDot} />
            )}
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: colors.momo.sand,
    borderBottomWidth: 1,
    borderBottomColor: colors.brand.primary,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    minHeight: 56,
  },
  leftSection: {
    flex: 1,
  },
  logo: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
  },
  rightSection: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  iconButton: {
    position: 'relative',
    padding: spacing.sm,
    borderRadius: 8,
    backgroundColor: colors.momo.cream,
    borderWidth: 1,
    borderColor: colors.brand.primary,
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: colors.brand.primary,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
    borderWidth: 2,
    borderColor: colors.momo.sand,
  },
  badgeText: {
    color: colors.white,
    fontSize: 10,
    fontWeight: fontWeights.bold,
  },
  notificationDot: {
    position: 'absolute',
    top: 2,
    right: 2,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.amako.brown1,
    borderWidth: 1,
    borderColor: colors.momo.sand,
  },
});
