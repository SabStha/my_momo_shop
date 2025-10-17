import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Image, StatusBar } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights } from '../../ui/tokens';
import { useCartSyncStore } from '../../state/cart-sync';
import { useUnreadCount } from '../../hooks/useNotifications';

interface TopBarProps {
  onCartPress?: () => void;
  onNotificationPress?: () => void;
  onOrdersPress?: () => void;
}

export default function TopBar({ onCartPress, onNotificationPress, onOrdersPress }: TopBarProps) {
  const insets = useSafeAreaInsets();
  const cartCount = useCartSyncStore((state) => state.itemCount);
  const { unreadCount, hasUnread } = useUnreadCount();

  return (
    <>
      <StatusBar barStyle="light-content" backgroundColor="#152039" />
      <View style={styles.container}>
        <View style={[styles.content, { paddingTop: insets.top }]}>
        {/* Left side - Logo and Brand */}
        <View style={styles.leftSection}>
          <View style={styles.logoContainer}>
            <Image 
              source={require('../../../assets/momokologo.png')} 
              style={styles.logoImage}
              resizeMode="contain"
            />
          </View>
        </View>

        {/* Right side - Icons */}
        <View style={styles.rightSection}>
          {/* Notification Bell */}
          <TouchableOpacity 
            style={styles.iconButton} 
            onPress={onNotificationPress}
            accessibilityLabel="Notifications"
          >
            <MCI 
              name="bell-outline" 
              size={20} 
              color={colors.white} 
            />
            {hasUnread && (
              <View style={styles.notificationBadge}>
                <Text style={styles.badgeText}>{unreadCount}</Text>
              </View>
            )}
          </TouchableOpacity>

          {/* Orders Icon */}
          <TouchableOpacity 
            style={styles.iconButton} 
            onPress={onOrdersPress}
            accessibilityLabel="My Orders"
          >
            <MCI 
              name="receipt-text-outline" 
              size={20} 
              color={colors.white} 
            />
          </TouchableOpacity>

          {/* Cart Icon */}
          <TouchableOpacity 
            style={styles.iconButton} 
            onPress={onCartPress}
            accessibilityLabel="Shopping Cart"
          >
            <MCI 
              name="cart-outline" 
              size={20} 
              color={colors.white} 
            />
            {cartCount > 0 && (
              <View style={styles.cartBadge}>
                <Text style={styles.badgeText}>{cartCount}</Text>
              </View>
            )}
          </TouchableOpacity>
        </View>
      </View>
    </View>
    </>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#152039', // Dark blue background
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
    paddingVertical: 4, // Reduced from spacing.sm (8) to 4 for more compact look
    minHeight: 56, // Reduced from 60 to 56
  },
  leftSection: {
    flex: 1,
  },
  logoContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  logoImage: {
    width: 200,
    height: 52,
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
  },
  notificationBadge: {
    position: 'absolute',
    top: -2,
    right: -2,
    minWidth: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: colors.amako.gold,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  badgeText: {
    color: colors.black,
    fontSize: 8,
    fontWeight: fontWeights.bold,
  },
  cartBadge: {
    position: 'absolute',
    top: -2,
    right: -2,
    minWidth: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: colors.amako.gold,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
});
