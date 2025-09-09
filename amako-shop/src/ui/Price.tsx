import React from 'react';
import { Text, TextStyle, TextProps } from 'react-native';
import { Money } from '../types';
import { colors, fontSizes, fontWeights } from './tokens';

export interface PriceProps extends TextProps {
  value: Money | number;
  size?: keyof typeof fontSizes;
  weight?: keyof typeof fontWeights;
  color?: string;
  showCurrency?: boolean;
  showDecimals?: boolean;
  style?: TextStyle;
  accessibilityLabel?: string;
}

export function Price({
  value,
  size = 'md',
  weight = 'medium',
  color = colors.gray[900],
  showCurrency = true,
  showDecimals = false,
  style,
  accessibilityLabel,
  ...props
}: PriceProps) {
  // Convert number to Money object if needed
  const moneyValue: Money = typeof value === 'number' 
    ? { currency: 'NPR', amount: value }
    : value;

  // Format the price
  const formatPrice = (amount: number): string => {
    if (showDecimals) {
      return amount.toFixed(2);
    }
    return Math.round(amount).toString();
  };

  // Build the display text
  const displayText = showCurrency 
    ? `Rs. ${formatPrice(moneyValue.amount)}`
    : formatPrice(moneyValue.amount);

  // Build accessibility label
  const buildAccessibilityLabel = (): string => {
    if (accessibilityLabel) return accessibilityLabel;
    
    const amount = formatPrice(moneyValue.amount);
    return showCurrency 
      ? `${amount} Nepalese Rupees`
      : `${amount}`;
  };

  const textStyle: TextStyle = {
    fontSize: fontSizes[size],
    fontWeight: fontWeights[weight],
    color,
    ...style,
  };

  return (
    <Text
      style={textStyle}
      accessibilityLabel={buildAccessibilityLabel()}
      accessibilityRole="text"
      {...props}
    >
      {displayText}
    </Text>
  );
}

// Convenience components for common use cases
export function SmallPrice(props: Omit<PriceProps, 'size'>) {
  return <Price {...props} size="sm" />;
}

export function LargePrice(props: Omit<PriceProps, 'size'>) {
  return <Price {...props} size="lg" />;
}

export function ExtraLargePrice(props: Omit<PriceProps, 'size'>) {
  return <Price {...props} size="xl" />;
}

export function BoldPrice(props: Omit<PriceProps, 'weight'>) {
  return <Price {...props} weight="bold" />;
}

export function SemiboldPrice(props: Omit<PriceProps, 'weight'>) {
  return <Price {...props} weight="semibold" />;
}

// Specialized price components
export function MenuItemPrice({ 
  value, 
  ...props 
}: Omit<PriceProps, 'size' | 'weight'>) {
  return (
    <Price
      value={value}
      size="lg"
      weight="semibold"
      color={colors.primary[600]}
      {...props}
    />
  );
}

export function CartItemPrice({ 
  value, 
  ...props 
}: Omit<PriceProps, 'size' | 'weight'>) {
  return (
    <Price
      value={value}
      size="md"
      weight="medium"
      color={colors.gray[700]}
      {...props}
    />
  );
}

export function TotalPrice({ 
  value, 
  ...props 
}: Omit<PriceProps, 'size' | 'weight'>) {
  return (
    <Price
      value={value}
      size="xl"
      weight="bold"
      color={colors.gray[900]}
      {...props}
    />
  );
}

// Price difference component for variants
export function PriceDifference({ 
  value, 
  isPositive = true,
  ...props 
}: Omit<PriceProps, 'color'> & { isPositive?: boolean }) {
  return (
    <Price
      value={value}
      size="sm"
      weight="medium"
      color={isPositive ? colors.success : colors.error}
      showCurrency={true}
      {...props}
    />
  );
}

// Compact price without currency symbol
export function CompactPrice(props: Omit<PriceProps, 'showCurrency'>) {
  return <Price {...props} showCurrency={false} />;
}

// Integer price without decimals
export function IntegerPrice(props: Omit<PriceProps, 'showDecimals'>) {
  return <Price {...props} showDecimals={false} />;
}
