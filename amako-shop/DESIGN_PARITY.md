# Design Parity: Web ↔ Mobile

This document explains how to maintain visual consistency between the Laravel web homepage and React Native mobile app.

## Design Tokens

All visual elements use centralized design tokens from `src/ui/tokens.ts`:

### Colors
- **Primary**: `#5a2e22` (AmaKo Brown 1) - Main brand color
- **Accent**: `#eeaf00` (AmaKo Gold) - Highlights and CTAs
- **Background**: `#d9dbbc` (Momo Sand) - Main background
- **Cards**: `#fcddbc` (Momo Cream) - Card backgrounds
- **Text**: `#69585f` (Momo Mocha) - Secondary text

### Spacing
- **xs**: 4px
- **sm**: 8px  
- **md**: 12px
- **lg**: 16px
- **xl**: 24px
- **2xl**: 32px

### Typography
- **Headings**: `fontSizes['2xl']` (28px), `fontWeights.bold`
- **Body**: `fontSizes.md` (16px), `fontWeights.normal`
- **Labels**: `fontSizes.sm` (14px), `fontWeights.medium`

### Shadows & Elevation
- **Light**: `shadows.light` - Subtle elevation
- **Medium**: `shadows.medium` - Cards and buttons
- **Heavy**: `shadows.heavy` - Modals and overlays

## Component Mapping

| Web Section | Mobile Component | File Location |
|-------------|------------------|---------------|
| Hero Carousel | `HeroCarousel` | `src/components/home/HeroCarousel.tsx` |
| KPI Cards | `KpiRow` + `KpiCard` | `src/components/home/KpiRow.tsx` |
| Featured Products | `ProductGrid` + `ProductCard` | `src/components/home/ProductGrid.tsx` |
| Benefits Grid | `BenefitsGrid` | `src/components/home/BenefitsGrid.tsx` |
| Customer Reviews | `ReviewsSection` | `src/components/home/ReviewsSection.tsx` |
| Visit Us | `VisitUs` | `src/components/home/VisitUs.tsx` |

## API Endpoints

The mobile app uses the same API endpoints as the web:

- **Featured Products**: `GET /products/featured`
- **Home Stats**: `GET /stats/home`
- **Reviews**: `GET /reviews?featured=true`
- **Store Info**: `GET /store/info`

## Visual QA

Use the design parity screen for visual comparison:

```bash
# Navigate to the dev screen
/debug/DesignParity
```

This screen renders all components with sample data for side-by-side comparison with the web version.

## Maintaining Parity

### 1. Color Changes
- Update `src/ui/tokens.ts` colors
- Both web and mobile will automatically use new colors

### 2. Spacing Changes
- Update spacing values in `tokens.ts`
- All components use tokenized spacing

### 3. Typography Changes
- Update font sizes/weights in `tokens.ts`
- Components automatically inherit changes

### 4. New Sections
- Create new component in `src/components/home/`
- Add to `home.tsx` composition
- Update design parity screen

## Testing Checklist

- [ ] Colors match web exactly
- [ ] Spacing is consistent
- [ ] Typography hierarchy matches
- [ ] Shadows and elevation feel right
- [ ] Icons are semantically correct
- [ ] Interactive elements work
- [ ] Cart integration functions
- [ ] API data displays correctly

## Common Issues

### Color Mismatches
- Check if hardcoded hex values exist
- Ensure all colors come from `tokens.ts`

### Spacing Issues
- Verify no hardcoded padding/margins
- Use tokenized spacing values

### Typography Problems
- Check font size/weight usage
- Ensure proper hierarchy

### Performance Issues
- Use `FlatList` for long lists
- Implement proper image caching
- Add loading states

## Development Workflow

1. **Design Changes**: Update tokens first
2. **Component Updates**: Modify individual components
3. **Visual QA**: Test on design parity screen
4. **Integration**: Update home screen composition
5. **Testing**: Verify on physical device

This ensures consistent visual experience across web and mobile platforms.
