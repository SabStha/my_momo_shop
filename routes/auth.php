<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CreatorController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', function (Request $request) {
    try {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }
        \Log::info('User logged out successfully', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip()
        ]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logged out successfully');
    } catch (\Exception $e) {
        \Log::error('Logout failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
        return redirect()->route('login')->with('error', 'Logout failed');
    }
})->name('logout')->middleware(['web', 'auth']);

// Registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Creator registration
Route::get('/creator/register', [CreatorController::class, 'showRegistrationForm'])->name('creator.register');
Route::post('/creator/register', [CreatorController::class, 'register'])->name('creator.register.submit');

// Password reset
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Payment authentication (separate login for the payment management panel)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/login', [App\Http\Controllers\PaymentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\PaymentAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\PaymentAuthController::class, 'logout'])->name('logout');
});

// POS login
Route::get('/pos/login', [App\Http\Controllers\PosAuthController::class, 'showLoginForm'])->name('pos.login');
Route::post('/pos/login', [App\Http\Controllers\PosAuthController::class, 'login'])->name('pos.login.submit');

// Mobile API auth shortcuts (direct, bypasses Sanctum for legacy compat)
Route::post('/mobile-api/auth/login', function (Request $request) {
    $request->validate([
        'emailOrPhone' => 'required|string',
        'password'     => 'required|string'
    ]);
    $credentials = ['password' => $request->password];
    if (filter_var($request->emailOrPhone, FILTER_VALIDATE_EMAIL)) {
        $credentials['email'] = $request->emailOrPhone;
    } else {
        $credentials['phone'] = $request->emailOrPhone;
    }
    if (Auth::attempt($credentials)) {
        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['success' => true, 'token' => $token, 'user' => $user->load('roles')]);
    }
    return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
})->name('api.auth.login.direct');

Route::post('/mobile-api/auth/register', function (Request $request) {
    $request->validate([
        'name'         => 'required|string|max:255',
        'emailOrPhone' => 'required|string|unique:users,email|unique:users,phone',
        'password'     => 'required|string|min:8|confirmed',
    ]);
    $userData = ['name' => $request->name, 'password' => Hash::make($request->password)];
    if (filter_var($request->emailOrPhone, FILTER_VALIDATE_EMAIL)) {
        $userData['email'] = $request->emailOrPhone;
    } else {
        $userData['phone'] = $request->emailOrPhone;
    }
    $user  = User::create($userData);
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['success' => true, 'token' => $token, 'user' => $user->load('roles')], 201);
})->name('api.auth.register.direct');
