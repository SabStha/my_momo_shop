import React from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ViewStyle,
  TextStyle,
} from 'react-native';
import { colors, spacing, radius, fontSizes, fontWeights } from './tokens';

export interface QuantityStepperProps {
  value: number;
  onValueChange: (value: number) => void;
  min?: number;
  max?: number;
  step?: number;
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  showValue?: boolean;
  style?: ViewStyle;
  buttonStyle?: ViewStyle;
  valueStyle?: TextStyle;
  accessibilityLabel?: string;
  accessibilityHint?: string;
}

export function QuantityStepper({
  value,
  onValueChange,
  min = 1,
  max,
  step = 1,
  size = 'md',
  disabled = false,
  showValue = true,
  style,
  buttonStyle,
  valueStyle,
  accessibilityLabel = 'Quantity stepper',
  accessibilityHint = 'Use + and - buttons to adjust quantity',
}: QuantityStepperProps) {
  // Size configurations
  const sizeConfig = {
    sm: {
      buttonSize: 28,
      fontSize: fontSizes.sm,
      borderRadius: radius.sm,
      valueFontSize: fontSizes.sm,
    },
    md: {
      buttonSize: 36,
      fontSize: fontSizes.md,
      borderRadius: radius.md,
      valueFontSize: fontSizes.md,
    },
    lg: {
      buttonSize: 44,
      fontSize: fontSizes.lg,
      borderRadius: radius.md,
      valueFontSize: fontSizes.lg,
    },
  };

  const config = sizeConfig[size];

  const handleDecrement = () => {
    const newValue = Math.max(min, value - step);
    if (newValue !== value) {
      onValueChange(newValue);
    }
  };

  const handleIncrement = () => {
    const newValue = max ? Math.min(max, value + step) : value + step;
    if (newValue !== value) {
      onValueChange(newValue);
    }
  };

  const canDecrement = value > min && !disabled;
  const canIncrement = !max || value < max;

  const stepperStyle: ViewStyle = {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    ...style,
  };

  const buttonBaseStyle: ViewStyle = {
    width: config.buttonSize,
    height: config.buttonSize,
    borderRadius: config.borderRadius,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 44, // Accessibility minimum touch target
    ...buttonStyle,
  };

  const decrementButtonStyle: ViewStyle = {
    ...buttonBaseStyle,
    backgroundColor: canDecrement ? colors.gray[100] : colors.gray[200],
    borderColor: canDecrement ? colors.gray[300] : colors.gray[300],
    borderWidth: 1,
  };

  const incrementButtonStyle: ViewStyle = {
    ...buttonBaseStyle,
    backgroundColor: colors.primary[100],
    borderColor: colors.primary[300],
    borderWidth: 1,
  };

  const valueContainerStyle: ViewStyle = {
    paddingHorizontal: spacing.md,
    minWidth: 40,
    alignItems: 'center',
  };

  const valueTextStyle: TextStyle = {
    fontSize: config.valueFontSize,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    textAlign: 'center',
    ...valueStyle,
  };

  const buttonTextStyle: TextStyle = {
    fontSize: config.fontSize,
    fontWeight: fontWeights.bold,
    color: colors.gray[700],
  };

  const incrementButtonTextStyle: TextStyle = {
    ...buttonTextStyle,
    color: colors.primary[700],
  };

  return (
    <View
      style={stepperStyle}
      accessibilityRole="adjustable"
      accessibilityLabel={accessibilityLabel}
      accessibilityHint={accessibilityHint}
      accessibilityValue={{
        min,
        max: max || undefined,
        now: value,
      }}
    >
      {/* Decrement Button */}
      <TouchableOpacity
        style={decrementButtonStyle}
        onPress={handleDecrement}
        disabled={!canDecrement || disabled}
        activeOpacity={0.7}
        accessibilityRole="button"
        accessibilityLabel="Decrease quantity"
        accessibilityHint="Tap to decrease quantity by one"
        accessibilityState={{ disabled: !canDecrement || disabled }}
      >
        <Text style={buttonTextStyle}>−</Text>
      </TouchableOpacity>

      {/* Value Display */}
      {showValue && (
        <View style={valueContainerStyle}>
          <Text style={valueTextStyle}>{value}</Text>
        </View>
      )}

      {/* Increment Button */}
      <TouchableOpacity
        style={incrementButtonStyle}
        onPress={handleIncrement}
        disabled={disabled || !canIncrement}
        activeOpacity={0.7}
        accessibilityRole="button"
        accessibilityLabel="Increase quantity"
        accessibilityHint="Tap to increase quantity by one"
        accessibilityState={{ disabled: disabled || !canIncrement }}
      >
        <Text style={incrementButtonTextStyle}>+</Text>
      </TouchableOpacity>
    </View>
  );
}

// Convenience components for common sizes
export function SmallQuantityStepper(props: Omit<QuantityStepperProps, 'size'>) {
  return <QuantityStepper {...props} size="sm" />;
}

export function LargeQuantityStepper(props: Omit<QuantityStepperProps, 'size'>) {
  return <QuantityStepper {...props} size="lg" />;
}

// Specialized stepper for cart items
export function CartQuantityStepper({
  value,
  onValueChange,
  ...props
}: Omit<QuantityStepperProps, 'min' | 'step'>) {
  return (
    <QuantityStepper
      value={value}
      onValueChange={onValueChange}
      min={1}
      step={1}
      {...props}
    />
  );
}
