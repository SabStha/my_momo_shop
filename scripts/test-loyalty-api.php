<?php

/**
 * Test script for loyalty API endpoint
 * Run this script directly: php scripts/test-loyalty-api.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\LoyaltyController;

echo "Testing Loyalty API endpoint...\n";

try {
    // Create a mock request
    $request = Request::create('/api/loyalty', 'GET');
    
    // Create controller instance
    $controller = new LoyaltyController();
    
    // Call the summary method
    $response = $controller->summary($request);
    
    // Get the response content
    $content = $response->getContent();
    $data = json_decode($content, true);
    
    echo "✅ API endpoint working!\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response data:\n";
    echo "  - Credits: " . $data['credits'] . "\n";
    echo "  - Tier: " . $data['tier'] . "\n";
    echo "  - Badges count: " . count($data['badges']) . "\n";
    
    echo "\nBadges:\n";
    foreach ($data['badges'] as $badge) {
        echo "  - {$badge['name']} ({$badge['tier']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ API test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nLoyalty API test completed successfully!\n";
