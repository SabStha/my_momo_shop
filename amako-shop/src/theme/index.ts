// AmaKo Brand Theme for React Native/Expo
// This file provides consistent brand colors and fonts for the mobile app

export const colors = {
  // AmaKo Brand Colors
  brown1: '#5a2e22',
  brown2: '#855335',
  olive: '#2c311a',
  blush: '#d1ad97',
  amber: '#ad8330',
  sand: '#c6ae73',
  gold: '#eeaf00',
  
  // Semantic colors
  primary: '#5a2e22',      // brown1
  secondary: '#855335',    // brown2
  accent: '#eeaf00',       // gold
  text: '#2c311a',         // olive
  textSecondary: '#855335', // brown2
  background: '#ffffff',
  surface: '#f8f9fa',
  
  // Status colors
  success: '#10b981',
  warning: '#f59e0b',
  error: '#5a2e22', // AmaKo brown1
  info: '#3b82f6',
  
  // Neutral colors
  white: '#ffffff',
  black: '#000000',
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
};

export const fonts = {
  // AmaKo Brand Fonts (React Native font names)
  title: 'TenorSans_400Regular',
  subtitle: 'PlayfairDisplay_600SemiBold',
  subheading: 'CormorantGaramond_600SemiBold',
  section: 'Oswald_600SemiBold',
  body: 'Nunito_400Regular',
  caption: 'EBGaramond_400Regular',
  quote: 'Prata_400Regular',
  
  // Font weights
  weights: {
    regular: '400',
    medium: '500',
    semibold: '600',
    bold: '700',
  },
};

export const typography = {
  // Display styles
  display: {
    fontFamily: fonts.title,
    fontSize: 32,
    lineHeight: 36,
    fontWeight: fonts.weights.bold,
    color: colors.brown1,
  },
  
  // Heading styles
  h1: {
    fontFamily: fonts.title,
    fontSize: 28,
    lineHeight: 32,
    fontWeight: fonts.weights.bold,
    color: colors.brown1,
  },
  
  h2: {
    fontFamily: fonts.title,
    fontSize: 24,
    lineHeight: 28,
    fontWeight: fonts.weights.semibold,
    color: colors.brown1,
  },
  
  h3: {
    fontFamily: fonts.section,
    fontSize: 20,
    lineHeight: 24,
    fontWeight: fonts.weights.semibold,
    color: colors.brown1,
  },
  
  // Body text styles
  body: {
    fontFamily: fonts.body,
    fontSize: 16,
    lineHeight: 24,
    fontWeight: fonts.weights.regular,
    color: colors.text,
  },
  
  bodySmall: {
    fontFamily: fonts.body,
    fontSize: 14,
    lineHeight: 20,
    fontWeight: fonts.weights.regular,
    color: colors.text,
  },
  
  // Specialized text styles
  subtitle: {
    fontFamily: fonts.subtitle,
    fontSize: 18,
    lineHeight: 24,
    fontWeight: fonts.weights.semibold,
    color: colors.brown2,
  },
  
  subheading: {
    fontFamily: fonts.subheading,
    fontSize: 16,
    lineHeight: 22,
    fontWeight: fonts.weights.semibold,
    color: colors.brown2,
  },
  
  caption: {
    fontFamily: fonts.caption,
    fontSize: 12,
    lineHeight: 16,
    fontWeight: fonts.weights.regular,
    color: colors.textSecondary,
  },
  
  quote: {
    fontFamily: fonts.quote,
    fontSize: 16,
    lineHeight: 24,
    fontWeight: fonts.weights.regular,
    color: colors.text,
    fontStyle: 'italic',
  },
};

export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  '2xl': 32,
  '3xl': 48,
  '4xl': 64,
};

export const borderRadius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  full: 9999,
};

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
};

// Component-specific styles
export const components = {
  button: {
    primary: {
      backgroundColor: colors.gold,
      color: colors.black,
      borderRadius: borderRadius.lg,
      paddingHorizontal: spacing.lg,
      paddingVertical: spacing.md,
      fontFamily: fonts.body,
      fontSize: 16,
      fontWeight: fonts.weights.semibold,
    },
    secondary: {
      backgroundColor: colors.brown2,
      color: colors.white,
      borderRadius: borderRadius.lg,
      paddingHorizontal: spacing.lg,
      paddingVertical: spacing.md,
      fontFamily: fonts.body,
      fontSize: 16,
      fontWeight: fonts.weights.semibold,
    },
  },
  
  card: {
    backgroundColor: colors.white,
    borderRadius: borderRadius.xl,
    padding: spacing.lg,
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
    borderWidth: 1,
    borderColor: `${colors.sand}60`, // 60% opacity
  },
  
  badge: {
    backgroundColor: colors.brown2,
    color: colors.white,
    borderRadius: borderRadius.full,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    fontSize: 12,
    fontWeight: fonts.weights.medium,
    fontFamily: fonts.body,
  },
  
  badgeAccent: {
    backgroundColor: colors.gold,
    color: colors.black,
    borderRadius: borderRadius.full,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    fontSize: 12,
    fontWeight: fonts.weights.medium,
    fontFamily: fonts.body,
  },
};

// Helper function to get font family with fallback
export const getFontFamily = (fontKey: keyof typeof fonts): string => {
  return fonts[fontKey] || fonts.body;
};

// Helper function to get color with fallback
export const getColor = (colorKey: keyof typeof colors): string => {
  return colors[colorKey] || colors.text;
};

// Export default theme object
export const theme = {
  colors,
  fonts,
  typography,
  spacing,
  borderRadius,
  shadows,
  components,
  getFontFamily,
  getColor,
};

export default theme;
