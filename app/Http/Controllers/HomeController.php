<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            // Get active products, ordered by latest first
            $products = Product::where('active', true)
                             ->latest()
                             ->get();

            // If no products found, log it but don't throw an error
            if ($products->isEmpty()) {
                Log::info('No active products found in the database');
            }

            return view('home', compact('products'));
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            // Return view with empty collection if there's an error
            return view('home', ['products' => collect()]);
        }
    }
}
