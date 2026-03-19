# Why Choose Amako Shop - Current Content Analysis

## 📋 What's Currently In This Section

### **1. Header**
- **Title**: "✨ Why Choose Ama Ko Shop?"
- **Subtitle**: "From our kitchen to your heart — here's why thousands trust us with their favorite comfort food."

---

### **2. KPI Stats (Top Row) - 3 Cards**

#### **Card 1: Orders Delivered**
- **Icon**: 🚚 `truck-delivery`
- **Value**: `179+`
- **Label**: "Orders Delivered"
- **Trend**: "+-100% this month"
- **Trend Icon**: `trending-up`

#### **Card 2: Happy Customers**
- **Icon**: ❤️ `account-heart`
- **Value**: `21+`
- **Label**: "Happy Customers"
- **Trend**: "100% satisfaction"
- **Trend Icon**: `emoticon-happy`

#### **Card 3: Years in Business**
- **Icon**: 🏆 `trophy`
- **Value**: `1+`
- **Label**: "Years in Business"
- **Trend**: "Trusted brand"
- **Trend Icon**: `shield-check`

---

### **3. Benefits (Bottom Row) - 3 Cards**

#### **Card 1: Fresh Ingredients**
- **Emoji**: 🥬
- **Title**: "Fresh Ingredients"
- **Description**: "High-quality ingredients sourced daily."

#### **Card 2: Authentic Recipes**
- **Emoji**: 👩‍🍳
- **Title**: "Authentic Recipes"
- **Description**: "Traditional Nepalese recipes."

#### **Card 3: Fast Delivery**
- **Emoji**: 🚚
- **Title**: "Fast Delivery"
- **Description**: "25 minutes average delivery."

---

### **4. Call to Action (Optional)**
- **Button Text**: "Try Our Momos Today"
- **Button Color**: Red (#EF4444)
- **Action**: Navigate to menu or trigger add to cart

---

## 🎨 Current Design Specs

### **Layout**
- **Container Background**: Light gray (#E8EDF5)
- **Container**: Rounded corners (16px), padding all around
- **Grid Layout**: 3 columns per row
- **Gap between cards**: 12px
- **Cards per row**: 3 cards (33.333% width each)

### **Card Design (Both Stats & Benefits)**
- **Background**: White (#fff)
- **Border Radius**: 12px
- **Padding**: 16px
- **Shadow**: Subtle (opacity 0.08, elevation 2)
- **Min Height**: 120px
- **Alignment**: Centered content

### **Icon Container (All Cards)**
- **Size**: 40x40px
- **Shape**: Circle (border-radius 20px)
- **Background**: Light peach (#FFF7F0)
- **Shadow**: Very subtle
- **Margin Bottom**: 12px

### **Typography**

#### **Section Title**:
- **Size**: XL
- **Weight**: Bold
- **Color**: Gray 900
- **Alignment**: Center

#### **Subtitle**:
- **Size**: SM
- **Color**: Gray 600
- **Alignment**: Center
- **Line Height**: 20px

#### **Card Title (Benefits)**:
- **Size**: LG
- **Weight**: 700
- **Color**: Brand primary (maroon)
- **Alignment**: Center

#### **Card Description (Benefits)**:
- **Size**: SM
- **Color**: Text secondary
- **Alignment**: Center
- **Line Height**: 16px

#### **Stat Value**:
- **Size**: LG
- **Weight**: 700
- **Color**: Brand primary
- **Alignment**: Center

#### **Stat Label**:
- **Size**: XS
- **Weight**: 700
- **Color**: Brand primary
- **Alignment**: Center

#### **Stat Trend**:
- **Size**: XS
- **Color**: Text secondary
- **Alignment**: Center

---

## 📊 Data Flow

### **Data Source**:
The component accepts props from API (`/api/home/benefits`):

```typescript
{
  benefits: Benefit[],
  stats: StatItem[],
  title: string,
  subtitle: string,
  ctaText: string
}
```

### **Fallback**:
If no data provided, uses hardcoded defaults (shown above).

---

## 🎯 Current Structure

```
┌─────────────────────────────────────────────────────┐
│  ✨ Why Choose Ama Ko Shop?                        │
│  From our kitchen to your heart — here's why...    │
│                                                     │
│  ┌───────┐  ┌───────┐  ┌───────┐                 │
│  │  🚚   │  │  ❤️   │  │  🏆   │  ← Stats Row   │
│  │ 179+  │  │ 21+   │  │  1+   │                 │
│  │Orders │  │Happy  │  │Years  │                 │
│  └───────┘  └───────┘  └───────┘                 │
│                                                     │
│  ┌───────┐  ┌───────┐  ┌───────┐                 │
│  │  🥬   │  │ 👩‍🍳   │  │  🚚   │  ← Benefits Row │
│  │Fresh  │  │Auth   │  │Fast   │                 │
│  │Ingred.│  │Recipe │  │Deliv. │                 │
│  └───────┘  └───────┘  └───────┘                 │
│                                                     │
│      [Try Our Momos Today]  ← CTA Button          │
└─────────────────────────────────────────────────────┘
```

---

## 📱 Component Props

```typescript
interface BenefitsGridProps {
  benefits?: Benefit[];
  stats?: StatItem[];
  title?: string;
  subtitle?: string;
  onCtaPress?: () => void;
  ctaText?: string;
}

interface Benefit {
  id: string;
  emoji: string;
  title: string;
  description: string;
}

interface StatItem {
  id: string;
  value: string;
  label: string;
  icon: string;
  trend: string;
  trendIcon: string;
}
```

---

## 🔧 Customizable Elements

### **Can be changed via props**:
- ✅ Title text
- ✅ Subtitle text
- ✅ Stats data (values, labels, trends)
- ✅ Benefits data (emojis, titles, descriptions)
- ✅ CTA button text
- ✅ CTA button action

### **Currently hardcoded in styles**:
- ❌ Colors (background, text, etc.)
- ❌ Spacing and padding
- ❌ Card dimensions
- ❌ Grid layout (3 columns)
- ❌ Typography sizes

---

## 🎨 Visual Summary

**Current Look**:
- Clean, card-based design
- 3x3 grid (3 stat cards + 3 benefit cards)
- White cards on light gray background
- Subtle shadows
- Circular icon containers with peach background
- Centered content alignment
- Optional red CTA button

**Color Palette**:
- Container: Light gray (#E8EDF5)
- Cards: White (#fff)
- Icon background: Light peach (#FFF7F0)
- Primary text: Brand maroon
- Secondary text: Gray
- CTA button: Red (#EF4444)

---

**Ready for UI/UX improvements! What would you like to change?** 🎨✨

