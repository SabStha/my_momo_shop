<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

echo "Testing POS API endpoints...\n\n";

// Test 1: Check if tables endpoint works
echo "1. Testing /api/pos/tables endpoint...\n";
try {
    $request = Request::create('/api/pos/tables', 'GET', ['branch' => 2]);
    $request->headers->set('X-Branch-ID', '2');
    $request->setLaravelSession(app('session.store'));
    
    // Simulate authenticated user
    $user = \App\Models\User::find(31);
    if ($user) {
        auth()->login($user);
        echo "   ✓ User authenticated: {$user->name}\n";
    }
    
    $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
    echo "   Response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "   ✓ Tables endpoint working\n";
    } else {
        echo "   ✗ Tables endpoint failed\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check if orders endpoint works
echo "2. Testing /api/pos/orders endpoint...\n";
try {
    $request = Request::create('/api/pos/orders', 'GET', ['branch' => 2]);
    $request->headers->set('X-Branch-ID', '2');
    $request->setLaravelSession(app('session.store'));
    
    $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
    echo "   Response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "   ✓ Orders endpoint working\n";
    } else {
        echo "   ✗ Orders endpoint failed\n";
        echo "   Response: " . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check ActivityLogService directly
echo "3. Testing ActivityLogService...\n";
try {
    \App\Services\ActivityLogService::logPosActivity(
        'test',
        'Testing activity log',
        ['test' => true]
    );
    echo "   ✓ ActivityLogService working\n";
} catch (Exception $e) {
    echo "   ✗ ActivityLogService error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";








