import { StyleSheet } from 'react-native';

// Re-export UI tokens for backward compatibility
export * from '../ui/tokens';

// Design tokens - inspired by Tailwind CSS
export const colors = {
  // Primary colors
  primary: {
    50: '#eff6ff',
    100: '#dbeafe',
    500: '#3b82f6',
    600: '#2563eb',
    700: '#1d4ed8',
    900: '#1e3a8a',
  },
  // Neutral colors
  gray: {
    50: '#f9fafb',
    100: '#f3f4f6',
    200: '#e5e7eb',
    300: '#d1d5db',
    400: '#9ca3af',
    500: '#6b7280',
    600: '#4b5563',
    700: '#374151',
    800: '#1f2937',
    900: '#111827',
  },
  // Semantic colors
  success: '#10b981',
  warning: '#f59e0b',
  error: '#ef4444',
  info: '#3b82f6',
  white: '#ffffff',
  black: '#000000',
} as const;

export const spacing = {
  0: 0,
  1: 4,
  2: 8,
  3: 12,
  4: 16,
  5: 20,
  6: 24,
  8: 32,
  10: 40,
  12: 48,
  16: 64,
  20: 80,
  24: 96,
  32: 128,
} as const;

// radius is imported from tokens.ts to avoid conflicts

export const typography = {
  sizes: {
    xs: 12,
    sm: 14,
    base: 16,
    lg: 18,
    xl: 20,
    '2xl': 24,
    '3xl': 30,
    '4xl': 36,
  },
  weights: {
    normal: '400' as const,
    medium: '500' as const,
    semibold: '600' as const,
    bold: '700' as const,
  },
} as const;

// Legacy utility functions (keeping for backward compatibility)
export const createSpacing = (value: keyof typeof spacing) => spacing[value];
// createRadius is imported from tokens.ts to avoid conflicts

// Common styles using the new token system
export const styles = {
  // Layout
  flex1: { flex: 1 },
  flexCenter: { justifyContent: 'center', alignItems: 'center' },
  flexRow: { flexDirection: 'row' },
  flexColumn: { flexDirection: 'column' },
  
  // Spacing
  p4: { padding: 4 },
  p6: { padding: 6 },
  p8: { padding: 8 },
  p12: { padding: 12 },
  p16: { padding: 16 },
  p24: { padding: 24 },
  
  px4: { paddingHorizontal: 4 },
  px6: { paddingHorizontal: 6 },
  px8: { paddingHorizontal: 8 },
  px12: { paddingHorizontal: 12 },
  px16: { paddingHorizontal: 16 },
  px24: { paddingHorizontal: 24 },
  
  py4: { paddingVertical: 4 },
  py6: { paddingVertical: 6 },
  py8: { paddingVertical: 8 },
  py12: { paddingVertical: 12 },
  py16: { paddingVertical: 16 },
  py24: { paddingVertical: 24 },
  
  m4: { margin: 4 },
  m6: { margin: 6 },
  m8: { margin: 8 },
  m12: { margin: 12 },
  m16: { margin: 16 },
  m24: { margin: 24 },
  
  mx4: { marginHorizontal: 4 },
  mx6: { marginHorizontal: 6 },
  mx8: { marginHorizontal: 8 },
  mx12: { marginHorizontal: 12 },
  mx16: { marginHorizontal: 16 },
  mx24: { marginHorizontal: 24 },
  
  my4: { marginVertical: 4 },
  my6: { marginVertical: 6 },
  my8: { marginVertical: 8 },
  my12: { marginVertical: 12 },
  my16: { marginVertical: 16 },
  my24: { marginVertical: 24 },
  
  // Typography
  textXs: { fontSize: 12 },
  textSm: { fontSize: 14 },
  textBase: { fontSize: 16 },
  textLg: { fontSize: 20 },
  textXl: { fontSize: 24 },
  text2xl: { fontSize: 28 },
  text3xl: { fontSize: 32 },
  
  // Background colors
  bgWhite: { backgroundColor: colors.white },
  bgGray50: { backgroundColor: colors.gray[50] },
  bgGray100: { backgroundColor: colors.gray[100] },
  bgGray200: { backgroundColor: colors.gray[200] },
  bgPrimary50: { backgroundColor: colors.primary[50] },
  bgPrimary100: { backgroundColor: colors.primary[100] },
  bgPrimary600: { backgroundColor: colors.primary[600] },
  
  // Border radius
  roundedSm: { borderRadius: 8 },
  roundedMd: { borderRadius: 12 },
  roundedLg: { borderRadius: 16 },
  roundedXl: { borderRadius: 24 },
  roundedFull: { borderRadius: 9999 },
} as const;
