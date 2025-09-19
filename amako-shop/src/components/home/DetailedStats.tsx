import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';

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
  {
    id: '4',
    value: '21+',
    label: 'Momo Varieties',
    icon: 'food',
    trend: 'Unique flavors',
    trendIcon: 'star',
  },
];

export default function DetailedStats({ stats = defaultStats }: DetailedStatsProps) {
  const renderStat = (stat: StatItem) => (
    <View key={stat.id} style={styles.statItem}>
      <View style={styles.statHeader}>
        <View style={styles.iconContainer}>
          <MCI name={stat.icon} size={20} color={colors.brand.primary} />
        </View>
        <Text style={styles.value}>{stat.value}</Text>
      </View>
      <Text style={styles.label}>{stat.label}</Text>
      {stat.trend && (
        <View style={styles.trendContainer}>
          {stat.trendIcon && (
            <MCI name={stat.trendIcon} size={14} color={colors.brand.accent} />
          )}
          <Text style={styles.trend}>{stat.trend}</Text>
        </View>
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
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  statItem: {
    width: '48%',
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    ...shadows.light,
  },
  statHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  iconContainer: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.momo.cream,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.sm,
  },
  value: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    flex: 1,
  },
  label: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.momo.mocha,
    marginBottom: spacing.xs,
  },
  trendContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  trend: {
    fontSize: fontSizes.xs,
    color: colors.brand.accent,
    marginLeft: spacing.xs,
    fontWeight: fontWeights.medium,
  },
});
