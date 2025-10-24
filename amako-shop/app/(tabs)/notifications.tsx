import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  RefreshControl,
  Pressable,
  Alert,
  Animated,
} from 'react-native';

// Create animated FlatList for native scroll tracking
const AnimatedFlatList = Animated.createAnimatedComponent(FlatList);
import { router } from 'expo-router';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui/tokens';
import { useNotifications, useMarkAsRead, useMarkAllAsRead, useDeleteNotification, useUnreadCount } from '../../src/hooks/useNotifications';
import NotificationCard from '../../src/components/notifications/NotificationCard';
import { Notification } from '../../src/api/notifications';
import LoadingSpinner from '../../src/components/LoadingSpinner';
import OfferSuccessModal from '../../src/components/OfferSuccessModal';

export default function NotificationsScreen() {
  const [refreshing, setRefreshing] = useState(false);
  const [isPulling, setIsPulling] = useState(false);
  const [page, setPage] = useState(1);
  const [processingNotificationId, setProcessingNotificationId] = useState<string | null>(null);
  const [deletingNotificationId, setDeletingNotificationId] = useState<string | null>(null);
  const scrollY = useRef(new Animated.Value(0)).current;
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [claimedOfferData, setClaimedOfferData] = useState<{ title: string; discount: number } | null>(null);
  
  const { data: notificationsData, isLoading, error, refetch } = useNotifications(page, 20);
  const markAsReadMutation = useMarkAsRead();
  const markAllAsReadMutation = useMarkAllAsRead();
  const deleteNotificationMutation = useDeleteNotification();
  const { unreadCount, hasUnread, isLoading: unreadLoading, error: unreadError } = useUnreadCount();
  
  // Safely handle undefined data
  const notifications = notificationsData?.notifications || [];
  
  // Track pulling state
  React.useEffect(() => {
    const listenerId = scrollY.addListener(({ value }) => {
      setIsPulling(value < -50); // Show custom spinner when pulled down 50px
    });
    return () => scrollY.removeListener(listenerId);
  }, [scrollY]);
  
  const handleRefresh = async () => {
    setRefreshing(true);
    const minDelay = new Promise(resolve => setTimeout(resolve, 2000)); // 2 seconds
    try {
      await Promise.all([refetch(), minDelay]);
    } catch (error) {
      console.error('Refresh error:', error);
    } finally {
      setRefreshing(false);
    }
  };
  
  const handleMarkAsRead = async (notificationId: string) => {
    try {
      setProcessingNotificationId(notificationId);
      await markAsReadMutation.mutateAsync(notificationId);
    } catch (error) {
      console.error('Mark as read error:', error);
    } finally {
      setProcessingNotificationId(null);
    }
  };
  
  const handleMarkAllAsRead = async () => {
    Alert.alert(
      'Mark All as Read',
      'Are you sure you want to mark all notifications as read?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Mark All',
          onPress: async () => {
            try {
              await markAllAsReadMutation.mutateAsync();
            } catch (error) {
              console.error('Mark all as read error:', error);
            }
          },
        },
      ]
    );
  };
  
  const handleDeleteNotification = async (notificationId: string) => {
    Alert.alert(
      'Delete Notification',
      'Are you sure you want to delete this notification?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              setDeletingNotificationId(notificationId);
              await deleteNotificationMutation.mutateAsync(notificationId);
            } catch (error) {
              console.error('Delete notification error:', error);
            } finally {
              setDeletingNotificationId(null);
            }
          },
        },
      ]
    );
  };
  
  const handleNotificationPress = (notification: Notification) => {
    // Mark as read if not already read
    if (!notification.read_at) {
      handleMarkAsRead(notification.id);
    }
    
    // Handle navigation based on notification type and action_url
    if (notification.data.action_url) {
      const actionUrl = notification.data.action_url;
      console.log('📱 Notification pressed, navigating to:', actionUrl);
      
      // Handle order-related notifications
      if (actionUrl.startsWith('/order/')) {
        const orderId = actionUrl.replace('/order/', '');
        
        // Validate order ID - don't navigate to invalid IDs (like timestamps)
        const numericId = parseInt(orderId);
        if (numericId > 10000000) {
          console.warn('⚠️ Invalid order ID in notification (looks like timestamp):', numericId);
          Alert.alert(
            'Invalid Order',
            'This order reference is invalid. Please check your orders list.',
            [
              { text: 'View Orders', onPress: () => router.push('/orders') },
              { text: 'Cancel', style: 'cancel' }
            ]
          );
          return;
        }
        
        router.push(actionUrl as any);
      } else {
        // Navigate to other URLs
        router.push(actionUrl as any);
      }
    }
  };
  
  const handleOfferClaimed = (offerTitle: string, discount: number) => {
    setClaimedOfferData({ title: offerTitle, discount });
    setShowSuccessModal(true);
  };
  
  const renderNotification = ({ item }: { item: Notification }) => (
    <NotificationCard
      notification={item}
      onPress={handleNotificationPress}
      onMarkAsRead={handleMarkAsRead}
      onDelete={handleDeleteNotification}
      onOfferClaimed={handleOfferClaimed}
      isMarkingAsRead={processingNotificationId === item.id}
      isDeletingNotification={deletingNotificationId === item.id}
    />
  );
  
  const renderEmptyState = () => (
    <View style={styles.emptyState}>
      <MCI name="bell-off-outline" size={64} color={colors.gray[400]} />
      <Text style={styles.emptyTitle}>No Notifications</Text>
      <Text style={styles.emptyMessage}>
        You're all caught up! We'll notify you when something important happens.
      </Text>
    </View>
  );
  
  const renderHeader = () => (
    <View style={styles.header}>
      <View style={styles.headerContent}>
        <Text style={styles.headerTitle}>Notifications</Text>
        {hasUnread && (
          <View style={styles.unreadBadge}>
            <Text style={styles.unreadCount}>{unreadCount}</Text>
          </View>
        )}
      </View>
      
      {hasUnread && (
        <Pressable 
          style={styles.markAllButton}
          onPress={handleMarkAllAsRead}
          disabled={markAllAsReadMutation.isPending}
        >
          <MCI name="check-all" size={20} color={colors.blue[600]} />
          <Text style={styles.markAllText}>Mark All Read</Text>
        </Pressable>
      )}
    </View>
  );
  
  if (isLoading && !refreshing) {
    return (
      <View style={styles.loadingContainer}>
        <LoadingSpinner size="large" text="Loading notifications..." />
      </View>
    );
  }
  
  if (error) {
    return (
      <View style={styles.errorContainer}>
        <MCI name="alert-circle-outline" size={64} color="#ef4444" />
        <Text style={styles.errorTitle}>Failed to Load Notifications</Text>
        <Text style={styles.errorMessage}>
          {error.message || 'Something went wrong. Please try again.'}
        </Text>
        <Pressable style={styles.retryButton} onPress={() => refetch()}>
          <Text style={styles.retryButtonText}>Try Again</Text>
        </Pressable>
      </View>
    );
  }
  
  return (
    <View style={styles.container}>
      {renderHeader()}
      
      <AnimatedFlatList
        data={notifications}
        renderItem={renderNotification}
        keyExtractor={(item) => item.id}
        ListEmptyComponent={renderEmptyState}
        onScroll={Animated.event(
          [{ nativeEvent: { contentOffset: { y: scrollY } } }],
          { useNativeDriver: true }
        )}
        scrollEventThrottle={16}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            colors={['transparent']}
            tintColor="transparent"
            progressViewOffset={-9999}
          />
        }
        contentContainerStyle={styles.listContainer}
        showsVerticalScrollIndicator={false}
      />
      
      {/* Loading Overlay - Shows during pull and refresh */}
      {(isPulling || refreshing) && (
        <Animated.View 
          style={[
            styles.loadingOverlay,
            refreshing ? {
              opacity: 1,
              transform: [{ translateY: 0 }]
            } : {
              opacity: scrollY.interpolate({
                inputRange: [-150, -50, 0],
                outputRange: [1, 0.5, 0],
                extrapolate: 'clamp',
              }),
              transform: [{
                translateY: scrollY.interpolate({
                  inputRange: [-150, 0],
                  outputRange: [0, 150],
                  extrapolate: 'clamp',
                })
              }]
            }
          ]}
        >
          <LoadingSpinner 
            size="large" 
            text={refreshing ? "Refreshing..." : "Pull to refresh"}
          />
        </Animated.View>
      )}
      
      {/* Success Modal */}
      {claimedOfferData && (
        <OfferSuccessModal
          visible={showSuccessModal}
          onClose={() => setShowSuccessModal(false)}
          type="claimed"
          offerTitle={claimedOfferData.title}
          discount={claimedOfferData.discount}
          onViewOffers={() => router.push('/offers')}
          onUseNow={() => router.push('/(tabs)/menu')}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  header: {
    backgroundColor: colors.white,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: spacing.sm,
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  unreadBadge: {
    backgroundColor: '#ef4444',
    borderRadius: radius.full,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    minWidth: 24,
    alignItems: 'center',
  },
  unreadCount: {
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
  },
  markAllButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.blue[50],
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    gap: spacing.xs,
  },
  markAllText: {
    color: colors.blue[600],
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  listContainer: {
    paddingBottom: spacing.xl,
  },
  emptyState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.xxl,
  },
  emptyTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginTop: spacing.md,
    marginBottom: spacing.sm,
  },
  emptyMessage: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 20,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[50],
  },
  loadingText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginTop: spacing.md,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[50],
    paddingHorizontal: spacing.xl,
  },
  errorTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginTop: spacing.md,
    marginBottom: spacing.sm,
  },
  errorMessage: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: spacing.lg,
  },
  retryButton: {
    backgroundColor: colors.blue[600],
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  retryButtonText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.15)',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1000,
  },
});
