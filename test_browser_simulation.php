<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BROWSER SIMULATION TEST ===\n\n";

// Test credentials
$testEmail = 'customer16@example.com';
$testPassword = 'password';

// Step 1: Get the login page (like a browser would)
echo "1. Getting login page...\n";
$getRequest = \Illuminate\Http\Request::create('/login', 'GET');
$getResponse = app('Illuminate\Contracts\Http\Kernel')->handle($getRequest);

echo "   Status: " . $getResponse->getStatusCode() . "\n";

// Extract CSRF token from the response
$content = $getResponse->getContent();
preg_match('/name="_token" value="([^"]+)"/', $content, $matches);
$csrfToken = $matches[1] ?? null;

echo "   CSRF Token found: " . ($csrfToken ? 'Yes' : 'No') . "\n";
if ($csrfToken) {
    echo "   Token: " . $csrfToken . "\n";
}

// Step 2: Submit the login form (like a browser would)
echo "\n2. Submitting login form...\n";
$postRequest = \Illuminate\Http\Request::create('/login', 'POST', [
    'email' => $testEmail,
    'password' => $testPassword,
    '_token' => $csrfToken
]);

// Set the same session
$postRequest->setLaravelSession(app('session.store'));

$postResponse = app('Illuminate\Contracts\Http\Kernel')->handle($postRequest);

echo "   Status: " . $postResponse->getStatusCode() . "\n";

if ($postResponse->isRedirect()) {
    echo "   ✅ Redirect to: " . $postResponse->headers->get('Location') . "\n";
} else {
    echo "   Response content: " . substr($postResponse->getContent(), 0, 200) . "...\n";
}

echo "\n=== END TEST ===\n";
