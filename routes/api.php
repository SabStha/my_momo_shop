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
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PosAuthController;
use App\Http\Controllers\Api\PosOrderController;
use App\Http\Controllers\Api\EmployeeAuthController;
use App\Http\Controllers\OrderController; // For mobile order creation with session checks
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
            try {
                $request->validate([
                    'name' => 'required|string|max:255',
                    'emailOrPhone' => 'required|string|max:255',
                    'password' => 'required|string|min:8|confirmed',
                ]);

                \Log::info('Registration attempt', [
                    'name' => $request->name,
                    'emailOrPhone' => $request->emailOrPhone,
                ]);

                // Check if user already exists
                $userExists = User::where('email', $request->emailOrPhone)
                    ->orWhere('phone', $request->emailOrPhone)
                    ->exists();

                if ($userExists) {
                    \Log::warning('Registration failed - user exists', [
                        'emailOrPhone' => $request->emailOrPhone
                    ]);
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

                \Log::info('User created successfully', ['user_id' => $user->id]);

                // Assign default role (user) - only if role exists
                try {
                    if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
                        $user->assignRole('user');
                        \Log::info('User role assigned', ['user_id' => $user->id]);
                    } else {
                        \Log::warning('User role does not exist, skipping role assignment');
                    }
                } catch (\Exception $roleError) {
                    \Log::error('Failed to assign role', [
                        'user_id' => $user->id,
                        'error' => $roleError->getMessage()
                    ]);
                    // Continue without role - don't fail registration
                }

                // Generate token
                $token = $user->createToken('api-token')->plainTextToken;

                \Log::info('Registration successful', ['user_id' => $user->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'token' => $token,
                    'user' => $user->load('roles')
                ], 201);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Registration validation failed', [
                    'errors' => $e->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Registration failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }
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

