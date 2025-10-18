# 🇯🇵 Japan/Fukuoka Delivery Test Setup

## **🎯 Your Scenario:**
- 🏪 **Shop Location**: Nepal (Kathmandu)
- 🚴 **Driver Location**: Japan (Fukuoka) - You testing as driver
- 🏠 **Customer Location**: Japan (Fukuoka area) - Local delivery
- 📱 **Goal**: Show driver moving toward customer in same city

---

## **🚀 Setup Test Data:**

### **Step 1: Create Fukuoka Customer Order**

Run this on your server:
```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan tinker
```

```php
// Create a new order with Fukuoka delivery address
$order = new \App\Models\Order();
$order->branch_id = 1;
$order->customer_name = 'Test Customer';
$order->customer_email = 'customer@test.com';
$order->customer_phone = '+81-90-1234-5678';
$order->delivery_address = json_encode([
    'city' => 'Fukuoka',
    'area_locality' => 'Hakata Ward',
    'ward_number' => '1',
    'building_name' => 'Fukuoka Station Building',
    'detailed_directions' => 'Near Fukuoka Station, 3rd floor',
    'latitude' => '33.5904',  // Fukuoka Station
    'longitude' => '130.4017'
]);
$order->payment_method = 'cash';
$order->subtotal = 1500;
$order->tax_amount = 150;
$order->total = 1650;
$order->total_amount = 1650;
$order->grand_total = 1650;
$order->order_type = 'online';
$order->status = 'out_for_delivery';
$order->payment_status = 'pending';
$order->order_number = 'ORD-' . strtoupper(uniqid());
$order->save();

echo "Created Fukuoka delivery order: {$order->order_number}\n";
exit
```

### **Step 2: Create Driver Tracking Data (You in Japan)**

```php
// Create tracking data showing you (driver) in Fukuoka
$order = \App\Models\Order::where('status', 'out_for_delivery')->latest()->first();
if ($order) {
    // Clear any old tracking data
    \App\Models\DeliveryTracking::where('order_id', $order->id)->delete();
    
    // Create initial driver location (you in Fukuoka)
    \App\Models\DeliveryTracking::create([
        'order_id' => $order->id,
        'driver_id' => 1,
        'status' => 'location_update',
        'latitude' => 33.5904, // Fukuoka Station (your starting point)
        'longitude' => 130.4017,
        'notes' => 'Driver started from Fukuoka Station'
    ]);
    
    echo "Created driver tracking data for order {$order->order_number}\n";
    echo "Driver location: Fukuoka Station (33.5904, 130.4017)\n";
    echo "Customer location: Fukuoka Station area\n";
} else {
    echo "No out_for_delivery orders found\n";
}

exit
```

---

## **📱 Test in Mobile App:**

1. **Refresh your mobile app**
2. **Go to Orders tab**
3. **Find the new order** (latest one)
4. **Tap "Track Delivery"**
5. **You should see:**
   - 🗺️ **Local Fukuoka map** (zoomed to city level)
   - 🚴 **Pink driver marker** at Fukuoka Station
   - 🏠 **Red customer marker** nearby in Fukuoka
   - **Both markers visible** in same city view

---

## **🎯 Expected Result:**

The map should show a **local Fukuoka street map** with:
- ✅ **Driver marker** at Fukuoka Station
- ✅ **Customer marker** nearby (same city)
- ✅ **Local zoom level** (not world map)
- ✅ **Clear delivery route** visible

---

## **🚴 Simulate Driver Movement:**

To test movement, run this to simulate you moving toward customer:

```php
// Simulate driver moving through Fukuoka toward customer
$order = \App\Models\Order::where('status', 'out_for_delivery')->latest()->first();
if ($order) {
    $locations = [
        ['lat' => 33.5904, 'lng' => 130.4017, 'note' => 'Starting from Fukuoka Station'],
        ['lat' => 33.5880, 'lng' => 130.3990, 'note' => 'Moving through Hakata Ward'],
        ['lat' => 33.5850, 'lng' => 130.3960, 'note' => 'Getting closer to customer'],
        ['lat' => 33.5820, 'lng' => 130.3930, 'note' => 'Almost at delivery location'],
        ['lat' => 33.5800, 'lng' => 130.3910, 'note' => 'At customer location'],
    ];
    
    foreach ($locations as $i => $loc) {
        \App\Models\DeliveryTracking::create([
            'order_id' => $order->id,
            'driver_id' => 1,
            'status' => 'location_update',
            'latitude' => $loc['lat'],
            'longitude' => $loc['lng'],
            'notes' => $loc['note']
        ]);
        
        echo "Created location {$i+1}: {$loc['note']}\n";
    }
    
    echo "Driver movement simulation complete!\n";
} else {
    echo "No out_for_delivery orders found\n";
}

exit
```

---

## **🌍 For Shop in Nepal + Delivery in Japan:**

This setup handles your scenario:
- 🏪 **Shop**: Nepal (backend processing)
- 🚴 **Driver**: Japan (you testing)
- 🏠 **Customer**: Japan (local delivery)
- 📱 **App**: Shows local Japan map for tracking

The system correctly shows:
- **Order processing** happens in Nepal
- **Delivery tracking** shows local Japan map
- **Customer sees** driver moving in their city

---

## **✅ What This Tests:**

1. **Local delivery tracking** (same city)
2. **Driver movement simulation**
3. **Real-time location updates**
4. **Proper map zoom levels**
5. **Cross-country order processing**

---

**Run the setup above and you should see a proper local Fukuoka delivery map!** 🗾
