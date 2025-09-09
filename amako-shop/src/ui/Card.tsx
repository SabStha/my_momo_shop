import React from 'react';
import { View, ViewStyle, ViewProps } from 'react-native';
import { colors, spacing, radius, shadows } from './tokens';

export interface CardProps extends ViewProps {
  children: React.ReactNode;
  padding?: keyof typeof spacing;
  radius?: keyof typeof radius;
  shadow?: keyof typeof shadows;
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
  style?: ViewStyle;
}

export function Card({
  children,
  padding = 'md',
  radius: borderRadius = 'md',
  shadow = 'light',
  backgroundColor = colors.white,
  borderColor = colors.gray[200],
  borderWidth = 1,
  style,
  ...props
}: CardProps) {
  const cardStyle: ViewStyle = {
    backgroundColor,
    borderRadius: radius[borderRadius],
    padding: spacing[padding],
    borderColor,
    borderWidth,
    ...shadows[shadow],
    ...style,
  };

  return (
    <View
      style={cardStyle}
      accessibilityRole="none"
      {...props}
    >
      {children}
    </View>
  );
}

// Convenience components for common card types
export function ElevatedCard(props: Omit<CardProps, 'shadow'>) {
  return <Card {...props} shadow="medium" />;
}

export function HeavyCard(props: Omit<CardProps, 'shadow'>) {
  return <Card {...props} shadow="heavy" />;
}

export function CompactCard(props: Omit<CardProps, 'padding'>) {
  return <Card {...props} padding="sm" />;
}

export function SpaciousCard(props: Omit<CardProps, 'padding'>) {
  return <Card {...props} padding="lg" />;
}

export function RoundedCard(props: Omit<CardProps, 'radius'>) {
  return <Card {...props} radius="lg" />;
}

export function PillCard(props: Omit<CardProps, 'radius'>) {
  return <Card {...props} radius="full" />;
}
