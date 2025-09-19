import React from 'react';
import { View, StyleSheet, Dimensions } from 'react-native';
import { colors, spacing } from '../../ui/tokens';
import KpiCard from './KpiCard';

const { width: screenWidth } = Dimensions.get('window');

interface KpiData {
  happyCustomers: string;
  momoVarieties: string;
  rating: string;
}

interface KpiRowProps {
  data?: KpiData;
}

const defaultData: KpiData = {
  happyCustomers: '21+',
  momoVarieties: '21+',
  rating: '4.8★',
};

export default function KpiRow({ data = defaultData }: KpiRowProps) {
  return (
    <View style={styles.container}>
      <View style={styles.row}>
        <KpiCard
          icon="account-heart"
          value={data.happyCustomers}
          label="Happy Customers"
          color={colors.momo.green}
          backgroundColor={colors.momo.cream}
        />
        <KpiCard
          icon="silverware-fork-knife"
          value={data.momoVarieties}
          label="Momo Varieties"
          color={colors.brand.primary}
          backgroundColor={colors.momo.sand}
        />
        <KpiCard
          icon="star"
          value={data.rating}
          label="Rating"
          color={colors.brand.accent}
          backgroundColor={colors.momo.pink}
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
});
