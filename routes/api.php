<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Health check endpoint for network detection
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'server' => 'Laravel API',
        'version' => '1.0.0'
    ]);
});
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

        Route::post('/change-password', function (Request $request) {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            $user = $request->user();
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
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

    // Notifications - accessible to all authenticated users
    Route::get('/notifications', function (Request $request) {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));
        
        return response()->json([
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    });
    
    Route::post('/notifications/mark-as-read', function (Request $request) {
        $notification = auth()->user()->notifications()->findOrFail($request->notification_id);
        $notification->markAsRead();
        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    });
    
    Route::post('/notifications/mark-all-as-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    });
    
    Route::delete('/notifications/{notification}', function (DatabaseNotification $notification) {
        $notification->delete();
        return response()->json(['success' => true, 'message' => 'Notification deleted']);
    });
    
    Route::get('/notifications/churn-risks', function () {
        $service = new \App\Services\ChurnRiskNotificationService();
        return response()->json($service->getCachedNotifications());
    })->name('api.notifications.churn-risks');

    // Branch routes
    Route::get('/branches', [App\Http\Controllers\Api\BranchController::class, 'index']);

    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\UserController::class, 'getProfile']);
        Route::put('/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    });
    
    // Profile picture upload
    Route::post('/profile/update-picture', [App\Http\Controllers\Api\UserController::class, 'updateProfilePicture']);

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

// Content API routes - Public access (no authentication required)
Route::get('/content/key/{key}', [App\Http\Controllers\Api\ContentController::class, 'getByKey']);
Route::get('/content/section/{section}', [App\Http\Controllers\Api\ContentController::class, 'getBySection']);
Route::get('/content/section/{section}/array', [App\Http\Controllers\Api\ContentController::class, 'getSectionAsArray']);
Route::get('/content/app-config', [App\Http\Controllers\Api\ContentController::class, 'getAppConfig']);
Route::post('/content/multiple-sections', [App\Http\Controllers\Api\ContentController::class, 'getMultipleSections']);

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

    // Finds API routes
    Route::get('/finds/data', function() {
        $selectedModel = request()->get('model', 'all');
        
        // Fetch merchandise data from database grouped by category and filtered by model
        $merchandise = [
            'tshirts' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('tshirts')->byModel($selectedModel)->get()),
            'accessories' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('accessories')->byModel($selectedModel)->get()),
            'toys' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('toys')->byModel($selectedModel)->get()),
            'limited' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('limited')->byModel($selectedModel)->get()),
        ];

        // Fetch bulk packages
        $bulkPackages = \App\Models\BulkPackage::active()->ordered()->get()->map(function($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'emoji' => $package->emoji,
                'badge' => $package->badge,
                'badge_color' => $package->badge_color,
                'items' => $package->items,
                'total_price' => $package->total_price,
            ];
        });

        // Dynamic configuration
        $config = [
            'finds_title' => config('finds.title'),
            'finds_subtitle' => config('finds.subtitle'),
            'add_to_cart_text' => config('finds.add_to_cart_text'),
            'unlockable_text' => config('finds.unlockable_text'),
            'progress_message' => config('finds.progress_message'),
            'earn_tooltip_message' => config('finds.earn_tooltip_message'),
            'urgency_badge_text' => config('finds.urgency_badge_text'),
            'earn_badge_text' => config('finds.earn_badge_text'),
        ];

        return response()->json([
            'merchandise' => $merchandise,
            'bulkPackages' => $bulkPackages,
            'config' => $config,
        ]);
    })->middleware('auth:sanctum');

    // Home screen API routes
    Route::get('/products/featured', function() {
        $baseUrl = request()->getSchemeAndHttpHost();
        $featuredProducts = \App\Models\Product::where('is_featured', true)
            ->where('is_active', true)
            ->limit(6)
            ->get()
            ->map(function($product) use ($baseUrl) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'subtitle' => $product->description ?: 'Delicious and authentic',
                    'price' => ['currency' => 'NPR', 'amount' => (int)$product->price],
                    'imageUrl' => $product->image ? $baseUrl . '/storage/' . $product->image : $baseUrl . '/storage/default.jpg',
                    'isFeatured' => true,
                    'ingredients' => $product->ingredients ?: 'Fresh ingredients',
                    'allergens' => $product->allergens ?: 'May contain allergens',
                    'calories' => $product->calories ?: 'Calories not specified',
                    'preparation_time' => $product->preparation_time ?: '15-20 minutes',
                    'spice_level' => $product->spice_level ?: 'Medium',
                    'serving_size' => $product->serving_size ?: '1 serving',
                    'is_vegetarian' => $product->is_vegetarian,
                    'is_vegan' => $product->is_vegan,
                    'is_gluten_free' => $product->is_gluten_free,
                ];
            });

        return response()->json([
            'data' => $featuredProducts
        ]);
    })->middleware('auth:sanctum');

    Route::get('/stats/home', function() {
        // Fetch real statistics from database
        $totalOrders = \App\Models\Order::count();
        $totalCustomers = \App\Models\User::where('role', 'customer')->count();
        $totalProducts = \App\Models\Product::where('is_active', true)->count();
        
        // Calculate average rating from reviews if available
        $avgRating = 4.8; // Default fallback
        
        return response()->json([
            'data' => [
                'orders_delivered' => $totalOrders > 1000 ? number_format($totalOrders / 1000, 1) . 'K+' : $totalOrders . '+',
                'happy_customers' => $totalCustomers > 100 ? number_format($totalCustomers / 100, 1) . 'K+' : $totalCustomers . '+',
                'years_in_business' => '3+', // Could be calculated from first order date
                'momo_varieties' => $totalProducts . '+',
                'growth_percentage' => '15', // Could be calculated from monthly growth
                'satisfaction_rate' => '98', // Could be calculated from reviews
                'customer_rating' => $avgRating . '⭐',
            ]
        ]);
    })->middleware('auth:sanctum');

    Route::get('/reviews', function() {
        // Try to fetch reviews from database if reviews table exists
        try {
            if (\Schema::hasTable('reviews')) {
                $reviews = DB::table('reviews')
                    ->where('is_featured', true)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get()
                    ->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'name' => $review->customer_name ?? 'Anonymous',
                            'rating' => (int) $review->rating,
                            'comment' => $review->comment,
                            'orderItem' => $review->product_name ?? 'Momo',
                            'date' => \Carbon\Carbon::parse($review->created_at)->diffForHumans(),
                        ];
                    });
                
                if ($reviews->count() > 0) {
                    return response()->json(['data' => $reviews]);
                }
            }
        } catch (\Exception $e) {
            // Fallback to mock data if reviews table doesn't exist
        }
        
        // Fallback to mock data
        return response()->json([
            'data' => [
                [
                    'id' => '1',
                    'name' => 'Ram Shrestha',
                    'rating' => 5,
                    'comment' => 'Best momos in town! Fresh and delicious.',
                    'orderItem' => 'Chicken Momo',
                    'date' => '2 days ago'
                ],
                [
                    'id' => '2',
                    'name' => 'Sita Maharjan',
                    'rating' => 4,
                    'comment' => 'Great taste and fast delivery. Highly recommended!',
                    'orderItem' => 'Vegetable Momo',
                    'date' => '1 week ago'
                ],
                [
                    'id' => '3',
                    'name' => 'Hari Thapa',
                    'rating' => 5,
                    'comment' => 'Authentic Nepali momos. Love the variety!',
                    'orderItem' => 'Pork Momo',
                    'date' => '2 weeks ago'
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

    // Home benefits and content API
    Route::get('/home/benefits', function() {
        return response()->json([
            'data' => [
                'benefits' => [
                    [
                        'id' => '1',
                        'emoji' => '🥬',
                        'title' => 'Fresh Ingredients',
                        'description' => 'High-quality ingredients sourced daily.',
                    ],
                    [
                        'id' => '2',
                        'emoji' => '👩‍🍳',
                        'title' => 'Authentic Recipes',
                        'description' => 'Traditional Nepalese recipes.',
                    ],
                    [
                        'id' => '3',
                        'emoji' => '🚚',
                        'title' => 'Fast Delivery',
                        'description' => '25 minutes average delivery.',
                    ],
                ],
                'stats' => [
                    [
                        'id' => '1',
                        'value' => '179+',
                        'label' => 'Orders Delivered',
                        'icon' => 'truck-delivery',
                        'trend' => '+-100% this month',
                        'trendIcon' => 'trending-up',
                    ],
                    [
                        'id' => '2',
                        'value' => '21+',
                        'label' => 'Happy Customers',
                        'icon' => 'account-heart',
                        'trend' => '100% satisfaction',
                        'trendIcon' => 'emoticon-happy',
                    ],
                    [
                        'id' => '3',
                        'value' => '1+',
                        'label' => 'Years in Business',
                        'icon' => 'trophy',
                        'trend' => 'Trusted brand',
                        'trendIcon' => 'shield-check',
                    ],
                ],
                'content' => [
                    'title' => '✨ Why Choose Ama Ko Shop?',
                    'subtitle' => 'From our kitchen to your heart — here\'s why thousands trust us with their favorite comfort food.',
                    'ctaText' => 'Try Our Momos Today'
                ]
            ]
        ]);
    })->middleware('auth:sanctum');
}

// Menu API routes - Public access (no authentication required)
Route::get('/menu', function() {
    try {
        $categories = \App\Models\Category::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => null, // Categories don't have images in current schema
                    'is_active' => $category->status === 'active',
                    'sort_order' => 0, // No sort_order column
                ];
            });

        $items = \App\Models\Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'desc' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'isFeatured' => (bool) $product->is_featured,
                    'categoryId' => $product->category, // This is a string field
                    'category' => [
                        'id' => $product->category,
                        'name' => $product->category,
                    ],
                    'ingredients' => $product->ingredients,
                    'allergens' => $product->allergens,
                    'calories' => $product->calories,
                    'preparation_time' => $product->preparation_time,
                    'spice_level' => $product->spice_level,
                    'serving_size' => $product->serving_size,
                    'is_vegetarian' => (bool) $product->is_vegetarian,
                    'is_vegan' => (bool) $product->is_vegan,
                    'is_gluten_free' => (bool) $product->is_gluten_free,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'items' => $items,
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Menu API Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching menu data'
        ], 500);
    }
});

