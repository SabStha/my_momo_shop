<?php
// Debug script to test delivery confirmation
// Place this in your project root and access via browser

echo "<h2>🔍 Delivery Confirmation Debug</h2>";

// Check if we can access the delivery endpoint
$baseUrl = 'https://amakomomo.com';
$orderId = 6; // Test with order 6

echo "<h3>Testing Delivery Confirmation for Order #{$orderId}</h3>";

// Test 1: Check if route exists
echo "<h4>1. Route Check</h4>";
echo "Route: POST /delivery/orders/{$orderId}/delivered<br>";
echo "Expected: 200 OK or 403 Unauthorized<br><br>";

// Test 2: Check authentication
echo "<h4>2. Authentication Check</h4>";
echo "Make sure you're logged in as a delivery driver<br>";
echo "Check if user has 'driver' role<br><br>";

// Test 3: Check order assignment
echo "<h4>3. Order Assignment Check</h4>";
echo "Order must have assigned_driver_id matching current user<br>";
echo "Order status must be 'out_for_delivery'<br><br>";

// Test 4: Check form data
echo "<h4>4. Form Data Requirements</h4>";
echo "Required: delivery_photo (image file)<br>";
echo "Optional: notes, latitude, longitude<br><br>";

// Test 5: Check file upload
echo "<h4>5. File Upload Check</h4>";
echo "Photo must be valid image file<br>";
echo "Max size: 5MB<br>";
echo "Accepted formats: jpg, png, gif, etc.<br><br>";

echo "<h4>6. Common Issues</h4>";
echo "❌ Not logged in as driver<br>";
echo "❌ Order not assigned to current driver<br>";
echo "❌ No photo selected<br>";
echo "❌ Photo file too large<br>";
echo "❌ Invalid image format<br>";
echo "❌ CSRF token mismatch<br><br>";

echo "<h4>7. Debug Steps</h4>";
echo "1. Open browser developer tools (F12)<br>";
echo "2. Go to Network tab<br>";
echo "3. Try to confirm delivery<br>";
echo "4. Check the POST request to /delivery/orders/{$orderId}/delivered<br>";
echo "5. Look for error messages in response<br><br>";

echo "<h4>8. Manual Test</h4>";
echo "Try this curl command (replace with your session cookie):<br>";
echo "<code>curl -X POST {$baseUrl}/delivery/orders/{$orderId}/delivered \\<br>";
echo "  -H 'Content-Type: multipart/form-data' \\<br>";
echo "  -H 'X-CSRF-TOKEN: YOUR_CSRF_TOKEN' \\<br>";
echo "  -F 'delivery_photo=@/path/to/test-image.jpg' \\<br>";
echo "  -F 'notes=Test delivery confirmation'</code><br><br>";

echo "<p><strong>If you're still having issues, check the Laravel logs:</strong></p>";
echo "<code>tail -f storage/logs/laravel.log</code>";
?>
