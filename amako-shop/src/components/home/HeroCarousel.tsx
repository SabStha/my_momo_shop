import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  Image,
  Pressable,
  StyleSheet,
  Dimensions,
  ScrollView,
  NativeScrollEvent,
  NativeSyntheticEvent,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import { useCartStore } from '../../state/cart';
import { useAppConfig } from '../../hooks/useSiteContent';

const { width: screenWidth } = Dimensions.get('window');
const carouselHeight = 280;

interface HeroSlide {
  id: string;
  imageUrl: string;
  title: string;
  subtitle: string;
  priceText?: string;
  ctaText?: string;
  onPress?: () => void;
  productId?: string;
}

interface HeroCarouselProps {
  slides: HeroSlide[];
}

export default function HeroCarousel({ slides }: HeroCarouselProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const scrollViewRef = useRef<ScrollView>(null);
  const addToCart = useCartStore((state) => state.addItem);
  const { config } = useAppConfig('mobile');

  const handleScroll = (event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const contentOffset = event.nativeEvent.contentOffset;
    const index = Math.round(contentOffset.x / screenWidth);
    setCurrentIndex(index);
  };

  const handleAddToCart = (slide: HeroSlide) => {
    if (slide.productId) {
      // Create a cart item from the slide
      const cartItem = {
        itemId: slide.productId,
        name: slide.title,
        unitBasePrice: { currency: 'NPR', amount: 0 }, // Will be updated with actual price
        qty: 1,
        imageUrl: slide.imageUrl,
      };
      addToCart(cartItem);
    }
    slide.onPress?.();
  };

  const renderSlide = (slide: HeroSlide, index: number) => (
    <View key={slide.id} style={styles.slide}>
      <Image
        source={{ uri: slide.imageUrl }}
        style={styles.slideImage}
        resizeMode="cover"
      />
      
      {/* Dark gradient overlay */}
      <LinearGradient
        colors={['transparent', 'rgba(0,0,0,0.7)']}
        style={styles.gradientOverlay}
      />
      
      {/* Content overlay */}
      <View style={styles.contentOverlay}>
        <View style={styles.textContent}>
          <Text style={styles.title}>{slide.title}</Text>
          <Text style={styles.subtitle}>{slide.subtitle}</Text>
          {slide.priceText && (
            <Text style={styles.priceText}>{slide.priceText}</Text>
          )}
        </View>
        
        {/* CTA Button */}
        <Pressable
          style={styles.ctaButton}
          onPress={() => handleAddToCart(slide)}
        >
          <MCI name="cart-plus" size={16} color={colors.white} />
          <Text style={styles.ctaText}>
            {slide.ctaText || config.hero_default_cta}
          </Text>
        </Pressable>
      </View>
    </View>
  );

  const renderPagerDots = () => (
    <View style={styles.pagerContainer}>
      {slides.map((_, index) => (
        <View
          key={index}
          style={[
            styles.pagerDot,
            index === currentIndex && styles.pagerDotActive,
          ]}
        />
      ))}
    </View>
  );

  if (slides.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <MCI name="image-outline" size={48} color={colors.gray[400]} />
        <Text style={styles.emptyText}>{config.empty_hero_message}</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <ScrollView
        ref={scrollViewRef}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={handleScroll}
        scrollEventThrottle={16}
        style={styles.scrollView}
      >
        {slides.map((slide, index) => renderSlide(slide, index))}
      </ScrollView>
      
      {/* Pager dots */}
      {slides.length > 1 && renderPagerDots()}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    height: carouselHeight,
    position: 'relative',
  },
  scrollView: {
    flex: 1,
  },
  slide: {
    width: screenWidth,
    height: carouselHeight,
    position: 'relative',
  },
  slideImage: {
    width: '100%',
    height: '100%',
  },
  gradientOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 120,
  },
  contentOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: spacing.lg,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
  },
  textContent: {
    flex: 1,
    marginRight: spacing.md,
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.xs,
    textShadowColor: 'rgba(0,0,0,0.5)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.white,
    opacity: 0.9,
    marginBottom: spacing.sm,
    textShadowColor: 'rgba(0,0,0,0.5)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
  },
  priceText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.accent,
    textShadowColor: 'rgba(0,0,0,0.5)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
  },
  ctaButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
    ...shadows.medium,
  },
  ctaText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    marginLeft: spacing.xs,
  },
  pagerContainer: {
    position: 'absolute',
    bottom: spacing.md,
    left: 0,
    right: 0,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  pagerDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(255,255,255,0.4)',
    marginHorizontal: 4,
  },
  pagerDotActive: {
    backgroundColor: colors.white,
    width: 12,
    height: 8,
    borderRadius: 4,
  },
  emptyContainer: {
    height: carouselHeight,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[100],
  },
  emptyText: {
    fontSize: fontSizes.md,
    color: colors.gray[500],
    marginTop: spacing.sm,
  },
});
