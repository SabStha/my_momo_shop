import React, { useState, useMemo, useEffect, useRef } from 'react';
import { 
  View, 
  Text, 
  FlatList, 
  RefreshControl, 
  StyleSheet, 
  Dimensions,
  Alert,
  TouchableOpacity,
  ScrollView,
  Pressable,
  Image,
  Modal,
  TextInput,
  Animated
} from 'react-native';
import { router } from 'expo-router';
import { useMenu } from '../../src/api/menu-hooks';
import { 
  ItemCard, 
  CategoryFilter, 
  SearchInput, 
  SkeletonCard,
  FeaturedCarousel,
  StatsRow 
} from '../../src/components';
import FoodInfoSheet from '../../src/components/product/FoodInfoSheet';
import { useCartSyncStore } from '../../src/state/cart-sync';
import { Card, Button } from '../../src/ui';
import LoadingSpinner from '../../src/components/LoadingSpinner';
import { spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { MenuItem, Category } from '../../src/types';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

// Create animated ScrollView for native scroll tracking
const AnimatedScrollView = Animated.createAnimatedComponent(ScrollView);

const { width: screenWidth } = Dimensions.get('window');
const numColumns = 2;
const itemWidth = (screenWidth - spacing.lg * 3) / numColumns;

type MainTab = 'combo' | 'food' | 'drinks' | 'desserts';
type FoodSubTab = 'buff' | 'chicken' | 'veg' | 'others';
type DrinksSubTab = 'hot' | 'cold' | 'boba';

// Helper function to get a valid image URL with fallbacks
const getValidImageUrl = (item: MenuItem): string => {
  // List of known broken images that should be replaced
  const brokenImages = [
    'http://192.168.56.1:8000/storage/default.jpg',
    'default.jpg'
  ];
  
  // Check if the image URL is in the broken list
  const imageUrl = item.image || item.imageUrl;
  if (imageUrl && !brokenImages.some(broken => imageUrl.includes(broken))) {
    return imageUrl;
  }
  
  // Use a working default image from your database
  const defaultImages = [
    'http://192.168.56.1:8000/storage/products/drinks/mango-lassi.jpg',
    'http://192.168.56.1:8000/storage/products/foods/classic-pork-momos.jpg',
    'http://192.168.56.1:8000/storage/products/drinks/matcha-latte.jpg'
  ];
  
  // Use a default image based on category
  if (item.categoryId?.toLowerCase() === 'cold' || item.categoryId?.toLowerCase() === 'hot' || item.categoryId?.toLowerCase() === 'boba') {
    return defaultImages[0]; // Use mango lassi for drinks
  } else if (item.categoryId?.toLowerCase() === 'buff' || item.categoryId?.toLowerCase() === 'chicken' || item.categoryId?.toLowerCase() === 'veg') {
    return defaultImages[1]; // Use classic pork momos for food
  } else {
    return defaultImages[2]; // Use matcha latte as general default
  }
};

export default function MenuScreen() {
  console.log('🍽️ MenuScreen: Component rendered');
  
  // Tab state
  const [activeTab, setActiveTab] = useState<MainTab>('combo');
  const [activeFoodTab, setActiveFoodTab] = useState<FoodSubTab>('buff');
  const [activeDrinksTab, setActiveDrinksTab] = useState<DrinksSubTab>('hot');
  const [showIngredientsModal, setShowIngredientsModal] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<MenuItem | null>(null);
  
  // Local UI state
  const [query, setQuery] = useState('');
  const [showSearchModal, setShowSearchModal] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [isPulling, setIsPulling] = useState(false);
  const [addingItems, setAddingItems] = useState<Set<string>>(new Set());
  
  // Animations
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const scrollY = useRef(new Animated.Value(0)).current;
  
  useEffect(() => {
    // Create pulsing animation
    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1.15,
          duration: 1000,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
        }),
      ])
    );
    pulse.start();
    
    return () => pulse.stop();
  }, []);
  
  // Cart store
  const addToCart = useCartSyncStore((state) => state.addItem);

  // Fetch menu data with timeout
  const { data, isLoading, isError, error, refetch } = useMenu();
  
  // Debug logging
  console.log('🍽️ Menu Page: useMenu result:', {
    isLoading,
    isError,
    error: error?.message,
    dataLength: data?.items?.length || 0,
    categoriesLength: data?.categories?.length || 0,
    dataSource: data?.items?.[0]?.categoryId?.includes('cat-') ? 'FALLBACK' : 'API'
  });

  // Test API call directly
  useEffect(() => {
    const testApiCall = async () => {
      try {
        console.log('🍽️ Testing direct API call to /menu...');
        const response = await fetch('http://192.168.56.1:8000/api/menu');
        console.log('🍽️ API Response status:', response.status);
        console.log('🍽️ API Response headers:', response.headers);
        
        const responseText = await response.text();
        console.log('🍽️ Raw API response:', responseText.substring(0, 500) + '...');
        
        const result = JSON.parse(responseText);
        console.log('🍽️ Direct API call result:', {
          status: response.status,
          success: result.success,
          itemsCount: result.data?.items?.length || 0,
          sampleItem: result.data?.items?.[0] ? {
            id: result.data.items[0].id,
            name: result.data.items[0].name,
            categoryId: result.data.items[0].categoryId
          } : null
        });
      } catch (error) {
        console.error('🍽️ Direct API call failed:', error);
        console.error('🍽️ Error details:', error instanceof Error ? error.message : String(error));
      }
    };
    
    testApiCall();
  }, []);

  // Featured carousel items (matching Laravel web carousel)
  const featuredItems = [
    {
      id: '1',
      title: 'Premium Nepali Momo',
      subtitle: 'Authentic flavors from the Himalayas',
      imageUrl: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=800&h=400&fit=crop',
      onPress: () => console.log('Navigate to menu'),
    },
    {
      id: '2', 
      title: 'Special Combo Offers',
      subtitle: 'Get more for less with our combo deals',
      imageUrl: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=800&h=400&fit=crop',
      onPress: () => console.log('Navigate to menu'),
    },
    {
      id: '3',
      title: 'Fresh Daily Specials',
      subtitle: 'New flavors added every day',
      imageUrl: 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&h=400&fit=crop',
      onPress: () => console.log('Navigate to menu'),
    },
  ];

  // Filter items based on active tab and search query
  const filteredItems = useMemo(() => {
    if (!data?.items) {
      console.log('🍽️ Menu Page: No items data available');
      return [];
    }
    
    console.log('🍽️ Menu Page: Total items available:', data.items.length);
    console.log('🍽️ Menu Page: Sample items:', data.items.slice(0, 3).map(item => ({
      id: item.id,
      name: item.name,
      categoryId: item.categoryId,
      image: item.image,
      imageUrl: item.imageUrl
    })));
    let items = data.items;
    
    // Filter by main tab
    console.log('🍽️ Menu Page: Filtering for tab:', activeTab);
    switch (activeTab) {
      case 'combo':
        console.log('🍽️ Menu Page: Looking for combo items');
        items = items.filter(item => {
          const categoryId = item.categoryId?.toLowerCase() || '';
          const matches = categoryId.includes('combo') || categoryId === 'bulk';
          if (matches) console.log('🍽️ Menu Page: Found combo item:', item.name, 'categoryId:', item.categoryId);
          return matches;
        });
        break;
      case 'food':
        items = items.filter(item => {
          const categoryId = item.categoryId?.toLowerCase() || '';
          // Handle both API format and fallback format (cat-momo, cat-buff, etc.)
          const isFood = ['buff', 'chicken', 'veg', 'main', 'side', 'momo', 'cat-momo', 'cat-buff', 'cat-chicken', 'cat-veg', 'cat-main', 'cat-side'].includes(categoryId);
          if (isFood) console.log('🍽️ Menu Page: Found food item:', item.name, 'categoryId:', item.categoryId);
          return isFood;
        });
        
        // Further filter by food sub-tab
        switch (activeFoodTab) {
          case 'buff':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              console.log('🍽️ Menu Page: Checking item for buff:', item.name, 'categoryId:', item.categoryId, 'lowercase:', categoryId);
              // Handle both API format and fallback format
              return categoryId === 'buff' || categoryId === 'momo' || categoryId === 'cat-momo' || categoryId === 'cat-buff';
            });
            break;
          case 'chicken':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              return categoryId === 'chicken' || categoryId === 'cat-chicken';
            });
            break;
          case 'veg':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              return categoryId === 'veg' || categoryId === 'cat-veg';
            });
            break;
          case 'others':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              return ['main', 'side', 'cat-main', 'cat-side'].includes(categoryId);
            });
            break;
        }
        break;
      case 'drinks':
        items = items.filter(item => {
          const categoryId = item.categoryId?.toLowerCase() || '';
          // Handle both API format and fallback format
          const isDrink = ['cold', 'hot', 'boba', 'cat-cold', 'cat-hot', 'cat-boba'].includes(categoryId);
          if (isDrink) console.log('🍽️ Menu Page: Found drink item:', item.name, 'categoryId:', item.categoryId);
          return isDrink;
        });
        
        // Further filter by drinks sub-tab
        switch (activeDrinksTab) {
          case 'hot':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              return categoryId === 'hot' || categoryId === 'cat-hot';
            });
            break;
          case 'cold':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              return categoryId === 'cold' || categoryId === 'cat-cold';
            });
            break;
          case 'boba':
            items = items.filter(item => {
              const categoryId = item.categoryId?.toLowerCase() || '';
              return categoryId === 'boba' || categoryId === 'cat-boba';
            });
            break;
        }
        break;
      case 'desserts':
        items = items.filter(item => {
          const categoryId = item.categoryId?.toLowerCase() || '';
          // Handle both API format and fallback format
          const matches = categoryId === 'desserts' || categoryId === 'cat-desserts';
          if (matches) console.log('🍽️ Menu Page: Found dessert item:', item.name, 'categoryId:', item.categoryId);
          return matches;
        });
        break;
    }
    
    // Filter by search query
    if (query.trim()) {
      const searchTerm = query.toLowerCase();
      items = items.filter(item => 
        item.name.toLowerCase().includes(searchTerm) ||
        item.desc?.toLowerCase().includes(searchTerm)
      );
    }
    
    console.log('🍽️ Menu Page: Filtered items count:', items.length, 'for tab:', activeTab, 'sub-tab:', activeTab === 'food' ? activeFoodTab : activeTab === 'drinks' ? activeDrinksTab : 'none');
    return items;
  }, [data?.items, activeTab, activeFoodTab, activeDrinksTab, query]);

  // Handle refresh
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
    } finally {
      setRefreshing(false);
    }
  };

  // Handle product info press
  const handleProductInfoPress = (product: MenuItem) => {
    setSelectedProduct(product);
    setShowIngredientsModal(true);
  };

  // Handle add to cart - same as featured product card
  const handleAddToCart = async (item: MenuItem) => {
    if (addingItems.has(item.id)) return;
    
    setAddingItems(prev => new Set(prev).add(item.id));
    
    const cartItem = {
      itemId: item.id,
      name: item.name,
      unitBasePrice: { currency: 'NPR' as const, amount: item.price || 0 },
      qty: 1,
      imageUrl: getValidImageUrl(item),
    };
    
    // Add to cart with callback to open the new sheet
    addToCart(cartItem, (payload) => {
      // Open the new cart added sheet
      (global as any).openCartAddedSheet?.(payload);
    });
    
    // Reset loading state after a short delay
    setTimeout(() => {
      setAddingItems(prev => {
        const newSet = new Set(prev);
        newSet.delete(item.id);
        return newSet;
      });
    }, 500);
  };

  // Get background color based on active tab
  const getBackgroundColor = () => {
    if (activeTab === 'food') {
      switch (activeFoodTab) {
        case 'buff': return '#F4E9E1';
        case 'chicken': return '#FEF3C7';
        case 'veg': return '#D1FAE5';
        case 'others': return '#E0E7FF';
        default: return '#F4E9E1';
      }
    }
    return '#F4E9E1';
  };

  // Handle retry on error
  const handleRetry = () => {
    refetch();
  };

  // Render skeleton loading
  const renderSkeletonItems = () => {
    const skeletonItems = Array.from({ length: 6 }, (_, index) => (
      <View key={index} style={{ width: itemWidth, marginBottom: spacing.md }}>
        <SkeletonCard height={200} />
      </View>
    ));

    return (
      <View style={styles.gridContainer}>
        {skeletonItems}
      </View>
    );
  };

  // Render menu item
  const renderMenuItem = ({ item }: { item: MenuItem }) => (
    <ItemCard 
      item={item} 
      onPress={() => router.push(`/item/${item.id}`)}
    />
  );

  // Render error state
  const renderErrorState = () => (
    <View style={styles.errorContainer}>
      <Card style={styles.errorCard} padding="lg" radius="md" shadow="light">
        <Text style={styles.errorTitle}>Something went wrong</Text>
        <Text style={styles.errorMessage}>
          {error?.message || 'Failed to load menu items. Please try again.'}
        </Text>
        <Button
          title="Retry"
          onPress={handleRetry}
          variant="solid"
          size="md"
          style={styles.retryButton}
        />
      </Card>
    </View>
  );

  // Render empty state
  const renderEmptyState = () => (
    <View style={styles.emptyContainer}>
      <Text style={styles.emptyTitle}>No items found</Text>
      <Text style={styles.emptyMessage}>
        {query.trim() 
          ? `No items match "${query}" in this category`
          : 'No items available in this category'
        }
      </Text>
      <Button
        title="Clear Filters"
        onPress={() => {
          setQuery('');
        }}
        variant="outline"
        size="md"
        style={styles.clearFiltersButton}
      />
    </View>
  );

  // Render offers banner
  const renderOffersBanner = () => (
    <Card style={styles.offersBanner} padding="md" radius="md" shadow="light">
      <Text style={styles.offersTitle}>🎉 Offers Coming Soon!</Text>
      <Text style={styles.offersMessage}>
        Stay tuned for exciting deals and discounts on your favorite momos and drinks.
      </Text>
    </Card>
  );

  // Show loading state only for a limited time, then show fallback
  const [showFallback, setShowFallback] = useState(false);
  
  useEffect(() => {
    if (isLoading && !data) {
      const timer = setTimeout(() => {
        setShowFallback(true);
      }, 5000); // Show fallback after 5 seconds
      
      return () => clearTimeout(timer);
    } else {
      setShowFallback(false);
    }
  }, [isLoading, data]);

  if (isLoading && !data && !showFallback) {
    return (
      <View style={[styles.container, { backgroundColor: getBackgroundColor() }]}>
        {/* Main Tab Navigation - Show during loading */}
        <View style={styles.tabContainer}>
          <View style={styles.tabBar}>
            {(['combo', 'food', 'drinks', 'desserts'] as MainTab[]).map((tab) => (
              <Pressable
                key={tab}
                style={[
                  styles.tabButton,
                  activeTab === tab && styles.activeTabButton
                ]}
                onPress={() => setActiveTab(tab)}
              >
                <Text style={[
                  styles.tabText,
                  activeTab === tab && styles.activeTabText
                ]}>
                  {tab.toUpperCase()}
                </Text>
              </Pressable>
            ))}
          </View>
        </View>

        {/* Food Sub-Tabs (only show when food tab is active) */}
        {activeTab === 'food' && (
          <View style={styles.subTabContainer}>
            <View style={styles.subTabBar}>
              {(['buff', 'chicken', 'veg', 'others'] as FoodSubTab[]).map((subTab) => (
                <Pressable
                  key={subTab}
                  style={[
                    styles.subTabButton,
                    activeFoodTab === subTab && styles.activeSubTabButton
                  ]}
                  onPress={() => setActiveFoodTab(subTab)}
                >
                  <Text style={[
                    styles.subTabText,
                    activeFoodTab === subTab && styles.activeSubTabText
                  ]}>
                    {subTab.toUpperCase()}
                  </Text>
                </Pressable>
              ))}
            </View>
          </View>
        )}

        {/* Drinks Sub-Tabs (only show when drinks tab is active) */}
        {activeTab === 'drinks' && (
          <View style={styles.subTabContainer}>
            <View style={styles.subTabBar}>
              {(['hot', 'cold', 'boba'] as DrinksSubTab[]).map((subTab) => (
                <Pressable
                  key={subTab}
                  style={[
                    styles.subTabButton,
                    activeDrinksTab === subTab && styles.activeSubTabButton
                  ]}
                  onPress={() => setActiveDrinksTab(subTab)}
                >
                  <Text style={[
                    styles.subTabText,
                    activeDrinksTab === subTab && styles.activeSubTabText
                  ]}>
                    {subTab.toUpperCase()}
                  </Text>
                </Pressable>
              ))}
            </View>
          </View>
        )}
        
        {/* Loading skeleton items */}
        <ScrollView style={styles.scrollContainer}>
          <View style={styles.itemsGrid}>
            {renderSkeletonItems()}
          </View>
        </ScrollView>
      </View>
    );
  }

  // Show fallback data if loading takes too long
  if (showFallback && !data) {
    return (
      <View style={[styles.container, { backgroundColor: getBackgroundColor() }]}>
        {/* Main Tab Navigation */}
        <View style={styles.tabContainer}>
          <View style={styles.tabBar}>
            {(['combo', 'food', 'drinks', 'desserts'] as MainTab[]).map((tab) => (
              <Pressable
                key={tab}
                style={[
                  styles.tabButton,
                  activeTab === tab && styles.activeTabButton
                ]}
                onPress={() => setActiveTab(tab)}
              >
                <Text style={[
                  styles.tabText,
                  activeTab === tab && styles.activeTabText
                ]}>
                  {tab.toUpperCase()}
                </Text>
              </Pressable>
            ))}
          </View>
        </View>
        
        <View style={styles.fallbackContainer}>
          <Text style={styles.fallbackTitle}>Unable to Load Menu</Text>
          <Text style={styles.fallbackMessage}>
            Unable to connect to server. Please check your connection and try again.
          </Text>
          <TouchableOpacity 
            style={styles.retryButton}
            onPress={() => {
              setShowFallback(false);
              refetch();
            }}
          >
            <Text style={styles.retryButtonText}>Try Again</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  if (isError) {
    return (
      <View style={[styles.container, { backgroundColor: getBackgroundColor() }]}>
        {/* Main Tab Navigation */}
        <View style={styles.tabContainer}>
          <View style={styles.tabBar}>
            {(['combo', 'food', 'drinks', 'desserts'] as MainTab[]).map((tab) => (
              <Pressable
                key={tab}
                style={[
                  styles.tabButton,
                  activeTab === tab && styles.activeTabButton
                ]}
                onPress={() => setActiveTab(tab)}
              >
                <Text style={[
                  styles.tabText,
                  activeTab === tab && styles.activeTabText
                ]}>
                  {tab.toUpperCase()}
                </Text>
              </Pressable>
            ))}
          </View>
        </View>
        
        {renderErrorState()}
      </View>
    );
  }

  // Get current category display text
  const getCurrentCategoryText = (): string => {
    switch (activeTab) {
      case 'combo':
        return 'COMBO';
      case 'desserts':
        return 'DESSERT';
      case 'food':
        return `FOOD ${activeFoodTab.toUpperCase()}`;
      case 'drinks':
        return `DRINKS ${activeDrinksTab.toUpperCase()}`;
      default:
        return (activeTab as string).toUpperCase();
    }
  };


  return (
    <View style={[styles.container, { backgroundColor: getBackgroundColor() }]}>
      {/* Main Tab Navigation */}
      <View style={styles.tabContainer}>
        <View style={styles.tabBar}>
          {(['combo', 'food', 'drinks', 'desserts'] as MainTab[]).map((tab) => (
            <Pressable
              key={tab}
              style={[
                styles.tabButton,
                activeTab === tab && styles.activeTabButton
              ]}
              onPress={() => setActiveTab(tab)}
            >
              <Text style={[
                styles.tabText,
                activeTab === tab && styles.activeTabText
              ]}>
                {tab.toUpperCase()}
              </Text>
            </Pressable>
          ))}
        </View>
      </View>

      {/* Food Sub-Tabs (only show when food tab is active) */}
      {activeTab === 'food' && (
        <View style={styles.subTabContainer}>
          <View style={styles.subTabBar}>
            {(['buff', 'chicken', 'veg', 'others'] as FoodSubTab[]).map((subTab) => (
              <Pressable
                key={subTab}
                style={[
                  styles.subTabButton,
                  activeFoodTab === subTab && styles.activeSubTabButton
                ]}
                onPress={() => setActiveFoodTab(subTab)}
              >
                <Text style={[
                  styles.subTabText,
                  activeFoodTab === subTab && styles.activeSubTabText
                ]}>
                  {subTab.toUpperCase()}
                </Text>
              </Pressable>
            ))}
          </View>
        </View>
      )}

      {/* Drinks Sub-Tabs (only show when drinks tab is active) */}
      {activeTab === 'drinks' && (
        <View style={styles.subTabContainer}>
          <View style={styles.subTabBar}>
            {(['hot', 'cold', 'boba'] as DrinksSubTab[]).map((subTab) => (
              <Pressable
                key={subTab}
                style={[
                  styles.subTabButton,
                  activeDrinksTab === subTab && styles.activeSubTabButton
                ]}
                onPress={() => setActiveDrinksTab(subTab)}
              >
                <Text style={[
                  styles.subTabText,
                  activeDrinksTab === subTab && styles.activeSubTabText
                ]}>
                  {subTab.toUpperCase()}
                </Text>
              </Pressable>
            ))}
          </View>
        </View>
      )}


      {/* Menu Items Grid */}
      <AnimatedScrollView 
        style={styles.scrollContainer}
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
        <View style={styles.itemsGrid}>
          {filteredItems.map((item) => {
            return (
            <View key={item.id} style={styles.itemCardLarge}>
              <Pressable 
                style={styles.productCardLarge}
                onPress={() => handleProductInfoPress(item)}
              >
                <Image 
                  source={{ 
                    uri: getValidImageUrl(item)
                  }}
                  style={styles.productImage}
                  resizeMode="cover"
                  onError={(error) => {
                    console.log('🍽️ Image load error for', item.name, ':', error.nativeEvent.error);
                    console.log('🍽️ Image URL:', item.image);
                    console.log('🍽️ ImageUrl URL:', item.imageUrl);
                  }}
                  onLoad={() => {
                    console.log('🍽️ Image loaded successfully for:', item.name);
                  }}
                />
                
                {/* Gradient overlay */}
                <LinearGradient
                  colors={['transparent', 'rgba(0,0,0,0.4)']}
                  style={styles.imageGradient}
                />
                
                {/* Featured badge */}
                {item.isFeatured && (
                  <View style={styles.featuredBadge}>
                    <Text style={styles.featuredText}>⭐ Featured</Text>
        </View>
      )}
                
                {/* Category badge */}
                <View style={styles.categoryBadge}>
                  <Text style={styles.categoryBadgeText}>{getCurrentCategoryText()}</Text>
                </View>
                
                {/* Product info overlay */}
                <View style={styles.productInfoOverlay}>
                  <View style={styles.mainContent}>
                    {/* Left side - Text content and Info button */}
                    <View style={styles.textContent}>
                      <Text style={styles.productNameLarge}>
                        {item.name}
                      </Text>
                      <Text style={styles.productDescriptionLarge}>
                        {item.desc || 'Delicious and authentic momo'}
                      </Text>
                      {/* Info Button */}
                      <Pressable
                        style={styles.infoButtonLarge}
                        onPress={() => handleProductInfoPress(item)}
                      >
                        <MCI name="information-outline" size={14} color={colors.white} />
                        <Text style={styles.infoButtonTextLarge}>Info</Text>
                      </Pressable>
                    </View>
                    
                    {/* Right side - Price and Add to Cart */}
                    <View style={styles.rightContent}>
                      {/* Price aligned to right */}
                      <View style={styles.priceContainer}>
                        <Text style={styles.priceLarge}>
                          Rs. {item.price || 0}
                        </Text>
                      </View>
                      {/* Add to Cart Button aligned to right */}
                      <View style={styles.buttonContainer}>
                        <Pressable 
                          style={[
                            styles.addToCartButtonLarge,
                            addingItems.has(item.id) && styles.addToCartButtonLoading
                          ]}
                          onPress={() => handleAddToCart(item)}
                          disabled={addingItems.has(item.id)}
                        >
                          {addingItems.has(item.id) ? (
                            <MCI name="loading" size={20} color={colors.white} />
                          ) : (
                            <MCI name="shopping-outline" size={20} color={colors.white} />
                          )}
                          <Text style={styles.addToCartTextLarge}>
                            {addingItems.has(item.id) ? 'Adding...' : 'Add'}
                          </Text>
                        </Pressable>
                      </View>
                    </View>
                  </View>
                </View>
              </Pressable>
            </View>
            );
          })}
        </View>
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

      {/* Product Info Sheet - Same as Featured Product Card */}
      <FoodInfoSheet
        visible={showIngredientsModal}
        onClose={() => setShowIngredientsModal(false)}
        data={{
          image: selectedProduct ? getValidImageUrl(selectedProduct) : '',
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

      {/* Floating Search Button with Pulse Animation */}
      <Animated.View 
        style={[
          styles.floatingSearchButton,
          { transform: [{ scale: pulseAnim }] }
        ]}
      >
        <TouchableOpacity 
          onPress={() => setShowSearchModal(true)}
          activeOpacity={0.8}
        >
          <LinearGradient
            colors={['#FF6B35', '#F7931E']} // Bright orange gradient - highly visible
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.floatingGradient}
          >
            <MCI name="magnify" size={32} color={colors.white} />
          </LinearGradient>
        </TouchableOpacity>
      </Animated.View>

      {/* Search Modal */}
      <Modal
        visible={showSearchModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => {
          setShowSearchModal(false);
          setQuery('');
        }}
      >
        <View style={styles.searchModalOverlay}>
          <View style={styles.searchModalContent}>
            <View style={styles.searchModalHeader}>
              <Text style={styles.searchModalTitle}>Search Menu</Text>
              <TouchableOpacity onPress={() => {
                setShowSearchModal(false);
                setQuery('');
              }}>
                <MCI name="close" size={24} color={colors.gray[600]} />
              </TouchableOpacity>
            </View>
            
            <View style={styles.searchInputWrapper}>
              <MCI name="magnify" size={20} color={colors.gray[400]} style={styles.searchIcon} />
              <TextInput
                style={styles.searchModalInput}
                placeholder="Search for momos, drinks, desserts..."
                value={query}
                onChangeText={setQuery}
                autoFocus={true}
                placeholderTextColor={colors.gray[400]}
              />
              {query.length > 0 && (
                <TouchableOpacity onPress={() => setQuery('')}>
                  <MCI name="close-circle" size={20} color={colors.gray[400]} />
                </TouchableOpacity>
              )}
            </View>

            <ScrollView style={styles.searchResults}>
              {query.length > 0 ? (
                filteredItems.length > 0 ? (
                  filteredItems.map((item) => (
                    <TouchableOpacity
                      key={item.id}
                      style={styles.searchResultItem}
                      onPress={() => {
                        setShowSearchModal(false);
                        handleProductInfoPress(item);
                      }}
                    >
                      <Image 
                        source={{ uri: getValidImageUrl(item) }}
                        style={styles.searchResultImage}
                      />
                      <View style={styles.searchResultInfo}>
                        <Text style={styles.searchResultName}>{item.name}</Text>
                        <Text style={styles.searchResultPrice}>Rs. {item.price}</Text>
                      </View>
                      <TouchableOpacity
                        style={styles.quickAddButton}
                        onPress={(e) => {
                          e.stopPropagation();
                          handleAddToCart(item);
                        }}
                      >
                        <MCI name="plus" size={20} color={colors.white} />
                      </TouchableOpacity>
                    </TouchableOpacity>
                  ))
                ) : (
                  <View style={styles.noResultsContainer}>
                    <MCI name="food-off" size={48} color={colors.gray[400]} />
                    <Text style={styles.noResultsText}>No items found</Text>
                    <Text style={styles.noResultsSubtext}>Try a different search term</Text>
                  </View>
                )
              ) : (
                <View style={styles.searchPlaceholder}>
                  <MCI name="magnify" size={48} color={colors.gray[300]} />
                  <Text style={styles.searchPlaceholderText}>Start typing to search</Text>
                </View>
              )}
            </ScrollView>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  // Tab Navigation Styles
  tabContainer: {
    paddingTop: spacing.lg,
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.sm,
  },
  tabBar: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.xs,
    flexDirection: 'row',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  tabButton: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.xs,
    borderRadius: radius.md,
    alignItems: 'center',
  },
  activeTabButton: {
    backgroundColor: colors.brand.primary,
    shadowColor: colors.brand.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  tabText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.gray[600],
  },
  activeTabText: {
    color: colors.white,
  },
  // Sub-Tab Styles
  subTabContainer: {
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.sm,
  },
  subTabBar: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.xs,
    flexDirection: 'row',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  subTabButton: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.xs,
    borderRadius: radius.md,
    alignItems: 'center',
  },
  activeSubTabButton: {
    backgroundColor: colors.brand.primary,
  },
  subTabText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
  },
  activeSubTabText: {
    color: colors.white,
  },
  // Search Styles
  searchContainer: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.md,
  },
  searchInput: {
    marginVertical: 0,
  },
  // Scroll and Grid Styles
  scrollContainer: {
    flex: 1,
  },
  itemsGrid: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl,
  },
  itemCard: {
    width: '100%',
    marginBottom: spacing.sm,
  },
  itemCardLarge: {
    width: '100%',
    marginBottom: spacing.lg,
  },
  productCard: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    height: 240,
  },
  productCardLarge: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 6,
    height: 320, // Increased height for larger cards
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  imageGradient: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 120,
  },
  featuredBadge: {
    position: 'absolute',
    top: spacing.sm,
    right: spacing.sm,
    backgroundColor: '#152039',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  featuredText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  categoryBadge: {
    position: 'absolute',
    top: spacing.sm,
    left: spacing.sm,
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.full,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 3,
  },
  categoryBadgeText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  productInfoOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: spacing.xs,
    zIndex: 10,
  },
  mainContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    minHeight: 60,
  },
  textContent: {
    flex: 1,
    marginRight: spacing.xs,
    maxWidth: '65%',
  },
  rightContent: {
    alignItems: 'flex-end',
  },
  priceContainer: {
    alignItems: 'flex-start',
    marginBottom: spacing.xs,
    width: '100%',
  },
  buttonContainer: {
    alignItems: 'flex-end',
    width: '100%',
  },
  productName: {
    fontSize: 9,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.xs,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: radius.sm,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 1,
    lineHeight: 11,
  },
  productNameLarge: {
    fontSize: 14,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.sm,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 1,
    lineHeight: 18,
  },
  productDescription: {
    fontSize: 8,
    color: colors.white,
    marginBottom: spacing.xs,
    opacity: 0.9,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: radius.sm,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 1,
    lineHeight: 10,
  },
  productDescriptionLarge: {
    fontSize: 12,
    color: colors.white,
    marginBottom: spacing.sm,
    opacity: 0.9,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    alignSelf: 'flex-start',
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 1,
    lineHeight: 16,
  },
  price: {
    fontSize: 11,
    fontWeight: fontWeights.bold,
    color: '#FCD34D',
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    marginBottom: 4,
  },
  priceLarge: {
    fontSize: 16,
    fontWeight: fontWeights.bold,
    color: '#FCD34D',
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    marginBottom: 8,
  },
  infoButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#3B82F6',
    paddingHorizontal: spacing.xs,
    paddingVertical: 2,
    borderRadius: radius.sm,
    gap: 2,
    shadowColor: '#3B82F6',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 1,
    elevation: 1,
    alignSelf: 'flex-start',
    marginTop: 2,
  },
  infoButtonLarge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#3B82F6',
    paddingHorizontal: spacing.sm,
    paddingVertical: 6,
    borderRadius: radius.md,
    gap: 4,
    shadowColor: '#3B82F6',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 2,
    elevation: 2,
    alignSelf: 'flex-start',
    marginTop: 4,
  },
  infoButtonText: {
    color: colors.white,
    fontSize: 8,
    fontWeight: fontWeights.medium,
  },
  infoButtonTextLarge: {
    color: colors.white,
    fontSize: 12,
    fontWeight: fontWeights.medium,
  },
  addToCartButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EF4444',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    shadowColor: '#EF4444',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 2,
    elevation: 2,
    alignSelf: 'flex-end',
  },
  addToCartButtonLarge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EF4444',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    shadowColor: '#EF4444',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.4,
    shadowRadius: 3,
    elevation: 3,
    alignSelf: 'flex-end',
  },
  addToCartText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    marginLeft: spacing.sm,
  },
  addToCartTextLarge: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    marginLeft: spacing.md,
  },
  addToCartButtonLoading: {
    opacity: 0.7,
  },
  // Missing styles
  gridContainer: {
    flex: 1,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.lg,
  },
  errorCard: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.lg,
    alignItems: 'center',
    shadowColor: colors.gray[900],
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  errorTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  errorMessage: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  retryButton: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  retryButtonText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.lg,
  },
  emptyTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  emptyMessage: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  clearFiltersButton: {
    backgroundColor: colors.gray[100],
    borderColor: colors.gray[300],
  },
  offersBanner: {
    backgroundColor: colors.brand.primary,
    padding: spacing.lg,
    margin: spacing.md,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  offersTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.sm,
  },
  offersMessage: {
    fontSize: fontSizes.md,
    color: colors.white,
    textAlign: 'center',
    opacity: 0.9,
  },
  fallbackContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.lg,
  },
  fallbackTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  fallbackMessage: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  // Floating Search Button
  floatingSearchButton: {
    position: 'absolute',
    bottom: 80, // Just above the bottom navigation bar
    right: 16,
    width: 64,
    height: 64,
    borderRadius: 32,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.4,
    shadowRadius: 12,
    elevation: 12,
    zIndex: 1000,
  },
  floatingGradient: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: colors.white,
  },
  // Search Modal Styles
  searchModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  searchModalContent: {
    backgroundColor: colors.white,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    height: '90%',
    paddingTop: spacing.lg,
  },
  searchModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  searchModalTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  searchInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.gray[100],
    borderRadius: radius.lg,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    margin: spacing.lg,
  },
  searchIcon: {
    marginRight: spacing.sm,
  },
  searchModalInput: {
    flex: 1,
    fontSize: fontSizes.md,
    color: colors.gray[900],
    paddingVertical: spacing.xs,
  },
  searchResults: {
    flex: 1,
    paddingHorizontal: spacing.lg,
  },
  searchResultItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.md,
    marginBottom: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  searchResultImage: {
    width: 60,
    height: 60,
    borderRadius: radius.md,
    marginRight: spacing.md,
  },
  searchResultInfo: {
    flex: 1,
  },
  searchResultName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  searchResultPrice: {
    fontSize: fontSizes.sm,
    color: colors.brand.primary,
    fontWeight: fontWeights.bold,
  },
  quickAddButton: {
    backgroundColor: colors.brand.primary,
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
  },
  noResultsContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.xl * 2,
  },
  noResultsText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
    marginTop: spacing.md,
  },
  noResultsSubtext: {
    fontSize: fontSizes.sm,
    color: colors.gray[400],
    marginTop: spacing.xs,
  },
  searchPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.xl * 2,
  },
  searchPlaceholderText: {
    fontSize: fontSizes.md,
    color: colors.gray[400],
    marginTop: spacing.md,
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
