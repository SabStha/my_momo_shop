# Design System & Component Architecture

## Overview
This project uses a custom design system inspired by Tailwind CSS, providing consistent spacing, colors, typography, and component patterns.

## Design Tokens

### Colors
- **Primary**: Blue color palette (50-900)
- **Gray**: Neutral color palette (50-900)
- **Semantic**: success, warning, error, info
- **Base**: white, black

### Spacing
- **Scale**: 0, 1(4px), 2(8px), 3(12px), 4(16px), 5(20px), 6(24px), 8(32px), 10(40px), 12(48px), 16(64px), 20(80px), 24(96px), 32(128px)

### Border Radius
- **Scale**: none, sm(2px), base(4px), md(6px), lg(8px), xl(12px), 2xl(16px), 3xl(24px), full

### Typography
- **Sizes**: xs(12px), sm(14px), base(16px), lg(18px), xl(20px), 2xl(24px), 3xl(30px), 4xl(36px)
- **Weights**: normal(400), medium(500), semibold(600), bold(700)

## Components

### Screen Wrapper
The `Screen` component provides consistent screen layouts with:
- SafeAreaView integration
- Optional ScrollView
- Configurable background colors
- Consistent padding and margins

#### Usage
```tsx
import { Screen } from '../src/components';

// Basic screen
<Screen>
  <Content />
</Screen>

// Scrollable screen
<Screen scrollable>
  <LongContent />
</Screen>

// Custom background
<Screen backgroundColor={colors.gray[50]}>
  <Content />
</Screen>
```

### Convenience Components
- `ScrollableScreen`: Pre-configured scrollable screen
- `FullScreen`: Screen without safe area edges

## Utility Functions

### Spacing
```tsx
import { createSpacing } from '../src/utils';

const padding = createSpacing(4); // 16px
```

### Radius
```tsx
import { createRadius } from '../src/utils';

const borderRadius = createRadius('lg'); // 8px
```

## Pre-defined Styles
Common style combinations are available in the `styles` object:
```tsx
import { styles } from '../src/utils';

// Layout
styles.flex1, styles.flexCenter, styles.flexRow

// Spacing
styles.p4, styles.px4, styles.py4

// Typography
styles.textBase, styles.textLg, styles.textXl

// Colors
styles.bgWhite, styles.bgGray50, styles.textGray900
```

## File Structure
```
src/
  components/
    Screen.tsx          # Main screen wrapper
    index.ts           # Component exports
  utils/
    design.ts          # Design tokens and utilities
    index.ts           # Utility exports
```

## Best Practices
1. Use the `Screen` component for all screen layouts
2. Leverage pre-defined styles from the design system
3. Use semantic color names (e.g., `colors.primary[600]` instead of hex values)
4. Maintain consistent spacing using the spacing scale
5. Use TypeScript for all component props and function parameters
