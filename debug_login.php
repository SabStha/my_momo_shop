<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG LOGIN ISSUE ===\n\n";

// 1. Check users
echo "1. Available Users:\n";
$users = \App\Models\User::select('id', 'email', 'name')->get();
foreach ($users as $user) {
    echo "   ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
}

echo "\n2. Testing Login Controller:\n";

// 2. Test login with first user
if ($users->count() > 0) {
    $testUser = $users->first();
    echo "   Testing with user: {$testUser->email}\n";
    
    // Test authentication
    $credentials = [
        'email' => $testUser->email,
        'password' => 'password' // Default password
    ];
    
    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        echo "   ✅ Authentication successful!\n";
        \Illuminate\Support\Facades\Auth::logout();
    } else {
        echo "   ❌ Authentication failed with 'password'\n";
        
        // Try with different common passwords
        $commonPasswords = ['123456', 'admin', 'test', '12345678'];
        foreach ($commonPasswords as $pwd) {
            $testCredentials = ['email' => $testUser->email, 'password' => $pwd];
            if (\Illuminate\Support\Facades\Auth::attempt($testCredentials)) {
                echo "   ✅ Authentication successful with password: {$pwd}\n";
                \Illuminate\Support\Facades\Auth::logout();
                break;
            }
        }
    }
}

echo "\n3. Checking Routes:\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'login')) {
        echo "   {$route->methods()[0]} {$route->uri()} -> {$route->getName()}\n";
    }
}

echo "\n4. Checking CSRF Configuration:\n";
$csrfMiddleware = new \App\Http\Middleware\VerifyCsrfToken();
$reflection = new ReflectionClass($csrfMiddleware);
$property = $reflection->getProperty('except');
$property->setAccessible(true);
$exceptions = $property->getValue($csrfMiddleware);
echo "   CSRF Exceptions: " . implode(', ', $exceptions) . "\n";

echo "\n=== END DEBUG ===\n";
