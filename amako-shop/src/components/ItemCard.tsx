import React from 'react';
import { View, Text, Image, TouchableOpacity, StyleSheet, Dimensions } from 'react-native';
import { router } from 'expo-router';
import { MenuItem } from '../types';
import { Card, Price } from '../ui';
import { spacing, radius, fontSizes, fontWeights, colors } from '../ui';

interface ItemCardProps {
  item: MenuItem;
  onPress?: () => void;
}

const { width: screenWidth } = Dimensions.get('window');
const cardWidth = (screenWidth - spacing.lg * 3) / 2; // 2 columns with spacing

export function ItemCard({ item, onPress }: ItemCardProps) {
  const handlePress = () => {
    if (onPress) {
      onPress();
    } else {
      router.push(`/item/${item.id}`);
    }
  };

  let startingPrice = item.basePrice.amount;
  if (item.variants && item.variants.length > 0) {
    const minVariantPrice = Math.min(...item.variants.map(v => v.priceDiff.amount));
    startingPrice = item.basePrice.amount + minVariantPrice;
  }

  return (
    <TouchableOpacity
      style={styles.container}
      onPress={handlePress}
      activeOpacity={0.7}
      accessibilityRole="button"
      accessibilityLabel={`${item.name}, ${item.desc || 'No description'}, Starting from Rs. ${startingPrice}`}
      accessibilityHint="Double tap to view item details"
    >
      <Card style={styles.card} padding="md" radius="md" shadow="light">
        {/* Image Container */}
        <View style={styles.imageContainer}>
          {item.imageUrl ? (
            <Image
              source={{ uri: item.imageUrl }}
              style={styles.image}
              resizeMode="cover"
              accessibilityLabel={`Image of ${item.name}`}
            />
          ) : (
            <View style={styles.placeholderImage}>
              <Text style={styles.placeholderText}>🍽️</Text>
            </View>
          )}
          
          {/* Availability Badge */}
          {!item.isAvailable && (
            <View style={styles.unavailableBadge}>
              <Text style={styles.unavailableText}>Unavailable</Text>
            </View>
          )}
        </View>

        {/* Content */}
        <View style={styles.content}>
          <Text style={styles.name} numberOfLines={2}>
            {item.name}
          </Text>
          
          {item.desc && (
            <Text style={styles.description} numberOfLines={1}>
              {item.desc}
            </Text>
          )}
          
          <View style={styles.priceContainer}>
            <Text style={styles.priceLabel}>From </Text>
            <Price 
              value={startingPrice} 
              size="sm" 
              weight="semibold" 
              color="primary"
            />
          </View>
        </View>
      </Card>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  container: {
    width: cardWidth,
    marginBottom: spacing.md,
  },
  card: {
    height: 200,
  },
  imageContainer: {
    position: 'relative',
    height: 120,
    borderRadius: radius.sm,
    overflow: 'hidden',
    marginBottom: spacing.sm,
  },
  image: {
    width: '100%',
    height: '100%',
  },
  placeholderImage: {
    width: '100%',
    height: '100%',
    backgroundColor: colors.gray[100],
    justifyContent: 'center',
    alignItems: 'center',
  },
  placeholderText: {
    fontSize: fontSizes.xl,
  },
  unavailableBadge: {
    position: 'absolute',
    top: spacing.xs,
    right: spacing.xs,
    backgroundColor: colors.error[500],
    paddingHorizontal: spacing.xs,
    paddingVertical: 2,
    borderRadius: radius.sm,
  },
  unavailableText: {
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  content: {
    flex: 1,
    justifyContent: 'space-between',
  },
  name: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.text.primary,
    marginBottom: spacing.xs,
    lineHeight: fontSizes.sm * 1.2,
  },
  description: {
    fontSize: fontSizes.xs,
    color: colors.text.secondary,
    marginBottom: spacing.sm,
    lineHeight: fontSizes.xs * 1.3,
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  priceLabel: {
    fontSize: fontSizes.xs,
    color: colors.text.secondary,
    marginRight: spacing.xs,
  },
});
