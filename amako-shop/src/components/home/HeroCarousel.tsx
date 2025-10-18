import React, { useState, useRef, useEffect } from 'react';
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
import { useCartSyncStore } from '../../state/cart-sync';
import { useAppConfig } from '../../hooks/useSiteContent';

const { width: screenWidth } = Dimensions.get('window');
const carouselHeight = 320;

interface HeroSlide {
  id: string;
  imageUrl: string;
  title: string;
  subtitle: string;
  priceText?: string;
  price?: number; // Add actual price for cart calculations
  ctaText?: string;
  onPress?: () => void;
  productId?: string;
  is_menu_highlight?: boolean;
}

interface HeroCarouselProps {
  slides: HeroSlide[];
  onAddToCart?: (item: any) => void;
  onInfoPress?: (product: any) => void;
}

export default function HeroCarousel({ slides, onAddToCart, onInfoPress }: HeroCarouselProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const scrollViewRef = useRef<ScrollView>(null);
  const addToCart = useCartSyncStore((state) => state.addItem);
  const { config } = useAppConfig('mobile');
  const autoRotateTimerRef = useRef<NodeJS.Timeout | null>(null);
  const [isPaused, setIsPaused] = useState(false);

  // Auto-rotation every 2 seconds
  useEffect(() => {
    if (slides.length > 1 && !isPaused) {
      autoRotateTimerRef.current = setInterval(() => {
        const newIndex = currentIndex < slides.length - 1 ? currentIndex + 1 : 0;
        scrollViewRef.current?.scrollTo({
          x: newIndex * screenWidth,
          animated: true,
        });
        setCurrentIndex(newIndex);
      }, 2000); // Auto-rotate every 2 seconds

      return () => {
        if (autoRotateTimerRef.current) {
          clearInterval(autoRotateTimerRef.current);
        }
      };
    }
  }, [currentIndex, slides.length, isPaused]);

  const handleScroll = (event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const contentOffset = event.nativeEvent.contentOffset;
    const index = Math.round(contentOffset.x / screenWidth);
    setCurrentIndex(index);
  };

  // Pause auto-rotation when user manually interacts
  const handleScrollBeginDrag = () => {
    setIsPaused(true);
    // Resume after 5 seconds
    setTimeout(() => {
      setIsPaused(false);
    }, 5000);
  };

  const handleAddToCart = (slide: HeroSlide) => {
    if (slide.productId) {
      // Create a cart item from the slide
      const cartItem = {
        itemId: slide.productId,
        name: slide.title,
        unitBasePrice: { currency: 'NPR' as const, amount: slide.price || 0 },
        qty: 1,
        imageUrl: slide.imageUrl,
      };
      
      // Add to cart with callback to open the new sheet
      addToCart(cartItem, (payload) => {
        // Open the new cart added sheet
        (global as any).openCartAddedSheet?.(payload);
      });
    }
    slide.onPress?.();
  };

  const handleImagePress = (slide: HeroSlide) => {
    if (onInfoPress && slide.productId) {
      // Create a product object similar to what ProductCard uses
      const product = {
        id: slide.productId,
        name: slide.title,
        subtitle: slide.subtitle,
        price: { currency: 'NPR', amount: slide.price || 0 },
        imageUrl: slide.imageUrl,
        ingredients: 'Fresh ingredients prepared daily',
        allergens: 'Contains: Gluten',
        calories: '350-400',
        preparation_time: '18-22 minutes',
        spice_level: 'Medium',
        serving_size: '6 pieces',
        is_vegetarian: false,
        is_vegan: false,
        is_gluten_free: false,
      };
      onInfoPress(product);
    }
  };

  const renderSlide = (slide: HeroSlide, index: number) => (
    <View key={slide.id} style={styles.slide}>
      <Pressable
        style={styles.imageContainer}
        onPress={() => handleImagePress(slide)}
      >
        <Image
          source={{ uri: slide.imageUrl }}
          style={styles.slideImage}
          resizeMode="cover"
        />
      </Pressable>
      
      {/* Top overlay for highlight badge - only show if is_menu_highlight is true */}
      {slide.is_menu_highlight && (
        <View style={styles.topOverlay}>
          <View style={styles.highlightBadge}>
            <Text style={styles.highlightText}>⭐ Highlighted</Text>
          </View>
        </View>
      )}
      
      {/* Bottom gradient overlay */}
      <LinearGradient
        colors={['transparent', 'rgba(0,0,0,0.3)', 'rgba(0,0,0,0.6)']}
        style={styles.gradientOverlay}
      />
      
      {/* Product overlay content */}
      <View style={styles.contentOverlay}>
        <View style={styles.productInfo}>
          <View style={styles.mainContent}>
            {/* Left side - Text content */}
            <View style={styles.textContent}>
              <Text style={styles.title}>{slide.title}</Text>
              <Text style={styles.subtitle}>{slide.subtitle}</Text>
            </View>
            
            {/* Right side - Price and Add to Cart */}
            <View style={styles.rightContent}>
              <Text style={styles.priceText}>{slide.priceText}</Text>
              <Pressable
                style={styles.addToCartButton}
                onPress={() => handleAddToCart(slide)}
              >
                <MCI name="shopping" size={16} color={colors.white} />
                <Text style={styles.addToCartText}>
                  {slide.ctaText || config.hero_default_cta}
                </Text>
              </Pressable>
            </View>
          </View>
        </View>
      </View>
    </View>
  );

  const renderPagerDots = () => (
    <View style={styles.pagerContainer}>
      {slides.map((_, index) => (
        <Pressable
          key={index}
          style={[
            styles.pagerDot,
            index === currentIndex && styles.pagerDotActive,
          ]}
          onPress={() => {
            const scrollView = scrollViewRef.current;
            if (scrollView) {
              scrollView.scrollTo({
                x: index * screenWidth,
                animated: true,
              });
            }
            // Pause auto-rotation when user clicks dots
            setIsPaused(true);
            setTimeout(() => setIsPaused(false), 5000);
          }}
        />
      ))}
    </View>
  );

  const goToPreviousSlide = () => {
    const newIndex = currentIndex > 0 ? currentIndex - 1 : slides.length - 1;
    const scrollView = scrollViewRef.current;
    if (scrollView) {
      scrollView.scrollTo({
        x: newIndex * screenWidth,
        animated: true,
      });
    }
    // Pause auto-rotation when user clicks arrows
    setIsPaused(true);
    setTimeout(() => setIsPaused(false), 5000);
  };

  const goToNextSlide = () => {
    const newIndex = currentIndex < slides.length - 1 ? currentIndex + 1 : 0;
    const scrollView = scrollViewRef.current;
    if (scrollView) {
      scrollView.scrollTo({
        x: newIndex * screenWidth,
        animated: true,
      });
    }
    // Pause auto-rotation when user clicks arrows
    setIsPaused(true);
    setTimeout(() => setIsPaused(false), 5000);
  };

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
        onScrollBeginDrag={handleScrollBeginDrag}
        scrollEventThrottle={16}
        style={styles.scrollView}
      >
        {slides.map((slide, index) => renderSlide(slide, index))}
      </ScrollView>
      
      {/* Navigation arrows */}
      {slides.length > 1 && (
        <>
          <Pressable style={styles.leftArrow} onPress={goToPreviousSlide}>
            <MCI name="chevron-left" size={24} color={colors.white} />
          </Pressable>
          <Pressable style={styles.rightArrow} onPress={goToNextSlide}>
            <MCI name="chevron-right" size={24} color={colors.white} />
          </Pressable>
        </>
      )}
      
      {/* Pager dots */}
      {slides.length > 1 && renderPagerDots()}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    height: carouselHeight,
    position: 'relative',
    marginHorizontal: spacing.sm,
    borderRadius: radius.xl,
    overflow: 'hidden',
  },
  scrollView: {
    flex: 1,
  },
  slide: {
    width: screenWidth - spacing.sm * 2,
    height: carouselHeight,
    position: 'relative',
  },
  imageContainer: {
    width: '100%',
    height: '100%',
  },
  slideImage: {
    width: '100%',
    height: '100%',
  },
  topOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 10,
    padding: spacing.md,
    alignItems: 'flex-end',
  },
  highlightBadge: {
    backgroundColor: 'transparent',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
  },
  highlightText: {
    color: '#FCD34D',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 3,
  },
  gradientOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 160,
  },
  contentOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: spacing.md,
    zIndex: 10,
  },
  productInfo: {
    padding: spacing.sm,
  },
  mainContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    minHeight: 80,
  },
  textContent: {
    flex: 1,
    marginRight: spacing.md,
    maxWidth: '70%',
  },
  rightContent: {
    alignItems: 'flex-end',
  },
  title: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.xs,
    backgroundColor: 'rgba(0,0,0,0.75)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 3,
    lineHeight: 20,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.15)',
  },
  subtitle: {
    fontSize: fontSizes.xs,
    color: colors.white,
    opacity: 0.95,
    backgroundColor: 'rgba(0,0,0,0.7)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    lineHeight: 14,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
  },
  priceText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#FCD34D', // This matches web's text-yellow-400
    backgroundColor: 'rgba(0,0,0,0.8)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    textShadowColor: 'rgba(0,0,0,0.9)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 3,
    marginBottom: spacing.xs,
    borderWidth: 1,
    borderColor: 'rgba(252, 211, 77, 0.3)',
    alignSelf: 'flex-start',
  },
  addToCartButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EF4444',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.lg,
    minHeight: 40,
    minWidth: 80,
    justifyContent: 'center',
    shadowColor: '#EF4444',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 3,
  },
  addToCartText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    marginLeft: spacing.xs,
  },
  leftArrow: {
    position: 'absolute',
    left: spacing.sm,
    top: '50%',
    transform: [{ translateY: -20 }],
    backgroundColor: 'rgba(0,0,0,0.3)',
    padding: spacing.sm,
    borderRadius: radius.full,
    minWidth: 44,
    minHeight: 44,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 20,
  },
  rightArrow: {
    position: 'absolute',
    right: spacing.sm,
    top: '50%',
    transform: [{ translateY: -20 }],
    backgroundColor: 'rgba(0,0,0,0.3)',
    padding: spacing.sm,
    borderRadius: radius.full,
    minWidth: 44,
    minHeight: 44,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 20,
  },
  pagerContainer: {
    position: 'absolute',
    bottom: spacing.lg,
    left: 0,
    right: 0,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 20,
  },
  pagerDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: 'rgba(255,255,255,0.6)',
    marginHorizontal: spacing.xs,
    borderWidth: 2,
    borderColor: 'rgba(255,255,255,0.3)',
  },
  pagerDotActive: {
    backgroundColor: colors.white,
    width: 16,
    height: 12,
    borderRadius: 6,
    shadowColor: colors.white,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.5,
    shadowRadius: 4,
    elevation: 5,
  },
  emptyContainer: {
    height: carouselHeight,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[100],
    marginHorizontal: spacing.sm,
    borderRadius: radius.xl,
  },
  emptyText: {
    fontSize: fontSizes.md,
    color: colors.gray[500],
    marginTop: spacing.sm,
  },
});
