import React from 'react';
import { View, Text, StyleSheet, ActivityIndicator } from 'react-native';
import { useImagePreloader } from '../hooks/useImagePreloader';

interface ImagePreloadIndicatorProps {
  show?: boolean;
}

export default function ImagePreloadIndicator({ show = true }: ImagePreloadIndicatorProps) {
  const { isPreloading, totalPreloaded, preloadProgress } = useImagePreloader();

  if (!show || !isPreloading) {
    return null;
  }

  return (
    <View style={styles.container}>
      <View style={styles.content}>
        <ActivityIndicator size="small" color="#FF6B35" />
        <Text style={styles.text}>
          Optimizing images... ({totalPreloaded} cached)
        </Text>
      </View>
      <View style={styles.progressBar}>
        <View 
          style={[
            styles.progressFill, 
            { width: `${preloadProgress}%` }
          ]} 
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    paddingVertical: 8,
    paddingHorizontal: 16,
    zIndex: 1000,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
  text: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '500',
  },
  progressBar: {
    height: 2,
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
    borderRadius: 1,
    marginTop: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#FF6B35',
    borderRadius: 1,
  },
});
