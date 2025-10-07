import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../../src/ui/tokens';

export default function HelpScreen() {
  const helpItems = [
    {
      id: '1',
      title: 'How to place an order?',
      icon: '🛒',
      description: 'Step-by-step guide to ordering your favorite momos',
    },
    {
      id: '2',
      title: 'Payment methods',
      icon: '💳',
      description: 'Learn about our accepted payment options',
    },
    {
      id: '3',
      title: 'Delivery information',
      icon: '🚚',
      description: 'Delivery areas, times, and tracking your order',
    },
    {
      id: '4',
      title: 'Account & Profile',
      icon: '👤',
      description: 'Managing your account and profile settings',
    },
    {
      id: '5',
      title: 'Loyalty Program',
      icon: '⭐',
      description: 'How to earn and redeem loyalty points',
    },
    {
      id: '6',
      title: 'Contact Support',
      icon: '📞',
      description: 'Get in touch with our customer support team',
    },
  ];

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.title}>Help Center</Text>
        <Text style={styles.subtitle}>Find answers to common questions</Text>
      </View>

      <View style={styles.content}>
        {helpItems.map((item) => (
          <TouchableOpacity key={item.id} style={styles.helpItem}>
            <View style={styles.helpIcon}>
              <Text style={styles.iconText}>{item.icon}</Text>
            </View>
            <View style={styles.helpContent}>
              <Text style={styles.helpTitle}>{item.title}</Text>
              <Text style={styles.helpDescription}>{item.description}</Text>
            </View>
            <Text style={styles.arrow}>›</Text>
          </TouchableOpacity>
        ))}

        <View style={styles.contactSection}>
          <Text style={styles.contactTitle}>Still need help?</Text>
          <Text style={styles.contactDescription}>
            Our support team is here to help you 24/7
          </Text>
          <TouchableOpacity style={styles.contactButton}>
            <Text style={styles.contactButtonText}>Contact Support</Text>
          </TouchableOpacity>
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.white,
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
  helpItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: 12,
    marginBottom: spacing.sm,
    borderWidth: 1,
    borderColor: colors.brand.primary,
  },
  helpIcon: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: colors.brand.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.md,
  },
  iconText: {
    fontSize: 24,
  },
  helpContent: {
    flex: 1,
  },
  helpTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  helpDescription: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
  },
  arrow: {
    fontSize: 24,
    color: colors.brand.primary,
    fontWeight: 'bold',
  },
  contactSection: {
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
  contactButton: {
    backgroundColor: colors.brand.accent,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: 8,
  },
  contactButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
});
