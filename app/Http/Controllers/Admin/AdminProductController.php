<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class AdminProductController extends Controller
{
    // Placeholder methods
    public function index() {
        $products = Product::latest()->paginate(10);
        return view('desktop.admin.products.index', compact('products'));
    }
} 