<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Drink;
use App\Services\QRCodeService;

class ProductController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
        // Only protect create, store, edit, update, destroy
        $this->middleware(['auth', 'role:admin|employee'])->only([
            'create', 'store', 'edit', 'update', 'destroy'
        ]);
    }

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::where('active', true)
                          ->where('stock', '>', 0)
                          ->latest()
                          ->paginate(12);

        $categories = Product::whereNotNull('tag')
                           ->distinct()
                           ->pluck('tag')
                           ->map(fn($tag) => strtolower($tag))
                           ->unique()
                           ->values();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        Log::info('Accessing product create page');
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        if (!$product->active) {
            abort(404);
        }

        $relatedProducts = Product::where('active', true)
                                ->where('id', '!=', $product->id)
                                ->where('tag', $product->tag)
                                ->take(4)
                                ->get();

        return view('desktop.products.show', compact('product', 'relatedProducts'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function menu()
    {
        $products = \App\Models\Product::all();
        $tags = \App\Models\Product::query()
            ->whereNotNull('tag')
            ->distinct()
            ->pluck('tag')
            ->map(fn($tag) => strtolower($tag))
            ->unique()
            ->values();
        $featuredProducts = \App\Models\Product::where('is_featured', 1)->get();
        // Build categories as objects with name and products
        $categories = collect();
        foreach ($tags as $tag) {
            $categories->push((object)[
                'name' => $tag,
                'products' => \App\Models\Product::where('tag', $tag)->get()
            ]);
        }
        $drinks = \App\Models\Drink::all();
        return view('desktop.menu', compact('products', 'tags', 'featuredProducts', 'categories', 'drinks'));
    }

    /**
     * Generate QR code for a product
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateQRCode(Product $product)
    {
        try {
            // Create direct URL for the product using specific IP
            $url = 'http://192.168.2.157:8000/products/' . $product->id;

            // Generate QR code with the direct URL
            $qrCode = $this->qrCodeService->generateQRCode($url, 'product');

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show product details from QR code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showFromQR(Request $request)
    {
        try {
            $url = $request->input('url');
            
            if (!$url) {
                throw new \Exception('Invalid QR code data');
            }

            // Extract product ID from URL
            $productId = basename($url);
            $product = Product::findOrFail($productId);

            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate QR code for PWA installation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePWAQRCode()
    {
        try {
            // Create direct URL for PWA installation using specific IP
            $url = 'http://192.168.2.157:8000';

            // Generate QR code with the direct URL
            $qrCode = $this->qrCodeService->generateQRCode($url, 'pwa');

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display products by category.
     */
    public function category($category)
    {
        $products = Product::where('active', true)
                          ->where('stock', '>', 0)
                          ->where('category', $category)
                          ->latest()
                          ->paginate(12);

        return view('products.category', compact('products', 'category'));
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $products = collect();

        if ($query) {
            $products = Product::where('active', true)
                             ->where('stock', '>', 0)
                             ->where(function($q) use ($query) {
                                 $q->where('name', 'like', "%{$query}%")
                                   ->orWhere('description', 'like', "%{$query}%")
                                   ->orWhere('tag', 'like', "%{$query}%");
                             })
                             ->latest()
                             ->paginate(12);
        }

        return view('products.search', compact('products', 'query'));
    }

    /**
     * Live product autocomplete for search bar.
     */
    public function autocomplete(Request $request)
    {
        $query = $request->input('q');
        $products = [];
        if ($query) {
            $products = \App\Models\Product::where('active', true)
                ->where(function($q2) use ($query) {
                    $q2->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('tag', 'like', "%{$query}%");
                })
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'image']);
        }
        return response()->json($products);
    }
} 