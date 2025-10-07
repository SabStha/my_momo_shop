import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';

interface KpiCardProps {
  icon: keyof typeof MCI.glyphMap;
  value: string;
  label: string;
  color?: string;
  backgroundColor?: string;
}

export default function KpiCard({ 
  icon, 
  value, 
  label, 
  color = colors.brand.primary,
  backgroundColor = colors.white 
}: KpiCardProps) {
  return (
    <View style={[styles.container, { backgroundColor }]}>
      <View style={styles.iconContainer}>
        <MCI name={icon} size={24} color={color} />
      </View>
      <Text style={[styles.value, { color }]}>{value}</Text>
      <Text style={styles.label}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: spacing.sm,
    borderRadius: radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: spacing.xs,
    ...shadows.light,
  },
  iconContainer: {
    marginBottom: spacing.xs,
  },
  value: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    marginBottom: spacing.xs,
  },
  label: {
    fontSize: fontSizes.xs,
    color: colors.momo.mocha,
    textAlign: 'center',
    fontWeight: fontWeights.medium,
  },
});
