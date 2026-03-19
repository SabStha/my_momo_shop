<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Orchestrator
|--------------------------------------------------------------------------
| This file was split from a 1,400-line monolith into focused sub-files.
| To add routes, edit the appropriate sub-file, not this one.
|
| Sub-files:
|   auth.php     — Login, logout, register, password reset, payment/POS auth
|   public.php   — Home, menu, products, cart, checkout, payments (public)
|   user.php     — Authenticated customer account, orders, credits, themes
|   pos.php      — POS interface, POS API, employee clock-in/out, managers
|   creator.php  — Creator portal & admin creator management
|   investor.php — Investor self-service, admin CRUD, accounting
|   admin.php    — Full admin panel (dashboard → settings → everything)
|   debug.php    — Debug/test routes (LOCAL ONLY)
*/

// Production health check
if (app()->environment('production')) {
    require __DIR__.'/health.php';
}

require __DIR__.'/auth.php';
require __DIR__.'/public.php';
require __DIR__.'/user.php';
require __DIR__.'/pos.php';
require __DIR__.'/creator.php';
require __DIR__.'/investor.php';
require __DIR__.'/admin.php';

// Debug routes — LOCAL ENVIRONMENT ONLY
if (app()->environment('local')) {
    require __DIR__.'/debug.php';
}
