import React, { useState, useRef } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Animated } from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';

// Create animated FlatList for native scroll tracking
const AnimatedFlatList = Animated.createAnimatedComponent(FlatList);
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui/tokens';
import { useHomeStats, useReviews, useStoreInfo, useBenefitsData } from '../../src/api/home-hooks';
import { useMenu } from '../../src/api/menu-hooks';
import HeroCarousel from '../../src/components/home/HeroCarousel';
import KpiRow from '../../src/components/home/KpiRow';
import SectionHeader from '../../src/components/home/SectionHeader';
import ProductGrid from '../../src/components/home/ProductGrid';
import BenefitsGrid from '../../src/components/home/BenefitsGrid';
import { useSectionContentArray, useAppConfig } from '../../src/hooks/useSiteContent';
import ReviewsSection from '../../src/components/home/ReviewsSection';
import VisitUs from '../../src/components/home/VisitUs';
import { BusinessHours, VisitUsMap, ContactUs, FollowUs, ContactFollowUs } from '../../src/components/home/VisitUs';
import FoodInfoSheet from '../../src/components/product/FoodInfoSheet';
import LoadingSpinner from '../../src/components/LoadingSpinner';

export default function HomeScreen() {
  const [selectedProduct, setSelectedProduct] = useState<any>(null);
  const [showProductModal, setShowProductModal] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [isPulling, setIsPulling] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;

  // Stagger /menu 1500ms after mount so cart and notifications finish
  // with the single-threaded PHP dev server before the larger menu request fires.
  const [menuEnabled, setMenuEnabled] = useState(false);
  React.useEffect(() => {
    const t = setTimeout(() => setMenuEnabled(true), 1500);
    return () => clearTimeout(t);
  }, []);

  // Fetch data using API hooks — useMenu() is the single /menu call shared with the menu tab.
  // React Query deduplicates on key ['menu'], so no second network request fires.
  const { data: menuData, isLoading: productsLoading, refetch: refetchProducts } = useMenu({ enabled: menuEnabled });
  const allItems: any[] = menuData?.items ?? [];
  const { data: homeStats, refetch: refetchStats } = useHomeStats();
  const { data: reviews, refetch: refetchReviews } = useReviews();
  const { data: storeInfo, refetch: refetchStoreInfo } = useStoreInfo();
  const { data: benefitsData, refetch: refetchBenefits } = useBenefitsData();
  
  // Fetch dynamic content
  const { content: homeContent } = useSectionContentArray('home', 'mobile');
  const { config } = useAppConfig('mobile');

  // Hero carousel: menu highlights only, max 3 slides
  const heroSlides = allItems
    .filter((item: any) => item.is_menu_highlight == 1 || item.is_menu_highlight === true)
    .slice(0, 3)
    .map((item: any) => {
      const imageUrl = item.image || item.imageUrl || '';
      const priceNum = parseFloat(item.price) || 0;
      return {
        id: String(item.id),
        imageUrl,
        title: item.name,
        subtitle: item.desc || item.description || config.product_default_subtitle,
        priceText: `Rs.${Math.round(priceNum)}`,
        price: priceNum,
        ctaText: config.hero_default_cta || 'Add to Cart',
        productId: String(item.id),
        is_menu_highlight: true,
      };
    });

  // Featured products grid: is_featured OR is_menu_highlight, normalized for ProductCard
  const featuredProductsGrid = allItems
    .filter((item: any) =>
      item.is_featured == 1 || item.is_featured === true ||
      item.is_menu_highlight == 1 || item.is_menu_highlight === true
    )
    .map((item: any) => ({
      ...item,
      id: String(item.id),
      imageUrl: item.image || item.imageUrl || '',
      price: { amount: parseFloat(item.price) || 0, currency: 'NPR' },
    }));

  // Track pulling state
  React.useEffect(() => {
    const listenerId = scrollY.addListener(({ value }) => {
      const shouldPull = value < -50;
      setIsPulling(shouldPull);
    });
    return () => {
      scrollY.removeListener(listenerId);
    };
  }, [scrollY]);

  const handleRefresh = async () => {
    setRefreshing(true);

    // Add minimum delay so loading spinner is visible and katana animation plays fully
    const minDelay = new Promise(resolve => setTimeout(resolve, 3500)); // 3.5 seconds for complete animation

    try {
      await Promise.all([
        refetchProducts(),
        refetchStats(),
        refetchReviews(),
        refetchStoreInfo(),
        refetchBenefits(),
        minDelay, // Ensure at least 3.5 seconds loading time
      ]);
    } finally {
      setRefreshing(false);
    }
  };

  const handleProductPress = (product: any) => {
    // Navigate to product detail screen
    console.log('Navigate to product:', product.id);
  };

  const handleWriteReview = () => {
    // Navigate to write review screen
    console.log('Navigate to write review');
  };

  const handleProductInfoPress = (product: any) => {
    setSelectedProduct(product);
    setShowProductModal(true);
  };

  const handleCloseProductModal = () => {
    setShowProductModal(false);
    setSelectedProduct(null);
  };

  const handleAddToCart = (item: any) => {
    // This function is now handled by the global CartAddedSheet
    // The ProductCard and HeroCarousel components will call the global openCartAddedSheet
    // No need to show old modals anymore
  };



  const renderHomeItem = ({ item }: { item: any }) => {
    switch (item.type) {
      case 'hero':
        return <HeroCarousel slides={heroSlides} onAddToCart={handleAddToCart} onInfoPress={handleProductInfoPress} />;
      case 'kpi':
        return <KpiRow data={homeStats} />;
      case 'featured-header':
        return <SectionHeader title={homeContent.home_featured_products_title || "FEATURED PRODUCTS"} icon="star" />;
      case 'featured-products':
        return (
          <ProductGrid 
            products={featuredProductsGrid} 
            onProductPress={handleProductPress}
            onProductInfoPress={handleProductInfoPress}
            onAddToCart={handleAddToCart}
            isLoading={productsLoading}
          />
        );
      case 'benefits-header':
        return (
          <BenefitsGrid 
            benefits={benefitsData?.benefits}
            stats={benefitsData?.stats}
            title={benefitsData?.content?.title || homeContent.home_benefits_title || "✨ Why Choose Ama Ko Shop?"}
            subtitle={benefitsData?.content?.subtitle || homeContent.home_benefits_subtitle || "From our kitchen to your heart — here's why thousands trust us with their favorite comfort food."}
            ctaText={benefitsData?.content?.ctaText || homeContent.home_cta_button_text || "Try Our Momos Today"}
            onCtaPress={() => router.push('/(tabs)/menu')}
          />
        );
      case 'reviews-header':
        // Calculate average rating and total from reviews array
        const reviewsArray = reviews || [];
        const totalReviews = reviewsArray.length;
        const averageRating = totalReviews > 0 
          ? reviewsArray.reduce((sum, review) => sum + review.rating, 0) / totalReviews 
          : 0;
        
        return (
          <View style={styles.reviewsSection}>
            <SectionHeader title={homeContent.home_reviews_title || "CUSTOMER REVIEWS"} icon="star" />
            <ReviewsSection 
              reviews={reviewsArray}
              averageRating={averageRating}
              totalReviews={totalReviews}
              onWriteReview={handleWriteReview}
            />
          </View>
        );
      case 'reviews':
        return null; // This case is now handled in reviews-header
      case 'business-hours':
        return (
          <View style={styles.businessHoursSection}>
            <SectionHeader title="BUSINESS HOURS" icon="clock-outline" />
            <BusinessHours storeInfo={storeInfo} />
          </View>
        );
      case 'visit-us':
        return (
          <View style={styles.visitUsSection}>
            <SectionHeader title="VISIT US" icon="map-marker" />
            <VisitUsMap storeInfo={storeInfo} />
          </View>
        );
      case 'contact-follow':
        return (
          <View style={styles.contactFollowSection}>
            <ContactFollowUs storeInfo={storeInfo} />
          </View>
        );
      case 'visit-header':
        return null; // This case is now handled separately
      case 'visit':
        return null; // This case is now handled separately
      default:
        return null;
    }
  };

  const homeData = [
    { id: '1', type: 'hero' },
    { id: '2', type: 'kpi' },
    { id: '3', type: 'featured-header' },
    { id: '4', type: 'featured-products' },
    { id: '5', type: 'benefits-header' },
    { id: '6', type: 'reviews-header' },
    { id: '7', type: 'business-hours' },
    { id: '8', type: 'visit-us' },
    { id: '9', type: 'contact-follow' },
  ];

  return (
    <>
      <AnimatedFlatList
        data={homeData}
        renderItem={renderHomeItem}
        keyExtractor={(item) => item.id}
        style={styles.container}
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
            colors={['transparent']} // Hide native spinner
            tintColor="transparent" // Hide native spinner
            progressViewOffset={-9999} // Move native spinner off-screen
          />
        }
      />
      
      {/* Loading Overlay - Shows during pull and refresh */}
      {(isPulling || refreshing) && (
        <Animated.View 
          style={[
            styles.loadingOverlay,
            refreshing ? {
              // When refreshing, keep it fully visible at top
              opacity: 1,
              transform: [{ translateY: 0 }]
            } : {
              // When pulling, follow the finger
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
      
      <FoodInfoSheet
        visible={showProductModal}
        onClose={handleCloseProductModal}
        data={{
          image: selectedProduct?.imageUrl || '',
          ingredients: selectedProduct?.ingredients || 'Fresh ingredients prepared daily',
          allergens: selectedProduct?.allergens || 'Contains: Gluten',
          nutrition: {
            cal: selectedProduct?.calories || '350-400',
            size: selectedProduct?.serving_size || '6 pieces',
            prep: selectedProduct?.preparation_time || '18-22 minutes',
            spice: selectedProduct?.spice_level || 'Medium'
          },
          dietary: selectedProduct?.is_vegetarian ? 'Vegetarian' : 
                  selectedProduct?.is_vegan ? 'Vegan' : 
                  selectedProduct?.is_gluten_free ? 'Gluten-Free' : 'Standard'
        }}
      />
      
    </>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.white,
  },
  reviewsSection: {
    marginTop: spacing.xl,
    marginHorizontal: spacing.lg,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    backgroundColor: '#e1e8f0', // Similar to benefits section but slightly different shade
    borderRadius: radius.lg,
  },
  businessHoursSection: {
    marginTop: spacing.xl,
    marginHorizontal: spacing.lg,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    backgroundColor: '#e1e8f0', // Same as customer reviews section
    borderRadius: radius.lg,
  },
  visitUsSection: {
    marginTop: spacing.xl,
    marginHorizontal: spacing.lg,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    backgroundColor: '#e8f0e8', // Light green
    borderRadius: radius.lg,
  },
  contactUsSection: {
    marginTop: spacing.xl,
    marginHorizontal: spacing.lg,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    backgroundColor: '#f0e8f0', // Light purple
    borderRadius: radius.lg,
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.15)', // Light dark blur - no white!
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1000,
  },
  contactFollowSection: {
    marginTop: spacing.xl,
    marginHorizontal: spacing.lg,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    backgroundColor: '#f0f0e8', // Light yellow-beige
    borderRadius: radius.lg,
  },
});
