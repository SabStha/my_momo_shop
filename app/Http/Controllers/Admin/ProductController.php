<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
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
            // Store the file in storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;

            // In production, ensure the file is accessible via public/storage
            if (app()->environment('production')) {
                $sourcePath = storage_path('app/public/' . $path);
                $targetPath = public_path('storage/' . $path);
                
                // Ensure the target directory exists
                if (!file_exists(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0755, true);
                }
                
                // Copy the file if it doesn't exist in public/storage
                if (!file_exists($targetPath)) {
                    copy($sourcePath, $targetPath);
                }
            }
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
            // Delete old image from storage
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
                
                // In production, also delete from public/storage
                if (app()->environment('production')) {
                    $publicPath = public_path('storage/' . $product->image);
                    if (file_exists($publicPath)) {
                        unlink($publicPath);
                    }
                }
            }

            // Store the new image
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;

            // In production, ensure the file is accessible via public/storage
            if (app()->environment('production')) {
                $sourcePath = storage_path('app/public/' . $path);
                $targetPath = public_path('storage/' . $path);
                
                // Ensure the target directory exists
                if (!file_exists(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0755, true);
                }
                
                // Copy the file if it doesn't exist in public/storage
                if (!file_exists($targetPath)) {
                    copy($sourcePath, $targetPath);
                }
            }
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            // Delete from storage
            Storage::disk('public')->delete($product->image);
            
            // In production, also delete from public/storage
            if (app()->environment('production')) {
                $publicPath = public_path('storage/' . $product->image);
                if (file_exists($publicPath)) {
                    unlink($publicPath);
                }
            }
        }
        
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
} 