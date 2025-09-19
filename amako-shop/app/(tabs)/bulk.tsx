import React from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../../src/ui/tokens';

export default function BulkScreen() {
  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.title}>Bulk Orders</Text>
        <Text style={styles.subtitle}>Perfect for parties and events</Text>
      </View>

      <View style={styles.content}>
        <View style={styles.bulkCard}>
          <Text style={styles.cardIcon}>🎉</Text>
          <Text style={styles.cardTitle}>Party Packs</Text>
          <Text style={styles.cardDescription}>
            Special packages for celebrations and gatherings
          </Text>
          <Text style={styles.cardPrice}>Starting from ₹299</Text>
        </View>

        <View style={styles.bulkCard}>
          <Text style={styles.cardIcon}>🏢</Text>
          <Text style={styles.cardTitle}>Corporate Orders</Text>
          <Text style={styles.cardDescription}>
            Catering solutions for office meetings and events
          </Text>
          <Text style={styles.cardPrice}>Starting from ₹499</Text>
        </View>

        <View style={styles.bulkCard}>
          <Text style={styles.cardIcon}>🎓</Text>
          <Text style={styles.cardTitle}>Student Specials</Text>
          <Text style={styles.cardDescription}>
            Budget-friendly options for student groups
          </Text>
          <Text style={styles.cardPrice}>Starting from ₹199</Text>
        </View>

        <View style={styles.contactCard}>
          <Text style={styles.contactTitle}>Need a Custom Quote?</Text>
          <Text style={styles.contactDescription}>
            Contact us for personalized bulk order solutions
          </Text>
          <Text style={styles.contactInfo}>📞 +977-1234567890</Text>
          <Text style={styles.contactInfo}>✉️ bulk@amako.com</Text>
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
  bulkCard: {
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
    marginBottom: spacing.sm,
  },
  cardPrice: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.accent,
  },
  contactCard: {
    backgroundColor: colors.brand.primary,
    padding: spacing.lg,
    borderRadius: 16,
    alignItems: 'center',
    marginTop: spacing.md,
  },
  contactTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.sm,
  },
  contactDescription: {
    fontSize: fontSizes.md,
    color: colors.white,
    textAlign: 'center',
    marginBottom: spacing.md,
  },
  contactInfo: {
    fontSize: fontSizes.md,
    color: colors.brand.accent,
    marginBottom: spacing.xs,
  },
});
