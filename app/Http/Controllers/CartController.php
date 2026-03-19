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
        if (\Illuminate\Support\Facades\Auth::check()) {
            $userCart = \Illuminate\Support\Facades\Auth::user()->getOrCreateCart();
            $dbCart = $userCart->cart_data ?? [];
            if (!empty($dbCart)) {
                session(['cart' => $dbCart]);
            }
        }

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
        // Get authenticated user data for auto-filling the form
        $user = auth()->user();
        $userData = null;
        
        if ($user) {
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city ?? '',
                'ward_number' => $user->ward_number ?? '',
                'area_locality' => $user->area_locality ?? '',
                'building_name' => $user->building_name ?? '',
                'detailed_directions' => $user->detailed_directions ?? '',
            ];
        }
        
        return view('checkout', compact('userData'));
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
            'city' => 'required|string|max:255',
            'ward_number' => 'required|string|max:50',
            'area_locality' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'detailed_directions' => 'nullable|string|max:1000',
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
     * Add item to cart (supports both products and bulk packages)
     */
    public function addToCart(Request $request)
    {
        try {
            // Must be logged in
            if (!auth('web')->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $user = auth('web')->user();
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            // Handle bulk packages or regular products
            if (str_starts_with($productId, 'bulk-')) {
                $bulkPackageId = str_replace('bulk-', '', $productId);
                $package = BulkPackage::findOrFail($bulkPackageId);
                $itemId = $productId;
                $itemName = $package->name;
                $itemPrice = $package->total_price;
                $itemImage = null;
                $itemType = 'bulk';
            } else {
                $product = Product::findOrFail($productId);
                $itemId = (string)$product->id;
                $itemName = $product->name;
                $itemPrice = $product->price;
                $itemImage = $product->image;
                $itemType = 'product';
            }

            $userCart = $user->getOrCreateCart();
            $cart = $userCart->cart_data ?? [];

            // Check if product already in cart
            $found = false;
            foreach ($cart as &$item) {
                if ((string)$item['id'] === (string)$itemId) {
                    $item['quantity'] += (int)$quantity;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $cart[] = [
                    'id' => (string)$itemId,
                    'name' => $itemName,
                    'price' => (float)$itemPrice,
                    'quantity' => (int)$quantity,
                    'image' => $itemImage,
                    'type' => $itemType
                ];
            }

            // Save to database (single source of truth)
            $userCart->updateCart($cart);

            // Also update session to keep web UI in sync
            session(['cart' => $cart]);

            \Log::info('Cart written to DB', ['user_id' => $user->id, 'count' => count($cart)]);

            return response()->json([
                'success' => true,
                'message' => $itemName . ' added to cart!',
                'cart_count' => count($cart)
            ]);

        } catch (\Exception $e) {
            \Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Update item quantity in cart
     */
    public function updateQuantity(Request $request)
    {
        try {
            if (!auth('web')->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $request->validate([
                'product_id' => 'required|string',
                'quantity' => 'required|integer|min:1',
            ]);

            $productId = $request->input('product_id');
            $quantity = $request->input('quantity');
            
            $user = auth('web')->user();
            $userCart = $user->getOrCreateCart();
            $cart = $userCart->cart_data ?? [];
            
            $updated = false;
            foreach ($cart as &$item) {
                if (isset($item['id']) && (string) $item['id'] === (string) $productId) {
                    $item['quantity'] = (int)$quantity;
                    $updated = true;
                    break;
                }
            }

            if ($updated) {
                // Save to DB
                $userCart->updateCart($cart);
                // Sync to session
                session(['cart' => $cart]);

                \Log::info('Cart written to DB (update)', ['user_id' => $user->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Quantity updated',
                    'cart' => $cart,
                    'cart_count' => count($cart)
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Item not found in cart'], 404);

        } catch (\Exception $e) {
            \Log::error('Update cart quantity error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update quantity'], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        try {
            if (!auth('web')->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $request->validate(['product_id' => 'required|string']);
            $productId = $request->input('product_id');
            
            $user = auth('web')->user();
            $userCart = $user->getOrCreateCart();
            $cart = $userCart->cart_data ?? [];

            $cart = array_filter($cart, function ($item) use ($productId) {
                return isset($item['id']) && (string) $item['id'] !== (string) $productId;
            });

            // Re-index array
            $cart = array_values($cart);
            
            // Save to DB
            $userCart->updateCart($cart);
            // Sync to session
            session(['cart' => $cart]);

            \Log::info('Cart written to DB (remove)', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => $cart,
                'cart_count' => count($cart)
            ]);

        } catch (\Exception $e) {
            \Log::error('Remove item from cart error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to remove item'], 500);
        }
    }

    /**
     * Clear the cart
     */
    public function clearCart(Request $request)
    {
        try {
            if (!auth('web')->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $user = auth('web')->user();
            $userCart = $user->getOrCreateCart();
            
            // Clear in DB
            $userCart->updateCart([]);
            // Clear in session
            session()->forget('cart');

            \Log::info('Cart written to DB (clear)', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared',
                'cart' => [],
                'cart_count' => 0
            ]);

        } catch (\Exception $e) {
            \Log::error('Clear cart error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to clear cart'], 500);
        }
    }

    /**
     * Get the authenticated user's cart from the database.
     * Used by the mobile app as GET /cart/items.
     */
    public function getCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $userCart = auth()->user()->getOrCreateCart();
            $items = $userCart->cart_data ?? [];

            return response()->json([
                'success' => true,
                'cart' => [
                    'items' => $items,
                    'item_count' => $userCart->getItemCount(),
                    'subtotal' => $userCart->getSubtotal(),
                ],
                'cart_count' => $userCart->getItemCount(),
            ]);
        } catch (\Exception $e) {
            \Log::error('CartController@getCart error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch cart'], 500);
        }
    }

    /**
     * Sync (replace) the authenticated user's cart in the database.
     * Used by the mobile app as POST /cart/sync — accepts full cart array.
     */
    public function syncCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $request->validate([
                'items' => 'required|array',
            ]);

            $items = $request->input('items', []);

            $userCart = auth()->user()->getOrCreateCart();
            $userCart->updateCart($items);

            // Also sync to session
            session(['cart' => $items]);

            return response()->json([
                'success' => true,
                'message' => 'Cart synced successfully',
                'cart' => $items,
                'cart_count' => count($items),
            ]);
        } catch (\Exception $e) {
            \Log::error('CartController@syncCart error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to sync cart'], 500);
        }
    }
} 