// Public stats endpoint (before auth group)
Route::get('/stats/home', function() {
    // Fetch real statistics from database
    $totalOrders = \App\Models\Order::count();
    
    // Count total users (excluding admins if you want only customers)
    $totalCustomers = \App\Models\User::count();
    
    $totalProducts = \App\Models\Product::where('is_active', true)->count();
    
    // Calculate average rating from reviews if available
    $avgRating = 0;
    try {
        if (\Schema::hasTable('reviews')) {
            $avgRating = \DB::table('reviews')
                ->where('is_featured', true)
                ->avg('rating');
            
            // Format rating to 1 decimal place
            $avgRating = $avgRating ? round($avgRating, 1) : 0;
        }
    } catch (\Exception $e) {
        // No reviews table or error - rating stays 0
        $avgRating = 0;
    }
    
    // Format the rating for display
    $ratingDisplay = $avgRating > 0 ? $avgRating . '⭐' : 'No reviews yet';
    
    return response()->json([
        'data' => [
            'orders_delivered' => $totalOrders > 1000 ? number_format($totalOrders / 1000, 1) . 'K+' : $totalOrders . '+',
            'happy_customers' => $totalCustomers > 100 ? number_format($totalCustomers / 100, 1) . 'K+' : $totalCustomers . '+',
            'years_in_business' => '3+', // Could be calculated from first order date
            'momo_varieties' => $totalProducts . '+',
            'growth_percentage' => '15', // Could be calculated from monthly growth
            'satisfaction_rate' => '98', // Could be calculated from reviews
            'customer_rating' => $ratingDisplay,
        ]
    ]);
});

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

        // Orders (for payment manager - uses Api\OrderController)
        Route::get('/orders', [ApiOrderController::class, 'index']);
        Route::get('/orders/{id}', [ApiOrderController::class, 'show']);
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

    // Admin: Mobile Notification Management
    Route::middleware(['role:admin'])->prefix('admin/mobile-notifications')->group(function () {
        Route::post('/test', [\App\Http\Controllers\Admin\MobileNotificationController::class, 'sendTestNotification']);
        Route::post('/generate-ai-offers', [\App\Http\Controllers\Admin\MobileNotificationController::class, 'generateAndSendAIOffers']);
        Route::post('/flash-sale', [\App\Http\Controllers\Admin\MobileNotificationController::class, 'sendFlashSale']);
        Route::post('/broadcast-offer/{offerId}', [\App\Http\Controllers\Admin\MobileNotificationController::class, 'broadcastOffer']);
        Route::get('/statistics', [\App\Http\Controllers\Admin\MobileNotificationController::class, 'getStatistics']);
    });

    // Branch routes
    Route::get('/branches', [App\Http\Controllers\Api\BranchController::class, 'index']);

    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\UserController::class, 'getProfile']);
        Route::put('/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    });
    
        // Profile picture upload
        Route::post('/profile/update-picture', [App\Http\Controllers\Api\UserController::class, 'updateProfilePicture']);
        
        // Cart synchronization endpoints
        Route::get('/cart', [App\Http\Controllers\Api\CartSyncController::class, 'getCart']);
        Route::post('/cart/sync', [App\Http\Controllers\Api\CartSyncController::class, 'syncCart']);
        Route::post('/cart/clear', [App\Http\Controllers\Api\CartSyncController::class, 'clearCart']);
        Route::post('/cart/add-item', [App\Http\Controllers\Api\CartSyncController::class, 'addItem']);
        Route::delete('/cart/remove-item', [App\Http\Controllers\Api\CartSyncController::class, 'removeItem']);
        Route::put('/cart/update-quantity', [App\Http\Controllers\Api\CartSyncController::class, 'updateQuantity']);
    
    // Wallet QR code processing (for all authenticated users)
    Route::post('/wallet/process-qr', [App\Http\Controllers\Admin\WalletController::class, 'processCode']);

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
        $user = $request->user();
        
        // Load user with roles and ensure all fields are included
        $userData = $user->load('roles');
        
        // Log for debugging
        \Log::info('📸 /me endpoint called', [
            'user_id' => $user->id,
            'has_profile_picture' => !empty($user->profile_picture),
            'profile_picture_url' => $user->profile_picture,
            'all_fields' => $user->toArray()
        ]);
        
        return response()->json($userData);
    });

    // Device management for push notifications
    Route::post('/devices', [\App\Http\Controllers\Api\DeviceController::class, 'store']);
    
    // Loyalty system
    Route::get('/loyalty', [\App\Http\Controllers\Api\LoyaltyController::class, 'summary']);

    // Orders API routes (for mobile app)
    Route::get('/orders', function (Request $request) {
        $user = auth()->user();
        
        // Get user's orders from database
        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->id, // For compatibility
                    'order_number' => $order->order_code ?? $order->order_number ?? 'ORD-' . $order->id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'total' => (float) $order->total,
                    'total_amount' => (float) ($order->total_amount ?? $order->grand_total ?? $order->total),
                    'grand_total' => (float) ($order->grand_total ?? $order->total_amount ?? $order->total),
                    'delivery_address' => $order->delivery_address,
                    'created_at' => $order->created_at->toISOString(),
                    'updated_at' => $order->updated_at->toISOString(),
                ];
            });
        
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    });
    Route::post('/orders', [OrderController::class, 'store']); // Mobile order creation WITH session check
    Route::get('/orders/{order}', function ($orderId) {
        $user = auth()->user();
        
        // Find order by ID
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_id' => $order->id,
                'order_number' => $order->order_code ?? $order->order_number ?? 'ORD-' . $order->id,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total' => (float) $order->total,
                'total_amount' => (float) ($order->total_amount ?? $order->grand_total ?? $order->total),
                'grand_total' => (float) ($order->grand_total ?? $order->total_amount ?? $order->total),
                'delivery_address' => $order->delivery_address,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
            ]
        ]);
    });
    Route::get('/orders/{order}/tracking', [\App\Http\Controllers\DeliveryController::class, 'getTracking']); // Get delivery tracking
    Route::post('/orders/{order}/process-payment', [OrderController::class, 'processPayment']);
    Route::post('/orders/{order}/status', [ApiOrderController::class, 'updateStatus']); // Use Api version for status updates
    Route::post('/employee/verify', [EmployeeAuthController::class, 'verify']);

    // Driver location tracking routes
    Route::post('/driver/location', function(Request $request) {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|string',
                'order_id' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'accuracy' => 'nullable|numeric',
                'timestamp' => 'required|integer',
            ]);

            // Store driver location in database
            $tracking = \App\Models\DeliveryTracking::create([
                'driver_id' => $validated['driver_id'],
                'order_id' => $validated['order_id'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy' => $validated['accuracy'] ?? 0,
                'timestamp' => \Carbon\Carbon::createFromTimestamp($validated['timestamp']),
            ]);

            \Log::info('Driver location updated', [
                'driver_id' => $validated['driver_id'],
                'order_id' => $validated['order_id'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy' => $validated['accuracy']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $tracking
            ]);
        } catch (\Exception $e) {
            \Log::error('Driver location update failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage()
            ], 500);
        }
    });

    // Get latest driver location for an order
    Route::get('/driver/location/{orderId}', function($orderId) {
        try {
            $latestLocation = \App\Models\DeliveryTracking::where('order_id', $orderId)
                ->orderBy('timestamp', 'desc')
                ->first();

            if (!$latestLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No location data found for this order'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'driver_id' => $latestLocation->driver_id,
                    'order_id' => $latestLocation->order_id,
                    'latitude' => $latestLocation->latitude,
                    'longitude' => $latestLocation->longitude,
                    'accuracy' => $latestLocation->accuracy,
                    'timestamp' => $latestLocation->timestamp->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get driver location', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get driver location: ' . $e->getMessage()
            ], 500);
        }
    });



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

}

