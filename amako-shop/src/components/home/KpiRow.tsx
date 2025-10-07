import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import { typography, fonts } from '../../theme';

interface KpiData {
  orders_delivered?: string;
  happy_customers?: string;
  years_in_business?: string;
  momo_varieties?: string;
  growth_percentage?: string;
  satisfaction_rate?: string;
  customer_rating?: string;
}

interface KpiRowProps {
  data?: KpiData;
}

export default function KpiRow({ data }: KpiRowProps) {
  const kpis = [
    {
      id: 'happy_customers',
      value: data?.happy_customers || '21+',
      label: 'Happy Customers',
      subtitle: '😊 100% satisfaction',
      color: '#10B981', // Green
    },
    {
      id: 'momo_varieties',
      value: data?.momo_varieties || '21+',
      label: 'Momo Varieties',
      subtitle: '🥟 Unique flavors',
      color: '#8B5CF6', // Purple
    },
    {
      id: 'customer_rating',
      value: data?.customer_rating || '4.5⭐',
      label: 'Customer Rating',
      subtitle: '🏆 Trusted brand',
      color: '#A0522D', // Brown
    },
  ];

  return (
    <View style={styles.container}>
      {kpis.map((kpi) => (
        <View
          key={kpi.id}
          style={styles.kpiCard}
        >
          <Text style={styles.value}>{kpi.value}</Text>
          <Text style={styles.label}>{kpi.label}</Text>
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg, // Add left and right padding
    paddingVertical: spacing.lg, // Keep vertical padding for spacing
    backgroundColor: 'transparent', // Remove background - parent handles it
    gap: spacing.sm, // Add consistent gap
    flexWrap: 'wrap', // Allow wrapping on smaller screens
  },
  kpiCard: {
    flex: 1, // Use flex instead of fixed width
    minWidth: 0, // Allow shrinking below content size
    maxWidth: '33.333%', // Maximum 3 items per row
    padding: spacing.xs, // Even smaller padding
    borderRadius: radius.sm,
    alignItems: 'center',
    backgroundColor: '#24355C', // Change to new blue color
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    minHeight: 50, // Much smaller height
  },
  value: {
    fontFamily: fonts.title,
    fontSize: fontSizes.lg, // Larger for better hierarchy
    fontWeight: '700' as const,
    color: colors.white, // Change to white for blue background
    marginBottom: spacing.xs,
  },
  label: {
    fontFamily: fonts.section, // Match BenefitsGrid font
    fontSize: fontSizes.xs,
    fontWeight: '700' as const, // Match BenefitsGrid weight
    color: colors.white, // Change to white for blue background
    textAlign: 'center',
    marginBottom: 1,
  },
  subtitle: {
    fontFamily: fonts.body, // Match BenefitsGrid font
    fontSize: fontSizes.xs,
    textAlign: 'center',
    lineHeight: 12,
    fontWeight: '500' as const,
    color: colors.white, // Change to white for blue background
  },
});