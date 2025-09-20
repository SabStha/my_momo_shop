<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\SalesAnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PosAuthController;
use App\Http\Controllers\Api\PosOrderController;
use App\Http\Controllers\Api\EmployeeAuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\PosTableController;
use App\Http\Controllers\Admin\CashDrawerController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Api\CustomerAnalyticsController;
use App\Http\Controllers\Admin\CampaignController;
use App\Services\ChurnRiskNotificationService;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\ProductImageController;
// use App\Http\Controllers\Api\KhaltiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Token refresh route
Route::post('/refresh-token', function (Request $request) {
    if (!Auth::check()) {
        return response()->json(['message' => 'Not authenticated'], 401);
    }

    try {
        $user = Auth::user();
        
        // Delete existing tokens
        $user->tokens()->delete();
        
        // Create new token with proper scopes
        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;
        
        // Store in session
        $request->session()->put('api_token', $token);
        
        // Log the token refresh
        \Log::info('Token refreshed successfully', [
            'user_id' => $user->id,
            'token_expires_at' => now()->addHours(24)
        ]);
        
        return response()->json([
            'token' => $token,
            'user' => $user->load('roles')
        ]);
    } catch (\Exception $e) {
        \Log::error('Token refresh failed', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);
        return response()->json(['message' => 'Token refresh failed'], 500);
    }
})->middleware(['web', 'auth']);

// Public routes
Route::middleware(['throttle:30,1'])->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'emailOrPhone' => 'required|string|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Check if user already exists
            $userExists = User::where('email', $request->emailOrPhone)
                ->orWhere('phone', $request->emailOrPhone)
                ->exists();

            if ($userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already exists with this email or phone number'
                ], 422);
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->emailOrPhone,
                'phone' => $request->emailOrPhone,
                'password' => bcrypt($request->password),
            ]);

            // Assign default role (user)
            $user->assignRole('user');

            // Generate token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => $user->load('roles')
            ], 201);
        });

        Route::post('/login', function (Request $request) {
            $request->validate([
                'emailOrPhone' => 'required|string',
                'password' => 'required|string'
            ]);

            // Try to authenticate with email or phone
            $credentials = [
                'password' => $request->password
            ];

            // Check if input is email or phone
            if (filter_var($request->emailOrPhone, FILTER_VALIDATE_EMAIL)) {
                $credentials['email'] = $request->emailOrPhone;
            } else {
                $credentials['phone'] = $request->emailOrPhone;
            }

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user->load('roles')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        });

        Route::post('/logout', function (Request $request) {
            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        })->middleware('auth:sanctum');
    });

    // Legacy login route (keeping for backward compatibility)
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user->load('roles')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    });

    Route::get('/leaderboard', function () {
        $creators = \App\Models\Creator::with('user:id,name')
            ->orderByDesc('points')
            ->take(10)
            ->get()
            ->map(function ($creator, $index) {
                $rank = $index + 1;
                $discount = match(true) {
                    $rank === 1 => 50,
                    $rank <= 3 => 40,
                    $rank <= 10 => 30,
                    default => 20
                };
                
                return [
                    'rank' => $rank,
                    'name' => $creator->user->name,
                    'points' => $creator->points,
                    'discount' => $discount,
                    'is_trending' => $creator->isTrending()
                ];
            });

        return response()->json($creators);
    });
});

