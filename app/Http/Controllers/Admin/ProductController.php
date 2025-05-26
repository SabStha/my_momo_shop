<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Get the storage path for product images based on environment.
     */
    protected function getStoragePath()
    {
        return app()->environment('production') 
            ? public_path('storage/products')
            : storage_path('app/public/products');
    }

    /**
     * Store a product image and return the relative path.
     */
    protected function storeProductImage($file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $relativePath = 'products/' . $filename;
        $storagePath = $this->getStoragePath();

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        if (app()->environment('production')) {
            $file->move($storagePath, $filename);
        } else {
            Storage::disk('public')->putFileAs('products', $file, $filename);
        }

        // Optional logging
        Log::info('Image stored', [
            'env' => app()->environment(),
            'relative' => $relativePath,
            'absolute' => $storagePath . '/' . $filename
        ]);

        return $relativePath;
    }

    /**
     * Delete a product image from the correct environment.
     */
    protected function deleteProductImage($path)
    {
        if (!$path || !Str::startsWith($path, 'products/')) return;

        if (app()->environment('production')) {
            $filePath = public_path('storage/' . $path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } else {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Show all products.
     */
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show product creation form.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a new product.
     */
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

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Show edit form for a product.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the product.
     */
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
            $this->deleteProductImage($product->image);
            $validated['image'] = $this->storeProductImage($request->file('image'));
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $this->deleteProductImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