Route::get('/categories', function() {
    try {
        $categories = \App\Models\Category::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => null, // Categories don't have images in current schema
                    'is_active' => $category->status === 'active',
                    'sort_order' => 0, // No sort_order column
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    } catch (\Exception $e) {
        \Log::error('Categories API Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching categories'
        ], 500);
    }
});

Route::get('/items/{id}', function($id) {
    try {
        $product = \App\Models\Product::findOrFail($id);
        
        $item = [
            'id' => $product->id,
            'name' => $product->name,
            'desc' => $product->description,
            'price' => (float) $product->price,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'isFeatured' => (bool) $product->is_featured,
            'categoryId' => $product->category, // This is a string field
            'category' => [
                'id' => $product->category,
                'name' => $product->category,
            ],
            'ingredients' => $product->ingredients,
            'allergens' => $product->allergens,
            'calories' => $product->calories,
            'preparation_time' => $product->preparation_time,
            'spice_level' => $product->spice_level,
            'serving_size' => $product->serving_size,
            'is_vegetarian' => (bool) $product->is_vegetarian,
            'is_vegan' => (bool) $product->is_vegan,
            'is_gluten_free' => (bool) $product->is_gluten_free,
        ];

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    } catch (\Exception $e) {
        \Log::error('Item API Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Item not found'
        ], 404);
    }
});

