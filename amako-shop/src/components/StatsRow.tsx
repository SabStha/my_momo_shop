import React from 'react';
import {
  View,
  Text,
  StyleSheet,
} from 'react-native';
import { spacing, fontSizes, fontWeights, colors, radius } from '../ui/tokens';

interface StatItem {
  icon: string;
  value: string;
  label: string;
}

interface StatsRowProps {
  stats?: StatItem[];
}

const defaultStats: StatItem[] = [
  {
    icon: '😊',
    value: '1000+',
    label: 'Happy Customers',
  },
  {
    icon: '🥟',
    value: '15+',
    label: 'Varieties',
  },
  {
    icon: '⭐',
    value: '4.8',
    label: 'Rating',
  },
];

export const StatsRow: React.FC<StatsRowProps> = ({ stats = defaultStats }) => {
  return (
    <View style={styles.container}>
      {stats.map((stat, index) => (
        <View key={index} style={styles.statItem}>
          <View style={styles.statIconContainer}>
            <Text style={styles.statIcon}>{stat.icon}</Text>
          </View>
          <Text style={styles.statValue}>{stat.value}</Text>
          <Text style={styles.statLabel}>{stat.label}</Text>
        </View>
      ))}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    backgroundColor: colors.momo.cream,
    borderRadius: radius.lg,
    paddingVertical: spacing.lg,
    paddingHorizontal: spacing.md,
    marginBottom: spacing.lg,
    borderWidth: 2,
    borderColor: colors.brand.primary,
    shadowColor: colors.brand.primary,
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  statItem: {
    alignItems: 'center',
    flex: 1,
  },
  statIconContainer: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: colors.brand.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.sm,
    shadowColor: colors.brand.primary,
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  statIcon: {
    fontSize: 24,
  },
  statValue: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
    textAlign: 'center',
  },
  statLabel: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    textAlign: 'center',
    fontWeight: fontWeights.medium,
  },
});
