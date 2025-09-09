import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Card } from '../ui';
import { spacing, radius, fontSizes, fontWeights, colors } from '../ui';

export function OffersBanner() {
  return (
    <View style={styles.container}>
      <Card 
        style={styles.banner} 
        padding="md" 
        radius="lg" 
        shadow="light"
        backgroundColor={colors.primary[50]}
        borderColor={colors.primary[200]}
        borderWidth={1}
      >
        <View style={styles.content}>
          <View style={styles.iconContainer}>
            <Ionicons 
              name="gift" 
              size={24} 
              color={colors.primary[600]} 
            />
          </View>
          
          <View style={styles.textContainer}>
            <Text style={styles.title}>Special Offers</Text>
            <Text style={styles.subtitle}>New deals coming soon!</Text>
          </View>
          
          <Ionicons 
            name="chevron-forward" 
            size={20} 
            color={colors.primary[400]} 
          />
        </View>
      </Card>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: spacing.lg,
  },
  banner: {
    backgroundColor: colors.primary[50],
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.primary[100],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  textContainer: {
    flex: 1,
  },
  title: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.primary[700],
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
  },
});
