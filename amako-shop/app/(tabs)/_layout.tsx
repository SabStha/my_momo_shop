import React from 'react';
import { Tabs } from 'expo-router';

export default function TabsLayout() {
  return (
    <Tabs screenOptions={{ headerShown: false }}>
      <Tabs.Screen name="index"   options={{ title: 'Menu' }} />
      <Tabs.Screen name="cart"    options={{ title: 'Cart' }} />
      <Tabs.Screen name="orders"  options={{ title: 'Orders' }} />
      <Tabs.Screen name="profile" options={{ title: 'Profile' }} />
    </Tabs>
  );
}
