import React from 'react';
import { View } from 'react-native';
import { Tabs } from 'expo-router';
import { useRouter } from 'expo-router';
import BottomBar from '../../src/components/navigation/BottomBar';
import TopBar from '../../src/components/navigation/TopBar';

export default function TabsLayout() {
  const router = useRouter();

  const handleCartPress = () => {
    // Navigate to cart screen or show cart modal
    router.push('/cart');
  };

  const handleNotificationPress = () => {
    // Navigate to notifications screen or show notifications modal
    router.push('/notifications');
  };

  return (
    <View style={{ flex: 1 }}>
      {/* Top Bar with Cart and Notifications */}
      <TopBar 
        onCartPress={handleCartPress}
        onNotificationPress={handleNotificationPress}
      />
      
      {/* Bottom Tabs */}
      <Tabs
        screenOptions={{ 
          headerShown: false
        }}
        initialRouteName="home"
        tabBar={(props: any) => <BottomBar {...props} />}
      >
        <Tabs.Screen name="home"    options={{ title: 'Home' }} />
        <Tabs.Screen name="menu"    options={{ title: 'Menu' }} />
        <Tabs.Screen name="finds"   options={{ title: "Ama's Finds" }} />
        <Tabs.Screen name="bulk"    options={{ title: 'Bulk' }} />
        <Tabs.Screen name="help"    options={{ title: 'Help' }} />
        <Tabs.Screen name="profile" options={{ title: 'Profile' }} />
      </Tabs>
    </View>
  );
}
