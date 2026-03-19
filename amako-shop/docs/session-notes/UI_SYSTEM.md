# UI System Documentation

## Overview
This project includes a comprehensive UI system built with TypeScript, featuring design tokens, reusable components, and consistent styling patterns. All components are built without external UI libraries and include full accessibility support.

## Architecture

### Core Components
- **Design Tokens**: Consistent spacing, colors, typography, and shadows
- **UI Components**: Reusable, accessible components with TypeScript interfaces
- **Type System**: Comprehensive type definitions for the food ordering app
- **Accessibility**: Full accessibility support with proper roles and labels

## File Structure
```
src/
├── types.ts              # Core type definitions
├── ui/
│   ├── tokens.ts         # Design tokens and styling constants
│   ├── Button.tsx        # Button component with variants
│   ├── Card.tsx          # Card component with shadows
│   ├── Chip.tsx          # Selectable pill component
│   ├── QuantityStepper.tsx # +/- quantity controls
│   ├── Price.tsx         # Price formatting for NPR
│   └── index.ts          # UI exports
└── utils/
    └── design.ts         # Legacy design system (backward compatible)
```

## Type System

### Core Types

#### Money
```typescript
interface Money {
  currency: "NPR";        // Nepalese Rupees
  amount: number;          // Amount in rupees
}
```

#### Menu System
```typescript
interface MenuItem {
  id: string;
  name: string;
  desc?: string;
  imageUrl?: string;
  basePrice: Money;
  variants?: Variant[];
  addOns?: AddOn[];
  categoryId: string;
  isAvailable: boolean;
}

interface Variant {
  id: string;
  name: string;
  priceDiff: Money;       // Price difference from base
}

interface AddOn {
  id: string;
  name: string;
  price: Money;
}

interface Category {
  id: string;
  name: string;
}
```

#### API Types
```typescript
interface ApiError {
  message: string;
  code?: string;
}

interface ApiResponse<T = any> {
  data: T;
  message?: string;
  success: boolean;
}
```

## Design Tokens

### Spacing Scale
```typescript
export const spacing = {
  xs: 4,    // 4px
  sm: 8,    // 8px
  md: 12,   // 12px
  lg: 16,   // 16px
  xl: 24,   // 24px
  xxl: 32,  // 32px
  xxxl: 48, // 48px
} as const;
```

### Border Radius
```typescript
export const radius = {
  sm: 8,     // 8px
  md: 12,    // 12px
  lg: 16,    // 16px
  xl: 24,    // 24px
  full: 9999, // Full circle
} as const;
```

### Font Sizes
```typescript
export const fontSizes = {
  xs: 12,   // 12px
  sm: 14,   // 14px
  md: 16,   // 16px
  lg: 20,   // 20px
  xl: 24,   // 24px
  xxl: 28,  // 28px
  xxxl: 32, // 32px
} as const;
```

### Colors
```typescript
export const colors = {
  primary: { 50: '#eff6ff', 100: '#dbeafe', /* ... */ },
  gray: { 50: '#f9fafb', 100: '#f3f4f6', /* ... */ },
  success: '#10b981',
  warning: '#f59e0b',
  error: '#ef4444',
  info: '#3b82f6',
  white: '#ffffff',
  black: '#000000',
} as const;
```

### Shadows
```typescript
export const shadows = {
  light: { shadowOpacity: 0.05, elevation: 2 },
  medium: { shadowOpacity: 0.1, elevation: 4 },
  heavy: { shadowOpacity: 0.15, elevation: 8 },
} as const;
```

## UI Components

### Button Component

#### Basic Usage
```tsx
import { Button, PrimaryButton, SecondaryButton } from '../src/ui';

// Basic button
<Button 
  title="Click Me" 
  onPress={() => {}} 
  variant="solid" 
/>

// Convenience components
<PrimaryButton title="Primary Action" onPress={() => {}} />
<SecondaryButton title="Secondary Action" onPress={() => {}} />
```

#### Props
```typescript
interface ButtonProps {
  title: string;
  onPress: () => void;
  variant?: 'solid' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  loading?: boolean;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  accessibilityLabel?: string;
  accessibilityHint?: string;
}
```

#### Variants
- **Solid**: Primary button with filled background
- **Outline**: Secondary button with border only
- **Sizes**: Small (sm), Medium (md), Large (lg)
- **States**: Normal, Disabled, Loading

### Card Component

#### Basic Usage
```tsx
import { Card, ElevatedCard, RoundedCard } from '../src/ui';

<Card padding="md" shadow="light">
  <Text>Card content</Text>
</Card>

<ElevatedCard padding="lg">
  <Text>Elevated card with medium shadow</Text>
</ElevatedCard>
```

