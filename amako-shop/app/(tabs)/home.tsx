import React, { useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui/tokens';
import { useFeaturedProducts, useHomeStats, useReviews, useStoreInfo, useBenefitsData } from '../../src/api/home-hooks';
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

export default function HomeScreen() {
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [showProductModal, setShowProductModal] = useState(false);

  // Fetch data using API hooks
  const { data: featuredProducts, isLoading: productsLoading, refetch: refetchProducts } = useFeaturedProducts();
  const { data: homeStats, refetch: refetchStats } = useHomeStats();
  const { data: reviews, refetch: refetchReviews } = useReviews();
  const { data: storeInfo, refetch: refetchStoreInfo } = useStoreInfo();
  const { data: benefitsData, refetch: refetchBenefits } = useBenefitsData();
  
  // Fetch dynamic content
  const { content: homeContent } = useSectionContentArray('home', 'mobile');
  const { config } = useAppConfig('mobile');

  // Create hero slides from featured products with actual banner images
  const heroSlides = featuredProducts?.slice(0, 3).map((product, index) => ({
    id: product.id,
    imageUrl: require('../../src/utils/urlHelper').getBannerUrl(index + 1), // Use banner images
    title: product.name,
    subtitle: product.subtitle || config.product_default_subtitle,
    priceText: `Rs.${Math.round(product.price.amount)}`, // No decimal places
    price: product.price.amount, // Add actual price for cart calculations
    ctaText: config.hero_default_cta || 'Add to Cart',
    productId: product.id,
    is_menu_highlight: index < 2, // Make first two slides highlighted
  })) || [];

  const handleRefresh = async () => {
    await Promise.all([
      refetchProducts(),
      refetchStats(),
      refetchReviews(),
      refetchStoreInfo(),
      refetchBenefits(),
    ]);
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
            products={featuredProducts || []} 
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
      <FlatList
        data={homeData}
        renderItem={renderHomeItem}
        keyExtractor={(item) => item.id}
        style={styles.container}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={false}
            onRefresh={handleRefresh}
            colors={[colors.brand.primary]}
            tintColor={colors.brand.primary}
          />
        }
      />
      
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
  contactFollowSection: {
    marginTop: spacing.xl,
    marginHorizontal: spacing.lg,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    backgroundColor: '#f0f0e8', // Light yellow-beige
    borderRadius: radius.lg,
  },
});
