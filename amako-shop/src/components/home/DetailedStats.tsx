import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import { typography, fonts } from '../../theme';

interface StatItem {
  id: string;
  value: string;
  label: string;
  icon: keyof typeof MCI.glyphMap;
  trend?: string;
  trendIcon?: keyof typeof MCI.glyphMap;
}

interface DetailedStatsProps {
  stats?: StatItem[];
}

const defaultStats: StatItem[] = [
  {
    id: '1',
    value: '179+',
    label: 'Orders Delivered',
    icon: 'truck-delivery',
    trend: '+-100% this month',
    trendIcon: 'trending-up',
  },
  {
    id: '2',
    value: '21+',
    label: 'Happy Customers',
    icon: 'account-heart',
    trend: '100% satisfaction',
    trendIcon: 'emoticon-happy',
  },
  {
    id: '3',
    value: '1+',
    label: 'Years in Business',
    icon: 'trophy',
    trend: 'Trusted brand',
    trendIcon: 'shield-check',
  },
];

export default function DetailedStats({ stats = defaultStats }: DetailedStatsProps) {
  const renderStat = (stat: StatItem) => (
    <View key={stat.id} style={styles.statItem}>
      <View style={styles.iconContainer}>
        <MCI name={stat.icon} size={10} color={colors.brand.primary} />
      </View>
      <Text style={styles.value}>{stat.value}</Text>
      <Text style={styles.label}>{stat.label}</Text>
      {stat.trend && (
        <Text style={styles.trend}>{stat.trend}</Text>
      )}
    </View>
  );

  return (
    <View style={styles.container}>
      <View style={styles.grid}>
        {stats.map(renderStat)}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: 0, // Remove padding - parent container handles it
    paddingVertical: spacing.lg, // Keep vertical padding for spacing
  },
  grid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: spacing.sm,
    flexWrap: 'wrap', // Allow wrapping on smaller screens
  },
  statItem: {
    flex: 1, // Use flex instead of fixed width
    minWidth: 0, // Allow shrinking below content size
    maxWidth: '33.333%', // Maximum 3 items per row
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.sm,
    alignItems: 'center',
    minHeight: 90, // Even taller
    ...shadows.light,
  },
  iconContainer: {
    width: 18, // Reduced from 24
    height: 18, // Reduced from 24
    borderRadius: 9,
    backgroundColor: '#FFF7F0', // bg-[#FFF7F0] from web
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 4, // Reduced from 1
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
    elevation: 1,
  },
  value: {
    fontFamily: fonts.title,
    fontSize: 8, // Reduced from 14 (sm) - half size
    fontWeight: '700' as const,
    color: colors.brand.primary,
    marginBottom: 1,
  },
  label: {
    fontFamily: fonts.section,
    fontSize: 7, // Reduced from 12 (xs) - smaller
    fontWeight: '700' as const,
    color: colors.brand.primary,
    textAlign: 'center',
    marginBottom: 1,
  },
  trend: {
    fontFamily: fonts.body,
    fontSize: 6, // Reduced from 12 (xs) - half size
    color: colors.brand.accent,
    textAlign: 'center',
    lineHeight: 8,
  },
});
