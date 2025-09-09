import React from 'react';
import { View, ScrollView, StyleSheet } from 'react-native';
import { Category } from '../types';
import { Chip } from '../ui';
import { spacing, colors } from '../ui';

interface CategoryFilterProps {
  categories: Category[];
  selectedCategory: string | null;
  onSelectCategory: (categoryId: string | null) => void;
  isLoading?: boolean;
}

export function CategoryFilter({ 
  categories, 
  selectedCategory, 
  onSelectCategory, 
  isLoading = false 
}: CategoryFilterProps) {
  if (isLoading) {
    return (
      <View style={styles.container}>
        <ScrollView 
          horizontal 
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.scrollContent}
        >
          {/* Loading skeleton chips */}
          {[1, 2, 3, 4, 5].map((i) => (
            <View key={i} style={styles.skeletonChip} />
          ))}
        </ScrollView>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <ScrollView 
        horizontal 
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* All Categories Option */}
        <Chip
          label="All"
          selected={selectedCategory === null}
          onPress={() => onSelectCategory(null)}
          variant="primary"
          size="md"
          style={styles.chip}
        />
        
        {/* Category Chips */}
        {categories.map(category => (
          <Chip
            key={category.id}
            label={category.name}
            selected={selectedCategory === category.id}
            onPress={() => onSelectCategory(category.id)}
            variant="primary"
            size="md"
            style={styles.chip}
          />
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: spacing.lg,
  },
  scrollContent: {
    paddingHorizontal: spacing.lg,
    gap: spacing.sm,
  },
  chip: {
    minWidth: 80,
  },
  skeletonChip: {
    width: 80,
    height: 36,
    backgroundColor: colors.gray[200],
    borderRadius: 18,
    marginRight: spacing.sm,
  },
});
