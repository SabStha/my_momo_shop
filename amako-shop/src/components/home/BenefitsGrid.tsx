import React from 'react';
import { View, Text, StyleSheet, Pressable } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import { typography, fonts } from '../../theme';

const CELL_GAP = 12;

interface Benefit {
  id: string;
  emoji: string;
  title: string;
  description: string;
}

interface StatItem {
  id: string;
  value: string;
  label: string;
  icon: string;
  trend: string;
  trendIcon: string;
}

interface BenefitsGridProps {
  benefits?: Benefit[];
  stats?: StatItem[];
  title?: string;
  subtitle?: string;
  onCtaPress?: () => void;
  ctaText?: string;
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

const defaultBenefits: Benefit[] = [
  {
    id: '1',
    emoji: '🥬',
    title: 'Fresh Ingredients',
    description: 'High-quality ingredients sourced daily.',
  },
  {
    id: '2',
    emoji: '👩‍🍳',
    title: 'Authentic Recipes',
    description: 'Traditional Nepalese recipes.',
  },
  {
    id: '3',
    emoji: '🚚',
    title: 'Fast Delivery',
    description: '25 minutes average delivery.',
  },
];

export default function BenefitsGrid({ 
  benefits, 
  stats,
  title,
  subtitle,
  onCtaPress,
  ctaText
}: BenefitsGridProps) {
  // Use provided data or fallback to defaults
  const displayBenefits = benefits || defaultBenefits;
  const displayStats = stats || defaultStats;
  const displayTitle = title || "✨ Why Choose Ama Ko Shop?";
  const displaySubtitle = subtitle || "From our kitchen to your heart — here's why thousands trust us with their favorite comfort food.";
  const displayCtaText = ctaText || "Try Our Momos Today";
  const renderStat = (stat: StatItem) => (
    <View key={stat.id} style={styles.cell}>
      <View style={styles.statCard}>
        <View style={styles.statIconContainer}>
          <MCI name={stat.icon as any} size={20} color={colors.brand.primary} />
        </View>
        <Text style={styles.statValue}>{stat.value}</Text>
        <Text style={styles.statLabel}>{stat.label}</Text>
        <Text style={styles.statTrend}>{stat.trend}</Text>
      </View>
    </View>
  );

  const renderBenefit = (benefit: Benefit) => (
    <View key={benefit.id} style={styles.cell}>
      <View style={styles.card}>
        <View style={styles.iconContainer}>
          <Text style={styles.emoji}>{benefit.emoji}</Text>
        </View>
        <Text style={styles.title}>{benefit.title}</Text>
        <Text style={styles.description}>{benefit.description}</Text>
      </View>
    </View>
  );

  return (
    <View style={styles.container}>
      {/* Title and Subtitle */}
      <View style={styles.header}>
        <Text style={styles.sectionTitle}>{displayTitle}</Text>
        <Text style={styles.subtitle}>{displaySubtitle}</Text>
      </View>

      {/* KPI Stats Section - 3 boxes per row */}
      <View style={styles.grid}>
        {displayStats.map((stat) => renderStat(stat))}
      </View>
      
      {/* Benefits Section - 3 boxes per row */}
      <View style={styles.grid}>
        {displayBenefits.map((benefit) => renderBenefit(benefit))}
      </View>

      {/* Call to Action Button */}
      {onCtaPress && (
        <View style={styles.ctaContainer}>
          <Pressable style={styles.ctaButton} onPress={onCtaPress}>
            <Text style={styles.ctaText}>{displayCtaText}</Text>
          </Pressable>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#E8EDF5',      // your gray
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: 24,
    overflow: 'hidden',              // clip to rounded corners
    marginHorizontal: spacing.lg,    // Add margin to match other sections
    marginTop: spacing.xl,           // Add top margin
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: -CELL_GAP / 2, // balance inner margins
  },
  cell: {
    width: '33.333%',                // exactly three columns
    paddingHorizontal: CELL_GAP / 2,
    marginBottom: CELL_GAP,
    minWidth: 0,                     // prevent overflow by long text
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    borderWidth: 0,                  // Remove border for cleaner look
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
    minHeight: 120,                  // Slightly taller for better proportions
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#FFF7F0',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  emoji: {
    fontSize: fontSizes.md,
  },
  title: {
    fontFamily: fonts.section,
    fontSize: fontSizes.lg, // Match KPI card value size
    fontWeight: '700' as const,
    color: colors.brand.primary,
    textAlign: 'center',
    marginBottom: spacing.xs,
    flexShrink: 1,
    lineHeight: 20,
  },
  description: {
    fontFamily: fonts.body,
    fontSize: fontSizes.sm, // Match KPI card label size
    color: colors.text.secondary,
    textAlign: 'center',
    lineHeight: 16,
    flexShrink: 1,
  },
  // Stat Card Styles
  statCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    borderWidth: 0,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
    minHeight: 120,
    alignItems: 'center',
    justifyContent: 'center',
  },
  statIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#FFF7F0',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  statValue: {
    fontFamily: fonts.title,
    fontSize: fontSizes.lg,
    fontWeight: '700' as const,
    color: colors.brand.primary,
    marginBottom: 4,
    textAlign: 'center',
  },
  statLabel: {
    fontFamily: fonts.section,
    fontSize: fontSizes.xs,
    fontWeight: '700' as const,
    color: colors.brand.primary,
    textAlign: 'center',
    marginBottom: 2,
  },
  statTrend: {
    fontFamily: fonts.body,
    fontSize: fontSizes.xs,
    color: colors.text.secondary,
    textAlign: 'center',
    lineHeight: 12,
  },
  // Header Styles
  header: {
    alignItems: 'center',
    marginBottom: spacing.lg,
    paddingHorizontal: spacing.md,
  },
  sectionTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  subtitle: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 20,
  },
  // CTA Button Styles
  ctaContainer: {
    alignItems: 'center',
    marginTop: spacing.lg,
    paddingHorizontal: spacing.md,
  },
  ctaButton: {
    backgroundColor: '#EF4444',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    minWidth: 200,
    alignItems: 'center',
  },
  ctaText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
});
