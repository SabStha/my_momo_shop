# 🔍 Session Check Debug Plan

## 📊 **What We Know:**

### **Evidence from Your Tests:**

1. **Database Check:**
   ```
   💰 Currently open sessions: 0
   ❌ No open sessions
   ```

2. **Mobile App Logs:**
   ```
   ✅ Order created successfully on backend
   ✅ Order ID: 3
   ✅ Order number: ORD-20251010-68E94D952ED79
   ```

3. **Database Orders:**
   ```
   Order #ORD-20251010-68E94D952ED79
     ID: 3
     Type: dine_in
     Branch: (null)
     Payment: (null)
     Status: pending
   ```

4. **Wallet Orders:**
   ```
   💰 Recent Amako Credits/Wallet orders: 0
   ```

---

## 🐛 **The Mystery:**

### **Contradictions:**
- ❌ NO open cash drawer sessions
- ✅ BUT orders are being created successfully!
- ❌ Session check should REJECT these orders
- ✅ BUT backend returns success (HTTP 201)

### **Strange Data:**
- `order_type`: `'dine_in'` (should be `'online'`)
- `branch_id`: `null` (should be `1`)
- `payment_method`: `null` (should be `'amako_credits'`)

---

## 💡 **Possible Causes:**

### **Theory 1: Wrong Controller Being Used**
- Mobile app might be hitting POS route instead of customer route
- POS route has different session check behavior
- Would explain null fields

### **Theory 2: Session Check Code Not Executing**
- Code exists but somehow bypassed
- Maybe try-catch is swallowing the error
- Would need to check logs

### **Theory 3: Middleware Issue**
- Route might not be in correct middleware group
- Session check might not be enforced
- Authentication might be issue

### **Theory 4: There IS an Open Session**
- Maybe session query is wrong
- Session might exist but not detected
- Timezone issue with timestamps

---

## 🔧 **Actions Taken:**

### **1. Added Detailed Logging** (OrderController.php)
```php
Log::info('OrderController@store request received', [...]);
Log::info('Cash drawer session check', [
    'branch_id' => $branchId,
    'session_found' => $cashDrawerSession ? 'yes' : 'no'
]);

if (!$cashDrawerSession) {
    Log::warning('Order rejected - business closed');
    return 423 error;
}

Log::info('Session check passed - proceeding');
```

### **2. Enhanced Mobile App Logs** (payment.tsx)
```typescript
console.log('✅ Backend order created successfully!');
console.log('✅ Order number:', result.order?.order_number);
console.log('✅ Order ID:', result.order?.id);
```

---

## 🧪 **Next Steps to Debug:**

### **Test 1: Place Order and Check Logs**

**Do this:**
1. Place another order from mobile app
2. Check mobile app console logs
3. Check Laravel logs: `Get-Content storage\logs\laravel.log -Tail 50`

**Look for:**
```
[timestamp] local.INFO: OrderController@store request received
[timestamp] local.INFO: Cash drawer session check
```

**Should see:**
- If hitting OrderController: "OrderController@store request received"
- If hitting PosOrderController: No OrderController logs
- Session status: "session_found": "yes" or "no"

### **Test 2: Check Database After Order**

```bash
php check_recent_orders.php
```

**Look for:**
- Order type: Should be 'online' (not 'dine_in')
- Branch ID: Should be 1 (not null)
- Payment: Should be 'amako_credits' (not null)

### **Test 3: Check API Route**

Mobile app calls: `POST /api/orders`

Which matches first:
- `/api/pos/orders` ← POS route (shouldn't match, different prefix)
- `/api/orders` ← Customer route (should match)

---

## 📋 **What to Check:**

1. **Laravel Logs:**
   ```bash
   Get-Content storage\logs\laravel.log -Tail 100 | Select-String "OrderController|session check|rejected"
   ```

2. **Recent Order Fields:**
   ```bash
   php artisan tinker --execute="\$o = App\Models\Order::latest()->first(); echo 'Type: ' . \$o->order_type . PHP_EOL; echo 'Branch: ' . \$o->branch_id . PHP_EOL; echo 'Payment: ' . \$o->payment_method . PHP_EOL;"
   ```

3. **Network Request:**
   - Mobile app logs should show: `🚀 API Request: POST /orders`
   - Should be `/api/orders` NOT `/api/pos/orders`

---

## 🎯 **Expected Behavior:**

### **When Session is CLOSED:**
```
Mobile App → POST /api/orders
     ↓
OrderController@store
     ↓
Check cash drawer session
     ↓
Session NOT found ❌
     ↓
Log: "Order rejected - business closed"
     ↓
Return 423 error
     ↓
Mobile App shows: "Business Closed" alert
     ↓
Order NOT created
```

### **When Session is OPEN:**
```
Mobile App → POST /api/orders
     ↓
OrderController@store
     ↓
Check cash drawer session
     ↓
Session found ✅
     ↓
Log: "Session check passed"
     ↓
Create order
     ↓
Deduct wallet (if amako_credits)
     ↓
Return success
     ↓
Order appears in payment manager
```

---

## 🚀 **Next Test:**

Please place ONE more order and share:

1. **Complete mobile app console logs** (all of them)
2. **Laravel logs output:**
   ```bash
   Get-Content storage\logs\laravel.log -Tail 100 | Select-String "Order"
   ```
3. **Check the new order:**
   ```bash
   php check_recent_orders.php
   ```

This will definitively tell us what's happening!

