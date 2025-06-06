<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    // Placeholder methods
    public function index() {
        return response()->json(['message' => 'Analytics index']);
    }
    public function sales() {
        return response()->json(['message' => 'Analytics sales']);
    }
    public function products() {
        return response()->json(['message' => 'Analytics products']);
    }
    public function reports() {
        return response()->json(['message' => 'Analytics reports']);
    }
} 