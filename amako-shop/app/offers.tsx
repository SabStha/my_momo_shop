import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  RefreshControl,
  TouchableOpacity,
  Animated,
  Alert,
} from 'react-native';
import { router } from 'expo-router';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { useMyOffers, OfferClaim } from '../src/api/offers';
import LoadingSpinner from '../src/components/LoadingSpinner';
import { ScreenWithBottomNav } from '../src/components';

// Create animated FlatList for native scroll tracking
const AnimatedFlatList = Animated.createAnimatedComponent(FlatList);

type OfferTab = 'active' | 'used' | 'expired';

export default function OffersScreen() {
  const [activeTab, setActiveTab] = useState<OfferTab>('active');
  const [refreshing, setRefreshing] = useState(false);
  const [isPulling, setIsPulling] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;
  
  const { data: offers = [], isLoading, error, refetch } = useMyOffers();
  
  // Track pulling state
  React.useEffect(() => {
    const listenerId = scrollY.addListener(({ value }) => {
      setIsPulling(value < -50);
    });
    return () => scrollY.removeListener(listenerId);
  }, [scrollY]);
  
  const handleRefresh = async () => {
    setRefreshing(true);
    const minDelay = new Promise(resolve => setTimeout(resolve, 2000));
    try {
      await Promise.all([refetch(), minDelay]);
    } catch (error) {
      console.error('Refresh error:', error);
    } finally {
      setRefreshing(false);
    }
  };
  
  // Filter offers by tab
  const filteredOffers = offers.filter(offer => {
    if (activeTab === 'active') {
      return offer.status === 'active';
    } else if (activeTab === 'used') {
      return offer.status === 'used';
    } else {
      return offer.status === 'expired';
    }
  });
  
  const formatExpiryTime = (validUntil: string) => {
    const expiry = new Date(validUntil);
    const now = new Date();
    const diffInMs = expiry.getTime() - now.getTime();
    
    if (diffInMs <= 0) {
      return 'Expired';
    }
    
    const days = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diffInMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diffInMs % (1000 * 60 * 60)) / (1000 * 60));
    
    if (days > 0) {
      return `${days}d ${hours}h`;
    } else if (hours > 0) {
      return `${hours}h ${minutes}m`;
    } else {
      return `${minutes}m`;
    }
  };
  
  const getOfferTypeIcon = (type: string) => {
    switch (type) {
      case 'flash':
        return 'flash';
      case 'loyalty':
        return 'gift';
      case 'bogo':
        return 'cart-plus';
      case 'discount':
        return 'tag';
      default:
        return 'ticket-percent';
    }
  };
  
  const renderOffer = ({ item }: { item: OfferClaim }) => {
    const isExpiringSoon = () => {
      if (item.status !== 'active') return false;
      const expiry = new Date(item.offer.valid_until);
      const now = new Date();
      const hoursUntilExpiry = (expiry.getTime() - now.getTime()) / (1000 * 60 * 60);
      return hoursUntilExpiry <= 24;
    };
    
    return (
      <TouchableOpacity
        style={[
          styles.offerCard,
          item.status === 'active' && styles.activeOfferCard,
          item.status === 'expired' && styles.expiredOfferCard,
        ]}
        onPress={() => {
          if (item.status === 'active') {
            Alert.alert(
              item.offer.title,
              `${item.offer.description}\n\nCode: ${item.offer.code}\nMin Purchase: Rs. ${item.offer.min_purchase}\nExpires: ${formatExpiryTime(item.offer.valid_until)}`,
              [
                { text: 'Shop Now', onPress: () => router.push('/(tabs)/menu') },
                { text: 'Close', style: 'cancel' }
              ]
            );
          }
        }}
      >
        {/* Icon and Status Badge */}
        <View style={styles.offerHeader}>
          <View style={[
            styles.offerIcon,
            { backgroundColor: colors.orange[500] + '20' }
          ]}>
            <MCI 
              name={getOfferTypeIcon(item.offer.type)} 
              size={24} 
              color={colors.orange[500]} 
            />
          </View>
          
          {item.status === 'used' && (
            <View style={styles.usedBadge}>
              <Text style={styles.usedBadgeText}>Used</Text>
            </View>
          )}
          
          {item.status === 'expired' && (
            <View style={styles.expiredBadge}>
              <Text style={styles.expiredBadgeText}>Expired</Text>
            </View>
          )}
          
          {isExpiringSoon() && (
            <View style={styles.expiringSoonBadge}>
              <Text style={styles.expiringSoonText}>⚡ Expiring Soon</Text>
            </View>
          )}
        </View>
        
        {/* Offer Content */}
        <View style={styles.offerContent}>
          <Text style={[
            styles.offerTitle,
            item.status !== 'active' && styles.inactiveText
          ]}>
            {item.offer.title}
          </Text>
          
          <Text style={[
            styles.offerDescription,
            item.status !== 'active' && styles.inactiveText
          ]} numberOfLines={2}>
            {item.offer.description}
          </Text>
          
          {/* Offer Details */}
          <View style={styles.offerDetailsRow}>
            <View style={styles.discountContainer}>
              <Text style={styles.discountAmount}>{item.offer.discount}% OFF</Text>
            </View>
            
            <Text style={styles.offerCode}>Code: {item.offer.code}</Text>
          </View>
          
          {/* Expiry and Action */}
          <View style={styles.offerFooter}>
            {item.status === 'active' ? (
              <>
                <View style={styles.expiryContainer}>
                  <Ionicons name="time-outline" size={14} color={colors.gray[500]} />
                  <Text style={styles.expiryText}>
                    Expires in: {formatExpiryTime(item.offer.valid_until)}
                  </Text>
                </View>
                
                <TouchableOpacity
                  style={styles.applyButton}
                  onPress={() => router.push('/(tabs)/menu')}
                >
                  <Text style={styles.applyButtonText}>Shop Now</Text>
                  <Ionicons name="chevron-forward" size={16} color={colors.orange[500]} />
                </TouchableOpacity>
              </>
            ) : item.status === 'used' ? (
              <View style={styles.usedInfo}>
                <Ionicons name="checkmark-circle" size={14} color={colors.green[500]} />
                <Text style={styles.usedInfoText}>
                  Used on {new Date(item.used_at || '').toLocaleDateString()}
                </Text>
              </View>
            ) : (
              <View style={styles.expiredInfo}>
                <Ionicons name="close-circle" size={14} color={colors.gray[400]} />
                <Text style={styles.expiredInfoText}>
                  Expired on {new Date(item.offer.valid_until).toLocaleDateString()}
                </Text>
              </View>
            )}
          </View>
        </View>
      </TouchableOpacity>
    );
  };
  
  const renderEmptyState = () => (
    <View style={styles.emptyState}>
      <MCI name="gift-outline" size={64} color={colors.gray[300]} />
      <Text style={styles.emptyStateTitle}>No {activeTab} offers</Text>
      <Text style={styles.emptyStateMessage}>
        {activeTab === 'active' 
          ? "You don't have any active offers yet. Check back soon for exclusive deals!"
          : activeTab === 'used'
          ? "You haven't used any offers yet. Claim an offer to start saving!"
          : "No expired offers to show."}
      </Text>
      
      {activeTab === 'active' && (
        <TouchableOpacity 
          style={styles.browseButton}
          onPress={() => router.push('/(tabs)/home')}
        >
          <Text style={styles.browseButtonText}>Browse Menu</Text>
        </TouchableOpacity>
      )}
    </View>
  );
  
  if (isLoading) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.loadingContainer}>
          <LoadingSpinner size="large" text="Loading your offers..." />
        </View>
      </ScreenWithBottomNav>
    );
  }
  
  if (error) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.errorContainer}>
          <MCI name="alert-circle-outline" size={64} color={colors.red[500]} />
          <Text style={styles.errorTitle}>Failed to Load Offers</Text>
          <Text style={styles.errorMessage}>
            {error.message || 'Something went wrong. Please try again.'}
          </Text>
          <TouchableOpacity style={styles.retryButton} onPress={() => refetch()}>
            <Text style={styles.retryButtonText}>Try Again</Text>
          </TouchableOpacity>
        </View>
      </ScreenWithBottomNav>
    );
  }
  
  return (
    <ScreenWithBottomNav>
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <View style={styles.headerTop}>
            <TouchableOpacity 
              style={styles.backButton}
              onPress={() => router.back()}
            >
              <Ionicons name="arrow-back" size={24} color={colors.gray[800]} />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>My Offers</Text>
            <View style={styles.headerSpacer} />
          </View>
          
          {/* Tabs */}
          <View style={styles.tabs}>
            <TouchableOpacity
              style={[styles.tab, activeTab === 'active' && styles.activeTab]}
              onPress={() => setActiveTab('active')}
            >
              <Text style={[
                styles.tabText,
                activeTab === 'active' && styles.activeTabText
              ]}>
                Active ({offers.filter(o => o.status === 'active').length})
              </Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              style={[styles.tab, activeTab === 'used' && styles.activeTab]}
              onPress={() => setActiveTab('used')}
            >
              <Text style={[
                styles.tabText,
                activeTab === 'used' && styles.activeTabText
              ]}>
                Used ({offers.filter(o => o.status === 'used').length})
              </Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              style={[styles.tab, activeTab === 'expired' && styles.activeTab]}
              onPress={() => setActiveTab('expired')}
            >
              <Text style={[
                styles.tabText,
                activeTab === 'expired' && styles.activeTabText
              ]}>
                Expired ({offers.filter(o => o.status === 'expired').length})
              </Text>
            </TouchableOpacity>
          </View>
        </View>
        
        {/* Offers List */}
        <AnimatedFlatList
          data={filteredOffers}
          renderItem={renderOffer}
          keyExtractor={(item) => item.id.toString()}
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
        
        {/* Loading Overlay */}
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
      </View>
    </ScreenWithBottomNav>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[50],
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
    backgroundColor: colors.orange[500],
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  retryButtonText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  header: {
    backgroundColor: colors.white,
    paddingBottom: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  headerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  headerSpacer: {
    width: 40,
  },
  tabs: {
    flexDirection: 'row',
    paddingHorizontal: spacing.md,
    gap: spacing.sm,
  },
  tab: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    borderRadius: radius.md,
    alignItems: 'center',
    backgroundColor: colors.gray[100],
  },
  activeTab: {
    backgroundColor: colors.orange[500],
  },
  tabText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[600],
  },
  activeTabText: {
    color: colors.white,
    fontWeight: fontWeights.semibold,
  },
  listContainer: {
    paddingVertical: spacing.md,
  },
  offerCard: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.md,
    marginVertical: spacing.xs,
    borderRadius: radius.lg,
    padding: spacing.md,
    borderWidth: 2,
    borderColor: colors.gray[200],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  activeOfferCard: {
    borderColor: colors.orange[300],
    backgroundColor: colors.orange[50],
  },
  expiredOfferCard: {
    opacity: 0.6,
    backgroundColor: colors.gray[100],
    borderColor: colors.gray[300],
  },
  offerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
    gap: spacing.sm,
  },
  offerIcon: {
    width: 48,
    height: 48,
    borderRadius: radius.full,
    justifyContent: 'center',
    alignItems: 'center',
  },
  usedBadge: {
    backgroundColor: colors.green[100],
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
    borderRadius: radius.sm,
    marginLeft: 'auto',
  },
  usedBadgeText: {
    fontSize: fontSizes.xs,
    color: colors.green[700],
    fontWeight: fontWeights.semibold,
  },
  expiredBadge: {
    backgroundColor: colors.gray[300],
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
    borderRadius: radius.sm,
    marginLeft: 'auto',
  },
  expiredBadgeText: {
    fontSize: fontSizes.xs,
    color: colors.gray[700],
    fontWeight: fontWeights.semibold,
  },
  expiringSoonBadge: {
    backgroundColor: colors.red[100],
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
    borderRadius: radius.sm,
    marginLeft: 'auto',
  },
  expiringSoonText: {
    fontSize: fontSizes.xs,
    color: colors.red[700],
    fontWeight: fontWeights.semibold,
  },
  offerContent: {
    gap: spacing.xs,
  },
  offerTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
  },
  offerDescription: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    lineHeight: 18,
  },
  inactiveText: {
    color: colors.gray[500],
  },
  offerDetailsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    marginTop: spacing.xs,
  },
  discountContainer: {
    backgroundColor: colors.orange[500],
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: radius.md,
  },
  discountAmount: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  offerCode: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    fontFamily: 'monospace',
  },
  offerFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: spacing.sm,
    paddingTop: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
  },
  expiryContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  expiryText: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
  },
  applyButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    backgroundColor: colors.orange[50],
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
  },
  applyButtonText: {
    fontSize: fontSizes.sm,
    color: colors.orange[500],
    fontWeight: fontWeights.semibold,
  },
  usedInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  usedInfoText: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
  },
  expiredInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  expiredInfoText: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
  },
  emptyState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.xl * 3,
  },
  emptyStateTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginTop: spacing.md,
    marginBottom: spacing.sm,
  },
  emptyStateMessage: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: spacing.lg,
  },
  browseButton: {
    backgroundColor: colors.orange[500],
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
  },
  browseButtonText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
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

