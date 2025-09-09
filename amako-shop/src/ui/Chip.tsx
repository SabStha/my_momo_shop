import React from 'react';
import {
  TouchableOpacity,
  Text,
  ViewStyle,
  TextStyle,
  View,
} from 'react-native';
import { colors, spacing, radius, fontSizes, fontWeights } from './tokens';

export interface ChipProps {
  label: string;
  selected?: boolean;
  onPress?: () => void;
  disabled?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'error';
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  style?: ViewStyle;
  textStyle?: TextStyle;
  accessibilityLabel?: string;
  accessibilityHint?: string;
}

export function Chip({
  label,
  selected = false,
  onPress,
  disabled = false,
  size = 'md',
  variant = 'default',
  leftIcon,
  rightIcon,
  style,
  textStyle,
  accessibilityLabel,
  accessibilityHint,
}: ChipProps) {
  const isInteractive = !!onPress && !disabled;

  // Size configurations
  const sizeConfig = {
    sm: {
      paddingVertical: spacing.xs,
      paddingHorizontal: spacing.sm,
      fontSize: fontSizes.xs,
      borderRadius: radius.sm,
      iconSize: 12,
    },
    md: {
      paddingVertical: spacing.xs,
      paddingHorizontal: spacing.sm,
      fontSize: fontSizes.sm,
      borderRadius: radius.md,
      iconSize: 16,
    },
    lg: {
      paddingVertical: spacing.sm,
      paddingHorizontal: spacing.md,
      fontSize: fontSizes.md,
      borderRadius: radius.md,
      iconSize: 20,
    },
  };

  const config = sizeConfig[size];

  // Variant styles
  const getVariantStyles = (): ViewStyle => {
    if (disabled) {
      return {
        backgroundColor: colors.gray[100],
        borderColor: colors.gray[300],
      };
    }

    if (selected) {
      switch (variant) {
        case 'primary':
          return {
            backgroundColor: colors.primary[600],
            borderColor: colors.primary[600],
          };
        case 'success':
          return {
            backgroundColor: colors.success,
            borderColor: colors.success,
          };
        case 'warning':
          return {
            backgroundColor: colors.warning,
            borderColor: colors.warning,
          };
        case 'error':
          return {
            backgroundColor: colors.error,
            borderColor: colors.error,
          };
        default:
          return {
            backgroundColor: colors.gray[800],
            borderColor: colors.gray[800],
          };
      }
    }

    // Unselected state
    switch (variant) {
      case 'primary':
        return {
          backgroundColor: colors.primary[50],
          borderColor: colors.primary[200],
        };
      case 'success':
        return {
          backgroundColor: colors.success + '20',
          borderColor: colors.success + '40',
        };
      case 'warning':
        return {
          backgroundColor: colors.warning + '20',
          borderColor: colors.warning + '40',
        };
      case 'error':
        return {
          backgroundColor: colors.error + '20',
          borderColor: colors.error + '40',
        };
      default:
        return {
          backgroundColor: colors.gray[50],
          borderColor: colors.gray[200],
        };
    }
  };

  // Text styles
  const getTextStyles = (): TextStyle => {
    if (disabled) {
      return {
        color: colors.gray[500],
        fontWeight: fontWeights.normal,
      };
    }

    if (selected) {
      return {
        color: colors.white,
        fontWeight: fontWeights.semibold,
      };
    }

    switch (variant) {
      case 'primary':
        return {
          color: colors.primary[700],
          fontWeight: fontWeights.medium,
        };
      case 'success':
        return {
          color: colors.success,
          fontWeight: fontWeights.medium,
        };
      case 'warning':
        return {
          color: colors.warning,
          fontWeight: fontWeights.medium,
        };
      case 'error':
        return {
          color: colors.error,
          fontWeight: fontWeights.medium,
        };
      default:
        return {
          color: colors.gray[700],
          fontWeight: fontWeights.medium,
        };
    }
  };

  const chipStyle: ViewStyle = {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: config.paddingVertical,
    paddingHorizontal: config.paddingHorizontal,
    borderRadius: config.borderRadius,
    borderWidth: 1,
    minHeight: 32, // Accessibility minimum touch target
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

  const content = (
    <>
      {leftIcon && (
        <View style={{ marginRight: iconSpacing }}>{leftIcon}</View>
      )}
      <Text style={textStyleObj}>{label}</Text>
      {rightIcon && (
        <View style={{ marginLeft: iconSpacing }}>{rightIcon}</View>
      )}
    </>
  );

  if (!isInteractive) {
    return (
      <View
        style={chipStyle}
        accessibilityRole="none"
        accessibilityLabel={accessibilityLabel || label}
      >
        {content}
      </View>
    );
  }

  return (
    <TouchableOpacity
      style={chipStyle}
      onPress={onPress}
      disabled={disabled}
      activeOpacity={0.7}
      accessibilityRole="button"
      accessibilityLabel={accessibilityLabel || label}
      accessibilityHint={accessibilityHint}
      accessibilityState={{ selected, disabled }}
    >
      {content}
    </TouchableOpacity>
  );
}

// Convenience components for common chip types
export function PrimaryChip(props: Omit<ChipProps, 'variant'>) {
  return <Chip {...props} variant="primary" />;
}

export function SuccessChip(props: Omit<ChipProps, 'variant'>) {
  return <Chip {...props} variant="success" />;
}

export function WarningChip(props: Omit<ChipProps, 'variant'>) {
  return <Chip {...props} variant="warning" />;
}

export function ErrorChip(props: Omit<ChipProps, 'variant'>) {
  return <Chip {...props} variant="error" />;
}

export function SmallChip(props: Omit<ChipProps, 'size'>) {
  return <Chip {...props} size="sm" />;
}

export function LargeChip(props: Omit<ChipProps, 'size'>) {
  return <Chip {...props} size="lg" />;
}
