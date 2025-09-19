# AmaKo Brand Identity Refactor - Implementation Summary

## Overview
This document summarizes the comprehensive brand identity refactor implemented for the AmaKo Momo Shop codebase, enforcing consistent AmaKo brand colors, typography, and design tokens across both web and mobile applications.

## Brand Colors Implemented

### Primary AmaKo Palette
- **AmaKo Brown 1**: `#5a2e22` - Deep brown for headings and primary elements
- **AmaKo Brown 2**: `#855335` - Medium brown for secondary elements
- **AmaKo Olive**: `#2c311a` - Dark olive for body text
- **AmaKo Blush**: `#d1ad97` - Warm blush for backgrounds and accents
- **AmaKo Amber**: `#ad8330` - Rich amber for links and highlights
- **AmaKo Sand**: `#c6ae73` - Neutral sand for borders and subtle elements
- **AmaKo Gold**: `#eeaf00` - Bright gold for primary buttons and accents

## Typography System

### Font Families
- **Title**: Tenor Sans - For main headings and brand elements
- **Subtitle**: Playfair Display - For elegant subtitles
- **Subheading**: Cormorant Garamond - For section subheadings
- **Section**: Oswald - For section headers
- **Body**: Nunito - For body text and UI elements
- **Caption**: EB Garamond - For captions and small text
- **Quote**: Prata - For quotes and special text

### Typography Scale
- **Display**: `clamp(2.2rem, 2.5vw, 3rem)`
- **H1**: `clamp(1.8rem, 2vw, 2.4rem)`
- **H2**: `clamp(1.5rem, 1.6vw, 1.9rem)`
- **H3**: `1.25rem`
- **Body**: `1rem`
- **Caption**: `0.85rem`

## Files Created/Modified

### New Files Created
1. **`src/styles/tokens.css`** - CSS custom properties for AmaKo brand colors and typography scale
2. **`src/styles/base.css`** - Global base styles with font assignments and utility classes
3. **`src/styles/globals.css`** - Tailwind CSS with AmaKo utility classes
4. **`amako-shop/src/theme/index.ts`** - React Native/Expo theme with AmaKo brand colors and fonts

### Files Modified
1. **`tailwind.config.js`** - Updated with AmaKo brand colors and fonts
2. **`resources/views/layouts/app.blade.php`** - Updated Google Fonts and added brand styles
3. **`public/css/theme.css`** - Updated with AmaKo brand colors and fonts
4. **`amako-shop/src/ui/tokens.ts`** - Updated mobile app tokens with AmaKo colors
5. **`resources/views/home/sections/featured-products.blade.php`** - Updated colors to use AmaKo palette
6. **`resources/views/pages/menu.blade.php`** - Updated tab colors to use AmaKo palette

## Implementation Details

### Web Application (Laravel/Blade)
- **CSS Variables**: All AmaKo brand colors are available as CSS custom properties
- **Tailwind Integration**: AmaKo colors available as `amk-*` classes (e.g., `amk-brown-1`, `amk-gold`)
- **Utility Classes**: Pre-built utility classes for common patterns:
  - `.amk-title` - Title styling
  - `.amk-subtitle` - Subtitle styling
  - `.amk-section` - Section header styling
  - `.amk-body` - Body text styling
  - `.amk-btn` - Primary button styling
  - `.amk-card` - Card styling
  - `.amk-badge` - Badge styling

### Mobile Application (React Native/Expo)
- **Theme Object**: Complete theme system with colors, fonts, typography, and components
- **Component Styles**: Pre-styled components for buttons, cards, badges
- **Helper Functions**: `getFontFamily()` and `getColor()` for consistent usage
- **Backward Compatibility**: Legacy colors maintained for gradual migration

### Google Fonts Integration
Updated font loading to include all AmaKo brand fonts:
```html
<link href="https://fonts.googleapis.com/css2?family=Tenor+Sans&family=Playfair+Display:wght@400;600&family=Cormorant+Garamond:wght@400;600&family=Oswald:wght@500;600&family=Nunito:wght@400;600&family=EB+Garamond:ital@0;1&family=Prata&display=swap" rel="stylesheet">
```

## Color Replacements Made

### From Legacy Colors to AmaKo Palette
- `#6E0D25` (old maroon) → `#5a2e22` (AmaKo Brown 1)
- `#DAA520` (old gold) → `#eeaf00` (AmaKo Gold)
- `#69585f` (old mocha) → `#2c311a` (AmaKo Olive)
- `#ef959d` (old pink) → `#eeaf00` (AmaKo Gold)
- Generic grays → AmaKo Olive for text

### Component Updates
- **Navigation**: Updated to use AmaKo Brown 1 background
- **Buttons**: Primary buttons now use AmaKo Gold
- **Cards**: Updated borders and shadows to use AmaKo Sand
- **Text**: Body text now uses AmaKo Olive
- **Headings**: All headings use AmaKo Brown 1
- **Badges**: Featured badges use AmaKo Gold

## Usage Examples

### CSS/Tailwind Usage
```css
/* Using CSS custom properties */
.my-component {
  color: var(--amako-olive);
  background: var(--amako-gold);
  font-family: var(--font-title);
}

/* Using Tailwind classes */
<h1 class="amk-title">Page Title</h1>
<button class="amk-btn">Primary Action</button>
<div class="amk-card">Card Content</div>
```

### React Native Usage
```typescript
import { theme } from './src/theme';

// Using theme colors
const styles = StyleSheet.create({
  title: {
    ...theme.typography.h1,
    color: theme.colors.brown1,
  },
  button: {
    ...theme.components.button.primary,
  },
});
```

## Backward Compatibility
- Legacy color variables maintained in `theme.css`
- Old font families still available as fallbacks
- Gradual migration path for existing components
- Mobile app tokens include both new and legacy colors

## Next Steps
1. **Component Audit**: Continue updating remaining components to use AmaKo palette
2. **Design System Documentation**: Create comprehensive design system documentation
3. **Linting Rules**: Implement stylelint rules to prevent non-brand colors
4. **Testing**: Validate brand consistency across all pages and components
5. **Mobile Font Loading**: Implement proper font loading for React Native

## Commit Message
```
feat(brand): enforce AmaKo color palette & typography; add tokens and utilities; codemods

- Add AmaKo brand color palette (7 colors) and typography scale
- Create CSS tokens, base styles, and Tailwind utilities
- Update Google Fonts to include AmaKo brand fonts
- Implement React Native theme system with brand colors
- Update key components (featured products, menu, navigation)
- Maintain backward compatibility with legacy colors
- Add utility classes for consistent brand application
```

## Files Changed Summary
- **Created**: 4 new files (tokens, base styles, globals, mobile theme)
- **Modified**: 6 existing files (config, layouts, themes, components)
- **Total**: 10 files updated for complete brand identity enforcement