Route::get('/categories/{categoryId}/items', function($categoryId) {
    try {
        $items = \App\Models\Product::where('category', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'desc' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'isFeatured' => (bool) $product->is_featured,
                    'categoryId' => $product->category, // This is a string field
                    'category' => [
                        'id' => $product->category,
                        'name' => $product->category,
                    ],
                    'ingredients' => $product->ingredients,
                    'allergens' => $product->allergens,
                    'calories' => $product->calories,
                    'preparation_time' => $product->preparation_time,
                    'spice_level' => $product->spice_level,
                    'serving_size' => $product->serving_size,
                    'is_vegetarian' => (bool) $product->is_vegetarian,
                    'is_vegan' => (bool) $product->is_vegan,
                    'is_gluten_free' => (bool) $product->is_gluten_free,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    } catch (\Exception $e) {
        \Log::error('Category Items API Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching category items'
        ], 500);
    }
});

Route::get('/items/search', function(\Illuminate\Http\Request $request) {
    try {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $items = \App\Models\Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'desc' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'isFeatured' => (bool) $product->is_featured,
                    'categoryId' => $product->category, // This is a string field
                    'category' => [
                        'id' => $product->category,
                        'name' => $product->category,
                    ],
                    'ingredients' => $product->ingredients,
                    'allergens' => $product->allergens,
                    'calories' => $product->calories,
                    'preparation_time' => $product->preparation_time,
                    'spice_level' => $product->spice_level,
                    'serving_size' => $product->serving_size,
                    'is_vegetarian' => (bool) $product->is_vegetarian,
                    'is_vegan' => (bool) $product->is_vegan,
                    'is_gluten_free' => (bool) $product->is_gluten_free,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    } catch (\Exception $e) {
        \Log::error('Search API Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error searching items'
        ], 500);
    }
});

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

