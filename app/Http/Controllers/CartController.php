<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('cart.index');
    }

    public function add(Request $request)
    {
        // Add to cart logic
    }

    public function remove(Request $request)
    {
        // Remove from cart logic
    }
} 