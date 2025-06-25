<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display the cart page
     */
    public function index()
    {
        try {
            // Get featured products for suggestions
            $suggestedProducts = Product::where('is_featured', true)
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->take(4)
                ->get();

            return view('cart.index', compact('suggestedProducts'));
        } catch (\Exception $e) {
            Log::error('Error loading cart page: ' . $e->getMessage());
            return view('cart.index', ['suggestedProducts' => collect()]);
        }
    }

    /**
     * Get cart data via AJAX
     */
    public function getCart(Request $request)
    {
        try {
            // In a real application, you might store cart in session or database
            // For now, we'll return empty cart structure
            return response()->json([
                'items' => [],
                'total' => 0,
                'itemCount' => 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting cart data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load cart'], 500);
        }
    }

    /**
     * Add item to cart via AJAX
     */
    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $product = Product::findOrFail($request->product_id);
            
            // Check if product is available
            if (!$product->is_active || $product->stock <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available'
                ], 400);
            }

            // In a real application, you would add to session cart or database
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => $product->name . ' added to cart',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image,
                    'quantity' => $request->quantity
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding to cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart'
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:0'
            ]);

            $product = Product::findOrFail($request->product_id);
            
            if ($request->quantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity exceeds available stock'
                ], 400);
            }

            // In a real application, you would update the cart
            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating cart quantity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity'
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            // In a real application, you would remove from cart
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing from cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item'
            ], 500);
        }
    }

    /**
     * Clear cart
     */
    public function clearCart()
    {
        try {
            // In a real application, you would clear the cart
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart'
            ], 500);
        }
    }

    /**
     * Get suggested products for cart
     */
    public function getSuggestions()
    {
        try {
            $suggestions = Product::where('is_featured', true)
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->take(4)
                ->get(['id', 'name', 'price', 'image']);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting suggestions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'suggestions' => []
            ]);
        }
    }

    /**
     * Add a product to the cart (legacy method)
     */
    public function add(Request $request, Product $product)
    {
        if (!$request->expectsJson()) {
            return redirect()->back()->with('info', 'Please use the site normally.');
        }

        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session()->get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ];
        }
        
        session(['cart' => $cart]);
        return response()->json(['success' => true, 'message' => 'Product added to cart!']);
    }

    /**
     * Update the quantity of a cart item
     */
    public function update(Request $request, $id)
    {
        $quantity = max(0, (int) $request->input('quantity', 0));
        $cart = session()->get('cart', []);
        
        if ($quantity == 0) {
            unset($cart[$id]);
        } else {
            $cart[$id]['quantity'] = $quantity;
        }
        
        session(['cart' => $cart]);
        return response()->json(['success' => true, 'message' => 'Cart updated!']);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session(['cart' => $cart]);
        return response()->json(['success' => true, 'message' => 'Item removed from cart!']);
    }

    /**
     * Buy now - direct checkout
     */
    public function buyNow(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = [
            $product->id => [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]
        ];
        session(['cart' => $cart]);
        return redirect()->route('checkout');
    }

    /**
     * Show checkout page
     */
    public function checkout(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }

        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $cartItems = [];
        $total = 0;

        foreach ($cart as $item) {
            $product = $products[$item['product_id']] ?? null;
            if ($product) {
                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                ];
                $total += $product->price * $item['quantity'];
            }
        }

        return view('checkout', [
            'cart' => $cartItems,
            'total' => $total,
        ]);
    }

    /**
     * Process checkout
     */
    public function checkoutSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'payment_method' => 'required|in:cash,card,online'
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }

        // Here you would typically:
        // 1. Create an order in the database
        // 2. Process payment
        // 3. Clear the cart
        // 4. Send confirmation email

        // For now, we'll just clear the cart and redirect
        session()->forget('cart');
        
        return redirect()->route('home')->with('success', 'Order placed successfully! We\'ll contact you soon.');
    }

    /**
     * Show order confirmation
     */
    public function confirmation(\App\Models\Order $order)
    {
        return view('orders.confirmation', compact('order'));
    }
} 