<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Placeholder methods
    public function index() {
        return response()->json(['message' => 'Product index']);
    }
    public function show($id) {
        return response()->json(['message' => 'Product show', 'id' => $id]);
    }
    public function inventory() {
        return response()->json(['message' => 'Product inventory']);
    }
} 