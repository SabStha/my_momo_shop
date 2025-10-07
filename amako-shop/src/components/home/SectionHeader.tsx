import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';
import { typography, fonts } from '../../theme';

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
          <MCI name={icon} size={18} color={colors.white} />
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
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
  },
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#152039', // Dark blue background
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
    alignSelf: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  pillText: {
    fontFamily: fonts.section,
    fontSize: fontSizes.md, // 16px (1.5x bigger than 12px)
    fontWeight: '700' as const,
    color: colors.white,
    marginLeft: spacing.xs,
    textTransform: 'uppercase',
  },
  header: {
    alignItems: 'center',
  },
  title: {
    fontFamily: fonts.title,
    fontSize: fontSizes.lg,
    fontWeight: '700' as const,
    color: colors.brand.primary,
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontFamily: fonts.body,
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    textAlign: 'center',
  },
});
