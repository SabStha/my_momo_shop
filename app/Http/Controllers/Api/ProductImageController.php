<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Get product image by product ID
     */
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        if (!$product->image) {
            return response()->json(['error' => 'Product has no image'], 404);
        }
        
        // Return the image path that can be used in the frontend
        return response()->json([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'image_path' => $product->image,
            'image_url' => url('storage/' . $product->image)
        ]);
    }
    
    /**
     * Get all products with their images
     */
    public function index()
    {
        $products = Product::whereNotNull('image')
            ->select('id', 'name', 'image', 'tag', 'category')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_path' => $product->image,
                    'image_url' => url('storage/' . $product->name),
                    'tag' => $product->tag,
                    'category' => $product->category
                ];
            });
        
        return response()->json([
            'products' => $products,
            'count' => $products->count()
        ]);
    }
    
    /**
     * Get product images by tag/category
     */
    public function byCategory($category)
    {
        $products = Product::whereNotNull('image')
            ->where('tag', $category)
            ->orWhere('category', $category)
            ->select('id', 'name', 'image', 'tag', 'category')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_path' => $product->image,
                    'image_url' => url('storage/' . $product->image),
                    'tag' => $product->tag,
                    'category' => $product->category
                ];
            });
        
        return response()->json([
            'category' => $category,
            'products' => $products,
            'count' => $products->count()
        ]);
    }
}
