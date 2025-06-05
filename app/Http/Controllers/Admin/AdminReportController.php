<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    // Placeholder methods
    public function index() {
        return response()->json(['message' => 'AdminReport index']);
    }
    public function generate() {
        return response()->json(['message' => 'AdminReport generate']);
    }
} 