<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Debug / Test Routes (LOCAL ENVIRONMENT ONLY)
|--------------------------------------------------------------------------
| These routes are only registered in the local environment.
| They are NEVER available in production or staging.
*/

// Cart & session test
Route::any('/test-logging', function () {
    \Log::info('🧪 TEST LOGGING ROUTE HIT - ' . now());
    return response()->json(['message' => 'Test logging route hit', 'timestamp' => now()]);
});

Route::get('/test-session', function () {
    $sessionId       = session()->getId();
    $isAuthenticated = Auth::check();
    $user            = Auth::user();
    \Log::info('🧪 SESSION TEST', [
        'session_id'      => $sessionId,
        'is_authenticated'=> $isAuthenticated,
        'user_id'         => $user ? $user->id : null,
        'user_email'      => $user ? $user->email : null,
    ]);
    return response()->json([
        'session_id'      => $sessionId,
        'is_authenticated'=> $isAuthenticated,
        'user_id'         => $user ? $user->id : null,
        'user_email'      => $user ? $user->email : null,
        'timestamp'       => now(),
    ]);
});

// Auth & user debug
Route::get('/debug-user', function () {
    if (!Auth::check()) return 'NOT LOGGED IN';
    $user = Auth::user();
    return response()->json([
        'user_id'            => $user->id,
        'name'               => $user->name,
        'email'              => $user->email,
        'roles'              => $user->getRoleNames(),
        'has_admin'          => $user->hasRole('admin'),
        'has_investor'       => $user->hasRole('investor'),
        'has_investor_profile'=> $user->investor ? 'Yes (ID: ' . $user->investor->id . ')' : 'No',
    ]);
})->middleware('auth');

Route::get('/test-admin', function () {
    if (!auth()->check()) return 'Not logged in';
    $user = auth()->user();
    return response()->json([
        'logged_in'  => true,
        'user_email' => $user->email,
        'is_admin'   => $user->hasRole('admin'),
        'user_roles' => $user->getRoleNames()->toArray(),
    ]);
})->middleware('auth');

Route::get('/admin-login-helper', function () {
    $adminUser = \App\Models\User::role('admin')->first();
    if (!$adminUser) return 'No admin user found';
    return view('admin-login-helper', [
        'admin_email'    => $adminUser->email,
        'admin_password' => 'password',
    ]);
});

// Order / product debug
Route::post('/debug/order-creation', [\App\Http\Controllers\OrderController::class, 'debugOrderCreation'])->name('debug.order-creation');
Route::post('/debug-order', [\App\Http\Controllers\OrderController::class, 'debugOrder'])->name('debug.order');
Route::get('/debug-products', [\App\Http\Controllers\OrderController::class, 'debugProducts'])->name('debug.products');

Route::get('/test-orders', function () {
    try {
        $orders = \App\Models\Order::where('branch_id', 1)
            ->where('status', '!=', 'completed')
            ->with(['items.product', 'table', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'count' => $orders->count(), 'orders' => $orders->take(5)->toArray()]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

// Admin debug
Route::get('/admin/test', fn() => view('admin.investors.test'))->middleware(['auth', 'admin'])->name('admin.test');

Route::get('/admin/debug-employees', function () {
    $employees = \App\Models\Employee::with('user')->get();
    return response()->json($employees->map(fn($emp) => [
        'id'        => $emp->id,
        'name'      => $emp->user->name,
        'branch_id' => $emp->branch_id,
    ]));
})->middleware(['auth', 'admin']);

Route::get('/admin/inventory/orders/supplier-view-debug', function () {
    $branch = \App\Models\Branch::find(session('selected_branch_id'));
    $user   = auth()->user();
    return response()->json([
        'authenticated'  => auth()->check(),
        'user_role'      => $user ? $user->roles->pluck('name')->first() : 'none',
        'selected_branch'=> $branch ? ['id' => $branch->id, 'name' => $branch->name, 'is_main' => $branch->is_main] : null,
        'all_branches'   => \App\Models\Branch::all()->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'is_main' => $b->is_main]),
    ]);
})->middleware(['auth', 'admin'])->name('inventory.orders.supplier-view-debug');

Route::get('/admin/test-product', function () {
    return response()->json(['success' => true, 'message' => 'Test route working']);
})->middleware(['auth', 'admin'])->name('test.product');

Route::get('/admin/bulk-packages/debug', function () {
    return response()->json(\App\Models\BulkPackage::all()->toArray());
})->middleware(['auth', 'admin']);

Route::post('/admin/test-bulk-package-form', function (\Illuminate\Http\Request $request) {
    return response()->json(['success' => true, 'data' => $request->all(), 'message' => 'Form data received successfully']);
})->middleware(['auth', 'admin']);

Route::get('/admin/test-bulk-package-form', fn() => view('admin.bulk-packages.test-form'))->middleware(['auth', 'admin']);

// QR test
Route::get('/test-qr', function () {
    $user = \App\Models\User::find(31);
    if ($user && $user->creditsAccount) {
        $qrCode = $user->creditsAccount->generateQRCode();
        return response()->json([
            'success'        => true,
            'qr_code'        => $qrCode,
            'is_url'         => filter_var($qrCode, FILTER_VALIDATE_URL),
            'account_number' => $user->creditsAccount->account_number,
        ]);
    }
    return response()->json(['success' => false, 'message' => 'No credits account found']);
});
