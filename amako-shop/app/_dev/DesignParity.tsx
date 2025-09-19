import React from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../../src/ui/tokens';
import HeroCarousel from '../../src/components/home/HeroCarousel';
import KpiRow from '../../src/components/home/KpiRow';
import SectionHeader from '../../src/components/home/SectionHeader';
import ProductGrid from '../../src/components/home/ProductGrid';
import BenefitsGrid from '../../src/components/home/BenefitsGrid';
import ReviewsSection from '../../src/components/home/ReviewsSection';
import VisitUs from '../../src/components/home/VisitUs';

// Sample data for testing
const sampleHeroSlides = [
  {
    id: '1',
    imageUrl: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=800&h=400&fit=crop',
    title: 'Premium Nepali Momo',
    subtitle: 'Authentic flavors from the Himalayas',
    priceText: 'NPR 180',
    ctaText: 'Add to Cart',
    productId: '1',
  },
  {
    id: '2',
    imageUrl: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=800&h=400&fit=crop',
    title: 'Special Combo Offers',
    subtitle: 'Get more for less with our combo deals',
    priceText: 'NPR 300',
    ctaText: 'Add to Cart',
    productId: '2',
  },
  {
    id: '3',
    imageUrl: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&h=400&fit=crop',
    title: 'Fresh Daily Specials',
    subtitle: 'New flavors added every day',
    priceText: 'NPR 200',
    ctaText: 'Add to Cart',
    productId: '3',
  },
];

const sampleProducts = [
  {
    id: '1',
    name: 'Classic Chicken Momo',
    subtitle: 'Juicy chicken, house spice blend',
    price: { currency: 'NPR', amount: 180 },
    imageUrl: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop',
    isFeatured: true,
  },
  {
    id: '2',
    name: 'Vegetable Momo',
    subtitle: 'Fresh vegetables, aromatic herbs',
    price: { currency: 'NPR', amount: 150 },
    imageUrl: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400&h=300&fit=crop',
    isFeatured: true,
  },
  {
    id: '3',
    name: 'Steamed Pork Momo',
    subtitle: 'Tender pork, traditional recipe',
    price: { currency: 'NPR', amount: 200 },
    imageUrl: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=400&h=300&fit=crop',
    isFeatured: true,
  },
  {
    id: '4',
    name: 'Masala Chai',
    subtitle: 'Spiced Indian tea with milk',
    price: { currency: 'NPR', amount: 50 },
    imageUrl: 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=400&h=300&fit=crop',
    isFeatured: true,
  },
];

const sampleReviews = [
  {
    id: '1',
    name: 'Sarah M.',
    rating: 5,
    comment: 'Amazing momos! Fresh and delicious. Will definitely order again.',
    orderItem: 'Chicken Momo',
    date: '2 days ago',
  },
  {
    id: '2',
    name: 'Raj K.',
    rating: 5,
    comment: 'Best momos in town! Fast delivery and great taste.',
    orderItem: 'Vegetable Momo',
    date: '1 week ago',
  },
  {
    id: '3',
    name: 'Priya S.',
    rating: 4,
    comment: 'Good quality and taste. Delivery was on time.',
    orderItem: 'Pork Momo',
    date: '2 weeks ago',
  },
];

const sampleStoreInfo = {
  address: '123 Momo Street, Kathmandu, Nepal',
  phone: '+977-1-2345678',
  email: 'info@amakoshop.com',
  businessHours: [
    { day: 'Monday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Tuesday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Wednesday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Thursday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Friday', open: '10:00', close: '23:00', isOpen: true },
    { day: 'Saturday', open: '10:00', close: '23:00', isOpen: true },
    { day: 'Sunday', open: '11:00', close: '21:00', isOpen: true },
  ],
  socialMedia: {
    facebook: 'https://facebook.com/amakoshop',
    instagram: 'https://instagram.com/amakoshop',
    twitter: 'https://twitter.com/amakoshop',
  },
};

export default function DesignParityScreen() {
  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.title}>Design Parity QA</Text>
        <Text style={styles.subtitle}>Visual comparison with Laravel web homepage</Text>
      </View>

      {/* Hero Carousel */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>1. Hero Carousel</Text>
        <HeroCarousel slides={sampleHeroSlides} />
      </View>

      {/* KPI Row */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>2. KPI Row</Text>
        <KpiRow />
      </View>

      {/* Featured Products */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>3. Featured Products</Text>
        <SectionHeader title="FEATURED PRODUCTS" icon="star" />
        <ProductGrid products={sampleProducts} />
      </View>

      {/* Benefits Grid */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>4. Benefits Grid</Text>
        <SectionHeader 
          title="Why Choose Ama Ko Shop?" 
          subtitle="Experience the best in authentic Nepali cuisine"
          showPill={false}
        />
        <BenefitsGrid />
      </View>

      {/* Reviews Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>5. Customer Reviews</Text>
        <SectionHeader title="CUSTOMER REVIEWS" icon="star" />
        <ReviewsSection reviews={sampleReviews} />
      </View>

      {/* Visit Us */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>6. Visit Us</Text>
        <SectionHeader title="VISIT US" icon="map-marker" />
        <VisitUs storeInfo={sampleStoreInfo} />
      </View>

      {/* Design Tokens Reference */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Design Tokens Reference</Text>
        <View style={styles.tokensContainer}>
          <View style={styles.tokenRow}>
            <Text style={styles.tokenLabel}>Primary Color:</Text>
            <View style={[styles.colorSwatch, { backgroundColor: colors.brand.primary }]} />
            <Text style={styles.tokenValue}>{colors.brand.primary}</Text>
          </View>
          <View style={styles.tokenRow}>
            <Text style={styles.tokenLabel}>Accent Color:</Text>
            <View style={[styles.colorSwatch, { backgroundColor: colors.brand.accent }]} />
            <Text style={styles.tokenValue}>{colors.brand.accent}</Text>
          </View>
          <View style={styles.tokenRow}>
            <Text style={styles.tokenLabel}>Background:</Text>
            <View style={[styles.colorSwatch, { backgroundColor: colors.momo.sand }]} />
            <Text style={styles.tokenValue}>{colors.momo.sand}</Text>
          </View>
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.momo.sand,
  },
  header: {
    padding: spacing.lg,
    alignItems: 'center',
    backgroundColor: colors.white,
    marginBottom: spacing.md,
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.momo.mocha,
    textAlign: 'center',
  },
  section: {
    marginBottom: spacing.xl,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    backgroundColor: colors.white,
    marginBottom: spacing.sm,
  },
  tokensContainer: {
    backgroundColor: colors.white,
    margin: spacing.lg,
    padding: spacing.md,
    borderRadius: 12,
  },
  tokenRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.sm,
  },
  tokenLabel: {
    fontSize: fontSizes.sm,
    color: colors.brand.primary,
    fontWeight: fontWeights.medium,
    width: 120,
  },
  colorSwatch: {
    width: 24,
    height: 24,
    borderRadius: 12,
    marginHorizontal: spacing.sm,
  },
  tokenValue: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    fontFamily: 'monospace',
  },
});
