# Ama's Finds - Earned Items Setup

## 🎯 Feature Added
**Special earned items** in Ama's Finds that **cannot be purchased** with money - they can only be **earned by buying specific combo sets**.

**User Request**: "add seeder in menu sedder for amas find ltes only add two item which needs to be earned cannot be bought and what earns means is it will only be geteen whne buying certain sets like couple and kids"

---

## ✅ Items Added

### **1. Amako Limited Edition Tote Bag** 👜
- **Category**: Accessories
- **Earned By**: Purchasing **Couple Combo Set**
- **Price**: Rs. 0.00 (not for sale!)
- **Status**: Exclusive
- **Badge**: "🏆 Earned by Couple Set"
- **Description**: Exclusive canvas tote bag with Amako Momo branding. Perfect for carrying your favorite momos!

### **2. Amako Kids Plush Toy** 🧸
- **Category**: Toys
- **Earned By**: Purchasing **Kids Combo Set**
- **Price**: Rs. 0.00 (not for sale!)
- **Status**: Exclusive
- **Badge**: "🎁 Earned by Kids Set"
- **Description**: Adorable momo-shaped plush toy for kids. Soft, huggable, and exclusively available as a reward!

---

## 🔧 Technical Implementation

### **Database Fields**:
```php
[
    'name' => 'Amako Limited Edition Tote Bag',
    'price' => 0.00,              // Free - not purchasable
    'purchasable' => false,        // ✅ Cannot be bought
    'status' => 'exclusive',       // ✅ Exclusive item
    'badge' => '🏆 Earned by Couple Set',
    'badge_color' => '#EF4444',   // Red
    'category' => 'accessories',
    'model' => 'limited-edition',
]
```

