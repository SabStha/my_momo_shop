<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes are used for monitoring and health checks in production.
| They help ensure all critical services are working properly.
|
*/

Route::get('/health', function () {
    $checks = [];
    $overallStatus = 'healthy';

    // Database check
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'healthy';
    } catch (Exception $e) {
        $checks['database'] = 'unhealthy: ' . $e->getMessage();
        $overallStatus = 'unhealthy';
    }

    // Redis check
    try {
        Redis::ping();
        $checks['redis'] = 'healthy';
    } catch (Exception $e) {
        $checks['redis'] = 'unhealthy: ' . $e->getMessage();
        $overallStatus = 'unhealthy';
    }

    // Storage check
    try {
        Storage::disk('local')->put('health-check.txt', 'test');
        Storage::disk('local')->delete('health-check.txt');
        $checks['storage'] = 'healthy';
    } catch (Exception $e) {
        $checks['storage'] = 'unhealthy: ' . $e->getMessage();
        $overallStatus = 'unhealthy';
    }

    // Application info
    $response = [
        'status' => $overallStatus,
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'checks' => $checks,
    ];

    $statusCode = $overallStatus === 'healthy' ? 200 : 503;

    return response()->json($response, $statusCode);
});

Route::get('/health/ready', function () {
    // Simple readiness check - just verify the app is running
    return response()->json([
        'status' => 'ready',
        'timestamp' => now()->toISOString(),
    ]);
});

Route::get('/health/live', function () {
    // Simple liveness check - just verify the app is alive
    return response()->json([
        'status' => 'alive',
        'timestamp' => now()->toISOString(),
    ]);
});
