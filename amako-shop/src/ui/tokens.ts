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
  black: '900' as const,
  extrabold: '800' as const,
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
  // Brand colors (updated to blue theme)
  brand: {
    primary: '#152039', // New blue primary color
    highlight: '#eeaf00', // AmaKo gold
    accent: '#eeaf00', // AmaKo gold (alias for highlight)
  },
  // Primary colors (blue theme)
  primary: {
    50: '#f0f4ff',
    100: '#e0e7ff',
    200: '#c7d2fe',
    300: '#a5b4fc',
    400: '#818cf8',
    500: '#152039', // New blue primary color
    600: '#0f1a2e',
    700: '#0a1423',
    800: '#050e18',
    900: '#00080d',
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
  error: '#152039', // Updated to blue theme
  info: '#3b82f6',
  // Additional color scales
  green: {
    50: '#f0fdf4',
    100: '#dcfce7',
    200: '#bbf7d0',
    300: '#86efac',
    400: '#4ade80',
    500: '#22c55e',
    600: '#16a34a',
    700: '#15803d',
    800: '#166534',
    900: '#14532d',
  },
  blue: {
    50: '#eff6ff',
    100: '#dbeafe',
    200: '#bfdbfe',
    300: '#93c5fd',
    400: '#60a5fa',
    500: '#3b82f6',
    600: '#2563eb',
    700: '#1d4ed8',
    800: '#1e40af',
    900: '#1e3a8a',
  },
  orange: {
    50: '#fff7ed',
    100: '#ffedd5',
    200: '#fed7aa',
    300: '#fdba74',
    400: '#fb923c',
    500: '#f59e0b',
    600: '#ea580c',
    700: '#c2410c',
    800: '#9a3412',
    900: '#7c2d12',
  },
  red: {
    50: '#fef2f2',
    100: '#fee2e2',
    200: '#fecaca',
    300: '#fca5a5',
    400: '#f87171',
    500: '#ef4444',
    600: '#dc2626',
    700: '#b91c1c',
    800: '#991b1b',
    900: '#7f1d1d',
  },
  // Base colors
  white: '#ffffff',
  black: '#000000',
  transparent: 'transparent',
  // Background colors (matching Laravel)
  background: '#ffffff', // White background
  // Text colors (blue theme)
  text: {
    primary: '#152039', // New blue primary
    secondary: '#4b5563', // Gray for secondary text
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
