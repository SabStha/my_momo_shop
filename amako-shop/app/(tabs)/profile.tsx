import React, { useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useSession } from '../../src/session/SessionProvider';
import { useProfile, useLogout } from '../../src/api/auth-hooks';
import { useLoyalty } from '../../src/api/loyalty';
import { Card, Button, Chip, spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { SkeletonCard } from '../../src/components';

export default function ProfileScreen() {
  const { user, clearToken } = useSession();
  const { data: profile, isLoading, refetch } = useProfile();
  const { data: loyalty, isLoading: loyaltyLoading, error: loyaltyError, refetch: refetchLoyalty } = useLoyalty();
  const logoutMutation = useLogout();

  // Load profile when component mounts
  useEffect(() => {
    if (user) {
      refetch();
    }
  }, [user, refetch]);

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Logout', 
          style: 'destructive',
          onPress: () => logoutMutation.mutate()
        }
      ]
    );
  };

  const handleRefreshProfile = () => {
    refetch();
  };


  // Helper function to get badge variant based on tier
  const getBadgeVariant = (tier: string) => {
    switch (tier.toLowerCase()) {
      case 'gold':
        return 'warning';
      case 'silver':
        return 'primary';
      case 'bronze':
        return 'default';
      case 'platinum':
        return 'success';
      default:
        return 'default';
    }
  };

  if (isLoading) {
    return (
      <ScrollView style={styles.container} contentContainerStyle={styles.content}>
        <View style={styles.header}>
          <SkeletonCard height={80} />
        </View>
        <View style={styles.section}>
          <SkeletonCard height={120} />
        </View>
        <View style={styles.section}>
          <SkeletonCard height={60} />
        </View>
      </ScrollView>
    );
  }

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Header */}
      <View style={styles.header}>
        <Card style={styles.profileCard} padding="lg" radius="lg" shadow="medium">
          <View style={styles.profileInfo}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>
                {profile?.name?.charAt(0)?.toUpperCase() || user?.name?.charAt(0)?.toUpperCase() || 'U'}
              </Text>
            </View>
            <View style={styles.profileDetails}>
              <Text style={styles.name}>
                {profile?.name || user?.name || 'User'}
              </Text>
              <Text style={styles.email}>
                {profile?.email || user?.email || profile?.phone || user?.phone || 'No contact info'}
              </Text>
            </View>
          </View>
        </Card>
      </View>

      {/* Profile Actions */}
      <View style={styles.section}>
        <Card style={styles.actionsCard} padding="md" radius="md" shadow="light">
          <TouchableOpacity style={styles.actionItem} onPress={handleRefreshProfile}>
            <Ionicons name="refresh-outline" size={24} color={colors.gray[600]} />
            <Text style={styles.actionText}>Refresh Profile</Text>
            <Ionicons name="chevron-forward" size={20} color={colors.gray[400]} />
          </TouchableOpacity>
          
          
          <TouchableOpacity style={styles.actionItem}>
            <Ionicons name="settings-outline" size={24} color={colors.gray[600]} />
            <Text style={styles.actionText}>Settings</Text>
            <Ionicons name="chevron-forward" size={20} color={colors.gray[400]} />
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.actionItem}>
            <Ionicons name="help-circle-outline" size={24} color={colors.gray[600]} />
            <Text style={styles.actionText}>Help & Support</Text>
            <Ionicons name="chevron-forward" size={20} color={colors.gray[400]} />
          </TouchableOpacity>
        </Card>
      </View>

      {/* Loyalty Card */}
      <View style={styles.section}>
        <Card style={styles.loyaltyCard} padding="lg" radius="lg" shadow="medium">
          <View style={styles.loyaltyHeader}>
            <Ionicons name="star" size={24} color={colors.warning[500]} />
            <Text style={styles.loyaltyTitle}>Loyalty Program</Text>
          </View>
          
          {loyaltyLoading ? (
            <View style={styles.loyaltySkeleton}>
              <View style={styles.skeletonLine} />
              <View style={styles.skeletonLine} />
              <View style={styles.badgesSkeleton}>
                <View style={styles.skeletonChip} />
                <View style={styles.skeletonChip} />
                <View style={styles.skeletonChip} />
              </View>
            </View>
          ) : loyaltyError ? (
            <View style={styles.loyaltyError}>
              <Text style={styles.errorText}>Failed to load loyalty data</Text>
              <TouchableOpacity style={styles.retryButton} onPress={() => refetchLoyalty()}>
                <Text style={styles.retryText}>Retry</Text>
              </TouchableOpacity>
            </View>
          ) : loyalty ? (
            <>
              <View style={styles.loyaltyStats}>
                <View style={styles.creditSection}>
                  <Text style={styles.creditLabel}>Credits</Text>
                  <Text style={styles.creditAmount}>Rs. {loyalty.credits}</Text>
                </View>
                <View style={styles.tierSection}>
                  <Text style={styles.tierLabel}>Tier</Text>
                  <Text style={styles.tierValue}>{loyalty.tier}</Text>
                </View>
              </View>
              
              <View style={styles.badgesSection}>
                <Text style={styles.badgesLabel}>Badges</Text>
                <ScrollView 
                  horizontal 
                  showsHorizontalScrollIndicator={false}
                  contentContainerStyle={styles.badgesContainer}
                >
                  {loyalty.badges.map((badge) => (
                    <Chip
                      key={badge.id}
                      label={badge.name}
                      variant={getBadgeVariant(badge.tier)}
                      size="sm"
                      style={styles.badgeChip}
                    />
                  ))}
                </ScrollView>
              </View>
            </>
          ) : null}
        </Card>
      </View>

      {/* Logout Button */}
      <View style={styles.section}>
        <Button
          title="Logout"
          onPress={handleLogout}
          variant="outline"
          size="lg"
          disabled={logoutMutation.isPending}
          loading={logoutMutation.isPending}
          style={styles.logoutButton}
          leftIcon={<Ionicons name="log-out-outline" size={20} color={colors.error[500]} />}
        />
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  content: {
    padding: spacing.lg,
  },
  header: {
    marginBottom: spacing.lg,
  },
  profileCard: {
    marginBottom: spacing.md,
  },
  profileInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  avatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: colors.primary[500],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  avatarText: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  profileDetails: {
    flex: 1,
  },
  name: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  email: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  section: {
    marginBottom: spacing.lg,
  },
  actionsCard: {
    gap: spacing.sm,
  },
  actionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    borderRadius: radius.sm,
  },
  actionText: {
    flex: 1,
    fontSize: fontSizes.md,
    color: colors.gray[700],
    marginLeft: spacing.sm,
  },
  logoutButton: {
    borderColor: colors.error[500],
  },
  loyaltyCard: {
    marginBottom: spacing.md,
  },
  loyaltyHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  loyaltyTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginLeft: spacing.sm,
  },
  loyaltyStats: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: spacing.lg,
  },
  creditSection: {
    alignItems: 'center',
    flex: 1,
  },
  creditLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  creditAmount: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.primary[600],
  },
  tierSection: {
    alignItems: 'center',
    flex: 1,
  },
  tierLabel: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  tierValue: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.warning[600],
  },
  badgesSection: {
    marginTop: spacing.sm,
  },
  badgesLabel: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
    marginBottom: spacing.sm,
  },
  badgesContainer: {
    paddingRight: spacing.md,
  },
  badgeChip: {
    marginRight: spacing.sm,
  },
  loyaltySkeleton: {
    gap: spacing.md,
  },
  skeletonLine: {
    height: 20,
    backgroundColor: colors.gray[200],
    borderRadius: radius.sm,
  },
  badgesSkeleton: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  skeletonChip: {
    width: 80,
    height: 32,
    backgroundColor: colors.gray[200],
    borderRadius: radius.md,
  },
  loyaltyError: {
    alignItems: 'center',
    paddingVertical: spacing.md,
  },
  errorText: {
    fontSize: fontSizes.sm,
    color: colors.error[600],
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  retryButton: {
    backgroundColor: colors.primary[100],
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.sm,
    borderWidth: 1,
    borderColor: colors.primary[300],
  },
  retryText: {
    fontSize: fontSizes.sm,
    color: colors.primary[700],
    fontWeight: fontWeights.medium,
  },
});
