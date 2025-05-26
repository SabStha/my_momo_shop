<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Get the storage path for product images based on environment
     */
    protected function getStoragePath()
    {
        return app()->environment('production') 
            ? public_path('storage/products')
            : storage_path('app/public/products');
    }

    /**
     * Store a product image and return the relative path
     */
    protected function storeProductImage($file)
    {
        // Generate a unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $relativePath = 'products/' . $filename;

        // Get the full storage path based on environment
        $storagePath = $this->getStoragePath();
        
        // Ensure the directory exists
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // Store the file
        if (app()->environment('production')) {
            // In production, store directly in public/storage
            $file->move($storagePath, $filename);
        } else {
            // In local, use Laravel's storage
            Storage::disk('public')->putFileAs('products', $file, $filename);
        }

        return $relativePath;
    }

    /**
     * Delete a product image
     */
    protected function deleteProductImage($path)
    {
        if (!$path) return;

        // Delete from Laravel storage
        Storage::disk('public')->delete($path);

        // In production, also delete from public/storage
        if (app()->environment('production')) {
            $publicPath = public_path('storage/' . $path);
            if (file_exists($publicPath)) {
                unlink($publicPath);
            }
        }
    }

    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'tag' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeProductImage($request->file('image'));
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'tag' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            $this->deleteProductImage($product->image);
            
            // Store new image
            $validated['image'] = $this->storeProductImage($request->file('image'));
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->deleteProductImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
} 