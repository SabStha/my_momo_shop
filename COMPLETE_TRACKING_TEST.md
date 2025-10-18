# 🔍 Complete Tracking Test Setup

## **🎯 The Issue:**
The map might be showing shop location instead of driver location because:
1. **No tracking data exists** in database
2. **Tracking data exists but API isn't returning it**
3. **Frontend isn't parsing tracking data correctly**

---

## **🚀 Complete Test Setup:**

### **Step 1: Create Test Order with Tracking Data**

Run this on your server:
```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan tinker
```

```php
// Create test order with Fukuoka delivery
$order = new \App\Models\Order();
$order->branch_id = 1;
$order->user_id = 1; // Your user ID
$order->customer_name = 'Test Customer';
$order->customer_email = 'test@example.com';
$order->customer_phone = '+81-90-1234-5678';
$order->delivery_address = json_encode([
    'city' => 'Fukuoka',
    'area_locality' => 'Hakata Ward',
    'building_name' => 'Fukuoka Station',
    'latitude' => '33.5904',
    'longitude' => '130.4017'
]);
$order->payment_method = 'cash';
$order->subtotal = 1500;
$order->tax_amount = 150;
$order->total = 1650;
$order->status = 'out_for_delivery';
$order->assigned_driver_id = 1; // Assign driver
$order->order_number = 'ORD-' . strtoupper(uniqid());
$order->save();

echo "Created order: {$order->order_number} (ID: {$order->id})\n";

// Create tracking data
\App\Models\DeliveryTracking::create([
    'order_id' => $order->id,
    'driver_id' => 1,
    'status' => 'location_update',
    'latitude' => '33.5904', // Driver at Fukuoka Station
    'longitude' => '130.4017',
    'notes' => 'Driver at Fukuoka Station'
]);

echo "Created tracking data for order {$order->id}\n";

// Verify data
$tracking = \App\Models\DeliveryTracking::where('order_id', $order->id)->first();
echo "Tracking data: Lat={$tracking->latitude}, Lng={$tracking->longitude}\n";

exit
```

### **Step 2: Test API Endpoint Directly**

Test the tracking API endpoint:
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     "https://amakomomo.com/api/orders/1/tracking"
```

Should return:
```json
{
  "success": true,
  "tracking": [
    {
      "id": 1,
      "order_id": 1,
      "driver_id": 1,
      "status": "location_update",
      "latitude": "33.5904",
      "longitude": "130.4017",
      "notes": "Driver at Fukuoka Station",
      "driver": {
        "id": 1,
        "name": "Driver Name"
      }
    }
  ]
}
```

### **Step 3: Check Mobile App Logs**

1. **Refresh mobile app**
2. **Go to Track Order page**
3. **Check console logs** for:
   - `🗺️ Tracking data:`
   - `🚴 Driver location:`
   - `🏠 Delivery location:`

---

## **🔍 Debug Steps:**

### **If No Tracking Data:**
```php
// Check if tracking data exists
$tracking = \App\Models\DeliveryTracking::all();
echo "Total tracking entries: " . $tracking->count() . "\n";

foreach ($tracking as $t) {
    echo "Order {$t->order_id}: Lat={$t->latitude}, Lng={$t->longitude}\n";
}
```

### **If API Returns Empty:**
```php
// Test the getTracking method directly
$controller = new \App\Http\Controllers\DeliveryController();
$response = $controller->getTracking(1);
echo "API Response: " . $response->getContent() . "\n";
```

### **If Frontend Shows Wrong Data:**
Check the console logs in mobile app to see what coordinates are being parsed.

---

## **🎯 Expected Behavior:**

### **Correct Setup:**
- 🚴 **Driver location**: Fukuoka Station (33.5904, 130.4017)
- 🏠 **Customer location**: Fukuoka Station area (33.5904, 130.4017)
- 🗺️ **Map view**: Local Fukuoka map with both markers

### **If Still Wrong:**
The issue might be:
1. **API not returning tracking data**
2. **Frontend not parsing coordinates correctly**
3. **Map component using wrong coordinates**

---

## **✅ Quick Fix - Force Correct Coordinates:**

If tracking data is missing, temporarily hardcode for testing:

```php
// Update the order-tracking component to use test coordinates
// This will show Fukuoka local map for testing
```

---

**Run the setup above and check the console logs to see exactly what data is being received!** 🔍