#### Props
```typescript
interface CardProps {
  children: React.ReactNode;
  padding?: keyof typeof spacing;
  radius?: keyof typeof radius;
  shadow?: keyof typeof shadows;
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
}
```

#### Convenience Components
- **ElevatedCard**: Medium shadow
- **HeavyCard**: Heavy shadow
- **CompactCard**: Small padding
- **SpaciousCard**: Large padding
- **RoundedCard**: Large border radius
- **PillCard**: Full border radius

### Chip Component

#### Basic Usage
```tsx
import { Chip, PrimaryChip, SuccessChip } from '../src/ui';

<Chip 
  label="Selectable Option" 
  selected={isSelected}
  onPress={() => setIsSelected(!isSelected)}
/>

<PrimaryChip label="Primary" selected={true} />
<SuccessChip label="Success" />
```

#### Props
```typescript
interface ChipProps {
  label: string;
  selected?: boolean;
  onPress?: () => void;
  disabled?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'error';
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
}
```

#### Variants
- **Default**: Gray styling
- **Primary**: Blue styling
- **Success**: Green styling
- **Warning**: Yellow styling
- **Error**: Red styling

### QuantityStepper Component

#### Basic Usage
```tsx
import { QuantityStepper, CartQuantityStepper } from '../src/ui';

<QuantityStepper
  value={quantity}
  onValueChange={setQuantity}
  min={1}
  max={10}
/>

<CartQuantityStepper
  value={cartQuantity}
  onValueChange={updateCartQuantity}
/>
```

#### Props
```typescript
interface QuantityStepperProps {
  value: number;
  onValueChange: (value: number) => void;
  min?: number;
  max?: number;
  step?: number;
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  showValue?: boolean;
}
```

#### Features
- **Smart Bounds**: Respects min/max values
- **Step Control**: Configurable increment/decrement
- **Size Variants**: Small, Medium, Large
- **Accessibility**: Proper ARIA labels and states

### Price Component

#### Basic Usage
```tsx
import { Price, MenuItemPrice, TotalPrice } from '../src/ui';

// With Money object
<Price value={{ currency: 'NPR', amount: 12.99 }} />

// With number (auto-converts to Money)
<Price value={12.99} />

// Specialized components
<MenuItemPrice value={12.99} />
<TotalPrice value={45.50} />
```

#### Props
```typescript
interface PriceProps {
  value: Money | number;
  size?: keyof typeof fontSizes;
  weight?: keyof typeof fontWeights;
  color?: string;
  showCurrency?: boolean;
  showDecimals?: boolean;
}
```

#### Convenience Components
- **SmallPrice**: Small font size
- **LargePrice**: Large font size
- **BoldPrice**: Bold weight
- **MenuItemPrice**: Optimized for menu display
- **CartItemPrice**: Optimized for cart display
- **TotalPrice**: Optimized for totals
- **PriceDifference**: Shows price differences

## Usage Examples

### Building a Menu Item Card
```tsx
import { Card, Button, Price, Chip } from '../src/ui';
import { MenuItem } from '../src/types';

function MenuItemCard({ item }: { item: MenuItem }) {
  return (
    <Card padding="md" shadow="light">
      <Text style={styles.textLg}>{item.name}</Text>
      {item.desc && (
        <Text style={styles.textBase}>{item.desc}</Text>
      )}
      
      <View style={styles.flexRow}>
        <Price value={item.basePrice} />
        <Chip label={item.categoryId} variant="primary" />
      </View>
      
      <Button 
        title="Add to Cart" 
        onPress={() => addToCart(item)}
        disabled={!item.isAvailable}
      />
    </Card>
  );
}
```

### Building a Cart Item
```tsx
import { Card, QuantityStepper, Price, Button } from '../src/ui';

function CartItem({ item, onUpdateQuantity, onRemove }) {
  return (
    <Card padding="md" shadow="light">
      <View style={styles.flexRow}>
        <View style={{ flex: 1 }}>
          <Text style={styles.textLg}>{item.name}</Text>
          <Price value={item.price} />
        </View>
        
        <QuantityStepper
          value={item.quantity}
          onValueChange={(qty) => onUpdateQuantity(item.id, qty)}
          min={1}
        />
        
        <Button 
          title="Remove" 
          onPress={() => onRemove(item.id)}
          variant="outline"
          size="sm"
        />
      </View>
    </Card>
  );
}
```

