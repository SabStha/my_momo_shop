<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminProductController extends Controller
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
        try {
            \Log::info('Product creation started', ['request' => $request->all()]);
            
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:products,code',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category' => 'nullable|string|max:100',
                'tag' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'is_menu_highlight' => 'boolean',
            ]);
            
            \Log::info('Validation passed', ['validated' => $validated]);
            
            // Get current branch ID
            $branchId = session('current_branch_id', 1);
            \Log::info('Using branch ID', ['branch_id' => $branchId]);
            
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                \Log::info('Image uploaded', ['path' => $imagePath]);
            }
            
            // Create the product
            $product = Product::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'category' => $validated['category'] ?? '',
                'tag' => $validated['tag'] ?? '',
                'description' => $validated['description'] ?? '',
                'image' => $imagePath,
                'is_active' => $request->has('is_active'),
                'is_featured' => $request->has('is_featured'),
                'branch_id' => $branchId,
                // Set default values for other required fields
                'cost_price' => 0,
                'unit' => 'piece',
                'points' => 0,
                'tax_rate' => 0,
                'discount_rate' => 0,
                'attributes' => json_encode([]),
                'notes' => '',
            ]);
            
            \Log::info('Product created successfully', ['product_id' => $product->id]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully!',
                    'product' => $product
                ]);
            }
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->errors()]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Product creation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create product: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to create product: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Product $product)
    {
        \Log::info('Edit method called', ['product_id' => $product->id, 'product_name' => $product->name]);
        
        // Test with a simple view first
        return view('admin.products.test', compact('product'));
        
        // If the above doesn't work, try this simple test:
        // return response()->json(['message' => 'Edit method working', 'product' => $product->name]);
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:products,code,' . $product->id,
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'tag' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
            ]);

            // Handle checkbox values
            $validated['is_active'] = $request->has('is_active');
            $validated['is_featured'] = $request->has('is_featured');

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $request->file('image')->store('products', 'public');
                $validated['image'] = $imagePath;
            }

            $product->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update product',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            if ($product->image) {
                Storage::delete('public/' . $product->image);
            }

            $product->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting product: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Error deleting product');
        }
    }
} 