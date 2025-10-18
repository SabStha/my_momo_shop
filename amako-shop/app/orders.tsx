import React, { useState, useMemo } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity,
  TextInput,
  RefreshControl,
  ActivityIndicator,
  Modal,
  StatusBar,
  Platform,
  Alert,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights } from '../src/ui/tokens';
import { ScreenWithBottomNav } from '../src/components';
import { useBackendOrders } from '../src/hooks/useOrders';
import WriteReviewModal from '../src/components/reviews/WriteReviewModal';
import { client } from '../src/api/client';
import { useQueryClient } from '@tanstack/react-query';
import { useSession } from '../src/session/SessionProvider';

type FilterOption = 'all' | 'pending' | 'confirmed' | 'preparing' | 'ready' | 'out_for_delivery' | 'delivered' | 'cancelled';

export default function OrdersScreen() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedFilter, setSelectedFilter] = useState<FilterOption>('all');
  const [showFilterModal, setShowFilterModal] = useState(false);
  const [showReviewModal, setShowReviewModal] = useState(false);
  const [selectedOrderForReview, setSelectedOrderForReview] = useState<any>(null);
  
  // Get orders from backend API (real-time data)
  const { data: orders = [], isLoading, error, refetch } = useBackendOrders();
  const [refreshing, setRefreshing] = useState(false);
  const queryClient = useQueryClient();
  const { user } = useSession();

  const handleRefresh = async () => {
    setRefreshing(true);
    try {
      await refetch();
    } catch (err) {
      console.error('Failed to refresh orders:', err);
    } finally {
      setRefreshing(false);
    }
  };

  const navigateToMenu = () => {
    router.push('/(tabs)/menu');
  };

  const handleWriteReview = (order: any) => {
    console.log('📝 Opening review modal for order:', order.id);
    setSelectedOrderForReview(order);
    setShowReviewModal(true);
  };

  const handleSubmitReview = async (reviewData: any) => {
    try {
      const response = await client.post('/reviews', {
        rating: reviewData.rating,
        comment: reviewData.comment,
        orderItem: reviewData.orderItem,
        order_id: selectedOrderForReview?.id,
        order_number: selectedOrderForReview?.order_number,
        userId: user?.id,
      });

      if (response.data.success) {
        // Refresh reviews and loyalty
        queryClient.invalidateQueries({ queryKey: ['reviews'] });
        queryClient.invalidateQueries({ queryKey: ['loyalty'] });
        
        setShowReviewModal(false);
        
        const isUpdate = response.data.action === 'updated';
        const pointsAwarded = response.data.points_awarded || 0;
        
        let message = isUpdate 
          ? 'Your review has been updated!' 
          : 'Thank you for your review!';
        
        if (pointsAwarded > 0) {
          message += `\n\n🎁 You earned ${pointsAwarded} Ama Credits!`;
        }
        
        Alert.alert(
          isUpdate ? 'Review Updated! ⭐' : 'Thank You! ⭐',
          message,
          [{ text: 'OK' }]
        );
      }
    } catch (error: any) {
      console.error('❌ Failed to submit review:', error);
      Alert.alert('Error', 'Failed to submit review. Please try again.');
    }
  };

  // Filter options
  const filterOptions: { value: FilterOption; label: string; icon: any }[] = [
    { value: 'all', label: 'All Orders', icon: 'list' },
    { value: 'pending', label: 'Pending', icon: 'time' },
    { value: 'confirmed', label: 'Confirmed', icon: 'checkmark-circle' },
    { value: 'preparing', label: 'Preparing', icon: 'restaurant' },
    { value: 'ready', label: 'Ready', icon: 'checkmark-done' },
    { value: 'out_for_delivery', label: 'Out for Delivery', icon: 'bicycle' },
    { value: 'delivered', label: 'Delivered', icon: 'checkmark-circle' },
    { value: 'cancelled', label: 'Cancelled', icon: 'close-circle' },
  ];

  // Get current filter label
  const currentFilterLabel = filterOptions.find(f => f.value === selectedFilter)?.label || 'All Orders';

  // Filter and search orders
  const filteredOrders = useMemo(() => {
    let filtered = orders;

    // Apply status filter
    if (selectedFilter !== 'all') {
      filtered = filtered.filter(order => order.status === selectedFilter);
    }

    // Apply search filter
    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase().trim();
      filtered = filtered.filter(order => {
        const orderNumber = (order.order_number || `Order #${order.id}`).toLowerCase();
        const status = formatStatus(order.status).toLowerCase();
        const amount = (order.total || order.total_amount || order.grand_total || 0).toString();
        
        return orderNumber.includes(query) || 
               status.includes(query) || 
               amount.includes(query);
      });
    }

    return filtered;
  }, [orders, selectedFilter, searchQuery]);

  const renderEmptyState = () => (
    <View style={styles.emptyStateContainer}>
      <Ionicons name="receipt-outline" size={80} color={colors.gray?.[400] || '#9CA3AF'} />
      <Text style={styles.emptyStateTitle}>No Orders Yet</Text>
      <Text style={styles.emptyStateText}>
        Your order history will appear here once you place your first order.
      </Text>
      <TouchableOpacity 
        style={styles.emptyStateButton}
        onPress={navigateToMenu}
      >
        <Text style={styles.emptyStateButtonText}>Start Shopping</Text>
      </TouchableOpacity>
    </View>
  );

  const formatDate = (dateString: string) => {
    const d = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - d.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0 || diffDays === 1) return 'Today';
    if (diffDays === 2) return 'Yesterday';
    if (diffDays < 7) return `${diffDays - 1} days ago`;
    
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'delivered':
      case 'confirmed':
        return { bg: '#D1FAE5', text: '#065F46' };
      case 'preparing':
      case 'ready':
      case 'out_for_delivery':
        return { bg: '#DBEAFE', text: '#1E40AF' };
      case 'cancelled':
        return { bg: '#FEE2E2', text: '#991B1B' };
      default:
        return { bg: '#FEF3C7', text: '#92400E' }; // pending
    }
  };

  const formatStatus = (status: string) => {
    // Replace underscores with spaces and capitalize each word
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  };

  const renderOrderCard = (order: any) => {
    const statusColors = getStatusColor(order.status);
    
    return (
      <TouchableOpacity
        key={order.id}
        style={styles.orderCard}
        onPress={() => router.push(`/order/${order.id}`)}
      >
        <View style={styles.orderCardHeader}>
          <View style={styles.orderCardIconContainer}>
            <Ionicons name="receipt" size={20} color={colors.white} />
          </View>
          <View style={styles.orderCardInfo}>
            <Text style={styles.orderCardTitle}>
              {order.order_number || `Order #${order.id}`}
            </Text>
            <View style={styles.orderCardDate}>
              <Ionicons name="time-outline" size={14} color="#6B7280" />
              <Text style={styles.orderCardDateText}> {formatDate(order.created_at)}</Text>
            </View>
          </View>
          <View style={styles.orderCardStatus}>
            <Text style={[styles.statusBadge, { 
              backgroundColor: statusColors.bg,
              color: statusColors.text
            }]}>
              {formatStatus(order.status)}
            </Text>
          </View>
        </View>
        
        <View style={styles.orderCardFooter}>
          <Text style={styles.orderCardAmount}>Rs. {(order.total || order.total_amount || order.grand_total || 0).toFixed(2)}</Text>
          <View style={styles.orderCardActions}>
            {order.status === 'delivered' && (
              <TouchableOpacity
                style={styles.reviewButton}
                onPress={(e) => {
                  e.stopPropagation();
                  handleWriteReview(order);
                }}
              >
                <Ionicons name="star" size={14} color="#F59E0B" />
                <Text style={styles.reviewButtonText}>Review</Text>
              </TouchableOpacity>
            )}
            <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  // Show loading state
  if (isLoading && !refreshing) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.container}>
          <View style={styles.header}>
            <TouchableOpacity 
              style={styles.backButton}
              onPress={() => router.back()}
            >
              <Ionicons name="arrow-back" size={24} color={colors.gray?.[800] || '#1F2937'} />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>My Orders</Text>
            <View style={{ width: 40 }} />
          </View>
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={colors.amako?.gold || '#F59E0B'} />
            <Text style={styles.loadingText}>Loading your orders...</Text>
          </View>
        </View>
      </ScreenWithBottomNav>
    );
  }

  // Show error state
  if (error) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.container}>
          <View style={styles.header}>
            <TouchableOpacity 
              style={styles.backButton}
              onPress={() => router.back()}
            >
              <Ionicons name="arrow-back" size={24} color={colors.gray?.[800] || '#1F2937'} />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>My Orders</Text>
            <View style={{ width: 40 }} />
          </View>
          <View style={styles.errorStateContainer}>
            <Ionicons name="alert-circle-outline" size={64} color="#EF4444" />
            <Text style={styles.errorTitle}>Failed to Load Orders</Text>
            <Text style={styles.errorText}>
              {error.message || 'Something went wrong. Please try again.'}
            </Text>
            <TouchableOpacity style={styles.retryButton} onPress={() => refetch()}>
              <Text style={styles.retryButtonText}>Try Again</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScreenWithBottomNav>
    );
  }

  return (
    <ScreenWithBottomNav>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      <ScrollView 
        style={styles.container}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity 
            style={styles.backButton}
            onPress={() => router.back()}
          >
            <Ionicons name="arrow-back" size={24} color={colors.gray?.[800] || '#1F2937'} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>My Orders</Text>
          <View style={{ width: 40 }} />
        </View>

        {/* Search and Filter */}
        <View style={styles.controlsContainer}>
          <View style={styles.searchContainer}>
            <Ionicons name="search" size={20} color="#9CA3AF" style={styles.searchIcon} />
            <TextInput 
              style={styles.searchInput}
              placeholder="Search orders..."
              placeholderTextColor="#9CA3AF"
              value={searchQuery}
              onChangeText={setSearchQuery}
            />
            {searchQuery.length > 0 && (
              <TouchableOpacity onPress={() => setSearchQuery('')}>
                <Ionicons name="close-circle" size={20} color="#9CA3AF" />
              </TouchableOpacity>
            )}
          </View>
          <TouchableOpacity 
            style={styles.filterContainer}
            onPress={() => setShowFilterModal(true)}
          >
            <Text style={styles.filterText}>{currentFilterLabel}</Text>
            <Ionicons name="chevron-down" size={16} color="#6B7280" />
          </TouchableOpacity>
        </View>

        {/* Results count */}
        {(searchQuery || selectedFilter !== 'all') && (
          <View style={styles.resultsContainer}>
            <Text style={styles.resultsText}>
              {filteredOrders.length} {filteredOrders.length === 1 ? 'order' : 'orders'} found
            </Text>
            {(searchQuery || selectedFilter !== 'all') && (
              <TouchableOpacity 
                onPress={() => {
                  setSearchQuery('');
                  setSelectedFilter('all');
                }}
              >
                <Text style={styles.clearFiltersText}>Clear filters</Text>
              </TouchableOpacity>
            )}
          </View>
        )}

        {/* Orders List or Empty State */}
        <View style={styles.content}>
          {filteredOrders.length === 0 ? (
            searchQuery || selectedFilter !== 'all' ? (
              <View style={styles.emptyStateContainer}>
                <Ionicons name="search-outline" size={64} color={colors.gray?.[400] || '#9CA3AF'} />
                <Text style={styles.emptyStateTitle}>No Orders Found</Text>
                <Text style={styles.emptyStateText}>
                  No orders match your search or filter criteria.
                </Text>
                <TouchableOpacity 
                  style={styles.emptyStateButton}
                  onPress={() => {
                    setSearchQuery('');
                    setSelectedFilter('all');
                  }}
                >
                  <Text style={styles.emptyStateButtonText}>Clear Filters</Text>
                </TouchableOpacity>
              </View>
            ) : (
              renderEmptyState()
            )
          ) : (
            filteredOrders.map(renderOrderCard)
          )}
        </View>

        {/* Filter Modal */}
        <Modal
          visible={showFilterModal}
          transparent
          animationType="slide"
          onRequestClose={() => setShowFilterModal(false)}
        >
          <TouchableOpacity 
            style={styles.modalOverlay}
            activeOpacity={1}
            onPress={() => setShowFilterModal(false)}
          >
            <View style={styles.modalContent}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>Filter Orders</Text>
                <TouchableOpacity onPress={() => setShowFilterModal(false)}>
                  <Ionicons name="close" size={24} color="#6B7280" />
                </TouchableOpacity>
              </View>
              
              <ScrollView style={styles.filterOptions}>
                {filterOptions.map((option) => (
                  <TouchableOpacity
                    key={option.value}
                    style={[
                      styles.filterOption,
                      selectedFilter === option.value && styles.filterOptionActive
                    ]}
                    onPress={() => {
                      setSelectedFilter(option.value);
                      setShowFilterModal(false);
                    }}
                  >
                    <View style={styles.filterOptionContent}>
                      <Ionicons 
                        name={option.icon} 
                        size={20} 
                        color={selectedFilter === option.value ? colors.primary?.[600] || '#3B82F6' : '#6B7280'} 
                      />
                      <Text style={[
                        styles.filterOptionText,
                        selectedFilter === option.value && styles.filterOptionTextActive
                      ]}>
                        {option.label}
                      </Text>
                    </View>
                    {selectedFilter === option.value && (
                      <Ionicons name="checkmark" size={20} color={colors.primary?.[600] || '#3B82F6'} />
                    )}
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </View>
          </TouchableOpacity>
        </Modal>

        {/* Write Review Modal */}
        <WriteReviewModal
          visible={showReviewModal}
          onClose={() => setShowReviewModal(false)}
          onSubmit={handleSubmitReview}
        />
      </ScrollView>
    </ScreenWithBottomNav>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingTop: Platform.OS === 'ios' ? 50 : 40,
    paddingBottom: spacing.md,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  backButton: {
    padding: spacing.xs,
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
  },
  controlsContainer: {
    padding: spacing.lg,
    gap: spacing.sm,
    backgroundColor: colors.white,
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
  },
  searchIcon: {
    marginRight: spacing.sm,
  },
  searchInput: {
    flex: 1,
    fontSize: fontSizes.sm,
    color: '#374151',
  },
  filterContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    justifyContent: 'space-between',
  },
  filterText: {
    fontSize: fontSizes.sm,
    color: '#374151',
  },
  content: {
    padding: spacing.lg,
    gap: spacing.md,
  },
  emptyStateContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.xl * 2,
  },
  emptyStateTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginTop: spacing.lg,
    marginBottom: spacing.sm,
  },
  emptyStateText: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: spacing.xl,
    maxWidth: 280,
  },
  emptyStateButton: {
    backgroundColor: colors.amako?.gold || '#F59E0B',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: 8,
  },
  emptyStateButtonText: {
    color: colors.black,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  orderCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: spacing.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  orderCardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: spacing.md,
  },
  orderCardIconContainer: {
    width: 40,
    height: 40,
    backgroundColor: '#3B82F6',
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.md,
  },
  orderCardInfo: {
    flex: 1,
  },
  orderCardTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
    marginBottom: spacing.xs,
  },
  orderCardDate: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  orderCardDateText: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
  },
  orderCardStatus: {
    marginLeft: spacing.sm,
  },
  statusBadge: {
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: 12,
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  orderCardFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: spacing.md,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
  },
  orderCardAmount: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
  },
  orderCardActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  reviewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF3C7',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: 12,
    gap: 4,
  },
  reviewButtonText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
    color: '#F59E0B',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: spacing.xl * 2,
  },
  loadingText: {
    marginTop: spacing.lg,
    fontSize: fontSizes.md,
    color: '#6B7280',
  },
  errorStateContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.xl * 2,
  },
  errorTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginTop: spacing.lg,
    marginBottom: spacing.sm,
  },
  errorText: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: spacing.xl,
    maxWidth: 280,
  },
  retryButton: {
    backgroundColor: colors.amako?.gold || '#F59E0B',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: 8,
  },
  retryButtonText: {
    color: colors.black,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  resultsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
    backgroundColor: '#F3F4F6',
  },
  resultsText: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
  },
  clearFiltersText: {
    fontSize: fontSizes.sm,
    color: colors.primary?.[600] || '#3B82F6',
    fontWeight: fontWeights.medium,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: colors.white,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  modalTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
  },
  filterOptions: {
    padding: spacing.md,
  },
  filterOption: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: spacing.md,
    borderRadius: 8,
    marginBottom: spacing.sm,
    backgroundColor: '#F9FAFB',
  },
  filterOptionActive: {
    backgroundColor: '#EEF2FF',
    borderWidth: 1,
    borderColor: colors.primary?.[600] || '#3B82F6',
  },
  filterOptionContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  filterOptionText: {
    fontSize: fontSizes.md,
    color: '#374151',
  },
  filterOptionTextActive: {
    color: colors.primary?.[600] || '#3B82F6',
    fontWeight: fontWeights.semibold,
  },
});

