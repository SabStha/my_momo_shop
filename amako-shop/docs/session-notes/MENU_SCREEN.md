# Menu Screen Implementation

## Overview
The Menu screen (`app/tabs/index.tsx`) is the main screen of the app, displaying a comprehensive menu with categories, search functionality, and a responsive grid layout. It includes offline support, loading states, and seamless navigation to item details.

## Features

### ✅ Core Functionality
- **Data Fetching**: Uses `useMenu()` hook with offline fallback
- **Category Filtering**: Horizontal scrollable category chips with "All" option
- **Search**: Client-side fuzzy search on item names and descriptions
- **Grid Layout**: 2-column responsive grid of menu items
- **Navigation**: Tap items to navigate to `/item/[id]` detail screen
- **Pull-to-Refresh**: RefreshControl for manual data refresh

### ✅ User Experience
- **Loading States**: 6 skeleton cards during initial load
- **Error Handling**: Error state with retry functionality
- **Offline Indicator**: Shows when using fallback data
- **Empty States**: Contextual messages for no results
- **Responsive Design**: Adapts to different screen sizes

### ✅ Visual Design
- **Offers Banner**: Placeholder for future promotional content
- **Consistent Spacing**: Uses design tokens from `tokens.ts`
- **Modern UI**: Cards with shadows, rounded corners, and proper typography
- **Accessibility**: Proper labels, hints, and touch targets

## Component Architecture

### Main Components

#### 1. **MenuScreen** (`app/tabs/index.tsx`)
- Main screen component with state management
- Orchestrates data fetching, filtering, and rendering
- Handles loading, error, and success states

#### 2. **ItemCard** (`src/components/ItemCard.tsx`)
- Individual menu item card component
- Displays image, name, description, and starting price
- Handles navigation to item detail screen
- Shows availability status badge

#### 3. **SkeletonCard** (`src/components/SkeletonCard.tsx`)
- Loading placeholder with animated opacity
- Matches exact dimensions of ItemCard
- Provides smooth loading experience

#### 4. **CategoryFilter** (`src/components/CategoryFilter.tsx`)
- Horizontal scrollable category chips
- Supports loading state with skeleton chips
- Handles category selection and "All" option

#### 5. **SearchInput** (`src/components/SearchInput.tsx`)
- Search input with icon and styling
- Integrated with Card component for consistent design
- Proper accessibility attributes

#### 6. **OffersBanner** (`src/components/OffersBanner.tsx`)
- Placeholder banner for future offers
- Uses primary color scheme with icon
- Interactive design with chevron

#### 7. **ErrorState** (`src/components/ErrorState.tsx`)
- Error display with icon and retry button
- Consistent error messaging
- User-friendly retry functionality

### Utility Functions

#### **Search Utilities** (`src/utils/search.ts`)
- `fuzzySearch()`: Simple text search implementation
- `searchItemsByCategory()`: Combined category and search filtering
- Client-side search for instant results

## Data Flow

### 1. **Initial Load**
```
useMenu() → API call → Fallback to bundled data → Render UI
```

### 2. **Category Filtering**
```
User selects category → Local state update → Filter items → Re-render grid
```

### 3. **Search**
```
User types query → Debounced search → Filter items → Update grid
```

### 4. **Navigation**
```
User taps item → Router navigation → Item detail screen
```

## State Management

### Local State
```typescript
const [selectedCategory, setSelectedCategory] = useState<string | null>(null);
const [searchQuery, setSearchQuery] = useState('');
```

### API State
```typescript
const { data, isLoading, isError, error, refetch, isFetching } = useMenu();
const { data: isOffline } = useIsOffline();
```

### Computed State
```typescript
const filteredItems = useMemo(() => {
  return searchItemsByCategory(items, selectedCategory, searchQuery);
}, [items, selectedCategory, searchQuery]);
```

## Responsive Design

### Grid Layout
```typescript
const numColumns = 2;
const itemWidth = (screenWidth - spacing.lg * 3) / numColumns;
```

### Spacing System
- Uses consistent spacing tokens from `tokens.ts`
- Responsive padding and margins
- Proper touch targets (minimum 44px)

### Typography Scale
- Hierarchical text sizing
- Consistent line heights
- Proper contrast ratios

## Loading States

### Initial Loading
- Shows 6 skeleton cards in grid layout
- Skeleton category filter
- Skeleton search input
- Maintains layout structure

### Refresh Loading
- Pull-to-refresh indicator
- Maintains current view state
- Smooth loading experience

## Error Handling

### Error States
- Network failures
- API errors
- Data parsing issues
- Graceful fallback to bundled data

### User Recovery
- Clear error messages
- Retry functionality
- Offline indicator
- Fallback data availability

## Offline Support

