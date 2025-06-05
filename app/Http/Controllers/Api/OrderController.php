<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Placeholder methods
    public function index() {
        return response()->json(['message' => 'Order index']);
    }
    public function show($id) {
        return response()->json(['message' => 'Order show', 'id' => $id]);
    }
    public function store(Request $request) {
        return response()->json(['message' => 'Order store']);
    }
    public function update(Request $request, $id) {
        return response()->json(['message' => 'Order update', 'id' => $id]);
    }
    public function destroy($id) {
        return response()->json(['message' => 'Order destroy', 'id' => $id]);
    }
} 