# 🧪 Loading Screen Test Guide

## 🎯 How to Test Your Loading Animations

I've created a dedicated test screen where you can see all your loading animations in action!

---

## 🚀 Access the Test Screen

### Option 1: Direct URL (Easiest)
Open your app and navigate to:
```
/test-loading
```

### Option 2: Add to Debug Menu
If you have a debug or settings menu, you can add a button that navigates to `/test-loading`

### Option 3: Temporary Link
You can add a temporary link anywhere in your app:
```tsx
import { router } from 'expo-router';

<TouchableOpacity onPress={() => router.push('/test-loading')}>
  <Text>Test Loading Animations</Text>
</TouchableOpacity>
```

---

## 📱 What You Can Test

### 1. **Size Variations**
See all three sizes side-by-side:
- Small (40px)
- Medium (80px) - Default
- Large (120px)

### 2. **Text Variations**
Different loading messages used in your app:
- "Loading your orders..."
- "Loading notifications..."
- "Processing payment..."
- No text (spinner only)

### 3. **Timed Loading Tests**
Test how the spinner looks over time:
- **5 seconds** - Quick load
- **10 seconds** - Medium load
- **30 seconds** - Long load
- **Infinite** - Keeps loading until you stop it

### 4. **Full Screen Test**
See how the loading screen appears in full-screen mode (like when app is initializing)

### 5. **Custom Styling**
See how the spinner looks on:
- Dark backgrounds
- Colored backgrounds
- Different text colors

### 6. **Real App Examples**
See the actual loading states from your app screens:
- Orders Screen
- Notifications Screen
- Branch Selection
- Order Details

---

## 🎯 Quick Start

### Step 1: Run Your App
```bash
cd amako-shop
npm start
```

### Step 2: Open Test Screen
Navigate to `/test-loading` in your app

### Step 3: Try Infinite Loading
1. Scroll down to "Timed Loading Tests"
2. Tap "Infinite Loading" button
3. Watch your loading.gif animation continuously
4. Tap "Stop Loading" when done

---

## 🔍 What to Look For

### ✅ Good Signs
- [ ] Loading GIF animates smoothly
- [ ] NO double rotation (GIF should not spin extra)
- [ ] Animation loops seamlessly
- [ ] Text is readable below spinner
- [ ] Consistent appearance across all sizes

### ❌ Problems to Check
- [ ] GIF spinning too fast (extra rotation)
- [ ] Animation stuttering or freezing
- [ ] Text overlapping spinner
- [ ] Different appearance on different screens

---

## 💡 Usage Tips

### Test Infinite Loading
**Best for:** Seeing the animation loop continuously to check for smoothness

1. Tap "Infinite Loading"
2. Watch for 30-60 seconds
3. Check if animation loops smoothly
4. Tap "Stop Loading" when satisfied

### Test Full Screen
**Best for:** Seeing how users experience loading when app opens

1. Tap "Show Full Screen Loading"
2. See the loading screen as users would
3. Tap "Close Full Screen" to exit

### Compare Sizes
**Best for:** Choosing the right size for different contexts

- **Small:** Good for inline loading (inside cards, etc.)
- **Medium:** Good for section loading
- **Large:** Good for full-screen loading

---

## 📸 Screenshots

Take screenshots while testing to:
- Document the appearance
- Share with team
- Compare before/after
- Report any issues

---

## 🐛 Troubleshooting

### Problem: Can't Access Test Screen
**Solution:** Make sure the file exists at `amako-shop/app/test-loading.tsx`

### Problem: Spinner Not Showing
**Solution:** Check that `loading.gif` exists at `amako-shop/assets/animations/loading.gif`

### Problem: Animation Spinning Twice
**Solution:** This was fixed! But if you see it, check that no rotation animation is applied to the container.

### Problem: Text Not Showing
**Solution:** Check the `textStyle` prop - text color might match background

---

## 🎨 Customization

You can modify `test-loading.tsx` to add more tests:

### Add New Time Duration
```tsx
<TouchableOpacity 
  style={styles.testButton}
  onPress={() => handleTestLoading(60000)} // 60 seconds
>
  <Text style={styles.testButtonText}>Load for 1 minute</Text>
</TouchableOpacity>
```

### Add New Custom Style
```tsx
<View style={[styles.card, { backgroundColor: '#FF0000' }]}>
  <Text style={styles.cardTitle}>Red Background</Text>
  <LoadingSpinner 
    size="large" 
    text="Loading on red..." 
    textStyle={{ color: '#fff' }}
  />
</View>
```

---

## 🚀 Quick Commands

### Navigate to Test Screen (in code)
```tsx
import { router } from 'expo-router';

router.push('/test-loading');
```

### Open in Browser (Expo Go)
```
exp://192.168.x.x:8081/--/test-loading
```

Replace `192.168.x.x` with your dev server IP

---

## ✨ Test Scenarios

### Scenario 1: Initial Load Test
**Goal:** Verify smooth animation on first load

1. Open test screen
2. Immediately tap "Infinite Loading"
3. Watch for first 5 seconds
4. ✅ Should be smooth from start

### Scenario 2: Long Duration Test
**Goal:** Verify animation doesn't degrade over time

1. Tap "30 seconds" or "Infinite Loading"
2. Watch entire duration
3. ✅ Should loop seamlessly without stuttering

### Scenario 3: Full Screen Test
**Goal:** Verify real-world loading experience

1. Tap "Show Full Screen Loading"
2. Pretend you're waiting for app to load
3. ✅ Should look professional and not jarring

### Scenario 4: Size Comparison Test
**Goal:** Verify all sizes work correctly

1. Scroll through "Size Variations"
2. Compare Small → Medium → Large
3. ✅ All should maintain aspect ratio and animate smoothly

---

## 📊 Test Results Template

Use this to document your testing:

```
Date: _________
Device: _________
OS: _________

✅ Infinite Loading Test: PASS / FAIL
   Notes: _________________

✅ Full Screen Test: PASS / FAIL
   Notes: _________________

✅ Size Variations: PASS / FAIL
   Notes: _________________

✅ Custom Styling: PASS / FAIL
   Notes: _________________

Overall: PASS / FAIL
```

---

## 🎯 Next Steps After Testing

### If Everything Looks Good ✅
1. Remove or hide the test screen (optional)
2. Deploy to production
3. Celebrate! 🎉

### If You Find Issues ❌
1. Take screenshots
2. Note the specific issue
3. Check the component or animation file
4. Make adjustments
5. Re-test

---

## 📝 Summary

**Test Screen Location:** `app/test-loading.tsx`

**Access:** Navigate to `/test-loading` in your app

**Features:**
- ✅ Size variations
- ✅ Text variations  
- ✅ Timed tests (5s, 10s, 30s, infinite)
- ✅ Full-screen mode
- ✅ Custom styling examples
- ✅ Real app examples

**Best Test:** Infinite Loading for continuous animation viewing

---

**Happy Testing! 🧪✨**

Your loading animations should look smooth and professional! 🚀

