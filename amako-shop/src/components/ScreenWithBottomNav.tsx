import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import StandaloneBottomBar from './navigation/StandaloneBottomBar';

interface ScreenWithBottomNavProps {
  children: React.ReactNode;
  showBottomNav?: boolean;
}

export default function ScreenWithBottomNav({ 
  children, 
  showBottomNav = true 
}: ScreenWithBottomNavProps) {
  const insets = useSafeAreaInsets();

  return (
    <View style={styles.container}>
      {/* Main content */}
      <View style={[styles.content, { paddingBottom: showBottomNav ? 85 : insets.bottom }]}>
        {children}
      </View>
      
      {/* Fixed bottom navigation */}
      {showBottomNav && (
        <View style={styles.bottomNavContainer}>
          <StandaloneBottomBar />
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  content: {
    flex: 1,
  },
  bottomNavContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'transparent',
    zIndex: 1000,
  },
});