// Finds API endpoint (requires auth)
Route::get('/finds/data', function() {
    $selectedModel = request()->get('model', 'all');

    // Fetch dynamic categories from database
    $categories = \App\Models\FindsCategory::active()->ordered()->get()->map(function($category) {
        return [
            'key' => $category->key,
            'label' => $category->label,
            'icon' => $category->icon,
            'description' => $category->description,
        ];
    });

    // Fetch merchandise data from database grouped by category and filtered by model
    $merchandise = [
        'tshirts' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('tshirts')->byModel($selectedModel)->get()),
        'accessories' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('accessories')->byModel($selectedModel)->get()),
        'toys' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('toys')->byModel($selectedModel)->get()),
        'limited' => \App\Http\Resources\MerchandiseResource::collection(\App\Models\Merchandise::active()->byCategory('limited')->byModel($selectedModel)->get()),
    ];

    // Fetch bulk packages
    $bulkPackages = \App\Models\BulkPackage::active()->ordered()->get()->map(function($package) {
        return [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'emoji' => $package->emoji,
            'badge' => $package->badge,
            'badge_color' => $package->badge_color,
            'items' => $package->items,
            'total_price' => $package->total_price,
        ];
    });

    // Dynamic configuration
    $config = [
        'finds_title' => config('finds.title'),
        'finds_subtitle' => config('finds.subtitle'),
        'add_to_cart_text' => config('finds.add_to_cart_text'),
        'unlockable_text' => config('finds.unlockable_text'),
        'progress_message' => config('finds.progress_message'),
        'earn_tooltip_message' => config('finds.earn_tooltip_message'),
        'urgency_badge_text' => config('finds.urgency_badge_text'),
        'earn_badge_text' => config('finds.earn_badge_text'),
    ];

    return response()->json([
        'categories' => $categories,
        'merchandise' => $merchandise,
        'bulkPackages' => $bulkPackages,
        'config' => $config,
    ]);
})->middleware('auth:sanctum');

// Bulk packages API endpoint (requires auth)
Route::get('/bulk/data', function() {
    // Fetch bulk packages from database
    $cookedPackages = \App\Models\BulkPackage::active()->byType('cooked')->ordered()->get();
    $frozenPackages = \App\Models\BulkPackage::active()->byType('frozen')->ordered()->get();

    $packages = [
        'cooked' => $cookedPackages->keyBy('package_key'),
        'frozen' => $frozenPackages->keyBy('package_key')
    ];

    // Fetch momo types from database
    $momoTypes = \App\Models\Product::where('is_active', true)
        ->where('category', 'Momo')
        ->where('stock', '>', 0)
        ->orderBy('name')
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'image' => $product->image,
            ];
        });

    // Fetch side dishes from database
    $sideDishes = \App\Models\Product::where('is_active', true)
        ->where('category', 'Side Dish')
        ->where('stock', '>', 0)
        ->orderBy('name')
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'image' => $product->image,
            ];
        });

    // Fetch drinks from database
    $drinks = \App\Models\Product::where('is_active', true)
        ->where('category', 'Drink')
        ->where('stock', '>', 0)
        ->orderBy('name')
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'image' => $product->image,
            ];
        });

    // Fetch desserts from database
    $desserts = \App\Models\Product::where('is_active', true)
        ->where('category', 'Dessert')
        ->where('stock', '>', 0)
        ->orderBy('name')
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'image' => $product->image,
            ];
        });

    // Combine all products
    $products = $momoTypes->concat($sideDishes)->concat($drinks)->concat($desserts);

    // Bulk discount percentage
    $bulkDiscountPercentage = 15; // 15% discount for bulk orders

    return response()->json([
        'packages' => $packages,
        'products' => $products,
        'bulkDiscountPercentage' => $bulkDiscountPercentage,
    ]);
})->middleware('auth:sanctum');

