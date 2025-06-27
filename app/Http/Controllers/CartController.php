<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\BulkPackage;
use App\Services\CouponService;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display the cart page (now fully client-side)
     */
    public function index(Request $request)
    {
        // Only pass suggested products for upsell
        $suggestedProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->take(4)
            ->get();
        return view('cart.index', compact('suggestedProducts'));
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
            \Log::error('Error getting suggestions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'suggestions' => []
            ]);
        }
    }

    /**
     * Show checkout page (cart data comes from POSTed localStorage)
     */
    public function checkout(Request $request)
    {
        // Only handle GET requests - cart data will be read from localStorage by JavaScript
        return view('checkout');
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
    
    /**
     * Debug method to log cart status
     */
    public function debugCartStatus(Request $request)
    {
        $data = $request->all();
        
        \Log::info('CART DEBUG - ' . ($data['action'] ?? 'unknown_action'), [
            'action' => $data['action'] ?? 'unknown',
            'checkout_cart' => $data['checkout_cart'] ?? 'not_set',
            'momo_cart' => $data['momo_cart'] ?? 'not_set',
            'cartManager_available' => $data['cartManager_available'] ?? false,
            'cartItems' => $data['cartItems'] ?? [],
            'subtotal' => $data['subtotal'] ?? 0,
            'deliveryFee' => $data['deliveryFee'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'total' => $data['total'] ?? 0,
            'timestamp' => now(),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip()
        ]);
        
        return response()->json(['status' => 'logged']);
    }
} 