// Cash Drawer routes (public)
Route::get('/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawer']);
Route::get('/cash-drawer/balance', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawerBalance']);
Route::post('/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'updateCashDrawer']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Admin routes
    Route::middleware(['role:admin,cashier'])->prefix('admin')->group(function () {
        // Cash Drawer routes with increased rate limit
        Route::middleware(['throttle:120,1'])->prefix('cash-drawer')->group(function () {
            Route::get('/', [CashDrawerController::class, 'getStatus']);
            Route::get('/balance', [CashDrawerController::class, 'getBalance']);
            Route::get('/current-denominations', [CashDrawerController::class, 'getCurrentDenominations']);
            Route::post('/update-denominations', [CashDrawerController::class, 'updateDenominations']);
            Route::post('/adjust', [CashDrawerController::class, 'adjust']);
            Route::post('/open', [CashDrawerController::class, 'openSession']);
            Route::post('/close', [CashDrawerController::class, 'closeSession']);
        });

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::get('/orders/{id}/details', [PaymentController::class, 'getOrder']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

        // Dashboard and Analytics
        Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/analytics/sales', [AnalyticsController::class, 'sales']);
        Route::get('/analytics/products', [AnalyticsController::class, 'products']);

        // Notifications
        Route::get('/notifications/churn-risks', function () {
            $service = new \App\Services\ChurnRiskNotificationService();
            return response()->json($service->getCachedNotifications());
        })->name('api.notifications.churn-risks');

        // Wallet routes
        Route::get('/wallets/{wallet_number}/balance', [AdminPaymentController::class, 'getWalletBalanceByNumber']);
    });

    // POS routes - require POS access
    Route::middleware(['pos.access'])->prefix('pos')->group(function () {
        Route::post('/verify-token', [PosAuthController::class, 'verifyToken']);
        Route::get('/tables', [PosController::class, 'tables']);
        Route::get('/products', [PosController::class, 'products']);
        Route::get('/orders', [PosOrderController::class, 'index']);
        
        // Orders
        Route::post('/orders', [PosOrderController::class, 'store']);
        Route::get('/orders/{order}', [PosOrderController::class, 'show']);
        Route::put('/orders/{order}', [PosOrderController::class, 'update']);
        Route::delete('/orders/{order}', [PosOrderController::class, 'destroy']);
        
        // Payments
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        // User info
        Route::get('/user-info', [PosController::class, 'userInfo']);
    });

    // Branch routes
    Route::get('/branches', [App\Http\Controllers\Api\BranchController::class, 'index']);

    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\UserController::class, 'getProfile']);
        Route::put('/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    });

    // Manager routes (admin and main_manager)
    Route::middleware(['role:admin|main_manager'])->prefix('manager')->group(function () {
        Route::get('/reports', [AnalyticsController::class, 'reports']);
        Route::get('/inventory', [ProductController::class, 'inventory']);
    });

    // Report routes (protected - admin only)
    Route::middleware(['role:admin', 'throttle:60,1'])->prefix('reports')->group(function () {
        Route::post('/generate', [ReportController::class, 'generate']);
        Route::post('/export', [ReportController::class, 'export']);
    });

    // User authentication check
    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->load('roles'));
    });

    // User profile endpoint (alias for /user)
    Route::get('/me', function (Request $request) {
        return response()->json($request->user()->load('roles'));
    });

    // Device management for push notifications
    Route::post('/devices', [\App\Http\Controllers\Api\DeviceController::class, 'store']);
    
    // Loyalty system
    Route::get('/loyalty', [\App\Http\Controllers\Api\LoyaltyController::class, 'summary']);

    // Orders API routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{order}/process-payment', [OrderController::class, 'processPayment']);
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('/employee/verify', [EmployeeAuthController::class, 'verify']);



    // Khalti Payment Routes (commented out due to missing controller)
    // Route::post('/khalti/initiate', [KhaltiController::class, 'initiatePayment']);
    // Route::post('/khalti/verify', [KhaltiController::class, 'verifyPayment']);
    // Route::get('/khalti/return', [KhaltiController::class, 'handleReturn']);

    // Customer Analytics API Routes
    Route::prefix('customer-analytics')->middleware(['web', 'auth'])->group(function () {
        Route::get('/', [CustomerAnalyticsController::class, 'index']);
        Route::get('/segment-suggestions', [CustomerAnalyticsController::class, 'getSegmentSuggestions']);
        Route::get('/retention-campaign/{customerId}', [CustomerAnalyticsController::class, 'generateRetentionCampaign']);
    });

    // Campaign Routes
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::post('/', [CampaignController::class, 'store']);
        Route::get('/{campaign}', [CampaignController::class, 'show']);
        Route::put('/{campaign}', [CampaignController::class, 'update']);
        Route::delete('/{campaign}', [CampaignController::class, 'destroy']);
        Route::get('/suggestions', [CampaignController::class, 'getSuggestions']);
        Route::get('/{campaign}/metrics', [CampaignController::class, 'getMetrics']);
        Route::put('/{campaign}/status', [CampaignController::class, 'updateStatus']);
    });
});

// Table API Routes
Route::prefix('admin')->group(function () {
    Route::put('/tables/{table}', [PosTableController::class, 'update']);
});

// Payment Webhooks
Route::post('webhooks/khalti', [WebhookController::class, 'handleKhaltiWebhook']);

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});

// Product Image routes - Public access (no authentication required)
Route::get('/product-images', [ProductImageController::class, 'index']);
Route::get('/product-images/{id}', [ProductImageController::class, 'show']);
Route::get('/product-images/category/{category}', [ProductImageController::class, 'byCategory']);

// Cart validation routes - Public access (no authentication required)
Route::post('/cart/validate', [App\Http\Controllers\Api\CartController::class, 'validateCart']);
Route::post('/cart/calculate', [App\Http\Controllers\Api\CartController::class, 'calculateTotals']);

// Debug routes - Public access (no authentication required)
Route::post('/debug/order-creation', [App\Http\Controllers\OrderController::class, 'debugOrderCreation']);

// Dev triage route (local only)
if (app()->environment('local')) {
    Route::get('/dev/triage/product', function (Request $request) {
        $pid = (int)$request->query('product_id');
        $bid = (int)$request->query('branch_id');
        $qty = (int)$request->query('qty', 1);

        $prod = \App\Models\Product::withTrashed()->find($pid);
        $exists = (bool)$prod;
        $active = $exists && !$prod->deleted_at && ($prod->is_active ?? true);

        // Note: branch_product table doesn't exist in current database
        $pivotExists = false; // No branch-product relationships yet
        $stockOk = true; // Assume unlimited stock for now

        return response()->json([
            'exists' => $exists,
            'active' => $active,
            'pivotExists' => $pivotExists,
            'stockOk' => $stockOk,
            'product' => $prod,
            'note' => 'branch_product table does not exist - using simplified validation'
        ]);
    });
}

