import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../src/ui/tokens';
import { ScreenWithBottomNav } from '../src/components';
import { useBranches } from '../src/api/branch-hooks';

interface Branch {
  id: number;
  name: string;
  address: string;
  phone: string;
  latitude?: number;
  longitude?: number;
  delivery_fee?: number;
  delivery_radius_km?: number;
  distance?: number; // Calculated distance if location available
}

export default function BranchSelectionScreen() {
  const params = useLocalSearchParams();
  const { data: branches, isLoading, error } = useBranches();
  const [selectedBranch, setSelectedBranch] = useState<number | null>(null);
  const [userLocation, setUserLocation] = useState<{ lat: number; lng: number } | null>(null);

  const handleSelectBranch = (branch: Branch) => {
    setSelectedBranch(branch.id);
  };

  const handleContinue = () => {
    if (!selectedBranch) {
      Alert.alert('Select Branch', 'Please select a delivery branch to continue.');
      return;
    }

    const branch = branches?.find((b: Branch) => b.id === selectedBranch);
    
    // Navigate to payment with branch information
    router.push({
      pathname: '/payment',
      params: {
        branchId: selectedBranch.toString(),
        branchName: branch?.name || '',
        deliveryFee: branch?.delivery_fee?.toString() || '0',
      },
    });
  };

  const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number) => {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  };

  const renderBranchCard = (branch: Branch, index: number) => {
    const isSelected = selectedBranch === branch.id;
    const distance = userLocation && branch.latitude && branch.longitude
      ? calculateDistance(userLocation.lat, userLocation.lng, branch.latitude, branch.longitude)
      : null;

    const isRecommended = index === 0 && distance && distance < 5;

    return (
      <TouchableOpacity
        key={branch.id}
        style={[
          styles.branchCard,
          isSelected ? styles.branchCardSelected : null,
          isRecommended ? styles.branchCardRecommended : null,
        ]}
        onPress={() => handleSelectBranch(branch)}
        activeOpacity={0.7}
      >
        {isRecommended && (
          <View style={styles.recommendedBadge}>
            <Ionicons name="star" size={12} color={colors.white} />
            <Text style={styles.recommendedText}>Recommended</Text>
          </View>
        )}

        <View style={styles.branchCardHeader}>
          <View style={styles.branchIconContainer}>
            <Ionicons 
              name="storefront" 
              size={24} 
              color={isSelected ? colors.white : colors.brand.primary} 
            />
          </View>
          <View style={styles.branchInfo}>
            <Text style={[styles.branchName, isSelected && styles.branchNameSelected]}>
              {branch.name}
            </Text>
            <Text style={[styles.branchAddress, isSelected && styles.branchAddressSelected]}>
              {branch.address}
            </Text>
          </View>
          {isSelected && (
            <Ionicons name="checkmark-circle" size={24} color={colors.white} />
          )}
        </View>

        <View style={styles.branchDetails}>
          {distance && (
            <View style={styles.branchDetailItem}>
              <Ionicons 
                name="navigate" 
                size={16} 
                color={isSelected ? colors.white : colors.gray[600]} 
              />
              <Text style={[styles.branchDetailText, isSelected && styles.branchDetailTextSelected]}>
                {distance.toFixed(1)} km away
              </Text>
            </View>
          )}
          
          <View style={styles.branchDetailItem}>
            <Ionicons 
              name="cash-outline" 
              size={16} 
              color={isSelected ? colors.white : colors.gray[600]} 
            />
            <Text style={[styles.branchDetailText, isSelected && styles.branchDetailTextSelected]}>
              Delivery: Rs. {branch.delivery_fee || 0}
            </Text>
          </View>

          <View style={styles.branchDetailItem}>
            <Ionicons 
              name="time-outline" 
              size={16} 
              color={isSelected ? colors.white : colors.gray[600]} 
            />
            <Text style={[styles.branchDetailText, isSelected && styles.branchDetailTextSelected]}>
              {distance && distance < 3 ? '20-30' : distance && distance < 5 ? '30-40' : '40-50'} mins
            </Text>
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (isLoading) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.container}>
          <View style={styles.header}>
            <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
              <Ionicons name="arrow-back" size={24} color={colors.gray[800]} />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>Select Branch</Text>
            <View style={{ width: 40 }} />
          </View>
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={colors.brand.primary} />
            <Text style={styles.loadingText}>Loading branches...</Text>
          </View>
        </View>
      </ScreenWithBottomNav>
    );
  }

  if (error) {
    return (
      <ScreenWithBottomNav>
        <View style={styles.container}>
          <View style={styles.header}>
            <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
              <Ionicons name="arrow-back" size={24} color={colors.gray[800]} />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>Select Branch</Text>
            <View style={{ width: 40 }} />
          </View>
          <View style={styles.errorContainer}>
            <Ionicons name="alert-circle" size={64} color={colors.error[500]} />
            <Text style={styles.errorTitle}>Unable to Load Branches</Text>
            <Text style={styles.errorText}>Please check your connection and try again.</Text>
            <TouchableOpacity style={styles.retryButton} onPress={() => window.location.reload()}>
              <Text style={styles.retryButtonText}>Retry</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScreenWithBottomNav>
    );
  }

  return (
    <ScreenWithBottomNav>
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
            <Ionicons name="arrow-back" size={24} color={colors.gray[800]} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Select Branch</Text>
          <View style={{ width: 40 }} />
        </View>

        <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
          {/* Info Banner */}
          <View style={styles.infoBanner}>
            <Ionicons name="information-circle" size={24} color={colors.blue[600]} />
            <View style={styles.infoBannerText}>
              <Text style={styles.infoBannerTitle}>Why select a branch?</Text>
              <Text style={styles.infoBannerDescription}>
                Choosing the nearest branch ensures faster delivery and may reduce delivery fees.
              </Text>
            </View>
          </View>

          {/* Branches List */}
          <View style={styles.branchesContainer}>
            <Text style={styles.sectionTitle}>
              Available Branches ({branches?.length || 0})
            </Text>
            
            {branches && branches.length > 0 ? (
              branches.map((branch: Branch, index: number) => renderBranchCard(branch, index))
            ) : (
              <View style={styles.emptyContainer}>
                <Ionicons name="storefront-outline" size={64} color={colors.gray[400]} />
                <Text style={styles.emptyText}>No branches available</Text>
              </View>
            )}
          </View>

          {/* Help Section */}
          <View style={styles.helpSection}>
            <Text style={styles.helpTitle}>Need Help?</Text>
            <View style={styles.helpItem}>
              <Ionicons name="call-outline" size={20} color={colors.gray[700]} />
              <Text style={styles.helpText}>Call us for assistance</Text>
            </View>
          </View>
        </ScrollView>

        {/* Continue Button */}
        {selectedBranch && (
          <View style={styles.footer}>
            <TouchableOpacity
              style={styles.continueButton}
              onPress={handleContinue}
              activeOpacity={0.8}
            >
              <Text style={styles.continueButtonText}>Continue to Payment</Text>
              <Ionicons name="arrow-forward" size={20} color={colors.white} />
            </TouchableOpacity>
          </View>
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
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  backButton: {
    padding: spacing.xs,
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  scrollView: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: spacing.md,
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
  },
  errorTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginTop: spacing.lg,
    marginBottom: spacing.sm,
  },
  errorText: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.xl,
  },
  retryButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
  },
  retryButtonText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  infoBanner: {
    flexDirection: 'row',
    backgroundColor: colors.blue[50],
    padding: spacing.lg,
    marginHorizontal: spacing.lg,
    marginTop: spacing.lg,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.blue[200],
  },
  infoBannerText: {
    flex: 1,
    marginLeft: spacing.md,
  },
  infoBannerTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.blue[900],
    marginBottom: spacing.xs,
  },
  infoBannerDescription: {
    fontSize: fontSizes.sm,
    color: colors.blue[700],
    lineHeight: 20,
  },
  branchesContainer: {
    padding: spacing.lg,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.md,
  },
  branchCard: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.md,
    borderWidth: 2,
    borderColor: colors.gray[200],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
    position: 'relative',
  },
  branchCardSelected: {
    backgroundColor: colors.brand.primary,
    borderColor: colors.brand.primary,
    shadowOpacity: 0.15,
    elevation: 4,
  },
  branchCardRecommended: {
    borderColor: colors.green[500],
  },
  recommendedBadge: {
    position: 'absolute',
    top: -8,
    right: spacing.md,
    backgroundColor: colors.green[500],
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    gap: 4,
    zIndex: 1,
  },
  recommendedText: {
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
  },
  branchCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  branchIconContainer: {
    width: 48,
    height: 48,
    borderRadius: radius.lg,
    backgroundColor: colors.primary[100],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  branchInfo: {
    flex: 1,
  },
  branchName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  branchNameSelected: {
    color: colors.white,
  },
  branchAddress: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    lineHeight: 18,
  },
  branchAddressSelected: {
    color: colors.white,
  },
  branchDetails: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
  branchDetailItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  branchDetailText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  branchDetailTextSelected: {
    color: colors.white,
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: spacing.xl * 2,
  },
  emptyText: {
    fontSize: fontSizes.md,
    color: colors.gray[500],
    marginTop: spacing.md,
  },
  helpSection: {
    backgroundColor: colors.white,
    marginHorizontal: spacing.lg,
    marginBottom: spacing.xl,
    padding: spacing.lg,
    borderRadius: radius.lg,
  },
  helpTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.md,
  },
  helpItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  helpText: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
  },
  footer: {
    backgroundColor: colors.white,
    padding: spacing.lg,
    borderTopWidth: 1,
    borderTopColor: colors.gray[200],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  continueButton: {
    backgroundColor: colors.brand.primary,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: colors.brand.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  continueButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
});

