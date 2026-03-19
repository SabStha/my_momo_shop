# 🔍 Check Your Tracking Data

## **Why Map Shows Korea Instead of Nepal:**

The map shows **real data from your database**, not the test coordinates. If you see Korea, it means:

1. ✅ **Map is working perfectly** (Google Maps API key is correct)
2. ❌ **Real driver coordinates are wrong** (stored in database)

---

## **🔍 Check Your Database:**

Run this on your server to see what coordinates are stored:

```bash
cd /var/www/amako-momo\(p\)/my_momo_shop
php artisan tinker
```

Then:
```php
// Check if there's any tracking data
$tracking = \App\Models\DeliveryTracking::latest()->first();
if ($tracking) {
    echo "Latest tracking data:\n";
    echo "Latitude: " . $tracking->latitude . "\n";
    echo "Longitude: " . $tracking->longitude . "\n";
    echo "Status: " . $tracking->status . "\n";
    echo "Created: " . $tracking->created_at . "\n";
} else {
    echo "No tracking data found\n";
}

// Check all tracking entries
$allTracking = \App\Models\DeliveryTracking::all();
echo "Total tracking entries: " . $allTracking->count() . "\n";

foreach ($allTracking as $t) {
    echo "ID: {$t->id}, Lat: {$t->latitude}, Lng: {$t->longitude}, Status: {$t->status}\n";
}

exit
```

---

## **🗺️ Expected vs Actual Coordinates:**

### **Nepal/Kathmandu (Correct):**
- Latitude: `27.7172`
- Longitude: `85.324`

### **If you see Korea:**
- Latitude: `~37.5665`
- Longitude: `~126.9780`

### **If you see other countries:**
- China: `~39.9042, 116.4074`
- India: `~28.6139, 77.2090`
- Thailand: `~13.7563, 100.5018`

---

## **🛠️ Fix Wrong Coordinates:**

### **Option 1: Update Existing Data**
```php
// Update all tracking data to Kathmandu
\App\Models\DeliveryTracking::whereNotNull('latitude')->update([
    'latitude' => 27.7172,
    'longitude' => 85.324
]);
echo "Updated " . \App\Models\DeliveryTracking::count() . " tracking entries\n";
```

### **Option 2: Delete Wrong Data**
```php
// Delete all tracking data
\App\Models\DeliveryTracking::truncate();
echo "Deleted all tracking data\n";
```

### **Option 3: Add Correct Test Data**
```php
// Create test tracking data for current order
$order = \App\Models\Order::where('status', 'out_for_delivery')->first();
if ($order) {
    \App\Models\DeliveryTracking::create([
        'order_id' => $order->id,
        'driver_id' => 1, // or any driver ID
        'status' => 'location_update',
        'latitude' => 27.7172,
        'longitude' => 85.324,
        'notes' => 'Test location in Kathmandu'
    ]);
    echo "Created test tracking data for order {$order->id}\n";
}
```

---

## **✅ After Fixing:**

1. **Refresh your mobile app**
2. **Go to Track Order page**
3. **Map should now show Kathmandu** instead of Korea

---

## **🎯 For Production:**

Make sure your **driver app** sends correct coordinates:
- Use GPS location services
- Validate coordinates are in Nepal
- Test with known Kathmandu addresses

**Example driver location update:**
```json
{
  "latitude": 27.7172,
  "longitude": 85.324,
  "order_id": 123
}
```
