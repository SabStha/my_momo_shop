<?php
// Creator & Finds Feature — Backend complete, mobile implementation pending
// See docs/FINDS_CREATOR_FEATURE_TODO.md for full mobile build plan

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\CreatorDashboardController;

/*
|--------------------------------------------------------------------------
| Creator / Finds Routes
|--------------------------------------------------------------------------
*/

// ── Creator self-service (role: creator) ──────────────────────────────── 
Route::middleware(['auth', 'role:creator'])->group(function () {
    Route::get('/creator/dashboard', [CreatorDashboardController::class, 'index'])->name('creator.dashboard');
    Route::get('/creator/profile', [CreatorController::class, 'profile'])->name('creator.profile');
    Route::get('/creator/referrals', [CreatorController::class, 'referrals'])->name('creator.referrals');
    Route::get('/creator/earnings', [CreatorController::class, 'earnings'])->name('creator.earnings');
});

// ── Admin creator management ──────────────────────────────────────────── 
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('admin.creator-dashboard.index');
    Route::resource('creators', App\Http\Controllers\Admin\AdminCreatorController::class)->names('admin.creators');
});
