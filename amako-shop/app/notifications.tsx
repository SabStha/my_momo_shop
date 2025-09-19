import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { useRouter } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights } from '../src/ui/tokens';
import { useNotificationStore } from '../src/state/notifications';

export default function NotificationsScreen() {
  const router = useRouter();
  const { notifications, hasUnread, markAsRead, markAllAsRead } = useNotificationStore();

  const handleBack = () => {
    router.back();
  };

  const handleMarkAllRead = () => {
    markAllAsRead();
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={handleBack} style={styles.backButton}>
          <Text style={styles.backButtonText}>← Back</Text>
        </TouchableOpacity>
        <Text style={styles.title}>Notifications</Text>
        {hasUnread && (
          <TouchableOpacity onPress={handleMarkAllRead} style={styles.markAllButton}>
            <Text style={styles.markAllButtonText}>Mark All Read</Text>
          </TouchableOpacity>
        )}
      </View>

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {notifications.length === 0 ? (
          <View style={styles.emptyState}>
            <Text style={styles.emptyIcon}>🔔</Text>
            <Text style={styles.emptyTitle}>No notifications</Text>
            <Text style={styles.emptyMessage}>
              You're all caught up! We'll notify you when something important happens.
            </Text>
          </View>
        ) : (
          <View style={styles.notificationsList}>
            {notifications.map((notification) => (
              <TouchableOpacity
                key={notification.id}
                style={[
                  styles.notificationItem,
                  !notification.isRead && styles.unreadNotification
                ]}
                onPress={() => markAsRead(notification.id)}
              >
                <View style={styles.notificationContent}>
                  <Text style={[
                    styles.notificationTitle,
                    !notification.isRead && styles.unreadTitle
                  ]}>
                    {notification.title}
                  </Text>
                  <Text style={styles.notificationMessage}>
                    {notification.message}
                  </Text>
                  <Text style={styles.notificationTime}>
                    {new Date(notification.createdAt).toLocaleDateString()}
                  </Text>
                </View>
                {!notification.isRead && <View style={styles.unreadDot} />}
              </TouchableOpacity>
            ))}
          </View>
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.momo.sand,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.brand.primary,
    backgroundColor: colors.momo.cream,
  },
  backButton: {
    padding: spacing.sm,
  },
  backButtonText: {
    fontSize: fontSizes.md,
    color: colors.brand.primary,
    fontWeight: fontWeights.medium,
  },
  title: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
  },
  markAllButton: {
    padding: spacing.sm,
  },
  markAllButtonText: {
    fontSize: fontSizes.sm,
    color: colors.brand.accent,
    fontWeight: fontWeights.medium,
  },
  content: {
    flex: 1,
    padding: spacing.lg,
  },
  emptyState: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing['2xl'],
  },
  emptyIcon: {
    fontSize: 64,
    marginBottom: spacing.lg,
  },
  emptyTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.sm,
  },
  emptyMessage: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha,
    textAlign: 'center',
    lineHeight: 22,
  },
  notificationsList: {
    gap: spacing.sm,
  },
  notificationItem: {
    backgroundColor: colors.momo.cream,
    padding: spacing.md,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: colors.brand.primary,
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  unreadNotification: {
    borderColor: colors.brand.accent,
    borderWidth: 2,
  },
  notificationContent: {
    flex: 1,
  },
  notificationTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  unreadTitle: {
    fontWeight: fontWeights.bold,
  },
  notificationMessage: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    lineHeight: 20,
    marginBottom: spacing.xs,
  },
  notificationTime: {
    fontSize: fontSizes.xs,
    color: colors.momo.mocha,
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.brand.accent,
    marginLeft: spacing.sm,
    marginTop: spacing.xs,
  },
});
