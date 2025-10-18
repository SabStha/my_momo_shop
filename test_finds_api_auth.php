<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Merchandise;

echo "=== Testing Finds API with Auth ===\n\n";

// Get a user for testing
$user = User::first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "👤 Testing as user: {$user->name} (ID: {$user->id})\n\n";

// Create a personal access token
$token = $user->createToken('test-token')->plainTextToken;
echo "🔑 Token created: " . substr($token, 0, 20) . "...\n\n";

// Test the API with authentication
$url = 'http://localhost:8000/api/finds/data';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 HTTP Status: $httpCode\n";

if ($httpCode === 200) {
    echo "✅ API request successful\n\n";
    
    $data = json_decode($response, true);
    
    echo "📦 Merchandise Data:\n";
    if (isset($data['merchandise'])) {
        foreach ($data['merchandise'] as $category => $items) {
            echo "   • $category: " . count($items) . " items\n";
            foreach ($items as $item) {
                $purchasable = $item['purchasable'] ? '💰' : '🏆';
                $badge = isset($item['badge']) ? " [{$item['badge']}]" : '';
                echo "      $purchasable {$item['name']}$badge\n";
            }
        }
    } else {
        echo "   ⚠️ No merchandise data in response\n";
    }
    
    echo "\n🏆 Earned Items Specifically:\n";
    $foundEarned = false;
    if (isset($data['merchandise'])) {
        foreach ($data['merchandise'] as $category => $items) {
            foreach ($items as $item) {
                if (!$item['purchasable']) {
                    $foundEarned = true;
                    echo "   ✅ {$item['name']} (Category: $category)\n";
                    echo "      └─ {$item['badge']}\n";
                }
            }
        }
    }
    
    if (!$foundEarned) {
        echo "   ❌ No earned items in API response\n";
    }
    
} else {
    echo "❌ API request failed\n";
    echo "Response: $response\n";
}

// Clean up token
DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->where('name', 'test-token')->delete();

echo "\n=== Test Complete ===\n";

