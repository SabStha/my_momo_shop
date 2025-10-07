import React from 'react';
import { View, Text, Pressable, StyleSheet } from 'react-native';
import { BottomTabBarProps } from '@react-navigation/bottom-tabs';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, radius } from '../../ui/tokens';
import { useCartStore } from '../../state/cart';

export default function BottomBar({ state, descriptors, navigation }: BottomTabBarProps) {
  const insets = useSafeAreaInsets();
  const cartCount = useCartStore((state) => state.itemCount);
  // TODO: Implement help store for unread notifications
  const hasHelpUnread = false;

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
        {state.routes.filter(route => 
          ['home', 'menu', 'finds', 'bulk', 'help', 'profile'].includes(route.name)
        ).map((route, index) => {
          const { options } = descriptors[route.key];
          const originalIndex = state.routes.findIndex(r => r.name === route.name);
          const isFocused = state.index === originalIndex;

          // Map route.name -> icon + label
          let label = options.title ?? route.name;
          let iconName: keyof typeof MCI.glyphMap = 'home-variant-outline';

          switch (route.name) {
            case 'home':
              label = 'Home';
              iconName = isFocused ? 'home-variant' : 'home-variant-outline';
              break;
            case 'index': // Current menu screen
            case 'menu':
              label = 'Menu';
              iconName = 'silverware-fork-knife'; // Same for active/inactive, just color swap
              break;
            case 'finds':
              label = "Ama's Finds";
              iconName = isFocused ? 'star' : 'star-outline';
              break;
            case 'bulk':
              label = 'Bulk';
              iconName = isFocused ? 'package-variant' : 'package-variant-closed';
              break;
            case 'help':
              label = 'Help';
              iconName = isFocused ? 'help-circle' : 'help-circle-outline';
              break;
            case 'profile':
              label = 'Profile';
              iconName = isFocused ? 'account-circle' : 'account-circle-outline';
              break;
          }

          const onPress = () => {
            const event = navigation.emit({
              type: 'tabPress',
              target: route.key,
              canPreventDefault: true,
            });
            if (!isFocused && !event.defaultPrevented) {
              navigation.navigate(route.name as never);
            }
          };

          return (
            <Pressable
              key={route.key}
              accessibilityRole="button"
              onPress={onPress}
              style={styles.tab}
            >
              <View style={styles.iconWrap}>
                <MCI
                  name={iconName}
                  size={22}
                  color={isFocused ? colors.brand.accent : 'rgba(255,255,255,0.85)'}
                />
                {/* Notification dot on Help */}
                {route.name === 'help' && hasHelpUnread && <View style={styles.dot} />}
              </View>
              <Text
                style={[
                  styles.label,
                  {
                    color: isFocused ? colors.brand.accent : 'rgba(255,255,255,0.85)',
                  },
                ]}
                numberOfLines={1}
              >
                {label}
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
