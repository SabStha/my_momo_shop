import React from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui/tokens';
import { useFeaturedProducts, useHomeStats, useReviews, useStoreInfo } from '../../src/api/home-hooks';
import HeroCarousel from '../../src/components/home/HeroCarousel';
import KpiRow from '../../src/components/home/KpiRow';
import SectionHeader from '../../src/components/home/SectionHeader';
import ProductGrid from '../../src/components/home/ProductGrid';
import BenefitsGrid from '../../src/components/home/BenefitsGrid';
import DetailedStats from '../../src/components/home/DetailedStats';
import { useSectionContentArray, useAppConfig } from '../../src/hooks/useSiteContent';
import ReviewsSection from '../../src/components/home/ReviewsSection';
import VisitUs from '../../src/components/home/VisitUs';

export default function HomeScreen() {
  // Fetch data using API hooks
  const { data: featuredProducts, isLoading: productsLoading, refetch: refetchProducts } = useFeaturedProducts();
  const { data: homeStats, refetch: refetchStats } = useHomeStats();
  const { data: reviews, refetch: refetchReviews } = useReviews();
  const { data: storeInfo, refetch: refetchStoreInfo } = useStoreInfo();
  
  // Fetch dynamic content
  const { content: homeContent } = useSectionContentArray('home', 'mobile');
  const { config } = useAppConfig('mobile');

  // Create hero slides from featured products
  const heroSlides = featuredProducts?.slice(0, 3).map(product => ({
    id: product.id,
    imageUrl: product.imageUrl,
    title: product.name,
    subtitle: product.subtitle || config.product_default_subtitle,
    priceText: `NPR ${product.price.amount}`,
    ctaText: config.hero_default_cta,
    productId: product.id,
  })) || [];

  const handleRefresh = async () => {
    await Promise.all([
      refetchProducts(),
      refetchStats(),
      refetchReviews(),
      refetchStoreInfo(),
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

  const renderHomeItem = ({ item }: { item: any }) => {
    switch (item.type) {
      case 'hero':
        return <HeroCarousel slides={heroSlides} />;
      case 'kpi':
        return <KpiRow data={homeStats} />;
      case 'featured-header':
        return <SectionHeader title={homeContent.home_featured_products_title || "FEATURED PRODUCTS"} icon="star" />;
      case 'featured-products':
        return (
          <ProductGrid 
            products={featuredProducts || []} 
            onProductPress={handleProductPress}
            isLoading={productsLoading}
          />
        );
      case 'benefits-header':
        return (
          <View style={styles.benefitsSection}>
            <SectionHeader 
              title={homeContent.home_benefits_title || "✨ Why Choose Ama Ko Shop?"} 
              subtitle={homeContent.home_benefits_subtitle || "From our kitchen to your heart — here's why thousands trust us with their favorite comfort food."}
              showPill={false}
            />
            <DetailedStats />
            <BenefitsGrid />
            
            {/* Call to Action */}
            <View style={styles.ctaContainer}>
              <TouchableOpacity 
                style={styles.ctaButton}
                onPress={() => router.push('/(tabs)/menu')}
              >
                <Text style={styles.ctaText}>{homeContent.home_cta_button_text || "Try Our Momos Today"}</Text>
              </TouchableOpacity>
            </View>
          </View>
        );
      case 'reviews-header':
        return <SectionHeader title={homeContent.home_reviews_title || "CUSTOMER REVIEWS"} icon="star" />;
      case 'reviews':
        return (
          <ReviewsSection 
            reviews={reviews}
            onWriteReview={handleWriteReview}
          />
        );
      case 'visit-header':
        return <SectionHeader title={homeContent.home_visit_title || "VISIT US"} icon="map-marker" />;
      case 'visit':
        return <VisitUs storeInfo={storeInfo} />;
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
    { id: '7', type: 'reviews' },
    { id: '8', type: 'visit-header' },
    { id: '9', type: 'visit' },
  ];

  return (
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
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.momo.sand,
  },
  benefitsSection: {
    marginTop: spacing.lg,
  },
  ctaContainer: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    alignItems: 'center',
  },
  ctaButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.full,
    shadowColor: colors.brand.primary,
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  ctaText: {
    color: colors.white,
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    textAlign: 'center',
  },
});
