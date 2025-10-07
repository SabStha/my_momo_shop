import React from 'react';
import {
  View,
  Text,
  Image,
  Modal,
  Pressable,
  StyleSheet,
  ScrollView,
  Dimensions,
} from 'react-native';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../ui/tokens';

const { width: screenWidth, height: screenHeight } = Dimensions.get('window');

interface Product {
  id: string;
  name: string;
  imageUrl: string;
  ingredients?: string;
  allergens?: string;
  calories?: string;
  preparation_time?: string;
  spice_level?: string;
  serving_size?: string;
  is_vegetarian?: boolean;
  is_vegan?: boolean;
  is_gluten_free?: boolean;
}

interface ProductInfoModalProps {
  visible: boolean;
  product: Product | null;
  onClose: () => void;
}

export default function ProductInfoModal({ visible, product, onClose }: ProductInfoModalProps) {
  if (!product) return null;

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <Pressable style={styles.backdrop} onPress={onClose} />
        
        <View style={styles.modalContainer}>
          <ScrollView 
            style={styles.scrollView}
            showsVerticalScrollIndicator={false}
          >
            {/* Product Image with Title Overlay */}
            <View style={styles.imageContainer}>
              <Image
                source={{ uri: product.imageUrl }}
                style={styles.image}
                resizeMode="cover"
              />
              <Text style={styles.productTitle}>{product.name}</Text>
              
              {/* Close Button - Top Right */}
              <Pressable style={styles.topCloseButton} onPress={onClose}>
                <Text style={styles.closeIcon}>×</Text>
              </Pressable>
            </View>
            
            {/* Content Sections */}
            <View style={styles.content}>
              {/* Ingredients Section - Green Background */}
              <View style={[styles.infoSection, styles.ingredientsSection]}>
                <View style={styles.sectionHeader}>
                  <Text style={styles.ingredientsIcon}>🥘</Text>
                  <Text style={[styles.sectionTitle, styles.ingredientsTitle]}>Ingredients</Text>
                </View>
                <Text style={[styles.sectionContent, styles.ingredientsContent]}>
                  {product.ingredients || 'Wheat flour, ground pork, onions, garlic, ginger, spices, oil, salt, water'}
                </Text>
              </View>
              
              {/* Allergen Information Section - Beige Background */}
              <View style={[styles.infoSection, styles.allergensSection]}>
                <View style={styles.sectionHeader}>
                  <Text style={styles.allergensIcon}>⚠️</Text>
                  <Text style={[styles.sectionTitle, styles.allergensTitle]}>Allergen Information</Text>
                </View>
                <Text style={[styles.sectionContent, styles.allergensContent]}>
                  {product.allergens || 'Contains: Gluten'}
                </Text>
              </View>
              
              {/* Nutritional Information Section - Orange Background */}
              <View style={[styles.infoSection, styles.nutritionSection]}>
                <View style={styles.sectionHeader}>
                  <Text style={styles.nutritionIcon}>🔥</Text>
                  <Text style={[styles.sectionTitle, styles.nutritionTitle]}>Nutritional Information</Text>
                </View>
                <View style={styles.nutritionGrid}>
                  <View style={styles.nutritionColumn}>
                    <Text style={[styles.nutritionContent, styles.nutritionText]}>
                      Calories: {product.calories || '350-400'}
                    </Text>
                    <Text style={[styles.nutritionContent, styles.nutritionText]}>
                      Prep Time: {product.preparation_time || '18-22 minutes'}
                    </Text>
                  </View>
                  <View style={styles.nutritionColumn}>
                    <Text style={[styles.nutritionContent, styles.nutritionText]}>
                      Serving Size: {product.serving_size || '6 pieces'}
                    </Text>
                    <Text style={[styles.nutritionContent, styles.nutritionText]}>
                      Spice Level: {product.spice_level || 'Medium'}
                    </Text>
                  </View>
                </View>
              </View>
              
              {/* Dietary Information Section - Blue Background */}
              <View style={[styles.infoSection, styles.dietarySection]}>
                <View style={styles.sectionHeader}>
                  <Text style={styles.dietaryIcon}>🌱</Text>
                  <Text style={[styles.sectionTitle, styles.dietaryTitle]}>Dietary Information</Text>
                </View>
                <View style={styles.dietaryContent}>
                  {product.is_vegetarian && (
                    <Text style={[styles.dietaryText, styles.dietaryTextColor]}>Vegetarian</Text>
                  )}
                  {product.is_vegan && (
                    <Text style={[styles.dietaryText, styles.dietaryTextColor]}>Vegan</Text>
                  )}
                  {product.is_gluten_free && (
                    <Text style={[styles.dietaryText, styles.dietaryTextColor]}>Gluten-Free</Text>
                  )}
                  {!product.is_vegetarian && !product.is_vegan && !product.is_gluten_free && (
                    <Text style={[styles.dietaryText, styles.dietaryTextColor]}>Standard</Text>
                  )}
                </View>
              </View>
            </View>
          </ScrollView>
          
          {/* Bottom Close Button */}
          <View style={styles.bottomCloseContainer}>
            <Pressable style={styles.bottomCloseButton} onPress={onClose}>
              <Text style={styles.bottomCloseIcon}>×</Text>
              <Text style={styles.bottomCloseText}>Close</Text>
            </Pressable>
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.md,
  },
  backdrop: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  modalContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.xl,
    maxHeight: screenHeight * 0.9,
    width: '100%',
    maxWidth: screenWidth * 0.95,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.25,
    shadowRadius: 20,
    elevation: 10,
  },
  scrollView: {
    flex: 1,
  },
  imageContainer: {
    height: 250,
    position: 'relative',
  },
  image: {
    width: '100%',
    height: '100%',
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
  },
  productTitle: {
    position: 'absolute',
    bottom: spacing.lg,
    left: spacing.lg,
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.white,
    textShadowColor: 'rgba(0,0,0,0.7)',
    textShadowOffset: { width: 0, height: 2 },
    textShadowRadius: 4,
  },
  topCloseButton: {
    position: 'absolute',
    top: spacing.lg,
    right: spacing.lg,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 4,
  },
  closeIcon: {
    fontSize: 20,
    fontWeight: fontWeights.bold,
    color: colors.gray[800],
  },
  content: {
    padding: spacing.lg,
  },
  infoSection: {
    padding: spacing.lg,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    marginLeft: spacing.sm,
  },
  sectionContent: {
    fontSize: fontSizes.md,
    lineHeight: 22,
  },
  
  // Ingredients Section - Green
  ingredientsSection: {
    backgroundColor: '#f0fdf4', // Light green
  },
  ingredientsIcon: {
    fontSize: 24,
  },
  ingredientsTitle: {
    color: '#16a34a', // Green
  },
  ingredientsContent: {
    color: '#16a34a', // Green
  },
  
  // Allergens Section - Beige
  allergensSection: {
    backgroundColor: '#fefce8', // Light beige
  },
  allergensIcon: {
    fontSize: 24,
  },
  allergensTitle: {
    color: '#a16207', // Dark beige
  },
  allergensContent: {
    color: '#a16207', // Dark beige
  },
  
  // Nutrition Section - Orange
  nutritionSection: {
    backgroundColor: '#fff7ed', // Light orange
  },
  nutritionIcon: {
    fontSize: 24,
  },
  nutritionTitle: {
    color: '#ea580c', // Orange
  },
  nutritionGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  nutritionColumn: {
    flex: 1,
  },
  nutritionContent: {
    fontSize: fontSizes.md,
    lineHeight: 22,
    marginBottom: spacing.xs,
  },
  nutritionText: {
    color: '#ea580c', // Orange
  },
  
  // Dietary Section - Blue
  dietarySection: {
    backgroundColor: '#eff6ff', // Light blue
  },
  dietaryIcon: {
    fontSize: 24,
  },
  dietaryTitle: {
    color: '#1d4ed8', // Blue
  },
  dietaryContent: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  dietaryText: {
    fontSize: fontSizes.md,
    lineHeight: 22,
  },
  dietaryTextColor: {
    color: '#1d4ed8', // Blue
  },
  
  // Bottom Close Button
  bottomCloseContainer: {
    padding: spacing.lg,
    alignItems: 'center',
  },
  bottomCloseButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.gray[200],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  bottomCloseIcon: {
    fontSize: 20,
    fontWeight: fontWeights.bold,
    color: colors.gray[800],
    marginRight: spacing.sm,
  },
  bottomCloseText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
  },
});