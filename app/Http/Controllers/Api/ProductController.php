<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => (float) $product->price,
                        'description' => $product->description,
                        'image' => $product->image,
                        'is_featured' => (bool) $product->is_featured,
                        'is_menu_highlight' => (bool) $product->is_menu_highlight,
                        'stock' => (int) $product->stock
                    ];
                });

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching products'], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    public function inventory()
    {
        try {
            $products = Product::where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching inventory'], 500);
        }
    }
} 