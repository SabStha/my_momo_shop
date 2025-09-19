<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== WEB FORM LOGIN TEST ===\n\n";

// Test credentials
$testEmail = 'customer16@example.com';
$testPassword = 'password';

echo "Testing web form submission...\n";

// Simulate the exact web form submission
$request = \Illuminate\Http\Request::create('/login', 'POST', [
    'email' => $testEmail,
    'password' => $testPassword,
    '_token' => csrf_token() // Get actual CSRF token
]);

// Set up session
$session = app('session.store');
$request->setLaravelSession($session);

echo "CSRF Token: " . csrf_token() . "\n";
echo "Request data: " . json_encode($request->all()) . "\n";

// Test the login route directly
try {
    $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response headers: " . json_encode($response->headers->all()) . "\n";
    
    if ($response->isRedirect()) {
        echo "✅ Redirect response: " . $response->headers->get('Location') . "\n";
    } else {
        echo "Response content: " . substr($response->getContent(), 0, 500) . "...\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== END TEST ===\n";
