import React, { useState } from 'react';
import { View, Text, Pressable, StyleSheet, Image, ActivityIndicator, Alert } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { router } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';
import { Notification } from '../../api/notifications';
import { useClaimOffer } from '../../api/offers';

interface NotificationCardProps {
  notification: Notification;
  onPress?: (notification: Notification) => void;
  onMarkAsRead?: (notificationId: string) => void;
  onDelete?: (notificationId: string) => void;
  isMarkingAsRead?: boolean;
  isDeletingNotification?: boolean;
}

export default function NotificationCard({ 
  notification, 
  onPress, 
  onMarkAsRead, 
  onDelete,
  isMarkingAsRead = false,
  isDeletingNotification = false,
}: NotificationCardProps) {
  const [isClaiming, setIsClaiming] = useState(false);
  const [isClaimed, setIsClaimed] = useState(false);
  const claimOfferMutation = useClaimOffer();
  
  // Safety checks to prevent undefined object errors
  if (!notification || !notification.data) {
    console.warn('NotificationCard: Invalid notification data');
    return null;
  }
  
  const isRead = !!notification.read_at;
  const notificationData = notification.data;
  
  const getNotificationIcon = (type: string) => {
    if (!type) return 'bell-outline';
    
    switch (type) {
      case 'order':
        return 'shopping-outline';
      case 'payment':
        return 'credit-card-outline';
      case 'promotion':
        return 'gift-outline';
      case 'system':
        return 'cog-outline';
      case 'churn':
        return 'alert-circle-outline';
      default:
        return 'bell-outline';
    }
  };
  
  const getNotificationColor = (type: string) => {
    if (!type) return '#6B7280';
    
    switch (type) {
      case 'order':
        return '#3B82F6'; // Blue
      case 'payment':
        return '#10B981'; // Green
      case 'promotion':
        return '#F59E0B'; // Yellow
      case 'system':
        return '#6B7280'; // Gray
      case 'churn':
        return '#EF4444'; // Red
      default:
        return '#6B7280';
    }
  };
  
  const formatTimeAgo = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
    
    if (diffInSeconds < 60) {
      return 'Just now';
    } else if (diffInSeconds < 3600) {
      const minutes = Math.floor(diffInSeconds / 60);
      return `${minutes}m ago`;
    } else if (diffInSeconds < 86400) {
      const hours = Math.floor(diffInSeconds / 3600);
      return `${hours}h ago`;
    } else {
      const days = Math.floor(diffInSeconds / 86400);
      return `${days}d ago`;
    }
  };
  
  const handlePress = () => {
    if (!isRead && onMarkAsRead) {
      onMarkAsRead(notification.id);
    }
    if (onPress) {
      onPress(notification);
    }
  };
  
  const handleDelete = () => {
    if (onDelete) {
      onDelete(notification.id);
    }
  };
  
  const handleClaimOffer = async () => {
    const offerCode = notificationData.data?.offer_code;
    
    if (!offerCode) {
      Alert.alert('Error', 'Offer code not found');
      return;
    }
    
    setIsClaiming(true);
    
    try {
      const result = await claimOfferMutation.mutateAsync(offerCode);
      
      if (result.success) {
        setIsClaimed(true);
        Alert.alert(
          '🎉 Offer Claimed!',
          result.message || 'Offer has been added to your account. Apply it at checkout!',
          [
            { text: 'View My Offers', onPress: () => router.push('/offers') },
            { text: 'Shop Now', onPress: () => router.push('/(tabs)/menu') },
            { text: 'OK', style: 'cancel' }
          ]
        );
      } else {
        Alert.alert('Error', result.message || 'Failed to claim offer');
      }
    } catch (error: any) {
      Alert.alert(
        'Claim Failed',
        error.response?.data?.message || 'Unable to claim offer. It may have expired or already been claimed.'
      );
    } finally {
      setIsClaiming(false);
    }
  };
  
  return (
    <Pressable 
      style={[
        styles.container,
        !isRead && styles.unreadContainer
      ]}
      onPress={handlePress}
    >
      <View style={styles.content}>
        {/* Icon */}
        <View style={[
          styles.iconContainer,
          { backgroundColor: getNotificationColor(notificationData.type) + '20' }
        ]}>
          <MCI 
            name={getNotificationIcon(notificationData.type)} 
            size={20} 
            color={getNotificationColor(notificationData.type)} 
          />
        </View>
        
        {/* Content */}
        <View style={styles.textContent}>
          <Text style={[
            styles.title,
            !isRead && styles.unreadTitle
          ]}>
            {notificationData.title}
          </Text>
          <Text style={styles.message} numberOfLines={2}>
            {notificationData.message}
          </Text>
          
          {/* Show offer details if promotion */}
          {notificationData.type === 'promotion' && notificationData.data && (
            <View style={styles.offerDetails}>
              {notificationData.data.discount && (
                <View style={styles.discountBadge}>
                  <Text style={styles.discountText}>
                    {notificationData.data.discount}% OFF
                  </Text>
                </View>
              )}
              {notificationData.data.offer_code && (
                <Text style={styles.offerCode}>
                  Code: {notificationData.data.offer_code}
                </Text>
              )}
            </View>
          )}
          
          <Text style={styles.time}>
            {formatTimeAgo(notification.created_at)}
          </Text>
        </View>
        
        {/* Actions */}
        <View style={styles.actions}>
          {/* Claim button for promotion notifications */}
          {notificationData.type === 'promotion' && notificationData.data?.offer_code && !isClaimed && (
            <Pressable 
              style={[
                styles.claimButton,
                isClaiming && styles.claimButtonProcessing
              ]}
              onPress={handleClaimOffer}
              disabled={isClaiming}
            >
              {isClaiming ? (
                <ActivityIndicator size="small" color={colors.white} />
              ) : (
                <>
                  <MCI name="gift" size={16} color={colors.white} />
                  <Text style={styles.claimButtonText}>Claim</Text>
                </>
              )}
            </Pressable>
          )}
          
          {/* Show claimed checkmark */}
          {isClaimed && (
            <View style={styles.claimedBadge}>
              <MCI name="check-circle" size={16} color={colors.green[500]} />
              <Text style={styles.claimedText}>Claimed</Text>
            </View>
          )}
          
          {!isRead && (
            <Pressable 
              style={[
                styles.markAsReadButton,
                isMarkingAsRead && styles.markAsReadButtonProcessing
              ]}
              onPress={() => !isMarkingAsRead && onMarkAsRead?.(notification.id)}
              disabled={isMarkingAsRead}
            >
              {isMarkingAsRead ? (
                <ActivityIndicator size="small" color={colors.white} />
              ) : (
                <MCI name="check" size={16} color={colors.white} />
              )}
            </Pressable>
          )}
          
          <Pressable 
            style={[
              styles.deleteButton,
              isDeletingNotification && styles.deleteButtonProcessing
            ]}
            onPress={handleDelete}
            disabled={isDeletingNotification}
          >
            {isDeletingNotification ? (
              <ActivityIndicator size="small" color={colors.gray[500]} />
            ) : (
              <MCI name="delete-outline" size={16} color={colors.gray[500]} />
            )}
          </Pressable>
        </View>
      </View>
      
      {/* Unread indicator */}
      {!isRead && <View style={styles.unreadIndicator} />}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.md,
    marginVertical: spacing.xs,
    borderRadius: radius.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderLeftWidth: 4,
    borderLeftColor: colors.gray[200],
  },
  unreadContainer: {
    borderLeftColor: colors.blue[500],
    backgroundColor: colors.blue[50],
  },
  content: {
    flexDirection: 'row',
    padding: spacing.md,
    alignItems: 'flex-start',
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: radius.full,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.sm,
  },
  textContent: {
    flex: 1,
    marginRight: spacing.sm,
  },
  title: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  unreadTitle: {
    fontWeight: fontWeights.bold,
  },
  message: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    lineHeight: 16,
    marginBottom: spacing.xs,
  },
  time: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
  },
  actions: {
    flexDirection: 'column',
    alignItems: 'flex-end',
    gap: spacing.xs,
  },
  claimButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.orange[500],
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    gap: spacing.xs,
  },
  claimButtonProcessing: {
    backgroundColor: colors.orange[400],
    opacity: 0.7,
  },
  claimButtonText: {
    fontSize: fontSizes.xs,
    color: colors.white,
    fontWeight: fontWeights.semibold,
  },
  claimedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
  },
  claimedText: {
    fontSize: fontSizes.xs,
    color: colors.green[500],
    fontWeight: fontWeights.medium,
  },
  offerDetails: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    marginTop: spacing.xs,
    marginBottom: spacing.xs,
  },
  discountBadge: {
    backgroundColor: colors.orange[500],
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
    borderRadius: radius.sm,
  },
  discountText: {
    fontSize: fontSizes.xs,
    color: colors.white,
    fontWeight: fontWeights.bold,
  },
  offerCode: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    fontFamily: 'monospace',
  },
  markAsReadButton: {
    backgroundColor: colors.green[500],
    width: 28,
    height: 28,
    borderRadius: radius.full,
    justifyContent: 'center',
    alignItems: 'center',
  },
  markAsReadButtonProcessing: {
    backgroundColor: colors.green[400],
    opacity: 0.7,
  },
  deleteButton: {
    width: 28,
    height: 28,
    borderRadius: radius.full,
    justifyContent: 'center',
    alignItems: 'center',
  },
  deleteButtonProcessing: {
    opacity: 0.5,
  },
  unreadIndicator: {
    position: 'absolute',
    top: spacing.sm,
    right: spacing.sm,
    width: 8,
    height: 8,
    borderRadius: radius.full,
    backgroundColor: colors.blue[500],
  },
});
