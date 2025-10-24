<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;

echo "Testing POS order creation...\n\n";

// Test order creation
echo "Testing /api/pos/pos-orders endpoint...\n";
try {
    // Simulate authenticated user
    $user = \App\Models\User::find(31);
    if ($user) {
        auth()->login($user);
        echo "   ✓ User authenticated: {$user->name}\n";
    }
    
    // Create test order data
    $orderData = [
        'items' => [
            [
                'product_id' => 4,
                'quantity' => 2,
                'price' => 6.0
            ]
        ],
        'order_type' => 'dine_in',
        'table_id' => 11,
        'subtotal' => 12.0,
        'tax' => 1.56,
        'total' => 13.56
    ];
    
    $request = Request::create('/api/pos/pos-orders', 'POST', $orderData);
    $request->headers->set('X-Branch-ID', '2');
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    $request->setLaravelSession(app('session.store'));
    
    $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
    echo "   Response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 201) {
        echo "   ✓ Order creation working\n";
        $responseData = json_decode($response->getContent(), true);
        if (isset($responseData['order'])) {
            echo "   ✓ Order created with ID: " . $responseData['order']['id'] . "\n";
        }
    } else {
        echo "   ✗ Order creation failed\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";








