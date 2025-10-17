import React from 'react';
import { View, Text, Pressable, StyleSheet } from 'react-native';
import { useRouter, usePathname } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, radius } from '../../ui/tokens';
import { useCartSyncStore } from '../../state/cart-sync';

export default function StandaloneBottomBar() {
  const router = useRouter();
  const pathname = usePathname();
  const insets = useSafeAreaInsets();
  const cartCount = useCartSyncStore((state) => state.itemCount);
  // TODO: Implement help store for unread notifications
  const hasHelpUnread = false;

  const tabs = [
    { name: 'home', label: 'Home', icon: 'home-variant-outline', activeIcon: 'home-variant', route: '/(tabs)/home' },
    { name: 'menu', label: 'Menu', icon: 'silverware-fork-knife', activeIcon: 'silverware-fork-knife', route: '/(tabs)/menu' },
    { name: 'finds', label: "Ama's Finds", icon: 'star-outline', activeIcon: 'star', route: '/(tabs)/finds' },
    { name: 'bulk', label: 'Bulk', icon: 'package-variant-closed', activeIcon: 'package-variant', route: '/(tabs)/bulk' },
    { name: 'help', label: 'Help', icon: 'help-circle-outline', activeIcon: 'help-circle', route: '/(tabs)/help' },
    { name: 'profile', label: 'Profile', icon: 'account-circle-outline', activeIcon: 'account-circle', route: '/(tabs)/profile' },
  ];

  const isActive = (tabName: string, route: string) => {
    // Check if current path matches the tab route or is a sub-route
    return pathname === route || pathname.startsWith(route + '/');
  };

  const handleTabPress = (route: string) => {
    router.push(route as any);
  };

  return (
    <View style={{ paddingBottom: insets.bottom || 8, backgroundColor: 'transparent' }}>
      <View
        style={[
          styles.container,
          {
            backgroundColor: '#152039', // Dark blue background like KPI cards
            borderRadius: radius.lg,
            shadowColor: '#000',
          },
        ]}
      >
        {tabs.map((tab) => {
          const active = isActive(tab.name, tab.route);
          const iconName = active ? tab.activeIcon : tab.icon;

          return (
            <Pressable
              key={tab.name}
              accessibilityRole="button"
              onPress={() => handleTabPress(tab.route)}
              style={styles.tab}
            >
              <View style={styles.iconWrap}>
                <MCI
                  name={iconName as keyof typeof MCI.glyphMap}
                  size={22}
                  color={active ? colors.brand.accent : 'rgba(255,255,255,0.85)'}
                />
                {/* Notification dot on Help */}
                {tab.name === 'help' && hasHelpUnread && <View style={styles.dot} />}
              </View>
              <Text
                style={[
                  styles.label,
                  {
                    color: active ? colors.brand.accent : 'rgba(255,255,255,0.85)',
                  },
                ]}
                numberOfLines={1}
              >
                {tab.label}
              </Text>
            </Pressable>
          );
        })}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginHorizontal: 12,
    marginTop: 6,
    paddingHorizontal: 8,
    height: 66,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-around',
    elevation: 12,
    shadowOpacity: 0.15,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 6 },
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconWrap: {
    position: 'relative',
  },
  label: {
    fontSize: 11,
    marginTop: 4,
    fontWeight: '500',
  },
  badge: {
    position: 'absolute',
    top: -8,
    right: -12,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: colors.amako.brown1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '700',
  },
  dot: {
    position: 'absolute',
    top: -4,
    right: -6,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.amako.brown1,
  },
});
