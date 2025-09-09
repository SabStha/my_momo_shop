import React from 'react';
import {
  TouchableOpacity,
  Text,
  ActivityIndicator,
  ViewStyle,
  TextStyle,
  View,
} from 'react-native';
import { colors, spacing, radius, fontSizes, fontWeights } from './tokens';

export interface ButtonProps {
  title: string;
  onPress: () => void;
  variant?: 'solid' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  loading?: boolean;
  style?: ViewStyle;
  textStyle?: TextStyle;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  accessibilityLabel?: string;
  accessibilityHint?: string;
}

export function Button({
  title,
  onPress,
  variant = 'solid',
  size = 'md',
  disabled = false,
  loading = false,
  style,
  textStyle,
  leftIcon,
  rightIcon,
  accessibilityLabel,
  accessibilityHint,
}: ButtonProps) {
  const isDisabled = disabled || loading;

  // Size configurations
  const sizeConfig = {
    sm: {
      paddingVertical: spacing.xs,
      paddingHorizontal: spacing.sm,
      fontSize: fontSizes.sm,
      borderRadius: radius.sm,
    },
    md: {
      paddingVertical: spacing.sm,
      paddingHorizontal: spacing.md,
      fontSize: fontSizes.md,
      borderRadius: radius.md,
    },
    lg: {
      paddingVertical: spacing.md,
      paddingHorizontal: spacing.lg,
      fontSize: fontSizes.lg,
      borderRadius: radius.md,
    },
  };

  const config = sizeConfig[size];

  // Variant styles
  const getVariantStyles = (): ViewStyle => {
    if (isDisabled) {
      return {
        backgroundColor: colors.gray[200],
        borderColor: colors.gray[300],
      };
    }

    if (variant === 'solid') {
      return {
        backgroundColor: colors.primary[600],
        borderColor: colors.primary[600],
      };
    }

    return {
      backgroundColor: colors.transparent,
      borderColor: colors.primary[600],
      borderWidth: 1,
    };
  };

  // Text styles
  const getTextStyles = (): TextStyle => {
    if (isDisabled) {
      return {
        color: colors.gray[500],
        fontWeight: fontWeights.medium,
      };
    }

    if (variant === 'solid') {
      return {
        color: colors.white,
        fontWeight: fontWeights.semibold,
      };
    }

    return {
      color: colors.primary[600],
      fontWeight: fontWeights.semibold,
    };
  };

  const buttonStyle: ViewStyle = {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: config.paddingVertical,
    paddingHorizontal: config.paddingHorizontal,
    borderRadius: config.borderRadius,
    minHeight: 44, // Accessibility minimum touch target
    ...getVariantStyles(),
    ...style,
  };

  const textStyleObj: TextStyle = {
    fontSize: config.fontSize,
    lineHeight: config.fontSize * 1.2,
    textAlign: 'center',
    ...getTextStyles(),
    ...textStyle,
  };

  const iconSpacing = spacing.xs;

  return (
    <TouchableOpacity
      style={buttonStyle}
      onPress={onPress}
      disabled={isDisabled}
      activeOpacity={0.8}
      accessibilityRole="button"
      accessibilityLabel={accessibilityLabel || title}
      accessibilityHint={accessibilityHint}
      accessibilityState={{ disabled: isDisabled }}
    >
      {loading ? (
        <ActivityIndicator
          size="small"
          color={variant === 'solid' ? colors.white : colors.primary[600]}
        />
      ) : (
        <>
          {leftIcon && (
            <View style={{ marginRight: iconSpacing }}>{leftIcon}</View>
          )}
          <Text style={textStyleObj}>{title}</Text>
          {rightIcon && (
            <View style={{ marginLeft: iconSpacing }}>{rightIcon}</View>
          )}
        </>
      )}
    </TouchableOpacity>
  );
}

// Convenience components for common button types
export function PrimaryButton(props: Omit<ButtonProps, 'variant'>) {
  return <Button {...props} variant="solid" />;
}

export function SecondaryButton(props: Omit<ButtonProps, 'variant'>) {
  return <Button {...props} variant="outline" />;
}

export function SmallButton(props: Omit<ButtonProps, 'size'>) {
  return <Button {...props} size="sm" />;
}

export function LargeButton(props: Omit<ButtonProps, 'size'>) {
  return <Button {...props} size="lg" />;
}