### Fallback Strategy
1. **Primary**: Attempt API call
2. **Fallback**: Use bundled `assets/menu.json`
3. **Seamless**: UI renders instantly with fallback data
4. **Indicator**: Shows offline banner when using fallback

### Offline Features
- Full menu browsing
- Category filtering
- Search functionality
- Navigation to item details

## Accessibility Features

### Screen Reader Support
- Proper accessibility labels
- Descriptive hints
- Role definitions
- State announcements

### Touch Targets
- Minimum 44px touch areas
- Proper spacing between interactive elements
- Clear visual feedback

### Keyboard Navigation
- Logical tab order
- Focus indicators
- Keyboard shortcuts (where applicable)

## Performance Optimizations

### Rendering
- `useMemo` for filtered items
- Efficient list rendering with `FlatList`
- Optimized re-renders

### Memory Management
- Proper cleanup of animations
- Efficient image loading
- Minimal component re-mounts

### Network
- Cached API responses
- Offline fallback data
- Efficient data fetching

## Testing Considerations

### Unit Tests
- Component rendering
- State management
- User interactions
- Error handling

### Integration Tests
- API integration
- Navigation flow
- Data filtering
- Search functionality

### E2E Tests
- Complete user journeys
- Cross-screen navigation
- Offline scenarios
- Error recovery

## Future Enhancements

### Planned Features
- [ ] **Add to Cart**: Direct cart integration from menu
- [ ] **Favorites**: Save favorite items
- [ ] **Recent Items**: Show recently viewed
- [ ] **Image Lazy Loading**: Optimize image performance
- [ ] **Advanced Search**: Filters, sorting, price ranges

### Technical Improvements
- [ ] **Virtual Scrolling**: For large menus
- [ ] **Image Caching**: Offline image storage
- [ ] **Search History**: Remember user searches
- [ ] **Analytics**: Track user interactions
- [ ] **A/B Testing**: Menu layout variations

## Usage Examples

### Basic Implementation
```tsx
import { MenuScreen } from './app/tabs/index';

// The screen is automatically rendered when navigating to the tabs
// No additional setup required
```

### Customization
```tsx
// Modify the number of columns
const numColumns = 3; // Change to 3 columns

// Custom search placeholder
<SearchInput
  placeholder="Find your favorite food..."
  value={searchQuery}
  onChangeText={setSearchQuery}
/>

// Custom category filter styling
<CategoryFilter
  categories={categories}
  selectedCategory={selectedCategory}
  onSelectCategory={setSelectedCategory}
  style={customStyles}
/>
```

## Troubleshooting

### Common Issues

1. **Items Not Loading**
   - Check API connectivity
   - Verify fallback data exists
   - Check console for errors

2. **Search Not Working**
   - Verify search query state
   - Check item data structure
   - Ensure search utility is imported

3. **Navigation Issues**
   - Verify route exists (`/item/[id]`)
   - Check item ID parameter
   - Ensure proper navigation setup

4. **Layout Problems**
   - Check screen dimensions
   - Verify spacing tokens
   - Test on different screen sizes

### Debug Mode
```typescript
if (__DEV__) {
  console.log('Menu Debug:', {
    selectedCategory,
    searchQuery,
    filteredItems: filteredItems.length,
    isOffline,
  });
}
```

## Best Practices

### 1. **Performance**
- Use `useMemo` for expensive computations
- Implement proper loading states
- Optimize image rendering

### 2. **User Experience**
- Provide immediate feedback
- Handle edge cases gracefully
- Maintain consistent interactions

### 3. **Accessibility**
- Test with screen readers
- Ensure proper contrast
- Provide alternative text

### 4. **Code Quality**
- Follow TypeScript best practices
- Use consistent naming conventions
- Implement proper error boundaries

## Dependencies

### Core Dependencies
- `react-native`: Core framework
- `expo-router`: Navigation
- `@tanstack/react-query`: Data fetching
- `zustand`: State management

### UI Dependencies
- `@expo/vector-icons`: Icons
- Custom UI components from `src/ui`
- Design tokens from `src/ui/tokens`

### Utility Dependencies
- Custom search utilities
- Type definitions
- Helper functions

## File Structure
```
app/
├── tabs/
│   └── index.tsx              # Main menu screen
└── item/
    └── [id].tsx               # Item detail screen

src/
├── components/
│   ├── ItemCard.tsx           # Menu item card
│   ├── SkeletonCard.tsx       # Loading placeholder
│   ├── CategoryFilter.tsx     # Category selection
│   ├── SearchInput.tsx        # Search functionality
│   ├── OffersBanner.tsx       # Promotional banner
│   └── ErrorState.tsx         # Error display
├── utils/
│   └── search.ts              # Search utilities
└── ui/
    └── tokens.ts              # Design tokens
```

This implementation provides a robust, user-friendly menu experience with comprehensive offline support, smooth loading states, and excellent accessibility features.
