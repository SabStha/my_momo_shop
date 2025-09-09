import React, { useEffect, useRef } from 'react';
import { View, StyleSheet, Animated, Dimensions } from 'react-native';
import { Card } from '../ui';
import { spacing, radius, colors } from '../ui';

const { width: screenWidth } = Dimensions.get('window');
const cardWidth = (screenWidth - spacing.lg * 3) / 2; // 2 columns with spacing

interface SkeletonCardProps {
  style?: any;
  height?: number;
}

export function SkeletonCard({ style, height = 200 }: SkeletonCardProps) {
  const animatedValue = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(animatedValue, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: false,
        }),
        Animated.timing(animatedValue, {
          toValue: 0,
          duration: 1000,
          useNativeDriver: false,
        }),
      ])
    );

    animation.start();

    return () => animation.stop();
  }, [animatedValue]);

  const opacity = animatedValue.interpolate({
    inputRange: [0, 1],
    outputRange: [0.3, 0.7],
  });

  return (
    <View style={[styles.container, style]}>
      <Card style={[styles.card, { height }] as any} padding="md" radius="md">
        {/* Image Skeleton */}
        <Animated.View style={[styles.imageSkeleton, { opacity }]} />
        
        {/* Content Skeletons */}
        <View style={styles.content}>
          {/* Title Skeleton */}
          <Animated.View 
            style={[styles.titleSkeleton, { opacity }]}
          />
          
          {/* Description Skeleton */}
          <Animated.View 
            style={[styles.descriptionSkeleton, { opacity }]}
          />
          
          {/* Price Skeleton */}
          <Animated.View 
            style={[styles.priceSkeleton, { opacity }]}
          />
        </View>
      </Card>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    width: cardWidth,
    marginBottom: spacing.md,
  },
  card: {
    // Height is applied dynamically
  },
  imageSkeleton: {
    height: 120,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    marginBottom: spacing.sm,
  },
  content: {
    flex: 1,
    justifyContent: 'space-between',
  },
  titleSkeleton: {
    height: 16,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    marginBottom: spacing.xs,
  },
  descriptionSkeleton: {
    height: 12,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    marginBottom: spacing.sm,
    width: '80%',
  },
  priceSkeleton: {
    height: 14,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
    width: '60%',
  },
});