### Building a Category Filter
```tsx
import { Chip } from '../src/ui';

function CategoryFilter({ categories, selectedCategory, onSelectCategory }) {
  return (
    <ScrollView horizontal showsHorizontalScrollIndicator={false}>
      {categories.map(category => (
        <Chip
          key={category.id}
          label={category.name}
          selected={selectedCategory === category.id}
          onPress={() => onSelectCategory(category.id)}
          variant="primary"
        />
      ))}
    </ScrollView>
  );
}
```

## Accessibility Features

### Screen Reader Support
- **Proper Roles**: button, adjustable, text
- **Accessibility Labels**: Descriptive text for actions
- **Accessibility Hints**: Additional context for complex interactions
- **Accessibility State**: Selected, disabled, loading states

### Touch Targets
- **Minimum Size**: 44px for buttons and interactive elements
- **Proper Spacing**: Adequate spacing between interactive elements
- **Visual Feedback**: Active states and hover effects

### Keyboard Navigation
- **Focus Management**: Proper focus indicators
- **Logical Order**: Tab order follows visual layout
- **Skip Links**: For complex interfaces

## Best Practices

### 1. Use Design Tokens
```tsx
// ✅ Good - Use tokens for consistency
<View style={{ padding: spacing.md, borderRadius: radius.md }}>

// ❌ Avoid - Hard-coded values
<View style={{ padding: 12, borderRadius: 12 }}>
```

### 2. Leverage Convenience Components
```tsx
// ✅ Good - Use specialized components
<MenuItemPrice value={item.price} />
<CartQuantityStepper value={quantity} onValueChange={setQuantity} />

// ❌ Avoid - Overriding props unnecessarily
<Price value={item.price} size="lg" weight="semibold" color={colors.primary[600]} />
```

### 3. Maintain Accessibility
```tsx
// ✅ Good - Provide accessibility information
<Button 
  title="Add to Cart"
  accessibilityLabel="Add pizza to shopping cart"
  accessibilityHint="This will add one pizza to your cart"
/>

// ❌ Avoid - Missing accessibility props
<Button title="Add to Cart" />
```

### 4. Use Proper TypeScript
```tsx
// ✅ Good - Proper typing
const handleAddToCart = (item: MenuItem) => {
  addToCart(item);
};

// ❌ Avoid - Any types
const handleAddToCart = (item: any) => {
  addToCart(item);
};
```

## Migration Guide

### From Old Design System
```tsx
// Old way
import { styles, colors } from '../src/utils/design';

// New way
import { spacing, colors, fontSizes } from '../src/ui';
// OR keep using styles for backward compatibility
import { styles, colors } from '../src/utils/design';
```

### Updating Existing Components
```tsx
// Old Button
<TouchableOpacity style={styles.button} onPress={onPress}>
  <Text style={styles.buttonText}>{title}</Text>
</TouchableOpacity>

// New Button
<Button title={title} onPress={onPress} variant="solid" />
```

## Future Enhancements

### Planned Features
- [ ] **Theme System**: Dark/light mode support
- [ ] **Animation Library**: Smooth transitions and micro-interactions
- [ ] **Icon System**: Consistent icon library
- [ ] **Form Components**: Input, Select, Checkbox, Radio
- [ ] **Modal System**: Dialog, Sheet, Popover components
- [ ] **Toast System**: Success, error, info notifications

### Technical Improvements
- [ ] **Performance**: Memoization and optimization
- [ ] **Testing**: Unit and integration tests
- [ ] **Storybook**: Component documentation and testing
- [ ] **Design Tokens**: CSS-in-JS and CSS variables
- [ ] **Internationalization**: Multi-language support

## Troubleshooting

### Common Issues

1. **Type Errors**
   - Ensure proper imports from `../src/ui`
   - Check TypeScript interfaces match usage
   - Verify Money objects have correct structure

2. **Styling Issues**
   - Use design tokens instead of hard-coded values
   - Check component prop combinations
   - Verify shadow and radius values

3. **Accessibility Issues**
   - Ensure all interactive elements have proper roles
   - Check touch target sizes (minimum 44px)
   - Verify screen reader compatibility

### Debug Mode
Enable component debugging in development:
```tsx
if (__DEV__) {
  console.log('Button props:', { title, variant, size, disabled });
  console.log('Design tokens:', { spacing, colors, fontSizes });
}
```

## Integration with Other Systems

### State Management
- **Zustand**: Seamless integration with cart store
- **React Query**: Works with API data fetching
- **Context**: Compatible with React Context API

### Navigation
- **Expo Router**: Proper navigation integration
- **Deep Linking**: Maintains component state
- **Screen Transitions**: Smooth navigation flows

### Testing
- **Jest**: Unit testing framework
- **React Native Testing Library**: Component testing
- **Accessibility Testing**: Screen reader compatibility