### **Key Fields**:
- **`purchasable: false`** → Item cannot be added to cart for purchase
- **`price: 0.00`** → No price shown (it's earned, not bought)
- **`status: 'exclusive'`** → Indicates special status
- **`badge`** → Shows how to earn it
- **`badge_color`** → Visual indicator color

---

## 📊 How It Works

### **Earning Flow**:

```
User buys Couple Combo Set
  ↓
Order is delivered
  ↓
System checks: Did they buy Couple Set?
  ↓
YES → Unlock "Amako Limited Edition Tote Bag"
  ↓
Item appears in user's Ama's Finds
  ↓
User can "claim" it (no payment needed)
```

---

## 🎨 Display in App

### **Ama's Finds Section**:

```
┌──────────────────────────────────────┐
│        🔍 AMA'S FINDS                │
│                                      │
│  Regular Items (Purchasable):       │
│  ┌────────┐  ┌────────┐            │
│  │ T-Shirt│  │  Mug   │  💰 Can buy│
│  │ Rs.499 │  │ Rs.199 │            │
│  └────────┘  └────────┘            │
│                                      │
│  Exclusive Earned Items:            │
│  ┌────────────────────────┐         │
│  │ 👜 Limited Tote Bag   │         │
│  │ 🏆 Earned by Couple Set│  ← Badge
│  │ ❌ Cannot Purchase     │         │
│  │ ✨ Unlock by buying    │         │
│  │    Couple Combo!       │         │
│  └────────────────────────┘         │
│                                      │
│  ┌────────────────────────┐         │
│  │ 🧸 Kids Plush Toy     │         │
│  │ 🎁 Earned by Kids Set  │  ← Badge
│  │ ❌ Cannot Purchase     │         │
│  │ ✨ Unlock by buying    │         │
│  │    Kids Combo!         │         │
│  └────────────────────────┘         │
└──────────────────────────────────────┘
```

---

## 🔐 Unlock Logic (To Implement)

### **Backend Logic Needed**:

```php
// In OrderController or after order delivery

public function checkAndUnlockEarnedItems($order) {
    $orderItems = $order->items;
    
    // Check if order contains Couple Combo
    $hasCoupleCombo = $orderItems->contains(function($item) {
        return stripos($item->name, 'Couple Combo') !== false;
    });
    
    if ($hasCoupleCombo) {
        // Unlock tote bag for this user
        DB::table('user_earned_items')->insert([
            'user_id' => $order->user_id,
            'merchandise_id' => 1, // Tote Bag
            'earned_at' => now(),
            'earned_by_order_id' => $order->id,
        ]);
    }
    
    // Check if order contains Kids Combo
    $hasKidsCombo = $orderItems->contains(function($item) {
        return stripos($item->name, 'Kids Combo') !== false;
    });
    
    if ($hasKidsCombo) {
        // Unlock plush toy for this user
        DB::table('user_earned_items')->insert([
            'user_id' => $order->user_id,
            'merchandise_id' => 2, // Plush Toy
            'earned_at' => now(),
            'earned_by_order_id' => $order->id,
        ]);
    }
}
```

---

### **Frontend Logic Needed**:

```typescript
// In Ama's Finds page

// Filter items based on user's earned items
const merchandiseItems = useMerchandise(); // All items
const userEarnedItems = useUserEarnedItems(); // User's unlocked items

const displayItems = merchandiseItems.map(item => {
    const isEarned = userEarnedItems.some(e => e.merchandise_id === item.id);
    
    return {
        ...item,
        isUnlocked: item.purchasable || isEarned,
        canClaim: !item.purchasable && isEarned,
        isLocked: !item.purchasable && !isEarned,
    };
});

// Display logic
{displayItems.map(item => (
    <View>
        {item.isLocked && (
            <Text>🔒 Locked - {item.badge}</Text>
        )}
        {item.canClaim && (
            <Button>Claim Your Reward!</Button>
        )}
        {item.purchasable && (
            <Button>Buy Now - Rs. {item.price}</Button>
        )}
    </View>
))}
```

---

## 📋 Database Schema Needed

You'll need a `user_earned_items` table to track which users have unlocked which items:

```php
Schema::create('user_earned_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('merchandise_id')->constrained()->onDelete('cascade');
    $table->timestamp('earned_at');
    $table->foreignId('earned_by_order_id')->nullable()->constrained('orders');
    $table->boolean('claimed')->default(false);
    $table->timestamp('claimed_at')->nullable();
    $table->timestamps();
    
    // Ensure user can only earn each item once
    $table->unique(['user_id', 'merchandise_id']);
});
```

---

## 🎁 Reward Triggers

### **Current Setup**:

| Combo Set Purchased | Reward Unlocked |
|---------------------|-----------------|
| Couple Combo        | 👜 Limited Edition Tote Bag |
| Kids Combo          | 🧸 Kids Plush Toy |

### **How to Add More**:

```php
// In AmasFindsSeed.php, add more items:

[
    'name' => 'Amako VIP Card',
    'description' => '💎 VIP membership card with exclusive benefits. Earned by purchasing Party Pack.',
    'price' => 0.00,
    'purchasable' => false,
    'status' => 'exclusive',
    'badge' => '💎 Earned by Party Pack',
    'badge_color' => '#8B5CF6', // Purple
    'category' => 'accessories',
],
```

---

## 🧪 Testing

### **Test in Database**:
```sql
SELECT * FROM merchandises WHERE purchasable = 0;
```

**Expected result**:
```
| id | name                        | purchasable | badge                   |
|----|-----------------------------|-------------|-------------------------|
| 1  | Amako Limited Tote Bag      | 0           | 🏆 Earned by Couple Set |
| 2  | Amako Kids Plush Toy        | 0           | 🎁 Earned by Kids Set   |
```

---

### **Test in App**:

1. **Go to Ama's Finds tab**
2. **See the 2 earned items** with badges
3. **Try to add to cart** → Should show "🔒 Locked" or "Earn by buying..."
4. **Buy Couple Combo**
5. **After delivery** → Tote Bag unlocks
6. **Return to Ama's Finds** → Can now claim the bag!

---

## 🎨 Visual Design Suggestions

### **Locked Item Card**:
```
┌────────────────────────────┐
│ 🔒 LOCKED                  │
│                            │
│ 👜 Limited Edition Tote    │
│                            │
│ 🏆 Earned by Couple Set    │
│                            │
│ [Buy Couple Combo to Earn] │
└────────────────────────────┘
```

### **Unlocked Item Card**:
```
┌────────────────────────────┐
│ ✅ EARNED!                 │
│                            │
│ 👜 Limited Edition Tote    │
│                            │
│ 🏆 Earned by Couple Set    │
│                            │
│ [✨ Claim Your Reward!]    │
└────────────────────────────┘
```

### **Claimed Item Card**:
```
┌────────────────────────────┐
│ ✓ CLAIMED                  │
│                            │
│ 👜 Limited Edition Tote    │
│                            │
│ Claimed on: Oct 18, 2025   │
│                            │
│ [View in My Items]         │
└────────────────────────────┘
```

---

## 📱 Next Steps to Complete Feature

### **1. Create Migration for user_earned_items** (Recommended):
```bash
php artisan make:migration create_user_earned_items_table
```

### **2. Add Unlock Logic**:
- In `DeliveryController` after marking order as delivered
- Check what combos were purchased
- Unlock corresponding earned items

### **3. Update Finds API**:
- Include earned items in response
- Mark which ones user has unlocked
- Show claim status

### **4. Update Finds UI**:
- Show locked/unlocked states
- Add claim button for earned items
- Visual indicators (badges, locks)

---

## ✅ Summary

**Added**: 2 exclusive earned items to Ama's Finds!

**Items**:
1. 👜 **Amako Limited Edition Tote Bag** (Couple Set reward)
2. 🧸 **Amako Kids Plush Toy** (Kids Set reward)

**Properties**:
- ✅ `purchasable: false` - Cannot be bought
- ✅ `price: 0.00` - Free when earned
- ✅ `status: exclusive` - Special items
- ✅ Badge shows how to earn them

**Database**: Items seeded and verified!

**Next**: Implement unlock logic when combos are purchased ✨

