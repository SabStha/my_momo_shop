<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CouponService;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    // Show the cart page
    public function index(Request $request)
    {
        $cart = session('cart', []);
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
                    'image' => $product->image, // adjust if your image field is named differently
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                ];
                $total += $product->price * $item['quantity'];
            }
        }

        return view('desktop.cart', [
            'cart' => $cartItems,
            'total' => $total,
        ]);
    }

    // Add a product to the cart
    public function add(Request $request, Product $product)
    {
        if (!$request->expectsJson()) {
            // Fallback for non-AJAX requests
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

    // Update the quantity of a cart item
    public function update(Request $request, $id)
    {
        // TODO: Implement update cart logic
    }

    // Remove an item from the cart
    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session(['cart' => $cart]);
            return redirect()->route('cart')->with('success', 'Product removed from cart.');
        }
        return redirect()->route('cart')->with('info', 'Product not found in cart.');
    }

    // Buy now: add to cart and go to checkout
    public function buyNow(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session()->get('cart', []);
        $cart[$product->id] = [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
        ];
        session(['cart' => $cart]);
        return redirect()->route('checkout')->with('success', 'Ready to checkout!');
    }

    // Checkout page
    public function checkout(Request $request)
    {
        // Only clear coupon and discount session data if not coming from coupon apply
        if (!$request->session()->has('coupon_applied')) {
            session()->forget('coupon');
            session()->forget('discount_amount');
        } else {
            // Remove the flag so it doesn't persist
            $request->session()->forget('coupon_applied');
        }

        $cart = session('cart', []);
        $productIds = collect($cart)->pluck('product_id')->all();
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

        $cartItems = [];
        $subtotal = 0;

        foreach ($cart as $item) {
            $product = $products[$item['product_id']] ?? null;
            if ($product) {
                $cartItems[] = (object)[
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];
                $subtotal += $product->price * $item['quantity'];
            }
        }

        $deliveryFee = 5.00;
        $discountAmount = session('coupon.discount_amount', 0);
        $total = $subtotal + $deliveryFee - $discountAmount;

        return view('desktop.checkout', compact('cartItems', 'subtotal', 'deliveryFee', 'total'));
    }

    public function checkoutSubmit(Request $request)
    {
        $cart = session('cart', []);
        if (!count($cart)) {
            return redirect()->route('cart')->with('info', 'Your cart is empty.');
        }
        $products = Product::whereIn('id', array_keys($cart))->get();
        $total = 0;
        foreach ($products as $product) {
            $qty = $cart[$product->id]['quantity'];
            $total += $qty * $product->price;
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'address' => 'required|string|max:255',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|in:cash,esewa,wallet',
            'wallet_payment_type' => 'required_if:payment_method,wallet|in:max,custom',
            'wallet_amount' => 'required_if:wallet_payment_type,custom|numeric|min:0.01',
            'remaining_payment_method' => 'required_if:payment_method,wallet|in:cod,esewa',
        ]);

        // Handle wallet payment
        if ($validated['payment_method'] === 'wallet') {
            if (!auth()->check()) {
                return redirect()->back()->with('error', 'You must be logged in to use wallet payment.');
            }
            
            $wallet = auth()->user()->wallet;
            if (!$wallet) {
                return redirect()->back()->with('error', 'Wallet not found. Please contact support.');
            }

            // Calculate wallet payment amount
            $walletAmount = $validated['wallet_payment_type'] === 'max' 
                ? min($wallet->balance, $total)
                : min($validated['wallet_amount'], $wallet->balance, $total);
            
            if ($walletAmount <= 0) {
                return redirect()->back()->with('error', 'Invalid wallet payment amount.');
            }
        }

        // Coupon logic
        $discount = 0;
        $discountType = null;
        $couponMessage = null;
        // Auto-apply creator coupon if referral_code exists in session and no coupon_code provided
        if (empty($validated['coupon_code']) && session('referral_code')) {
            $creator = \App\Models\Creator::where('code', session('referral_code'))->first();
            if ($creator) {
                $coupon = \App\Models\Coupon::where('campaign_name', $creator->code)->first();
                if ($coupon) {
                    $validated['coupon_code'] = $coupon->code;
                }
            }
        }
        if (!empty($validated['coupon_code'])) {
            $couponService = new CouponService();
            $user = auth()->user();
            if ($user) {
                $result = $couponService->validateAndRedeem($user, $validated['coupon_code'], ['is_shop' => false]);
                if ($result['success']) {
                    $discountType = $result['discount_type'];
                    if ($discountType === 'fixed') {
                        $discount = $result['discount'];
                    } elseif ($discountType === 'percent') {
                        $discount = $total * ($result['discount'] / 100);
                    }
                    $couponMessage = $result['message'];
                } else {
                    $couponMessage = $result['message'];
                }
            } else {
                $couponMessage = 'You must be logged in to use a coupon.';
            }
        }

        // Create order
        $order = new \App\Models\Order();
        if (auth()->check()) {
            $order->user_id = auth()->id();
        }
        $order->type = 'online'; // Always set type to online for online checkout
        $order->order_number = 'ORD-' . strtoupper(uniqid());
        $order->total_amount = $total;
        $order->tax_amount = $total * 0.13;
        $order->discount_amount = $discount;
        $order->grand_total = $order->total_amount + $order->tax_amount - $discount;
        $order->status = 'pending';
        $order->shipping_address = $validated['address'];
        $order->billing_address = $validated['address'];
        $order->payment_method = $validated['payment_method'] === 'wallet' 
            ? $validated['remaining_payment_method'] 
            : $validated['payment_method'];
        $order->payment_status = ($validated['payment_method'] === 'wallet' && $walletAmount >= $total) 
            ? 'paid' 
            : 'pending';
        $order->save();

        // Process wallet payment if selected
        if ($validated['payment_method'] === 'wallet') {
            try {
                $wallet->balance -= $walletAmount;
                $wallet->save();
                
                // Record wallet transaction
                $wallet->transactions()->create([
                    'amount' => $walletAmount,
                    'type' => 'debit',
                    'description' => 'Payment for order #' . $order->order_number . 
                        ($walletAmount < $total ? ' (Partial Payment)' : ''),
                ]);

                // Update the order with wallet payment details
                $order->wallet_payment = $walletAmount;
                $order->remaining_payment = $total - $walletAmount;
                $order->save();
            } catch (\Exception $e) {
                Log::error('Wallet payment failed: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Payment failed. Please try again or choose another payment method.');
            }
        }

        // Save guest info if not logged in
        if (!auth()->check()) {
            $order->guest_name = $validated['name'];
            $order->guest_email = $validated['email'];
            $order->save();
        }
        // Create order items
        foreach ($products as $product) {
            $qty = $cart[$product->id]['quantity'];
            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $product->price,
                'item_name' => $product->name,
                'subtotal' => $qty * $product->price,
            ]);
        }
        // Clear cart
        session()->forget('cart');
        $redirect = redirect()->route('checkout.confirmation', $order)->with('success', 'Order placed successfully!');
        if ($couponMessage) {
            $redirect->with('coupon_message', $couponMessage);
        }
        return $redirect;
    }

    public function confirmation(\App\Models\Order $order)
    {
        return view('desktop.thankyou', compact('order'));
    }
} 