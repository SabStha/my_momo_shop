import React from 'react';
import { View, TextInput, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Card } from '../ui';
import { spacing, radius, fontSizes, colors } from '../ui';

interface SearchInputProps {
  value: string;
  onChangeText: (text: string) => void;
  placeholder?: string;
  style?: any;
}

export function SearchInput({ 
  value, 
  onChangeText, 
  placeholder = "Search menu items...",
  style 
}: SearchInputProps) {
  return (
    <View style={[styles.container, style]}>
      <Card padding="sm" radius="lg" shadow="light">
        <View style={styles.inputContainer}>
          <Ionicons 
            name="search" 
            size={20} 
            color={colors.gray[400]} 
            style={styles.searchIcon}
          />
          <TextInput
            style={styles.input}
            value={value}
            onChangeText={onChangeText}
            placeholder={placeholder}
            placeholderTextColor={colors.gray[400]}
            returnKeyType="search"
            clearButtonMode="while-editing"
            autoCapitalize="none"
            autoCorrect={false}
            accessibilityRole="search"
            accessibilityLabel="Search menu items"
            accessibilityHint="Type to search for food items"
          />
        </View>
      </Card>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: spacing.lg,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  searchIcon: {
    marginRight: spacing.sm,
  },
  input: {
    flex: 1,
    fontSize: fontSizes.md,
    color: colors.text.primary,
    paddingVertical: spacing.sm,
    paddingHorizontal: 0,
  },
});
