<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class PosProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }
} 