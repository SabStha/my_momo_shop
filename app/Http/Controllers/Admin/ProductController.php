<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Store a product image and return the relative path.
     */
    protected function storeProductImage($file)
    {
        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $relativePath = 'products/' . $filename;
        
        // Get absolute path to public storage
        $storagePath = public_path('storage/products');
        
        // Create directory if it doesn't exist
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        // Collect file information before moving
        $debugInfo = [
            'relative_path' => $relativePath,
            'absolute_path' => $storagePath . '/' . $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ];
        
        // Move the file to public storage
        $file->move($storagePath, $filename);
        
        // Log the file details for debugging
        Log::info('Product image stored', $debugInfo);
        
        return $relativePath;
    }

    /**
     * Delete a product image.
     */
    protected function deleteProductImage($path)
    {
        if (!$path) return;
        
        $absolutePath = public_path('storage/' . $path);
        
        if (file_exists($absolutePath)) {
            unlink($absolutePath);
            
            // Log the deletion
            Log::info('Product image deleted', [
                'relative_path' => $path,
                'absolute_path' => $absolutePath
            ]);
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

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
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

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $this->deleteProductImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
