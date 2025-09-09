import React from 'react';
import { View, ScrollView, ViewStyle, ScrollViewProps } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { colors } from '../ui';

interface ScreenProps {
  children: React.ReactNode;
  scrollable?: boolean;
  style?: ViewStyle;
  contentContainerStyle?: ViewStyle;
  scrollViewProps?: ScrollViewProps;
  safeAreaEdges?: ('top' | 'bottom' | 'left' | 'right')[];
  backgroundColor?: string;
}

export function Screen({
  children,
  scrollable = false,
  style,
  contentContainerStyle,
  scrollViewProps,
  safeAreaEdges = ['top', 'bottom'],
  backgroundColor = colors.white,
}: ScreenProps) {
  const containerStyle: ViewStyle = {
    flex: 1,
    backgroundColor,
    ...style,
  };

  if (scrollable) {
    return (
      <SafeAreaView style={containerStyle} edges={safeAreaEdges}>
        <ScrollView
          style={{ flex: 1 }}
          contentContainerStyle={[
            { flexGrow: 1 },
            contentContainerStyle,
          ]}
          showsVerticalScrollIndicator={false}
          showsHorizontalScrollIndicator={false}
          {...scrollViewProps}
        >
          {children}
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={containerStyle} edges={safeAreaEdges}>
      <View style={[{ flex: 1 }, contentContainerStyle]}>
        {children}
      </View>
    </SafeAreaView>
  );
}

// Convenience components for common screen patterns
export function ScrollableScreen(props: Omit<ScreenProps, 'scrollable'>) {
  return <Screen {...props} scrollable={true} />;
}

export function FullScreen(props: Omit<ScreenProps, 'safeAreaEdges'>) {
  return <Screen {...props} safeAreaEdges={[]} />;
}
