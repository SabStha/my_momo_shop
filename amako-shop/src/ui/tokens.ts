// Design tokens for consistent UI spacing, sizing, and styling

// Spacing scale
export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  '2xl': 32,
  xxl: 32,
  xxxl: 48,
} as const;

// Border radius scale
export const radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  full: 9999,
} as const;

// Font sizes
export const fontSizes = {
  xs: 12,
  sm: 14,
  md: 16,
  lg: 20,
  xl: 24,
  '2xl': 28,
  xxl: 28,
  xxxl: 32,
} as const;

// Font weights
export const fontWeights = {
  normal: '400' as const,
  medium: '500' as const,
  semibold: '600' as const,
  bold: '700' as const,
} as const;

// Colors - AmaKo Brand Colors
export const colors = {
  // AmaKo Brand Colors
  amako: {
    brown1: '#5a2e22',
    brown2: '#855335',
    olive: '#2c311a',
    blush: '#d1ad97',
    amber: '#ad8330',
    sand: '#c6ae73',
    gold: '#eeaf00',
  },
  // Legacy Momo Shop Brand Colors (for backward compatibility)
  momo: {
    green: '#b8d8ba',
    sand: '#d9dbbc', 
    cream: '#fcddbc',
    pink: '#ef959d',
    mocha: '#69585f',
  },
  // Brand colors (updated to AmaKo palette)
  brand: {
    primary: '#5a2e22', // AmaKo brown1
    highlight: '#eeaf00', // AmaKo gold
    accent: '#eeaf00', // AmaKo gold (alias for highlight)
  },
  // Primary colors (AmaKo brown theme)
  primary: {
    50: '#faf7f6',
    100: '#f5ede9',
    200: '#e8d5cc',
    300: '#d9b8a7',
    400: '#c8967a',
    500: '#5a2e22', // AmaKo brown1
    600: '#4a251c',
    700: '#3a1c16',
    800: '#2a1310',
    900: '#1a0a0a',
  },
  // Gray scale
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
  error: '#5a2e22', // AmaKo brown1
  info: '#3b82f6',
  // Base colors
  white: '#ffffff',
  black: '#000000',
  transparent: 'transparent',
  // Background colors (matching Laravel)
  background: '#d9dbbc', // Momo sand background
  // Text colors (AmaKo palette)
  text: {
    primary: '#2c311a', // AmaKo olive
    secondary: '#855335', // AmaKo brown2
  },
} as const;

// Shadow presets
export const shadows = {
  light: {
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 2,
  },
  medium: {
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  heavy: {
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 8,
  },
} as const;

// Typography presets
export const typography = {
  xs: {
    fontSize: fontSizes.xs,
    lineHeight: 16,
  },
  sm: {
    fontSize: fontSizes.sm,
    lineHeight: 20,
  },
  md: {
    fontSize: fontSizes.md,
    lineHeight: 24,
  },
  lg: {
    fontSize: fontSizes.lg,
    lineHeight: 28,
  },
  xl: {
    fontSize: fontSizes.xl,
    lineHeight: 32,
  },
  xxl: {
    fontSize: fontSizes.xxl,
    lineHeight: 36,
  },
  xxxl: {
    fontSize: fontSizes.xxxl,
    lineHeight: 40,
  },
} as const;

// Helper functions
export const createSpacing = (value: keyof typeof spacing) => spacing[value];
export const createRadius = (value: keyof typeof radius) => radius[value];
export const createFontSize = (value: keyof typeof fontSizes) => fontSizes[value];
export const createShadow = (value: keyof typeof shadows) => shadows[value];
export const createTypography = (value: keyof typeof typography) => typography[value];