// Store info API - Public access (no auth required)
Route::get('/store/info', function() {
    // Get main branch from database
    $mainBranch = App\Models\Branch::find(1);
    
    return response()->json([
        'data' => [
            'name' => 'Ama Ko Shop',
            'address' => $mainBranch->address ?? 'Kathmandu, Nepal',
            'latitude' => $mainBranch->latitude ?? 27.7172,
            'longitude' => $mainBranch->longitude ?? 85.3240,
            'phone' => $mainBranch->phone ?? '+977-1-1234567',
            'email' => $mainBranch->email ?? 'info@amakoshop.com',
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
});

// Home benefits and content API - Public access (no auth required)
Route::get('/home/benefits', function() {
    // Fetch real statistics from database
    $totalOrders = \App\Models\Order::count();
    $totalCustomers = \App\Models\User::count();
    
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
                    'value' => $totalOrders . '+',
                    'label' => 'Orders Delivered',
                    'icon' => 'truck-delivery',
                    'trend' => $totalOrders > 0 ? 'Growing fast' : 'Just getting started',
                    'trendIcon' => 'trending-up',
                ],
                [
                    'id' => '2',
                    'value' => $totalCustomers . '+',
                    'label' => 'Happy Customers',
                    'icon' => 'account-heart',
                    'trend' => $totalCustomers > 0 ? '100% satisfaction' : 'Building our community',
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
                'subtitle' => 'From our kitchen to your heart — authentic momos made with love.',
                'ctaText' => 'Try Our Momos Today'
            ]
        ]
    ]);
});

// Review routes - Public access (with optional authentication)
// GET reviews (featured or all)
Route::get('/reviews', function() {
    // Try to fetch reviews from database if reviews table exists
    try {
        if (\Schema::hasTable('reviews')) {
            $query = DB::table('reviews')
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->limit(10);
            
            // Filter by featured if requested
            if (request()->get('featured') === 'true') {
                $query->where('is_featured', true);
            }
            
            $reviews = $query->get()
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
            
            return response()->json([
                'success' => true,
                'data' => $reviews,
                'count' => $reviews->count()
            ]);
        }
    } catch (\Exception $e) {
        // Log error but return empty array
        \Log::info('Reviews table check failed: ' . $e->getMessage());
    }
    
    // Return empty array if no reviews exist or table doesn't exist
    return response()->json([
        'success' => true,
        'data' => [],
        'count' => 0
    ]);
});

