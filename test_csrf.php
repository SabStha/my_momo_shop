<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CSRF TOKEN TEST ===\n\n";

// Start a session
$session = app('session.store');
$session->start();

echo "Session ID: " . $session->getId() . "\n";
echo "Session started: " . ($session->isStarted() ? 'Yes' : 'No') . "\n";

// Generate CSRF token
$token = csrf_token();
echo "CSRF Token: " . $token . "\n";

// Test if token is valid
if ($token && strlen($token) > 0) {
    echo "✅ CSRF token generated successfully\n";
} else {
    echo "❌ CSRF token generation failed\n";
}

// Test session data
echo "Session data: " . json_encode($session->all()) . "\n";

echo "\n=== END TEST ===\n";