// Test notification route (dev only)
if (app()->environment('local', 'development')) {
    Route::post('/notify/test', function(\Illuminate\Http\Request $r) {
        $user = $r->user();
        $tokens = \App\Models\Device::where('user_id', $user->id)->pluck('token')->all();
        if (!$tokens) return response()->json(['msg'=>'no tokens'], 404);
        app(\App\Services\ExpoPushService::class)->send($tokens, 'AmaKo', 'Test push', ['hello'=>'world']);
        return ['ok'=>true];
    })->middleware('auth:sanctum');

    // Home screen API routes
    Route::get('/products/featured', function() {
        return response()->json([
            'data' => [
                [
                    'id' => '1',
                    'name' => 'Chicken Momo',
                    'subtitle' => 'Delicious steamed dumplings',
                    'price' => ['currency' => 'NPR', 'amount' => 120],
                    'imageUrl' => 'https://via.placeholder.com/300x200?text=Chicken+Momo',
                    'isFeatured' => true
                ],
                [
                    'id' => '2',
                    'name' => 'Veg Momo',
                    'subtitle' => 'Fresh vegetable dumplings',
                    'price' => ['currency' => 'NPR', 'amount' => 100],
                    'imageUrl' => 'https://via.placeholder.com/300x200?text=Veg+Momo',
                    'isFeatured' => true
                ],
                [
                    'id' => '3',
                    'name' => 'Buff Momo',
                    'subtitle' => 'Tender buffalo meat dumplings',
                    'price' => ['currency' => 'NPR', 'amount' => 140],
                    'imageUrl' => 'https://via.placeholder.com/300x200?text=Buff+Momo',
                    'isFeatured' => true
                ]
            ]
        ]);
    })->middleware('auth:sanctum');

    Route::get('/stats/home', function() {
        return response()->json([
            'data' => [
                'happyCustomers' => '21+',
                'momoVarieties' => '21+',
                'rating' => '4.8★'
            ]
        ]);
    })->middleware('auth:sanctum');

    Route::get('/reviews', function() {
        return response()->json([
            'data' => [
                [
                    'id' => '1',
                    'name' => 'Ram Shrestha',
                    'rating' => 5,
                    'comment' => 'Best momos in town! Fresh and delicious.',
                    'date' => '2024-01-15'
                ],
                [
                    'id' => '2',
                    'name' => 'Sita Maharjan',
                    'rating' => 4,
                    'comment' => 'Great taste and fast delivery. Highly recommended!',
                    'date' => '2024-01-14'
                ],
                [
                    'id' => '3',
                    'name' => 'Hari Thapa',
                    'rating' => 5,
                    'comment' => 'Authentic Nepali momos. Love the variety!',
                    'date' => '2024-01-13'
                ]
            ]
        ]);
    })->middleware('auth:sanctum');

    Route::get('/store/info', function() {
        return response()->json([
            'data' => [
                'name' => 'Ama Ko Shop',
                'address' => 'Kathmandu, Nepal',
                'phone' => '+977-1-1234567',
                'email' => 'info@amakoshop.com',
                'hours' => '9:00 AM - 10:00 PM',
                'description' => 'Authentic Nepali momos and traditional cuisine',
                'businessHours' => [
                    ['day' => 'Monday', 'open' => '10:00', 'close' => '22:00', 'isOpen' => true],
                    ['day' => 'Tuesday', 'open' => '10:00', 'close' => '22:00', 'isOpen' => true],
                    ['day' => 'Wednesday', 'open' => '10:00', 'close' => '22:00', 'isOpen' => true],
                    ['day' => 'Thursday', 'open' => '10:00', 'close' => '22:00', 'isOpen' => true],
                    ['day' => 'Friday', 'open' => '10:00', 'close' => '23:00', 'isOpen' => true],
                    ['day' => 'Saturday', 'open' => '10:00', 'close' => '23:00', 'isOpen' => true],
                    ['day' => 'Sunday', 'open' => '11:00', 'close' => '21:00', 'isOpen' => true],
                ],
                'socialMedia' => [
                    'facebook' => 'https://facebook.com/amakoshop',
                    'instagram' => 'https://instagram.com/amakoshop',
                    'twitter' => 'https://twitter.com/amakoshop',
                ]
            ]
        ]);
    })->middleware('auth:sanctum');
}

// Public business status endpoint (no auth required)
Route::get('/business/status/{branch_id}', function($branchId) {
    $cashDrawerSession = \App\Models\CashDrawerSession::where('branch_id', $branchId)
        ->whereNull('closed_at')
        ->first();
    
    return response()->json([
        'is_open' => $cashDrawerSession ? true : false,
        'branch_id' => $branchId,
        'message' => $cashDrawerSession ? 'We are open!' : 'We are currently closed.'
    ]);
});

