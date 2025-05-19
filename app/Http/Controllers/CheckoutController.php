<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function store(Request $request)
    {
        // Process checkout logic
    }

    public function thankyou()
    {
        return view('checkout.thankyou');
    }
} 