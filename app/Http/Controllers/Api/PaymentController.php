<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Placeholder methods
    public function store(Request $request) {
        return response()->json(['message' => 'Payment store']);
    }
    public function show($id) {
        return response()->json(['message' => 'Payment show', 'id' => $id]);
    }
} 