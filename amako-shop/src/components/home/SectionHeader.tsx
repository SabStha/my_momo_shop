import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';

interface SectionHeaderProps {
  title: string;
  icon?: keyof typeof MCI.glyphMap;
  subtitle?: string;
  showPill?: boolean;
}

export default function SectionHeader({ 
  title, 
  icon = 'star', 
  subtitle,
  showPill = true 
}: SectionHeaderProps) {
  return (
    <View style={styles.container}>
      {showPill && (
        <View style={styles.pill}>
          <MCI name={icon} size={20} color={colors.brand.accent} />
          <Text style={styles.pillText}>{title}</Text>
        </View>
      )}
      
      {!showPill && (
        <View style={styles.header}>
          <Text style={styles.title}>{title}</Text>
          {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.momo.cream,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.full,
    alignSelf: 'center',
    borderWidth: 1,
    borderColor: colors.brand.primary,
  },
  pillText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginLeft: spacing.xs,
    textTransform: 'uppercase',
  },
  header: {
    alignItems: 'center',
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha,
    textAlign: 'center',
  },
});
