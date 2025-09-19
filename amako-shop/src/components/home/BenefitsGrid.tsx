import React from 'react';
import { View, Text, StyleSheet, Dimensions } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';

const { width: screenWidth } = Dimensions.get('window');
const itemWidth = (screenWidth - spacing.lg * 3) / 2;

interface Benefit {
  id: string;
  icon: keyof typeof MCI.glyphMap;
  title: string;
  description: string;
}

interface BenefitsGridProps {
  benefits?: Benefit[];
}

const defaultBenefits: Benefit[] = [
  {
    id: '1',
    icon: 'leaf',
    title: 'Fresh Ingredients',
    description: 'Fresh, high-quality ingredients sourced daily from local markets.',
  },
  {
    id: '2',
    icon: 'chef-hat',
    title: 'Authentic Recipes',
    description: 'Traditional Nepalese recipes passed down through generations.',
  },
  {
    id: '3',
    icon: 'truck-delivery',
    title: 'Fast Delivery',
    description: 'Average delivery time: 25 minutes. Hot and fresh!',
  },
  {
    id: '4',
    icon: 'shield-check',
    title: 'Quality Assured',
    description: 'Every order carefully prepared and quality-checked.',
  },
  {
    id: '5',
    icon: 'headset',
    title: '24/7 Support',
    description: 'Our support team always ready to help you.',
  },
  {
    id: '6',
    icon: 'currency-usd',
    title: 'Great Value',
    description: 'Delicious momos at affordable prices with combo deals.',
  },
];

export default function BenefitsGrid({ benefits = defaultBenefits }: BenefitsGridProps) {
  const renderBenefit = (benefit: Benefit, index: number) => (
    <View key={benefit.id} style={styles.benefitItem}>
      <View style={styles.iconContainer}>
        <MCI name={benefit.icon} size={24} color={colors.brand.primary} />
      </View>
      <Text style={styles.title}>{benefit.title}</Text>
      <Text style={styles.description}>{benefit.description}</Text>
    </View>
  );

  return (
    <View style={styles.container}>
      <View style={styles.grid}>
        {benefits.map((benefit, index) => renderBenefit(benefit, index))}
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
  benefitItem: {
    width: itemWidth,
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    alignItems: 'center',
    ...shadows.light,
  },
  iconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.momo.cream,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  title: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  description: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    textAlign: 'center',
    lineHeight: 18,
  },
});
