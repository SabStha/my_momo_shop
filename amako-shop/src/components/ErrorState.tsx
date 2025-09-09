import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Button } from '../ui';
import { spacing, fontSizes, fontWeights, colors } from '../ui';

interface ErrorStateProps {
  message?: string;
  onRetry?: () => void;
  style?: any;
}

export function ErrorState({ 
  message = "Something went wrong", 
  onRetry, 
  style 
}: ErrorStateProps) {
  return (
    <View style={[styles.container, style]}>
      <View style={styles.iconContainer}>
        <Ionicons 
          name="alert-circle" 
          size={48} 
          color={colors.error[500]} 
        />
      </View>
      
      <Text style={styles.title}>Oops!</Text>
      <Text style={styles.message}>{message}</Text>
      
      {onRetry && (
        <Button
          title="Try Again"
          onPress={onRetry}
          variant="solid"
          size="md"
          style={styles.retryButton}
          leftIcon={<Ionicons name="refresh" size={18} color={colors.white} />}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
  },
  iconContainer: {
    marginBottom: spacing.lg,
  },
  title: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.text.primary,
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  message: {
    fontSize: fontSizes.md,
    color: colors.text.secondary,
    textAlign: 'center',
    marginBottom: spacing.xl,
    lineHeight: fontSizes.md * 1.4,
  },
  retryButton: {
    minWidth: 120,
  },
});
