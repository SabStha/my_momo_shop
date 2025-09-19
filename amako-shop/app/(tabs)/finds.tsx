import React from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../../src/ui/tokens';

export default function FindsScreen() {
  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.title}>Ama's Finds</Text>
        <Text style={styles.subtitle}>Curated selections just for you</Text>
      </View>

      <View style={styles.content}>
        <View style={styles.featuredCard}>
          <Text style={styles.cardIcon}>⭐</Text>
          <Text style={styles.cardTitle}>Featured This Week</Text>
          <Text style={styles.cardDescription}>
            Discover our chef's special recommendations and seasonal favorites
          </Text>
        </View>

        <View style={styles.featuredCard}>
          <Text style={styles.cardIcon}>🔥</Text>
          <Text style={styles.cardTitle}>Trending Now</Text>
          <Text style={styles.cardDescription}>
            See what's popular among our customers this week
          </Text>
        </View>

        <View style={styles.featuredCard}>
          <Text style={styles.cardIcon}>🎯</Text>
          <Text style={styles.cardTitle}>Personalized Picks</Text>
          <Text style={styles.cardDescription}>
            Based on your order history and preferences
          </Text>
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.momo.sand,
  },
  header: {
    padding: spacing.lg,
    alignItems: 'center',
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha,
  },
  content: {
    padding: spacing.lg,
  },
  featuredCard: {
    backgroundColor: colors.momo.cream,
    padding: spacing.lg,
    borderRadius: 16,
    marginBottom: spacing.md,
    borderWidth: 2,
    borderColor: colors.brand.primary,
    alignItems: 'center',
  },
  cardIcon: {
    fontSize: 48,
    marginBottom: spacing.md,
  },
  cardTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.sm,
  },
  cardDescription: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha,
    textAlign: 'center',
    lineHeight: 22,
  },
});
