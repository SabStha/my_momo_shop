import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  ScrollView,
  Pressable,
  Image,
  TextInput,
  Alert,
  Dimensions,
  FlatList,
  Platform,
} from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
// import DateTimePicker from '@react-native-community/datetimepicker';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';
import { useBulkData, Product } from '../../api/bulk-hooks';
import { useCartSyncStore } from '../../state/cart-sync';

const { width: screenWidth } = Dimensions.get('window');

interface CustomBuilderModalProps {
  visible: boolean;
  onClose: () => void;
  initialPackage?: any;
}

interface CustomItem {
  id: number;
  name: string;
  category: string;
  quantity: number;
  regularPrice: number;
  bulkPrice: number;
  image?: string;
  preparationType: 'cooked' | 'frozen';
}

export default function CustomBuilderModal({ visible, onClose, initialPackage }: CustomBuilderModalProps) {
  const router = useRouter();
  const [orderType, setOrderType] = useState<'cooked' | 'frozen'>('cooked');
  const [deliveryDateTime, setDeliveryDateTime] = useState<Date>(new Date());
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [showCustomDatePicker, setShowCustomDatePicker] = useState(false);
  const [tempDate, setTempDate] = useState(new Date());
  const [tempTime, setTempTime] = useState(new Date());
  const [customItems, setCustomItems] = useState<CustomItem[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<string>('Food');
  const [selectedSubcategory, setSelectedSubcategory] = useState<string>('Buff');
  const [showProductSelector, setShowProductSelector] = useState(false);

  const addToCart = useCartSyncStore((state) => state.addItem);
  const { data: bulkData, isLoading: bulkLoading, error: bulkError } = useBulkData();
  
  // Debug logging
  console.log('🔍 CustomBuilder: Component rendering...');
  console.log('🔍 CustomBuilder: showProductSelector state:', showProductSelector);
  console.log('🔍 CustomBuilder: bulkData received:', bulkData);
  console.log('🔍 CustomBuilder: bulkLoading:', bulkLoading);
  console.log('🔍 CustomBuilder: bulkError:', bulkError);
  console.log('🔍 CustomBuilder: selectedCategory:', selectedCategory);
  console.log('🔍 CustomBuilder: selectedSubcategory:', selectedSubcategory);

  const categories = ['Food', 'Drinks', 'Desserts'];
  const bulkDiscountPercentage = bulkData?.bulkDiscountPercentage || 15;

  // Reset form when modal opens
  useEffect(() => {
    if (visible) {
      setCustomItems([]);
      setDeliveryDateTime(new Date());
      setShowProductSelector(false);
      setShowDatePicker(false);
      setShowCustomDatePicker(false);
      setTempDate(new Date());
      setTempTime(new Date());
    } else {
      // Clean up when modal closes
      setShowDatePicker(false);
      setShowCustomDatePicker(false);
      setTempDate(new Date());
      setTempTime(new Date());
      setShowProductSelector(false);
    }
  }, [visible]);

  const getSubcategories = (category: string): string[] => {
    if (category === 'Food') {
      return ['Buff', 'Chicken', 'Veg', 'Others'];
    } else if (category === 'Drinks') {
      return ['Hot', 'Cold', 'Boba'];
    }
    return [];
  };

  const getProductsByCategory = (category: string): Product[] => {
    console.log('🔍 CustomBuilder: getProductsByCategory called with category:', category);
    console.log('🔍 CustomBuilder: bulkData?.products:', bulkData?.products?.length || 0);
    
    if (!bulkData?.products) {
      console.log('🔍 CustomBuilder: No products data available');
      return [];
    }
    
    console.log('🔍 CustomBuilder: Available products:', bulkData.products.map(p => ({
      id: p.id,
      name: p.name,
      category: p.category,
      price: p.price
    })));
    
    let filteredProducts: Product[] = [];
    
    if (category === 'Food') {
      console.log('🔍 CustomBuilder: Filtering Food category, subcategory:', selectedSubcategory);
      if (selectedSubcategory === 'Buff') {
        // Filter by tag = 'buff' for buff momos
        filteredProducts = bulkData.products.filter((p: any) => 
          p.category === 'momo' && p.tag === 'buff'
        );
      } else if (selectedSubcategory === 'Chicken') {
        // Filter by tag = 'chicken' for chicken momos
        filteredProducts = bulkData.products.filter((p: any) => 
          p.category === 'momo' && p.tag === 'chicken'
        );
      } else if (selectedSubcategory === 'Veg') {
        // Filter by tag = 'veg' for vegetarian momos
        filteredProducts = bulkData.products.filter((p: any) => 
          p.category === 'momo' && p.tag === 'veg'
        );
      } else if (selectedSubcategory === 'Others') {
        // Filter other food items (sides, mains)
        filteredProducts = bulkData.products.filter((p: any) => 
          p.category === 'side' || p.category === 'main'
        );
      }
    } else if (category === 'Drinks') {
      console.log('🔍 CustomBuilder: Filtering Drinks category, subcategory:', selectedSubcategory);
      if (selectedSubcategory === 'Hot') {
        filteredProducts = bulkData.products.filter((p: any) => p.category === 'hot-drinks');
      } else if (selectedSubcategory === 'Cold') {
        filteredProducts = bulkData.products.filter((p: any) => p.category === 'cold-drinks');
      } else if (selectedSubcategory === 'Boba') {
        filteredProducts = bulkData.products.filter((p: any) => p.category === 'boba');
      } else {
        // Show all drinks
        filteredProducts = bulkData.products.filter((p: any) => 
          p.category === 'hot-drinks' || p.category === 'cold-drinks' || p.category === 'boba'
        );
      }
    } else if (category === 'Desserts') {
      console.log('🔍 CustomBuilder: Filtering Desserts category');
      filteredProducts = bulkData.products.filter((p: any) => 
        p.category === 'desserts'
      );
    }
    
    console.log('🔍 CustomBuilder: Filtered products count:', filteredProducts.length);
    console.log('🔍 CustomBuilder: Filtered products:', filteredProducts.map(p => ({
      id: p.id,
      name: p.name,
      category: p.category
    })));
    
    return filteredProducts;
  };

  const addProductToOrder = (product: Product) => {
    const existingItem = customItems.find(item => item.name === product.name && item.preparationType === orderType);
    const regularPrice = Number(product.price);
    const discountAmount = (regularPrice * bulkDiscountPercentage) / 100;
    const bulkPrice = regularPrice - discountAmount;

    if (existingItem) {
      setCustomItems(items => 
        items.map(item => 
          item.id === existingItem.id 
            ? { ...item, quantity: item.quantity + 1 }
            : item
        )
      );
    } else {
      const newItem: CustomItem = {
        id: product.id,
        name: product.name,
        category: product.category,
        quantity: 1,
        regularPrice,
        bulkPrice,
        image: product.image,
        preparationType: orderType
      };
      setCustomItems(items => [...items, newItem]);
    }
    // Don't close product selector - allow adding multiple items
    console.log('✅ Product added to custom order:', product.name);
  };

  const removeItem = (index: number) => {
    setCustomItems(items => items.filter((_, i) => i !== index));
  };

  const updateItemQuantity = (index: number, quantity: number) => {
    if (quantity < 1) return;
    setCustomItems(items => 
      items.map((item, i) => 
        i === index ? { ...item, quantity } : item
      )
    );
  };

  const updateItemPreparationType = (index: number, preparationType: 'cooked' | 'frozen') => {
    setCustomItems(items => 
      items.map((item, i) => 
        i === index ? { ...item, preparationType } : item
      )
    );
  };

  const clearOrder = () => {
    setCustomItems([]);
    setDeliveryDateTime(new Date());
  };

  const handleDatePickerPress = () => {
    console.log('📅 Date picker button pressed!');
    console.log('📅 Current deliveryDateTime:', deliveryDateTime);
    setTempDate(new Date(deliveryDateTime));
    setTempTime(new Date(deliveryDateTime));
    setShowCustomDatePicker(true);
    console.log('📅 Set showCustomDatePicker to true');
  };

  const handleCustomDateChange = (newDate: Date) => {
    setDeliveryDateTime(newDate);
    setShowCustomDatePicker(false);
  };

  const handleTempDateChange = (newDate: Date) => {
    setTempDate(newDate);
  };

  const handleTempTimeChange = (newTime: Date) => {
    setTempTime(newTime);
  };

  const handleConfirmCustomDateTime = () => {
    // Combine the selected date and time
    const combinedDateTime = new Date(tempDate);
    combinedDateTime.setHours(tempTime.getHours(), tempTime.getMinutes(), 0, 0);
    
    setDeliveryDateTime(combinedDateTime);
    setShowCustomDatePicker(false);
  };

  const formatDateTime = (date: Date) => {
    return date.toLocaleString('en-US', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getItemsByPreparationType = (type: 'cooked' | 'frozen') => {
    return customItems.filter(item => item.preparationType === type);
  };

  const getTotalPrice = () => {
    return customItems.reduce((sum, item) => sum + (item.bulkPrice * item.quantity), 0);
  };

  const getOriginalTotal = () => {
    return customItems.reduce((sum, item) => sum + (item.regularPrice * item.quantity), 0);
  };

  const getTotalSavings = () => {
    return getOriginalTotal() - getTotalPrice();
  };

  const getSavingsPercentage = () => {
    const originalTotal = getOriginalTotal();
    if (originalTotal === 0) return 0;
    return (getTotalSavings() / originalTotal) * 100;
  };

  const handleAddToCart = () => {
    console.log('🛒 CustomBuilder: handleAddToCart called');
    console.log('🛒 CustomBuilder: customItems:', customItems.length);
    console.log('🛒 CustomBuilder: getTotalPrice():', getTotalPrice());
    
    if (getTotalPrice() === 0 || customItems.length === 0) {
      console.log('🛒 CustomBuilder: No items to add to cart');
      Alert.alert('Empty Order', 'Please add some items to your custom order first.');
      return;
    }

    console.log('🛒 CustomBuilder: Adding items to cart...');
    
    // Add each item to cart
    customItems.forEach((item, index) => {
      const cartItem = {
        itemId: `custom-${item.id}-${item.preparationType}`,
        name: `${item.name} (${item.preparationType === 'cooked' ? 'Hot' : 'Frozen'})`,
        unitBasePrice: { currency: 'NPR' as const, amount: item.bulkPrice },
        qty: item.quantity,
        imageUrl: item.image ? `/storage/${item.image}` : undefined,
        metadata: {
          orderType: orderType,
          isBulk: true,
          isCustom: true,
        }
      };

      console.log(`🛒 CustomBuilder: Adding item ${index + 1}:`, cartItem);
      addToCart(cartItem);
    });

    console.log('🛒 CustomBuilder: Items added to cart, navigating to delivery page');
    
    // Close modal
    onClose();
    
    // Navigate directly to checkout/delivery page
    router.push('/checkout');
  };

  // Helper function to get a valid image URL with fallbacks (copied from menu.tsx)
  const getValidImageUrl = (product: Product): string => {
    const brokenImages = [
      'http://192.168.56.1:8000/storage/default.jpg',
      'default.jpg'
    ];
    
    const imageUrl = product.image;
    if (imageUrl && !brokenImages.some(broken => imageUrl.includes(broken))) {
      // Handle both relative and absolute URLs
      if (imageUrl.startsWith('http')) {
        return imageUrl;
      } else if (imageUrl.startsWith('storage/')) {
        return `http://192.168.2.142:8000/${imageUrl}`;
      } else {
        return `http://192.168.2.142:8000/storage/${imageUrl}`;
      }
    }
    
    // Default fallback image for momos
    return 'http://192.168.2.142:8000/storage/products/foods/veg-momos.jpg';
  };

  // Get background color based on active tab (copied from menu.tsx)
  const getBackgroundColor = () => {
    if (selectedCategory === 'Food') {
      switch (selectedSubcategory.toLowerCase()) {
        case 'buff': return '#F4E9E1';
        case 'chicken': return '#FEF3C7';
        case 'veg': return '#D1FAE5';
        case 'others': return '#E0E7FF';
        default: return '#F4E9E1';
      }
    }
    return '#F4E9E1';
  };

  // Get current category display text (copied from menu.tsx)
  const getCurrentCategoryText = (): string => {
    switch (selectedCategory) {
      case 'Food':
        return `MOMO ${selectedSubcategory.toUpperCase()}`;
      case 'Drinks':
        return `DRINKS ${selectedSubcategory.toUpperCase()}`;
      case 'Desserts':
        return 'DESSERT';
      case 'Sides':
        return 'SIDES';
      default:
        return selectedCategory.toUpperCase();
    }
  };


  const renderSelectedItem = (item: CustomItem, index: number) => (
    <View key={index} style={styles.selectedItemCard}>
      <View style={styles.selectedItemHeader}>
        <View style={styles.selectedItemImageContainer}>
          {item.image ? (
            <Image
              source={{ uri: `/storage/${item.image}` }}
              style={styles.selectedItemImage}
            />
          ) : (
            <View style={styles.selectedItemImagePlaceholder}>
              <MCI name="image" size={16} color="#9CA3AF" />
            </View>
          )}
        </View>
        <View style={styles.selectedItemInfo}>
          <Text style={styles.selectedItemName}>{item.name}</Text>
          <Text style={styles.selectedItemCategory}>Category: {item.category}</Text>
        </View>
        <Pressable
          style={styles.removeButton}
          onPress={() => removeItem(index)}
        >
          <MCI name="delete" size={16} color="#FFFFFF" />
        </Pressable>
      </View>

      <View style={styles.selectedItemControls}>
        <View style={styles.controlRow}>
          <View style={styles.controlItem}>
            <Text style={styles.controlLabel}>Preparation</Text>
            <View style={styles.preparationSelector}>
              <Pressable
                style={[
                  styles.preparationOption,
                  item.preparationType === 'cooked' && styles.preparationOptionActive
                ]}
                onPress={() => updateItemPreparationType(index, 'cooked')}
              >
                <Text style={[
                  styles.preparationOptionText,
                  item.preparationType === 'cooked' && styles.preparationOptionTextActive
                ]}>
                  🔥 Hot
                </Text>
              </Pressable>
              <Pressable
                style={[
                  styles.preparationOption,
                  item.preparationType === 'frozen' && styles.preparationOptionActive
                ]}
                onPress={() => updateItemPreparationType(index, 'frozen')}
              >
                <Text style={[
                  styles.preparationOptionText,
                  item.preparationType === 'frozen' && styles.preparationOptionTextActive
                ]}>
                  ❄️ Frozen
                </Text>
              </Pressable>
            </View>
          </View>

          <View style={styles.controlItem}>
            <Text style={styles.controlLabel}>Quantity</Text>
            <View style={styles.quantityControls}>
              <Pressable
                style={styles.quantityButton}
                onPress={() => updateItemQuantity(index, item.quantity - 1)}
              >
                <MCI name="minus" size={16} color="#6B7280" />
              </Pressable>
              <Text style={styles.quantityText}>{item.quantity}</Text>
              <Pressable
                style={styles.quantityButton}
                onPress={() => updateItemQuantity(index, item.quantity + 1)}
              >
                <MCI name="plus" size={16} color="#6B7280" />
              </Pressable>
            </View>
          </View>
        </View>

        <View style={styles.priceRow}>
          <View style={styles.priceItem}>
            <Text style={styles.priceLabel}>Regular (Rs.)</Text>
            <Text style={styles.regularPrice}>{(item.regularPrice * item.quantity).toFixed(2)}</Text>
          </View>
          <View style={styles.priceItem}>
            <Text style={styles.priceLabel}>Bulk (Rs.)</Text>
            <Text style={styles.bulkPrice}>{(item.bulkPrice * item.quantity).toFixed(2)}</Text>
          </View>
        </View>
      </View>
    </View>
  );

  return (
    <>
      <Modal
        visible={visible}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={onClose}
      >
        <View style={styles.container}>
          <View style={styles.header}>
            <Text style={styles.title}>✍️ Build Your Own Custom Order</Text>
            <Text style={styles.subtitle}>Have something specific in mind? Create your perfect order!</Text>
            <Pressable onPress={onClose} style={styles.closeButton}>
              <MCI name="close" size={24} color="#6B7280" />
            </Pressable>
          </View>

          <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
            {/* Order Type Selection */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Default Order Type</Text>
              <View style={styles.orderTypeRow}>
                <Pressable
                  style={[
                    styles.orderTypeButtonCompact,
                    orderType === 'cooked' && styles.orderTypeButtonActive
                  ]}
                  onPress={() => setOrderType('cooked')}
                >
                  <Text style={styles.orderTypeIconCompact}>🔥</Text>
                  <View style={styles.orderTypeTextCompact}>
                    <Text style={styles.orderTypeTitleCompact}>Hot & Ready</Text>
                    <Text style={styles.orderTypeDescriptionCompact}>Cooked</Text>
                  </View>
                </Pressable>
                <Pressable
                  style={[
                    styles.orderTypeButtonCompact,
                    orderType === 'frozen' && styles.orderTypeButtonActive
                  ]}
                  onPress={() => setOrderType('frozen')}
                >
                  <Text style={styles.orderTypeIconCompact}>❄️</Text>
                  <View style={styles.orderTypeTextCompact}>
                    <Text style={styles.orderTypeTitleCompact}>Frozen</Text>
                    <Text style={styles.orderTypeDescriptionCompact}>Freezer Ready</Text>
                  </View>
                </Pressable>
              </View>

              <View style={styles.infoBoxCompact}>
                <MCI name="information" size={16} color="#2563EB" />
                <View style={styles.infoContentCompact}>
                  <Text style={styles.infoTitleCompact}>💡 Mix & Match Available!</Text>
                  <Text style={styles.infoDescriptionCompact}>
                    Choose different preparation types for each item.
                  </Text>
                </View>
              </View>

              <View style={styles.dateTimeContainer}>
                <Text style={styles.dateTimeLabel}>When do you need it?</Text>
                <Pressable
                  style={styles.dateTimeInput}
                  onPress={handleDatePickerPress}
                >
                  <Text style={styles.dateTimeText}>
                    {formatDateTime(deliveryDateTime)}
                  </Text>
                  <MCI name="calendar-clock" size={20} color="#6B7280" />
                </Pressable>
                
                <Text style={styles.dateTimeFallback}>
                  Tap above to select date and time
                </Text>
              </View>
            </View>

            {/* Menu Selection */}
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Select Items from Menu</Text>

              {customItems.length > 0 ? (
                <View style={styles.selectedItemsContainer}>
                  {customItems.map((item, index) => renderSelectedItem(item, index))}
                </View>
              ) : (
                <View style={styles.emptyState}>
                    <MCI name="shopping-outline" size={48} color="#9CA3AF" />
                  <Text style={styles.emptyStateTitle}>No items added yet</Text>
                  <Text style={styles.emptyStateDescription}>
                    Select items from our menu to create your custom order.
                  </Text>
                  <Pressable
                    style={styles.emptyStateButton}
                    onPress={() => {
                      console.log('📋 Browse Menu - Toggling product selector');
                      console.log('📋 Before toggle - showProductSelector:', showProductSelector);
                      setShowProductSelector(prev => {
                        console.log('📋 Toggling from', prev, 'to', !prev);
                        return !prev;
                      });
                    }}
                  >
                    <Text style={styles.emptyStateButtonText}>
                      {showProductSelector ? 'Hide Menu' : 'Browse Menu'}
                    </Text>
                  </Pressable>
                </View>
              )}
              
            </View>

            {/* Order Summary */}
            {customItems.length > 0 && (
              <View style={styles.section}>
                <Text style={styles.sectionTitle}>Order Summary</Text>
                <View style={styles.summaryContainer}>
                  <View style={styles.summaryRow}>
                    <Text style={styles.summaryLabel}>Total Items:</Text>
                    <Text style={styles.summaryValue}>{customItems.length} items</Text>
                  </View>
                  
                  <View style={styles.summaryRow}>
                    <Text style={styles.summaryLabel}>🔥 Hot & Ready:</Text>
                    <Text style={styles.summaryValue}>{getItemsByPreparationType('cooked').length} items</Text>
                  </View>
                  
                  <View style={styles.summaryRow}>
                    <Text style={styles.summaryLabel}>❄️ Frozen:</Text>
                    <Text style={styles.summaryValue}>{getItemsByPreparationType('frozen').length} items</Text>
                  </View>
                  
                  <View style={styles.summaryRow}>
                    <Text style={styles.summaryLabel}>Original Total:</Text>
                    <Text style={styles.originalTotalPrice}>Rs. {getOriginalTotal().toFixed(2)}</Text>
                  </View>
                  
                  <View style={styles.summaryRow}>
                    <Text style={styles.summaryLabel}>Total Savings:</Text>
                    <Text style={styles.savingsPrice}>Rs. {getTotalSavings().toFixed(2)}</Text>
                  </View>
                  
                  <View style={styles.finalPriceRow}>
                    <Text style={styles.finalPriceLabel}>Final Price:</Text>
                    <Text style={styles.finalPriceValue}>Rs. {getTotalPrice().toFixed(2)}</Text>
                  </View>
                  
                  <View style={styles.savingsBadge}>
                    <Text style={styles.savingsBadgeText}>
                      You're saving {getSavingsPercentage().toFixed(1)}% with bulk discount!
                    </Text>
                  </View>
                </View>
              </View>
            )}

            {/* Delivery Details - Removed, will be on delivery page */}

            {/* Action Buttons */}
            <View style={styles.actionButtons}>
              <Pressable style={styles.clearButton} onPress={clearOrder}>
                <Text style={styles.clearButtonText}>Clear Order</Text>
              </Pressable>
              <Pressable
                style={[
                  styles.addToCartButton,
                  (getTotalPrice() === 0 || customItems.length === 0) && styles.addToCartButtonDisabled
                ]}
                onPress={handleAddToCart}
                disabled={getTotalPrice() === 0 || customItems.length === 0}
              >
                <MCI name="map-marker" size={20} color={colors.white} style={{ marginRight: spacing.xs }} />
                <Text style={styles.addToCartButtonText}>
                  Go to Delivery Page (Rs. {getTotalPrice().toFixed(2)})
                </Text>
              </Pressable>
            </View>
          </ScrollView>
        </View>
      </Modal>

      {/* Product Selector Modal - Full Screen Popup */}
      <Modal
        visible={showProductSelector}
        animationType="slide"
        transparent={false}
        onRequestClose={() => setShowProductSelector(false)}
      >
        <View style={styles.productSelectorFullScreen}>
          {/* Header */}
          <LinearGradient
            colors={['#152039', '#1e3a5f']}
            style={styles.productSelectorHeader}
          >
            <View style={styles.productSelectorHeaderContent}>
              <View style={styles.productSelectorTitleRow}>
                <MCI name="food-variant" size={28} color="#FFFFFF" />
                <Text style={styles.productSelectorTitle}>Browse Menu</Text>
              </View>
              <Pressable 
                style={styles.productSelectorCloseButton}
                onPress={() => setShowProductSelector(false)}
              >
                <MCI name="close-circle" size={32} color="#FFFFFF" />
              </Pressable>
            </View>
            
            {/* Items Added Counter */}
            {customItems.length > 0 && (
              <View style={styles.itemsAddedBanner}>
                <MCI name="checkbox-marked-circle" size={16} color="#10B981" />
                <Text style={styles.itemsAddedText}>
                  {customItems.length} item{customItems.length > 1 ? 's' : ''} added to your custom order
                </Text>
              </View>
            )}
          </LinearGradient>

          {/* Category Tabs */}
          <View style={styles.productCategorySection}>
            <ScrollView horizontal showsHorizontalScrollIndicator={false}>
              <View style={styles.productCategoryTabs}>
                {categories.map(category => (
                  <Pressable
                    key={category}
                    style={[
                      styles.productCategoryTab,
                      selectedCategory === category && styles.productCategoryTabActive
                    ]}
                    onPress={() => {
                      setSelectedCategory(category);
                      setSelectedSubcategory(getSubcategories(category)[0] || '');
                    }}
                  >
                    <Text style={styles.productCategoryIcon}>
                      {category === 'Food' ? '🥟' : category === 'Drinks' ? '🥤' : '🍰'}
                    </Text>
                    <Text style={[
                      styles.productCategoryTabText,
                      selectedCategory === category && styles.productCategoryTabTextActive
                    ]}>
                      {category}
                    </Text>
                  </Pressable>
                ))}
              </View>
            </ScrollView>
          </View>

          {/* Subcategory Tabs */}
          {(selectedCategory === 'Food' || selectedCategory === 'Drinks') && (
            <View style={styles.productSubcategorySection}>
              <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                <View style={styles.productSubcategoryTabs}>
                  {getSubcategories(selectedCategory).map(subcategory => (
                    <Pressable
                      key={subcategory}
                      style={[
                        styles.productSubcategoryTab,
                        selectedSubcategory === subcategory && styles.productSubcategoryTabActive
                      ]}
                      onPress={() => setSelectedSubcategory(subcategory)}
                    >
                      <Text style={[
                        styles.productSubcategoryTabText,
                        selectedSubcategory === subcategory && styles.productSubcategoryTabTextActive
                      ]}>
                        {subcategory.toUpperCase()}
                      </Text>
                    </Pressable>
                  ))}
                </View>
              </ScrollView>
            </View>
          )}

          {/* Products List */}
          <ScrollView style={styles.productSelectorContent}>
            <View style={styles.productSelectorGrid}>
              {getProductsByCategory(selectedCategory).length > 0 ? (
                getProductsByCategory(selectedCategory).map((product) => {
                  const isAdded = customItems.some(item => item.id === product.id);
                  const addedItem = customItems.find(item => item.id === product.id);
                  
                  return (
                    <Pressable
                      key={product.id}
                      style={[
                        styles.productSelectorCard,
                        isAdded && styles.productSelectorCardAdded
                      ]}
                      onPress={() => {
                        console.log('📋 Adding product to custom order:', product.name);
                        addProductToOrder(product);
                      }}
                    >
                      {/* Product Image */}
                      <View style={styles.productSelectorImageContainer}>
                        <Image
                          source={{ uri: getValidImageUrl(product) }}
                          style={styles.productSelectorImage}
                          resizeMode="cover"
                        />
                        {isAdded && (
                          <View style={styles.productAddedOverlay}>
                            <MCI name="check-circle" size={32} color="#10B981" />
                            <View style={styles.productQuantityBadge}>
                              <Text style={styles.productQuantityText}>×{addedItem?.quantity || 1}</Text>
                            </View>
                          </View>
                        )}
                      </View>
                      
                      {/* Product Info */}
                      <View style={styles.productSelectorInfo}>
                        <Text style={styles.productSelectorName} numberOfLines={2}>
                          {product.name}
                        </Text>
                        <View style={styles.productSelectorPriceRow}>
                          <Text style={styles.productSelectorPrice}>
                            Rs. {Number(product.price).toFixed(0)}
                          </Text>
                          <Text style={styles.productSelectorBulkPrice}>
                            Bulk: Rs. {(Number(product.price) * (1 - bulkDiscountPercentage / 100)).toFixed(0)}
                          </Text>
                        </View>
                      </View>
                      
                      {/* Add Button */}
                      <View style={[
                        styles.productSelectorAddButton,
                        isAdded && styles.productSelectorAddButtonActive
                      ]}>
                        <MCI 
                          name={isAdded ? "check" : "plus"} 
                          size={24} 
                          color="#FFFFFF" 
                        />
                      </View>
                    </Pressable>
                  );
                })
              ) : (
                <View style={styles.noProductsContainer}>
                  <MCI name="food-off" size={64} color="#9CA3AF" />
                  <Text style={styles.noProductsText}>No items in this category</Text>
                  <Text style={styles.noProductsSubtext}>Try another category</Text>
                </View>
              )}
            </View>
          </ScrollView>

          {/* Bottom Action Bar */}
          <View style={styles.productSelectorFooter}>
            <Pressable
              style={styles.productSelectorDoneButton}
              onPress={() => setShowProductSelector(false)}
            >
              <MCI name="check-circle" size={20} color="#FFFFFF" />
              <Text style={styles.productSelectorDoneText}>
                Done - {customItems.length} item{customItems.length !== 1 ? 's' : ''} selected
              </Text>
            </Pressable>
          </View>
        </View>
      </Modal>

      {/* Custom Date Picker Modal */}
      {showCustomDatePicker && (
        <Modal
          visible={showCustomDatePicker}
          transparent={true}
          animationType="slide"
          onRequestClose={() => setShowCustomDatePicker(false)}
        >
          <View style={styles.datePickerOverlay}>
            <View style={styles.datePickerModal}>
              <View style={styles.datePickerHeader}>
                <Text style={styles.datePickerTitle}>Select Date & Time</Text>
                <Pressable
                  style={styles.datePickerCloseButton}
                  onPress={() => setShowCustomDatePicker(false)}
                >
                  <MCI name="close" size={24} color="#374151" />
                </Pressable>
              </View>

              <ScrollView style={styles.datePickerContent}>
                {/* Custom Date Selection */}
                <Text style={styles.datePickerLabel}>Select Date:</Text>
                <View style={styles.dateSelectorContainer}>
                  <Pressable
                    style={styles.dateSelectorButton}
                    onPress={() => {
                      const newDate = new Date(tempDate);
                      newDate.setDate(newDate.getDate() - 1);
                      if (newDate >= new Date()) {
                        setTempDate(newDate);
                      }
                    }}
                  >
                    <MCI name="chevron-left" size={20} color="#EF4444" />
                  </Pressable>
                  
                  <View style={styles.dateDisplay}>
                    <Text style={styles.dateDisplayText}>
                      {tempDate.toLocaleDateString('en-US', {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric'
                      })}
                    </Text>
                    <Text style={styles.dateDisplayYear}>
                      {tempDate.getFullYear()}
                    </Text>
                  </View>
                  
                  <Pressable
                    style={styles.dateSelectorButton}
                    onPress={() => {
                      const newDate = new Date(tempDate);
                      newDate.setDate(newDate.getDate() + 1);
                      setTempDate(newDate);
                    }}
                  >
                    <MCI name="chevron-right" size={20} color="#EF4444" />
                  </Pressable>
                </View>

                {/* Custom Time Selection */}
                <Text style={styles.datePickerLabel}>Select Time:</Text>
                <View style={styles.timeSelectorContainer}>
                  {/* Hour Selection */}
                  <View style={styles.timeSection}>
                    <Text style={styles.timeSectionLabel}>Hour</Text>
                    <View style={styles.timeSelector}>
                      <Pressable
                        style={styles.timeButton}
                        onPress={() => {
                          const newTime = new Date(tempTime);
                          newTime.setHours((newTime.getHours() + 23) % 24);
                          setTempTime(newTime);
                        }}
                      >
                        <MCI name="chevron-up" size={16} color="#EF4444" />
                      </Pressable>
                      <Text style={styles.timeDisplay}>
                        {tempTime.getHours().toString().padStart(2, '0')}
                      </Text>
                      <Pressable
                        style={styles.timeButton}
                        onPress={() => {
                          const newTime = new Date(tempTime);
                          newTime.setHours((newTime.getHours() + 1) % 24);
                          setTempTime(newTime);
                        }}
                      >
                        <MCI name="chevron-down" size={16} color="#EF4444" />
                      </Pressable>
                    </View>
                  </View>

                  <Text style={styles.timeSeparator}>:</Text>

                  {/* Minute Selection */}
                  <View style={styles.timeSection}>
                    <Text style={styles.timeSectionLabel}>Min</Text>
                    <View style={styles.timeSelector}>
                      <Pressable
                        style={styles.timeButton}
                        onPress={() => {
                          const newTime = new Date(tempTime);
                          newTime.setMinutes((newTime.getMinutes() + 55) % 60);
                          setTempTime(newTime);
                        }}
                      >
                        <MCI name="chevron-up" size={16} color="#EF4444" />
                      </Pressable>
                      <Text style={styles.timeDisplay}>
                        {tempTime.getMinutes().toString().padStart(2, '0')}
                      </Text>
                      <Pressable
                        style={styles.timeButton}
                        onPress={() => {
                          const newTime = new Date(tempTime);
                          newTime.setMinutes((newTime.getMinutes() + 5) % 60);
                          setTempTime(newTime);
                        }}
                      >
                        <MCI name="chevron-down" size={16} color="#EF4444" />
                      </Pressable>
                    </View>
                  </View>

                  {/* AM/PM Selection */}
                  <View style={styles.timeSection}>
                    <Text style={styles.timeSectionLabel}>Period</Text>
                    <Pressable
                      style={styles.periodButton}
                      onPress={() => {
                        const newTime = new Date(tempTime);
                        const currentHour = newTime.getHours();
                        if (currentHour < 12) {
                          newTime.setHours(currentHour + 12);
                        } else {
                          newTime.setHours(currentHour - 12);
                        }
                        setTempTime(newTime);
                      }}
                    >
                      <Text style={styles.periodText}>
                        {tempTime.getHours() < 12 ? 'AM' : 'PM'}
                      </Text>
                    </Pressable>
                  </View>
                </View>

                {/* Preview */}
                <Text style={styles.datePickerLabel}>Selected Date & Time:</Text>
                <View style={styles.previewContainer}>
                  <Text style={styles.previewText}>
                    {tempDate.toLocaleDateString('en-US', {
                      weekday: 'long',
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    })}
                  </Text>
                  <Text style={styles.previewTime}>
                    {tempTime.toLocaleTimeString('en-US', {
                      hour: '2-digit',
                      minute: '2-digit',
                      hour12: true
                    })}
                  </Text>
                </View>

                {/* Quick Options */}
                <Text style={styles.datePickerLabel}>Quick Options:</Text>
                <View style={styles.datePickerButtons}>
                  <Pressable
                    style={styles.datePickerButton}
                    onPress={() => {
                      const tomorrow = new Date();
                      tomorrow.setDate(tomorrow.getDate() + 1);
                      tomorrow.setHours(12, 0, 0, 0);
                      setTempDate(tomorrow);
                      setTempTime(tomorrow);
                    }}
                  >
                    <Text style={styles.datePickerButtonText}>Tomorrow 12:00 PM</Text>
                  </Pressable>

                  <Pressable
                    style={styles.datePickerButton}
                    onPress={() => {
                      const nextWeek = new Date();
                      nextWeek.setDate(nextWeek.getDate() + 7);
                      nextWeek.setHours(18, 0, 0, 0);
                      setTempDate(nextWeek);
                      setTempTime(nextWeek);
                    }}
                  >
                    <Text style={styles.datePickerButtonText}>Next Week 6:00 PM</Text>
                  </Pressable>

                  <Pressable
                    style={styles.datePickerButton}
                    onPress={() => {
                      const today = new Date();
                      today.setHours(20, 0, 0, 0);
                      setTempDate(today);
                      setTempTime(today);
                    }}
                  >
                    <Text style={styles.datePickerButtonText}>Today 8:00 PM</Text>
                  </Pressable>
                </View>
              </ScrollView>

              <View style={styles.datePickerFooter}>
                <Pressable
                  style={styles.datePickerCancelButton}
                  onPress={() => setShowCustomDatePicker(false)}
                >
                  <Text style={styles.datePickerCancelText}>Cancel</Text>
                </Pressable>
                <Pressable
                  style={styles.datePickerConfirmButton}
                  onPress={handleConfirmCustomDateTime}
                >
                  <Text style={styles.datePickerConfirmText}>Confirm</Text>
                </Pressable>
              </View>
            </View>
          </View>
        </Modal>
      )}

      {/* Success Popup Modal - Removed, navigating directly to checkout */}
    </>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  header: {
    padding: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
    position: 'relative',
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#6E0D25',
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
  },
  closeButton: {
    position: 'absolute',
    top: spacing.lg,
    right: spacing.lg,
  },
  content: {
    flex: 1,
  },
  section: {
    padding: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#374151',
    marginBottom: spacing.md,
  },
  
  // Order Type Selection
  orderTypeContainer: {
    gap: spacing.md,
  },
  orderTypeRow: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  orderTypeButton: {
    borderWidth: 2,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  orderTypeButtonActive: {
    borderColor: '#3B82F6',
    backgroundColor: '#EFF6FF',
  },
  orderTypeButtonCompact: {
    borderWidth: 2,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.sm,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    flex: 1,
  },
  orderTypeIconCompact: {
    fontSize: 20,
  },
  orderTypeTextCompact: {
    flex: 1,
  },
  orderTypeTitleCompact: {
    fontSize: fontSizes.sm,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 2,
  },
  orderTypeDescriptionCompact: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  orderTypeIcon: {
    fontSize: 24,
  },
  orderTypeTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  orderTypeDescription: {
    fontSize: 12,
    color: '#6B7280',
  },
  infoBox: {
    backgroundColor: '#EFF6FF',
    borderWidth: 1,
    borderColor: '#BFDBFE',
    borderRadius: 8,
    padding: spacing.md,
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.md,
  },
  infoBoxCompact: {
    backgroundColor: '#EFF6FF',
    borderWidth: 1,
    borderColor: '#BFDBFE',
    borderRadius: 6,
    padding: spacing.sm,
    flexDirection: 'row',
    gap: spacing.xs,
    marginTop: spacing.sm,
  },
  infoContent: {
    flex: 1,
  },
  infoContentCompact: {
    flex: 1,
  },
  infoTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E40AF',
    marginBottom: 2,
  },
  infoTitleCompact: {
    fontSize: 12,
    fontWeight: '600',
    color: '#1E40AF',
    marginBottom: 1,
  },
  infoDescription: {
    fontSize: 12,
    color: '#1D4ED8',
    lineHeight: 16,
  },
  infoDescriptionCompact: {
    fontSize: 11,
    color: '#1D4ED8',
    lineHeight: 14,
  },
  dateTimeContainer: {
    marginTop: spacing.md,
  },
  dateTimeLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151',
    marginBottom: spacing.sm,
  },
  dateTimeInput: {
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.sm,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  dateTimeText: {
    fontSize: 14,
    color: '#374151',
  },
  dateTimeFallback: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginTop: spacing.xs,
    textAlign: 'center',
    fontStyle: 'italic',
  },
  
  // Custom Date Picker Modal Styles
  datePickerOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  datePickerModal: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: spacing.lg,
    margin: spacing.lg,
    width: '90%',
    maxWidth: 400,
  },
  datePickerHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.lg,
  },
  datePickerTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#111827',
  },
  datePickerCloseButton: {
    padding: spacing.xs,
  },
  datePickerContent: {
    marginBottom: spacing.lg,
  },
  datePickerLabel: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#374151',
    marginBottom: spacing.xs,
    marginTop: spacing.md,
  },
  datePickerCurrentDate: {
    fontSize: fontSizes.md,
    color: '#111827',
    marginBottom: spacing.xs,
  },
  datePickerCurrentTime: {
    fontSize: fontSizes.md,
    color: '#111827',
    marginBottom: spacing.md,
  },
  datePickerButtons: {
    gap: spacing.sm,
  },
  datePickerButton: {
    backgroundColor: '#F3F4F6',
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.md,
    alignItems: 'center',
  },
  datePickerButtonText: {
    fontSize: fontSizes.md,
    color: '#374151',
    fontWeight: fontWeights.medium,
  },
  datePickerFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  datePickerCancelButton: {
    flex: 1,
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.md,
    alignItems: 'center',
  },
  datePickerCancelText: {
    fontSize: fontSizes.md,
    color: '#374151',
    fontWeight: fontWeights.medium,
  },
  datePickerConfirmButton: {
    flex: 1,
    backgroundColor: '#EF4444',
    borderRadius: 8,
    padding: spacing.md,
    alignItems: 'center',
  },
  datePickerConfirmText: {
    fontSize: fontSizes.md,
    color: '#FFFFFF',
    fontWeight: fontWeights.semibold,
  },
  
  // Enhanced Date Picker Styles
  dateSelectorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  dateSelectorButton: {
    backgroundColor: '#FFFFFF',
    borderRadius: 6,
    padding: spacing.sm,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  dateDisplay: {
    alignItems: 'center',
    flex: 1,
    marginHorizontal: spacing.md,
  },
  dateDisplayText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: '#111827',
  },
  dateDisplayYear: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    marginTop: 2,
  },
  
  timeSelectorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  timeSection: {
    alignItems: 'center',
    marginHorizontal: spacing.sm,
  },
  timeSectionLabel: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginBottom: spacing.xs,
    fontWeight: fontWeights.medium,
  },
  timeSelector: {
    alignItems: 'center',
  },
  timeButton: {
    backgroundColor: '#FFFFFF',
    borderRadius: 6,
    padding: spacing.xs,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    marginVertical: 2,
  },
  timeDisplay: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#111827',
    marginVertical: spacing.sm,
    minWidth: 40,
    textAlign: 'center',
  },
  timeSeparator: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#111827',
    marginHorizontal: spacing.sm,
  },
  periodButton: {
    backgroundColor: '#EF4444',
    borderRadius: 6,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    marginTop: spacing.sm,
  },
  periodText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#FFFFFF',
  },
  
  previewContainer: {
    backgroundColor: '#FEF2F2',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: '#FECACA',
  },
  previewText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#111827',
    textAlign: 'center',
  },
  previewTime: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#EF4444',
    textAlign: 'center',
    marginTop: spacing.xs,
  },
  
  // Success Popup Styles
  successOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  successPopup: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: spacing.xl,
    margin: spacing.lg,
    width: '90%',
    maxWidth: 400,
    alignItems: 'center',
  },
  successIconContainer: {
    marginBottom: spacing.lg,
  },
  successTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#111827',
    marginBottom: spacing.sm,
  },
  successMessage: {
    fontSize: fontSizes.md,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  successDetails: {
    width: '100%',
    marginBottom: spacing.lg,
  },
  successDetailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  successDetailLabel: {
    fontSize: fontSizes.md,
    color: '#374151',
    fontWeight: fontWeights.medium,
  },
  successDetailValue: {
    fontSize: fontSizes.md,
    color: '#111827',
    fontWeight: fontWeights.semibold,
  },
  successButtons: {
    flexDirection: 'row',
    gap: spacing.md,
    width: '100%',
  },
  successButtonSecondary: {
    flex: 1,
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.md,
    alignItems: 'center',
  },
  successButtonSecondaryText: {
    fontSize: fontSizes.md,
    color: '#374151',
    fontWeight: fontWeights.medium,
  },
  successButtonPrimary: {
    flex: 1,
    backgroundColor: '#EF4444',
    borderRadius: 8,
    padding: spacing.md,
    alignItems: 'center',
  },
  successButtonPrimaryText: {
    fontSize: fontSizes.md,
    color: '#FFFFFF',
    fontWeight: fontWeights.semibold,
  },
  
  // Menu Selection
  menuHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  browseButton: {
    backgroundColor: '#059669',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 8,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  browseButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  selectedItemsContainer: {
    gap: spacing.md,
  },
  selectedItemCard: {
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  selectedItemHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  selectedItemImageContainer: {
    width: 48,
    height: 48,
    backgroundColor: '#F3F4F6',
    borderRadius: 8,
    overflow: 'hidden',
  },
  selectedItemImage: {
    width: '100%',
    height: '100%',
  },
  selectedItemImagePlaceholder: {
    width: '100%',
    height: '100%',
    alignItems: 'center',
    justifyContent: 'center',
  },
  selectedItemInfo: {
    flex: 1,
  },
  selectedItemName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  selectedItemCategory: {
    fontSize: 12,
    color: '#6B7280',
  },
  removeButton: {
    backgroundColor: '#DC2626',
    padding: spacing.sm,
    borderRadius: 6,
  },
  selectedItemControls: {
    gap: spacing.sm,
  },
  controlRow: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  controlItem: {
    flex: 1,
  },
  controlLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#374151',
    marginBottom: 4,
  },
  preparationSelector: {
    flexDirection: 'row',
    gap: 4,
  },
  preparationOption: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.xs,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    alignItems: 'center',
  },
  preparationOptionActive: {
    backgroundColor: '#3B82F6',
    borderColor: '#3B82F6',
  },
  preparationOptionText: {
    fontSize: 12,
    color: '#6B7280',
  },
  preparationOptionTextActive: {
    color: '#FFFFFF',
  },
  quantityControls: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
  },
  quantityButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    alignItems: 'center',
    justifyContent: 'center',
  },
  quantityText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    minWidth: 32,
    textAlign: 'center',
  },
  priceRow: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  priceItem: {
    flex: 1,
  },
  priceLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#374151',
    marginBottom: 4,
  },
  regularPrice: {
    fontSize: 12,
    color: '#6B7280',
    backgroundColor: '#F9FAFB',
    padding: spacing.sm,
    borderRadius: 6,
    textAlign: 'center',
  },
  bulkPrice: {
    fontSize: 12,
    fontWeight: '600',
    color: '#059669',
    backgroundColor: '#ECFDF5',
    padding: spacing.sm,
    borderRadius: 6,
    textAlign: 'center',
  },
  emptyState: {
    alignItems: 'center',
    padding: spacing.xl,
    backgroundColor: '#F9FAFB',
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#E5E7EB',
    borderStyle: 'dashed',
  },
  emptyStateTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginTop: spacing.md,
  },
  emptyStateDescription: {
    fontSize: 12,
    color: '#6B7280',
    textAlign: 'center',
    marginTop: spacing.xs,
  },
  emptyStateButton: {
    backgroundColor: '#059669',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 8,
    marginTop: spacing.md,
  },
  emptyStateButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  
  // Order Summary
  summaryContainer: {
    backgroundColor: '#EFF6FF',
    borderRadius: 8,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: '#BFDBFE',
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  summaryLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1D4ED8',
  },
  summaryValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#1E40AF',
  },
  originalTotalPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
    textDecorationLine: 'line-through',
  },
  savingsPrice: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#059669',
  },
  finalPriceRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: '#BFDBFE',
    paddingTop: spacing.sm,
    marginTop: spacing.sm,
  },
  finalPriceLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1E40AF',
  },
  finalPriceValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#1E40AF',
  },
  savingsBadge: {
    backgroundColor: '#D1FAE5',
    borderRadius: 8,
    padding: spacing.sm,
    marginTop: spacing.md,
    borderWidth: 1,
    borderColor: '#A7F3D0',
  },
  savingsBadgeText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#047857',
    textAlign: 'center',
  },
  
  // Delivery Details
  deliveryContainer: {
    gap: spacing.md,
  },
  inputContainer: {
    gap: spacing.xs,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151',
  },
  textInput: {
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: spacing.sm,
    fontSize: 14,
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  
  // Action Buttons
  actionButtons: {
    flexDirection: 'row',
    gap: spacing.md,
    padding: spacing.lg,
  },
  clearButton: {
    flex: 1,
    backgroundColor: '#F3F4F6',
    paddingVertical: spacing.md,
    borderRadius: 8,
    alignItems: 'center',
  },
  clearButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151',
  },
  addToCartButton: {
    flex: 1,
    backgroundColor: '#3B82F6',
    paddingVertical: spacing.md,
    borderRadius: 8,
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'center',
  },
  addToCartButtonDisabled: {
    backgroundColor: '#9CA3AF',
  },
  addToCartButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  
  // Full Screen Product Selector Modal - BEAUTIFUL UI
  productSelectorFullScreen: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  productSelectorHeader: {
    paddingTop: 40,
    paddingBottom: spacing.md,
    paddingHorizontal: spacing.lg,
  },
  productSelectorHeaderContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  productSelectorTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  productSelectorTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  productSelectorCloseButton: {
    padding: spacing.xs,
  },
  itemsAddedBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    borderRadius: 8,
    marginTop: spacing.sm,
  },
  itemsAddedText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  productCategorySection: {
    backgroundColor: '#FFFFFF',
    paddingVertical: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  productCategoryTabs: {
    flexDirection: 'row',
    gap: spacing.md,
    paddingHorizontal: spacing.lg,
  },
  productCategoryTab: {
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    backgroundColor: '#F3F4F6',
    borderRadius: 16,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#E5E7EB',
    minWidth: 110,
  },
  productCategoryTabActive: {
    backgroundColor: '#6E0D25',
    borderColor: '#6E0D25',
    shadowColor: '#6E0D25',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  productCategoryIcon: {
    fontSize: 24,
    marginBottom: 4,
  },
  productCategoryTabText: {
    fontSize: 14,
    fontWeight: '700',
    color: '#6B7280',
  },
  productCategoryTabTextActive: {
    color: '#FFFFFF',
  },
  productSubcategorySection: {
    backgroundColor: '#FFFFFF',
    paddingVertical: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  productSubcategoryTabs: {
    flexDirection: 'row',
    gap: spacing.sm,
    paddingHorizontal: spacing.lg,
  },
  productSubcategoryTab: {
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    backgroundColor: '#FEF3C7',
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#FCD34D',
    minWidth: 80,
  },
  productSubcategoryTabActive: {
    backgroundColor: '#F59E0B',
    borderColor: '#F59E0B',
  },
  productSubcategoryTabText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#92400E',
  },
  productSubcategoryTabTextActive: {
    color: '#FFFFFF',
  },
  productSelectorContent: {
    flex: 1,
  },
  productSelectorGrid: {
    padding: spacing.lg,
    gap: spacing.md,
  },
  productSelectorCard: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: spacing.md,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#E5E7EB',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15,
    shadowRadius: 6,
    elevation: 5,
  },
  productSelectorCardAdded: {
    borderColor: '#10B981',
    backgroundColor: '#ECFDF5',
  },
  productSelectorImageContainer: {
    position: 'relative',
  },
  productSelectorImage: {
    width: 90,
    height: 90,
    borderRadius: 12,
    marginRight: spacing.md,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  productAddedOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: spacing.md,
    bottom: 0,
    backgroundColor: 'rgba(16, 185, 129, 0.7)',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  productQuantityBadge: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
    borderRadius: 12,
    marginTop: spacing.xs,
  },
  productQuantityText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#10B981',
  },
  productSelectorInfo: {
    flex: 1,
  },
  productSelectorName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: spacing.sm,
    lineHeight: 20,
  },
  productSelectorPriceRow: {
    gap: spacing.sm,
  },
  productSelectorPrice: {
    fontSize: 15,
    fontWeight: '600',
    color: '#6B7280',
    textDecorationLine: 'line-through',
  },
  productSelectorBulkPrice: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#6E0D25',
  },
  productSelectorAddButton: {
    backgroundColor: '#6E0D25',
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: spacing.md,
    shadowColor: '#6E0D25',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.4,
    shadowRadius: 4,
    elevation: 4,
  },
  productSelectorAddButtonActive: {
    backgroundColor: '#10B981',
    shadowColor: '#10B981',
  },
  productSelectorFooter: {
    backgroundColor: '#FFFFFF',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 8,
  },
  productSelectorDoneButton: {
    backgroundColor: '#10B981',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    borderRadius: 12,
    gap: spacing.sm,
    shadowColor: '#10B981',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  productSelectorDoneText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  noProductsContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.xl * 3,
  },
  noProductsText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#6B7280',
    marginTop: spacing.md,
  },
  noProductsSubtext: {
    fontSize: 14,
    color: '#9CA3AF',
    marginTop: spacing.xs,
  },
  
  // Product Selector Modal (kept for reference, but now using inline browser)
  productSelectorContainer: {
    flex: 1,
    backgroundColor: '#F4E9E1',
  },
  productSelectorHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  productSelectorTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#111827',
  },
  categoryTabs: {
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  categoryTab: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  categoryTabActive: {
    borderBottomColor: '#3B82F6',
  },
  categoryTabText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
  },
  categoryTabTextActive: {
    color: '#3B82F6',
  },
  subcategoryTabs: {
    backgroundColor: '#F9FAFB',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  subcategoryTab: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  subcategoryTabActive: {
    borderBottomColor: '#3B82F6',
  },
  subcategoryTabText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
  },
  subcategoryTabTextActive: {
    color: '#3B82F6',
  },
  productsGrid: {
    padding: spacing.md,
  },
  productCard: {
    flex: 1,
    margin: spacing.xs,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 8,
    overflow: 'hidden',
  },
  productImageContainer: {
    height: 120,
    backgroundColor: '#F3F4F6',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  productImagePlaceholder: {
    width: '100%',
    height: '100%',
    alignItems: 'center',
    justifyContent: 'center',
  },
  productInfo: {
    padding: spacing.sm,
  },
  productName: {
    fontSize: 12,
    fontWeight: '600',
    color: '#111827',
    marginBottom: spacing.xs,
  },
  productPrice: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#3B82F6',
    marginBottom: spacing.xs,
  },
  addButton: {
    backgroundColor: '#3B82F6',
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: 4,
    alignSelf: 'flex-start',
  },
  addButtonText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  
  // Menu Styles - Exact copy from menu.tsx
  menuTabContainer: {
    paddingTop: spacing.lg,
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.sm,
  },
  menuTabBar: {
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
  menuTabButton: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.xs,
    borderRadius: radius.md,
    alignItems: 'center',
  },
  menuActiveTabButton: {
    backgroundColor: colors.brand.primary,
    shadowColor: colors.brand.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  menuTabText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.gray[600],
  },
  menuActiveTabText: {
    color: colors.white,
  },
  // Sub-Tab Styles
  menuSubTabContainer: {
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.sm,
  },
  menuSubTabBar: {
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
  menuSubTabButton: {
    flex: 1,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.xs,
    borderRadius: radius.md,
    alignItems: 'center',
  },
  menuActiveSubTabButton: {
    backgroundColor: colors.brand.primary,
  },
  menuSubTabText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
    color: colors.gray[600],
  },
  menuActiveSubTabText: {
    color: colors.white,
  },
  // Scroll and Grid Styles
  menuScrollContainer: {
    flex: 1,
  },
  menuItemsGrid: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl,
  },
  menuItemCardLarge: {
    width: '100%',
    marginBottom: spacing.lg,
  },
  menuProductCardLarge: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 6,
    height: 320,
  },
  menuProductImage: {
    width: '100%',
    height: '100%',
  },
  menuImageGradient: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 120,
  },
  menuCategoryBadge: {
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
  menuCategoryBadgeText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: colors.white,
  },
  menuProductInfoOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: spacing.xs,
    zIndex: 10,
  },
  menuMainContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    minHeight: 60,
  },
  menuTextContent: {
    flex: 1,
    marginRight: spacing.xs,
    maxWidth: '65%',
  },
  menuRightContent: {
    alignItems: 'flex-end',
  },
  menuPriceContainer: {
    alignItems: 'flex-start',
    marginBottom: spacing.xs,
    width: '100%',
  },
  menuButtonContainer: {
    alignItems: 'flex-end',
    width: '100%',
  },
  menuProductNameLarge: {
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
  menuProductDescriptionLarge: {
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
  menuPriceLarge: {
    fontSize: 16,
    fontWeight: fontWeights.bold,
    color: '#FCD34D',
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
    marginBottom: 8,
  },
  menuAddToCartButtonLarge: {
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
  menuAddToCartTextLarge: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    marginLeft: spacing.md,
  },
});
