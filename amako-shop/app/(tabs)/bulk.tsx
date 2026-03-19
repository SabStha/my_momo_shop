import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  Image,
  Pressable,
  Dimensions,
  RefreshControl,
  Alert,
  FlatList,
  Animated,
  ActivityIndicator,
} from 'react-native';

// Create animated ScrollView for native scroll tracking
const AnimatedScrollView = Animated.createAnimatedComponent(ScrollView);
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui/tokens';
import { useBulkData, BulkPackage } from '../../src/api/bulk-hooks';
import { useCartSyncStore } from '../../src/state/cart-sync';
import { LinearGradient } from 'expo-linear-gradient';
import CustomBuilderModal from '../../src/components/bulk/CustomBuilderModal';
import LoadingSpinner from '../../src/components/LoadingSpinner';

const { width: screenWidth } = Dimensions.get('window');

export default function BulkScreen() {
  const [orderType, setOrderType] = useState<'cooked' | 'frozen'>('cooked');
  const [refreshing, setRefreshing] = useState(false);
  const [isPulling, setIsPulling] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;
  const [showCustomBuilder, setShowCustomBuilder] = useState(false);
  const [selectedPackage, setSelectedPackage] = useState<BulkPackage | null>(null);
  const [imageLoadingStates, setImageLoadingStates] = useState<Record<string, boolean>>({});
  
  const addToCart = useCartSyncStore((state) => state.addItem);
  
  // Get dynamic bulk data from API
  const { data: bulkData, isLoading, error, refetch } = useBulkData();
  
  // Debug: Log bulk data when it loads AND prefetch images
  useEffect(() => {
    if (bulkData) {
      console.log('📦 [BULK DATA] ========================================');
      console.log('📦 [BULK DATA] Full bulk data received:');
      console.log('📦 [BULK DATA]', JSON.stringify(bulkData, null, 2));
      console.log('📦 [BULK DATA] Cooked packages:', Object.keys(bulkData?.packages?.cooked || {}));
      console.log('📦 [BULK DATA] Frozen packages:', Object.keys(bulkData?.packages?.frozen || {}));
      console.log('📦 [BULK DATA] ========================================');
      
      // Prefetch images for faster loading
      const allPackages = [
        ...Object.values(bulkData?.packages?.cooked || {}),
        ...Object.values(bulkData?.packages?.frozen || {})
      ];
      
      console.log('🚀 [PREFETCH] Starting to prefetch', allPackages.length, 'images...');
      allPackages.forEach((pkg: BulkPackage) => {
        const imageUrl = getPackageImage(pkg);
        // Prefetch the image
        Image.prefetch(imageUrl)
          .then(() => {
            console.log('✅ [PREFETCH] Cached:', pkg.name);
          })
          .catch(() => {
            console.log('⚠️ [PREFETCH] Failed:', pkg.name);
          });
      });
    }
    if (error) {
      console.error('📦 [BULK ERROR] Failed to load bulk data:', error);
    }
  }, [bulkData, error]);

  // Track pulling state
  useEffect(() => {
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

  const handleSelectPackage = (packageKey: string, packageData: BulkPackage) => {
    const cartItem = {
      itemId: `bulk-${packageData.id}`,
      name: packageData.name,
      unitBasePrice: { currency: 'NPR' as const, amount: Number(packageData.bulk_price) || Number(packageData.total_price) },
      qty: 1,
      imageUrl: packageData.image ? `/storage/${packageData.image}` : undefined,
    };

    addToCart(cartItem, (payload) => {
      (global as any).openCartAddedSheet?.(payload);
    });
  };

  const handleCustomizePackage = (packageKey: string, packageData: BulkPackage) => {
    setSelectedPackage(packageData);
    setShowCustomBuilder(true);
  };

  const getPackageIcon = (packageName: string) => {
    const name = packageName.toLowerCase();
    if (name.includes('family')) return '👥';
    if (name.includes('office')) return '💼';
    if (name.includes('party')) return '🎉';
    if (name.includes('couple')) return '💕';
    if (name.includes('kids')) return '🧸';
    if (name.includes('event') || name.includes('bulk')) return '📦';
    return '🍽️';
  };

  const getPackageImage = (packageData: BulkPackage) => {
    console.log('📦 [IMAGE DEBUG] Getting image for:', packageData.name);
    
    // First, try to use the actual image from backend
    if (packageData.image) {
      // Check if it's a full URL or just a path
      if (packageData.image.startsWith('http')) {
        console.log('📦 [IMAGE DEBUG] Using full URL:', packageData.image);
        return packageData.image;
      }
      // If it's a relative path, construct the full URL with optimization
      // Add resize parameters to load smaller, faster images
      const fullUrl = `https://amakomomo.com/storage/${packageData.image}`;
      console.log('📦 [IMAGE DEBUG] Using URL:', fullUrl);
      return fullUrl;
    }
    
    // Fallback: Return optimized placeholder images (smaller size = faster load)
    console.log('📦 [IMAGE DEBUG] Using fallback placeholder');
    const key = packageData.package_key.toLowerCase();
    // Using smaller image sizes (400x300 instead of 800x600) for faster loading
    if (key.includes('family')) return 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop&q=80';
    if (key.includes('office')) return 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&h=300&fit=crop&q=80';
    if (key.includes('party')) return 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=400&h=300&fit=crop&q=80';
    if (key.includes('couple')) return 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=400&h=300&fit=crop&q=80';
    if (key.includes('kids')) return 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop&q=80';
    return 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400&h=300&fit=crop&q=80';
  };

  const getItemIcon = (itemName: string) => {
    const name = itemName.toLowerCase();
    if (name.includes('momo')) return '🥟';
    if (name.includes('side')) return '🍽️';
    if (name.includes('drink') || name.includes('beverage')) return '🥤';
    if (name.includes('sauce')) return '🌶️';
    if (name.includes('delivery')) return '🚚';
    return '🍽️';
  };

  const renderPackageCard = ({ item: packageData }: { item: BulkPackage }) => {
    const imageUrl = getPackageImage(packageData);
    const isImageLoading = imageLoadingStates[packageData.package_key] !== false;
    
    return (
    <View style={styles.packageSection}>
      {/* Loading indicator */}
      {isImageLoading && (
        <View style={styles.imageLoadingOverlay}>
          <ActivityIndicator size="large" color="#6E0D25" />
          <Text style={styles.imageLoadingText}>Loading image...</Text>
        </View>
      )}
      
      <Image 
        source={{ uri: imageUrl }}
        style={styles.packageSectionBackground}
        resizeMode="cover"
        // Enable progressive loading for faster perceived load time
        progressiveRenderingEnabled={true}
        // Cache the image for faster subsequent loads
        defaultSource={require('../../assets/icon.png')}
        onLoadStart={() => {
          console.log('⏳ [IMAGE] Loading started:', packageData.name);
          setImageLoadingStates(prev => ({ ...prev, [packageData.package_key]: true }));
        }}
        onError={(error) => {
          console.error('❌ [IMAGE ERROR] Failed for:', packageData.name);
          console.error('❌ [IMAGE ERROR] URL:', imageUrl);
          setImageLoadingStates(prev => ({ ...prev, [packageData.package_key]: false }));
        }}
        onLoad={() => {
          console.log('✅ [IMAGE] Loaded successfully:', packageData.name);
          setImageLoadingStates(prev => ({ ...prev, [packageData.package_key]: false }));
        }}
      />
      <View style={styles.packageSectionOverlay}>
        <View style={styles.packageCard}>
      {/* Package Header */}
      <View style={styles.packageHeader}>
        <View style={styles.packageIcon}>
          <Text style={styles.packageIconText}>{getPackageIcon(packageData.name)}</Text>
        </View>
        <Text style={styles.packageName}>{packageData.name}</Text>
        <Text style={styles.packageDescription}>{packageData.description}</Text>
      </View>

      {/* Item Breakdown */}
      <View style={styles.itemBreakdown}>
        {packageData.items && packageData.items.length > 0 ? (
          packageData.items.map((item, index) => (
            <View key={index} style={styles.itemCard}>
              <Text style={styles.itemIcon}>{getItemIcon(item.name)}</Text>
              <Text style={styles.itemName}>{item.name}</Text>
              <Text style={styles.itemQuantity}>{item.quantity || 1} pcs</Text>
            </View>
          ))
        ) : (
          <View style={styles.itemCard}>
            <Text style={styles.itemIcon}>🍽️</Text>
            <Text style={styles.itemName}>Package Items</Text>
            <Text style={styles.itemQuantity}>See Details</Text>
          </View>
        )}
      </View>

      {/* Offer Card */}
      <View style={styles.offerCard}>
        <View style={styles.bestSellerBadge}>
          <Text style={styles.bestSellerText}>🔥 Best Seller</Text>
        </View>
        
        <Text style={styles.dealTitle}>🎉 {packageData.deal_title || 'Package Deal'}</Text>
        
        <View style={styles.valueProposition}>
          <View style={styles.valueItem}>
            <Text style={styles.valueIcon}>✅</Text>
            <Text style={styles.valueText}>{packageData.feeds_people || 'Feeds 8–10 people'}</Text>
          </View>
          <View style={styles.valueItem}>
            <Text style={styles.valueIcon}>💰</Text>
            <Text style={styles.valueText}>{packageData.savings_description || 'Save Rs. 250+ vs buying individually'}</Text>
          </View>
        </View>

        <View style={styles.priceSection}>
          {packageData.bulk_price && Number(packageData.total_price) > Number(packageData.bulk_price) && (
            <Text style={styles.originalPrice}>
              Original Price: Rs. {Number(packageData.total_price).toFixed(2)}
            </Text>
          )}
          <Text style={styles.bulkPrice}>
            Rs. {(Number(packageData.bulk_price) || Number(packageData.total_price)).toFixed(2)}
          </Text>
        </View>

        <Text style={styles.deliveryNote}>
          🕒 {packageData.delivery_note || 'Order before 2PM for same-day delivery'}
        </Text>

        <View style={styles.actionButtons}>
          <Pressable 
            style={styles.orderButton}
            onPress={() => handleSelectPackage(packageData.package_key, packageData)}
          >
            <Text style={styles.orderButtonText}>🛒 Order the Party Pack Now</Text>
          </Pressable>
          <Pressable 
            style={styles.customizeButton}
            onPress={() => handleCustomizePackage(packageData.package_key, packageData)}
          >
            <Text style={styles.customizeButtonText}>+ Customize this pack</Text>
          </Pressable>
        </View>
      </View>
        </View>
      </View>
    </View>
    );
  };

  const renderCustomOrderSection = () => (
    <View style={styles.customOrderSection}>
      <View style={styles.customOrderHeader}>
        <Text style={styles.customOrderTitle}>Build Your Own Custom Order</Text>
        <Text style={styles.customOrderSubtitle}>
          Create the perfect bulk order with your favorite combinations
        </Text>
      </View>
      
      <View style={styles.customOrderContent}>
        <View style={styles.customOrderFeatures}>
          <View style={styles.customFeature}>
            <Text style={styles.customFeatureIcon}>🎯</Text>
            <Text style={styles.customFeatureText}>Choose exactly what you want</Text>
          </View>
          <View style={styles.customFeature}>
            <Text style={styles.customFeatureIcon}>💵</Text>
            <Text style={styles.customFeatureText}>Best prices for bulk orders</Text>
          </View>
          <View style={styles.customFeature}>
            <Text style={styles.customFeatureIcon}>📅</Text>
            <Text style={styles.customFeatureText}>Flexible delivery scheduling</Text>
          </View>
        </View>
        
        <Pressable 
          style={styles.customOrderButton}
          onPress={() => setShowCustomBuilder(true)}
        >
          <Text style={styles.customOrderButtonText}>Start Building Your Order</Text>
          <Text style={styles.customOrderButtonIcon}>→</Text>
        </Pressable>
      </View>
    </View>
  );

  const renderWhyChooseSection = () => (
    <View style={styles.whyChooseSection}>
      <Text style={styles.whyChooseTitle}>✨ Why Choose AmaKo Bulk?</Text>
      <Text style={styles.whyChooseSubtitle}>
        When you're feeding teams, events, or families — trust a momo brand that delivers more than just food.
      </Text>

      <View style={styles.featuresList}>
        {[
          {
            icon: '✅',
            title: 'Uncompromising Hygiene & Freshness',
            description: '🧼 Real ingredients. Centralized prep. Daily QC. Zero compromise.',
            color: '#10B981'
          },
          {
            icon: '🐶',
            title: 'Purpose-Driven: We Feed Dogs Too',
            description: 'Part of every bulk order funds our Do One Good (DOG) mission. Eat well. Do good.',
            color: '#F59E0B'
          },
          {
            icon: '⏱️',
            title: 'On-Time or It\'s Free',
            description: 'We respect your time — so we guarantee it. Delay? You don\'t pay.',
            color: '#3B82F6'
          },
          {
            icon: '📦',
            title: 'Bulk Without the B.S.',
            description: 'Flat pricing. Centralized packaging. No hidden charges. Big orders, simplified.',
            color: '#8B5CF6'
          },
          {
            icon: '👨‍🍳',
            title: 'Chef-Crafted, Event-Ready',
            description: 'AmaKo Bulk is cooked by trained pros, not your average kitchen team. Consistency. Quantity. Quality — scaled.',
            color: '#EF4444'
          }
        ].map((feature, index) => (
          <View key={index} style={styles.featureItem}>
            <View style={[styles.featureIcon, { backgroundColor: feature.color }]}>
              <Text style={styles.featureIconText}>{feature.icon}</Text>
            </View>
            <View style={styles.featureContent}>
              <Text style={styles.featureTitle}>{feature.title}</Text>
              <Text style={styles.featureDescription}>{feature.description}</Text>
            </View>
          </View>
        ))}
      </View>
    </View>
  );

  const currentPackages = orderType === 'cooked' 
    ? Object.values(bulkData?.packages.cooked || {})
    : Object.values(bulkData?.packages.frozen || {});

  if (isLoading && !bulkData) {
    return (
      <View style={styles.loadingContainer}>
        <LoadingSpinner size="large" text="Loading bulk packages..." />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <AnimatedScrollView 
        style={styles.scrollView} 
        showsVerticalScrollIndicator={false}
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
      >
      {/* Hero Section */}
      <View style={styles.heroSection}>
        <Image 
          source={{ uri: 'https://via.placeholder.com/400x300/6E0D25/FFFFFF?text=AmaKo+Party+Pack' }}
          style={styles.heroImage}
        />
        <LinearGradient
          colors={['rgba(0,0,0,0.4)', 'rgba(0,0,0,0.6)']}
          style={styles.heroOverlay}
        />
        <View style={styles.heroContent}>
          <View style={styles.heroTitleContainer}>
            <Text style={styles.heroTitle}>AmaKo Party Pack</Text>
            <Text style={styles.heroSubtitle}>Perfectly portioned for 8–10 guests</Text>
            <Text style={styles.heroDescription}>Includes momos, sides, sauces & sealed delivery</Text>
          </View>
          <View style={styles.heroBottom}>
            <Pressable 
              style={styles.heroCTA}
              onPress={() => setShowCustomBuilder(true)}
            >
              <Text style={styles.heroCTAText}>🎉 Customize & Order Now</Text>
            </Pressable>
            <View style={styles.trustIndicators}>
              <View style={styles.trustItem}>
                <MCI name="star" size={12} color="#FBBF24" />
                <Text style={styles.trustText} numberOfLines={1}>Dynamic Rating</Text>
              </View>
              <View style={styles.trustItem}>
                <MCI name="check-circle" size={12} color="#10B981" />
                <Text style={styles.trustText} numberOfLines={1}>100% Satisfaction</Text>
              </View>
              <View style={styles.trustItem}>
                <MCI name="clock-outline" size={12} color="#3B82F6" />
                <Text style={styles.trustText} numberOfLines={1}>Cancel free 12hrs</Text>
              </View>
            </View>
          </View>
        </View>
      </View>

      {/* Toggle Switch */}
      <View style={styles.toggleContainer}>
        <View style={styles.toggleWrapper}>
          <Pressable
            style={[
              styles.toggleButton,
              orderType === 'cooked' && styles.toggleButtonActive
            ]}
            onPress={() => setOrderType('cooked')}
          >
            <Text style={styles.toggleIcon}>🔥</Text>
            <Text style={[
              styles.toggleText,
              orderType === 'cooked' && styles.toggleTextActive
            ]}>Hot</Text>
          </Pressable>
          <Pressable
            style={[
              styles.toggleButton,
              orderType === 'frozen' && styles.toggleButtonActive
            ]}
            onPress={() => setOrderType('frozen')}
          >
            <Text style={styles.toggleIcon}>❄️</Text>
            <Text style={[
              styles.toggleText,
              orderType === 'frozen' && styles.toggleTextActive
            ]}>Frozen</Text>
          </Pressable>
        </View>
      </View>

      {/* Package Section */}
      <View style={styles.packageSectionContainer}>
        <Text style={styles.sectionTitle}>
          {orderType === 'cooked' ? '🔥 Hot & Ready Packages' : '❄️ Frozen & Ready Packages'}
        </Text>
        <Text style={styles.sectionSubtitle}>
          {orderType === 'cooked' ? 'Perfect for immediate consumption' : 'Perfect for stocking up your freezer'}
        </Text>

        <FlatList
          data={currentPackages}
          renderItem={renderPackageCard}
          keyExtractor={(item) => item.package_key}
          scrollEnabled={false}
          showsVerticalScrollIndicator={false}
        />
      </View>

      {/* Build Your Own Custom Order Section */}
      {renderCustomOrderSection()}

      {/* Why Choose Section */}
      {renderWhyChooseSection()}

      {/* Custom Builder Modal */}
      <CustomBuilderModal
        visible={showCustomBuilder}
        onClose={() => {
          setShowCustomBuilder(false);
          setSelectedPackage(null);
        }}
        initialPackage={selectedPackage}
      />
    </AnimatedScrollView>
    
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
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  scrollView: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
  },
  loadingText: {
    fontSize: 16,
    color: '#6E0D25',
  },
  
  // Hero Section
  heroSection: {
    height: 250,
    position: 'relative',
    marginBottom: spacing.md,
  },
  heroImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  heroOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  heroContent: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'space-between',
    padding: spacing.lg,
  },
  heroTitleContainer: {
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    borderRadius: 16,
    padding: spacing.md,
    maxWidth: screenWidth * 0.8,
  },
  heroTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 2,
  },
  heroSubtitle: {
    fontSize: 12,
    fontWeight: '600',
    color: '#FFFFFF',
    marginBottom: 2,
  },
  heroDescription: {
    fontSize: 10,
    color: '#FFFFFF',
    opacity: 0.95,
  },
  heroBottom: {
    gap: spacing.md,
  },
  heroCTA: {
    backgroundColor: '#6E0D25',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    borderRadius: 12,
    alignSelf: 'flex-start',
  },
  heroCTAText: {
    color: '#FFFFFF',
    fontWeight: 'bold',
    fontSize: 14,
  },
  trustIndicators: {
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    borderRadius: 12,
    padding: spacing.sm,
    flexDirection: 'row',
    gap: spacing.xs,
    maxWidth: screenWidth * 0.9,
    justifyContent: 'space-between',
  },
  trustItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    flex: 1,
    minWidth: 0,
  },
  trustText: {
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: '600',
    flexShrink: 1,
  },

  // Toggle Switch
  toggleContainer: {
    alignItems: 'center',
    marginBottom: spacing.md,
    paddingHorizontal: spacing.md,
  },
  toggleWrapper: {
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    borderRadius: 12,
    padding: 4,
    flexDirection: 'row',
    width: '100%',
    maxWidth: 400,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  toggleButton: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.sm,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: spacing.xs,
    minHeight: 40,
  },
  toggleButtonActive: {
    backgroundColor: '#6E0D25',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  toggleIcon: {
    fontSize: 16,
  },
  toggleText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6E0D25',
  },
  toggleTextActive: {
    color: '#FFFFFF',
  },

  // Package Section Container
  packageSectionContainer: {
    paddingHorizontal: spacing.md,
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#6E0D25',
    textAlign: 'center',
    marginBottom: 2,
  },
  sectionSubtitle: {
    fontSize: 11,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: spacing.md,
  },

  // Package Section with Background
  packageSection: {
    marginBottom: spacing.md,
    borderRadius: 12,
    overflow: 'hidden',
    position: 'relative',
    minHeight: 200,
  },
  packageSectionBackground: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: '100%',
    height: '100%',
  },
  packageSectionOverlay: {
    position: 'relative',
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    padding: spacing.md,
    minHeight: 200,
    justifyContent: 'center',
  },

  // Package Cards
  packageCard: {
    backgroundColor: 'transparent',
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
    elevation: 6,
    overflow: 'hidden',
    minHeight: 96,
  },
  packageHeader: {
    alignItems: 'center',
    padding: spacing.xs,
    backgroundColor: 'transparent',
  },
  packageIcon: {
    width: 19,
    height: 19,
    borderRadius: 10,
    backgroundColor: '#6E0D25',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
    shadowColor: '#6E0D25',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 3,
  },
  packageIconText: {
    fontSize: 8,
  },
  packageName: {
    fontSize: 7,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 1,
    textAlign: 'center',
    textShadowColor: 'rgba(0, 0, 0, 0.8)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },
  packageDescription: {
    fontSize: 6,
    color: '#FFFFFF',
    textAlign: 'center',
    lineHeight: 7,
    textShadowColor: 'rgba(0, 0, 0, 0.8)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },

  // Item Breakdown
  itemBreakdown: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: spacing.xs,
    justifyContent: 'space-between',
    backgroundColor: 'transparent',
  },
  itemCard: {
    backgroundColor: 'rgba(243, 244, 246, 0.3)',
    borderRadius: 6,
    padding: spacing.xs,
    alignItems: 'center',
    width: '48%',
    marginBottom: spacing.xs,
  },
  itemIcon: {
    fontSize: 8,
    marginBottom: 1,
  },
  itemName: {
    fontSize: 5,
    fontWeight: '600',
    color: '#FFFFFF',
    textAlign: 'center',
    marginBottom: 1,
    textShadowColor: 'rgba(0, 0, 0, 0.8)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },
  itemQuantity: {
    fontSize: 6,
    fontWeight: 'bold',
    color: '#FFFFFF',
    textShadowColor: 'rgba(0, 0, 0, 0.8)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },

  // Offer Card
  offerCard: {
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.3)',
    borderRadius: 16,
    padding: spacing.md,
    margin: spacing.md,
    position: 'relative',
  },
  bestSellerBadge: {
    position: 'absolute',
    top: 0,
    right: 0,
    backgroundColor: '#6E0D25',
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderBottomLeftRadius: 8,
  },
  bestSellerText: {
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: 'bold',
    backgroundColor: 'rgba(0,0,0,0.8)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    textShadowColor: 'rgba(0,0,0,0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.2)',
  },
  dealTitle: {
    fontSize: 17,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: spacing.md,
    marginTop: spacing.sm,
    backgroundColor: 'rgba(0,0,0,0.75)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.lg,
    textShadowColor: 'rgba(0, 0, 0, 0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 3,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.15)',
    alignSelf: 'flex-start',
  },
  valueProposition: {
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  valueItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  valueIcon: {
    fontSize: 14,
  },
  valueText: {
    fontSize: 13,
    color: '#FFFFFF',
    backgroundColor: 'rgba(0,0,0,0.7)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    textShadowColor: 'rgba(0, 0, 0, 0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
    flex: 1,
  },
  priceSection: {
    gap: spacing.xs,
    marginBottom: spacing.md,
  },
  originalPrice: {
    fontSize: 10,
    color: '#FFFFFF',
    textDecorationLine: 'line-through',
    textShadowColor: 'rgba(0, 0, 0, 0.8)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },
  bulkPrice: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFFFFF',
    textShadowColor: 'rgba(0, 0, 0, 0.8)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },
  deliveryNote: {
    fontSize: 11,
    color: '#FFFFFF',
    marginBottom: spacing.md,
    backgroundColor: 'rgba(0,0,0,0.75)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.md,
    textShadowColor: 'rgba(0, 0, 0, 0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
    alignSelf: 'flex-start',
  },
  actionButtons: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  orderButton: {
    backgroundColor: '#6E0D25',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.sm,
    borderRadius: 6,
    alignItems: 'center',
    flex: 1,
  },
  orderButtonText: {
    color: '#FFFFFF',
    fontWeight: 'bold',
    fontSize: 10,
  },
  customizeButton: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#D1D5DB',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.sm,
    borderRadius: 6,
    alignItems: 'center',
    flex: 1,
  },
  customizeButtonText: {
    color: '#6B7280',
    fontSize: 9,
    fontWeight: '600',
  },

  // Custom Order Section
  customOrderSection: {
    backgroundColor: '#F8F9FA',
    borderRadius: 10,
    padding: spacing.md,
    marginHorizontal: spacing.md,
    marginBottom: spacing.md,
  },
  customOrderHeader: {
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  customOrderTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#6E0D25',
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  customOrderSubtitle: {
    fontSize: 11,
    color: '#6B7280',
    textAlign: 'center',
    lineHeight: 14,
  },
  customOrderContent: {
    gap: spacing.md,
  },
  customOrderFeatures: {
    gap: spacing.sm,
  },
  customFeature: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  customFeatureIcon: {
    fontSize: 16,
  },
  customFeatureText: {
    fontSize: 11,
    color: '#374151',
    flex: 1,
  },
  customOrderButton: {
    backgroundColor: '#6E0D25',
    borderRadius: 8,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    shadowColor: '#6E0D25',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 3,
  },
  customOrderButtonText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: 'bold',
  },
  customOrderButtonIcon: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: 'bold',
  },

  // Why Choose Section
  whyChooseSection: {
    backgroundColor: '#FDF7F2',
    borderRadius: 8,
    padding: spacing.sm,
    margin: spacing.sm,
    borderWidth: 1,
    borderColor: '#F5E6D3',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  whyChooseTitle: {
    fontSize: 10,
    fontWeight: 'bold',
    color: '#6E0D25',
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  whyChooseSubtitle: {
    fontSize: 7,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: spacing.sm,
    lineHeight: 8,
  },
  featuresList: {
    gap: spacing.sm,
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.xs,
    backgroundColor: '#FFFFFF',
    padding: spacing.sm,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#F3F4F6',
  },
  featureIcon: {
    width: 19,
    height: 19,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 2,
    elevation: 2,
  },
  featureIconText: {
    fontSize: 10,
  },
  featureContent: {
    flex: 1,
  },
  featureTitle: {
    fontSize: 8,
    fontWeight: 'bold',
    color: '#6E0D25',
    marginBottom: 1,
  },
  featureDescription: {
    fontSize: 7,
    color: '#6B7280',
    lineHeight: 10,
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
  imageLoadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(110, 13, 37, 0.1)',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 10,
  },
  imageLoadingText: {
    marginTop: spacing.sm,
    color: '#6E0D25',
    fontSize: 14,
    fontWeight: '600',
  },
});