// POST review submission
Route::post('/reviews', function() {
    try {
        $validated = request()->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
            'orderItem' => 'nullable|string',
            'userId' => 'nullable|integer',
            'order_id' => 'nullable|integer',
            'order_number' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();
        
        // Use provided userId first, fall back to authenticated user
        $userId = $validated['userId'] ?? $user?->id;
        $userName = $user?->name ?? 'Anonymous';
        $userEmail = $user?->email;
        
        \Log::info('Review submission attempt', [
            'auth_user_id' => $user?->id,
            'auth_user_name' => $user?->name,
            'provided_user_id' => $validated['userId'] ?? null,
            'final_user_id' => $userId,
            'final_user_name' => $userName,
            'order_id' => $validated['order_id'] ?? null,
            'order_number' => $validated['order_number'] ?? null,
            'rating' => $validated['rating'],
            'has_auth' => !!$user,
            'has_sanctum_token' => !!request()->bearerToken(),
        ]);
        
        // Check if user already has a review for this order or product
        $existingReview = null;
        if ($validated['order_id']) {
            // If order_id provided, check for review on that specific order
            $existingReview = DB::table('reviews')
                ->where('user_id', $userId)
                ->where('order_id', $validated['order_id'])
                ->first();
        } else {
            // If no order_id, check for review on the same product by same user
            $existingReview = DB::table('reviews')
                ->where('user_id', $userId)
                ->where('product_name', $validated['orderItem'] ?? 'General')
                ->first();
        }

        $reviewData = [
            'user_id' => $userId,
            'order_id' => $validated['order_id'] ?? null,
            'customer_name' => $userName,
            'customer_email' => $userEmail,
            'product_name' => $validated['orderItem'] ?? 'General',
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_featured' => $validated['rating'] >= 4, // Auto-feature 4-5 star reviews
            'is_approved' => true,
            'updated_at' => now(),
        ];

        if ($existingReview) {
            // Update existing review
            DB::table('reviews')
                ->where('id', $existingReview->id)
                ->update($reviewData);
            
            $reviewId = $existingReview->id;
            $action = 'updated';
            
            \Log::info('Review updated successfully', [
                'review_id' => $reviewId,
                'order_id' => $validated['order_id'] ?? null,
                'previous_rating' => $existingReview->rating,
                'new_rating' => $validated['rating'],
            ]);
        } else {
            // Create new review
            $reviewData['created_at'] = now();
            $reviewId = DB::table('reviews')->insertGetId($reviewData);
            $action = 'created';
            
            // Award points for new review (10 points per review, bonus for 5-star reviews)
            if ($user) {
                try {
                    $reviewPoints = 10; // Base points
                    if ($validated['rating'] === 5) {
                        $reviewPoints = 25; // Bonus for 5-star review
                    } elseif ($validated['rating'] >= 4) {
                        $reviewPoints = 15; // Bonus for 4-star review
                    }
                    
                    $user->addAmaCredits(
                        $reviewPoints,
                        "Review submitted for " . ($validated['order_number'] ?? 'order'),
                        'review_submitted',
                        [
                            'review_id' => $reviewId,
                            'rating' => $validated['rating'],
                            'order_id' => $validated['order_id'] ?? null,
                        ]
                    );
                    
                    \Log::info('Review points awarded', [
                        'review_id' => $reviewId,
                        'points' => $reviewPoints,
                        'user_id' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    // Don't fail the review if points fail
                    \Log::warning('Failed to award review points: ' . $e->getMessage());
                }
            }
            
            \Log::info('Review created successfully', [
                'review_id' => $reviewId,
                'order_id' => $validated['order_id'] ?? null,
            ]);
        }

        // Get points awarded (if any)
        $pointsAwarded = 0;
        if ($action === 'created' && isset($reviewPoints)) {
            $pointsAwarded = $reviewPoints;
        }
        
        return response()->json([
            'success' => true,
            'message' => $action === 'updated' 
                ? 'Review updated successfully!' 
                : 'Review submitted successfully!',
            'action' => $action,
            'points_awarded' => $pointsAwarded,
            'data' => [
                'id' => $reviewId,
                'rating' => $validated['rating'],
                'order_id' => $validated['order_id'] ?? null,
            ]
        ], $action === 'updated' ? 200 : 201);
    } catch (\Exception $e) {
        \Log::error('Review submission failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to submit review: ' . $e->getMessage()
        ], 500);
    }
});

// Menu API routes - Public access (no authentication required)
Route::get('/menu', function() {
    try {
        // Get proper base URL for images
        $baseUrl = request()->getSchemeAndHttpHost();
        
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
            ->map(function ($product) use ($baseUrl) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'desc' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image ? $baseUrl . '/storage/' . $product->image : null,
                    'isFeatured' => (bool) $product->is_featured,
                    'is_featured' => (bool) $product->is_featured, // For mobile app compatibility
                    'is_menu_highlight' => (bool) $product->is_menu_highlight, // For hero carousel
                    'categoryId' => $product->tag ?: $product->category, // Use tag for filtering (buff/chicken/veg/hot/cold)
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
        $baseUrl = request()->getSchemeAndHttpHost();
        $product = \App\Models\Product::findOrFail($id);
        
        $item = [
            'id' => $product->id,
            'name' => $product->name,
            'desc' => $product->description,
            'price' => (float) $product->price,
            'image' => $product->image ? $baseUrl . '/storage/' . $product->image : null,
            'isFeatured' => (bool) $product->is_featured,
            'categoryId' => $product->tag ?: $product->category, // Use tag for filtering
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
        $baseUrl = request()->getSchemeAndHttpHost();
        $items = \App\Models\Product::where('category', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($baseUrl) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'desc' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image ? $baseUrl . '/storage/' . $product->image : null,
                    'isFeatured' => (bool) $product->is_featured,
                    'categoryId' => $product->tag ?: $product->category, // Use tag for filtering
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

        $baseUrl = request()->getSchemeAndHttpHost();
        $items = \App\Models\Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($baseUrl) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'desc' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image ? $baseUrl . '/storage/' . $product->image : null,
                    'isFeatured' => (bool) $product->is_featured,
                    'categoryId' => $product->tag ?: $product->category, // Use tag for filtering
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

    // Fetch ALL active products from database for bulk ordering
    // Include proper base URL for images
    $baseUrl = request()->getSchemeAndHttpHost();
    
    $products = \App\Models\Product::where('is_active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get()
        ->map(function($product) use ($baseUrl) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'tag' => $product->tag, // buff, chicken, veg, etc.
                'image' => $product->image ? $baseUrl . '/storage/' . $product->image : null,
            ];
        });

    // Bulk discount percentage
    $bulkDiscountPercentage = 15; // 15% discount for bulk orders

    return response()->json([
        'packages' => $packages,
        'products' => $products,
        'bulkDiscountPercentage' => $bulkDiscountPercentage,
    ]);
})->middleware('auth:sanctum');

