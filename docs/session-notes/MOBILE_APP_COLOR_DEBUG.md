# Mobile App Color Debug Guide

## Issue
The red/maroon colors are still showing in the mobile app despite updating the color tokens.

## What We've Updated
1. ✅ `amako-shop/src/ui/tokens.ts` - Updated with AmaKo brand colors
2. ✅ `amako-shop/src/components/navigation/TopBar.tsx` - Updated notification dot color
3. ✅ `amako-shop/src/components/navigation/BottomBar.tsx` - Updated badge colors
4. ✅ `amako-shop/src/utils/design.ts` - Updated brand colors
5. ✅ `amako-shop/src/theme/index.ts` - Updated theme colors

## Current Color Configuration

### In `src/ui/tokens.ts`:
```typescript
export const colors = {
  amako: {
    brown1: '#5a2e22',  // Should replace all red/maroon
    brown2: '#855335',
    olive: '#2c311a',
    blush: '#d1ad97',
    amber: '#ad8330',
    sand: '#c6ae73',
    gold: '#eeaf00',
  },
  brand: {
    primary: '#5a2e22', // AmaKo brown1
    highlight: '#eeaf00', // AmaKo gold
    accent: '#eeaf00', // AmaKo gold
  },
  error: '#5a2e22', // AmaKo brown1
}
```

### In Components:
- `TopBar.tsx`: `backgroundColor: colors.amako.brown1` (line 139)
- `BottomBar.tsx`: `backgroundColor: colors.amako.brown1` (lines 149, 166)

## Possible Issues & Solutions

### 1. **Expo Cache Issue**
The mobile app might be using cached versions of the components.

**Solution**: Restart the Expo development server:
```bash
cd amako-shop
npm run start
# Or with cache clear:
expo start -c
```

### 2. **Hot Reload Not Working**
Changes might not be hot-reloading properly.

**Solution**: 
- Stop the Expo server (Ctrl+C)
- Clear Expo cache: `expo start -c`
- Restart the app on device/simulator

### 3. **Build Cache Issue**
The app might be using a cached build.

**Solution**:
```bash
cd amako-shop
# Clear all caches
expo start -c --clear
# Or rebuild completely
expo run:android --clear
expo run:ios --clear
```

### 4. **Device/Simulator Cache**
The device or simulator might have cached the old colors.

**Solution**:
- **Android**: Uninstall and reinstall the app
- **iOS**: Delete app from simulator and reinstall
- **Web**: Hard refresh (Ctrl+Shift+R)

### 5. **Import Path Issue**
Components might be importing from wrong path.

**Current imports in components**:
- `TopBar.tsx`: `import { colors } from '../../ui/tokens';` ✅
- `BottomBar.tsx`: `import { colors } from '../../ui/tokens';` ✅

### 6. **Color Override**
Some other style might be overriding the colors.

**Check**: Look for any inline styles or other color definitions that might override the token colors.

## Verification Steps

1. **Check the actual rendered colors**:
   - Open React Native debugger
   - Inspect the notification badges
   - Verify the backgroundColor property

2. **Add debug logging**:
   ```typescript
   console.log('Notification dot color:', colors.amako.brown1);
   ```

3. **Test with a different color**:
   Temporarily change `colors.amako.brown1` to `'#00ff00'` (bright green) to see if changes are being applied.

## Expected Result
After clearing caches and restarting:
- Notification badges should be `#5a2e22` (AmaKo Brown 1)
- All red/maroon colors should be replaced with AmaKo brand colors
- The app should show consistent brown/gold color scheme

## Next Steps
1. Try restarting Expo with cache clear: `expo start -c`
2. If still not working, try rebuilding the app completely
3. Check if there are any other color definitions overriding the tokens
