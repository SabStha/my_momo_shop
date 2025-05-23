<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('cart.index');
    }

    // Add to cart
    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session()->get('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ];
        }
        session(['cart' => $cart]);
        return response()->json(['success' => true, 'message' => 'Product added to cart!']);
    }

    // Buy now: add to cart and go to checkout
    public function buyNow(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session()->get('cart', []);
        $cart[$product->id] = [
            'product_id' => $product->id,
            'quantity' => $quantity,
        ];
        session(['cart' => $cart]);
        return redirect()->route('checkout')->with('success', 'Ready to checkout!');
    }

    // Show cart
    public function show()
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();
        return view('cart.show', compact('cart', 'products'));
    }

    // Checkout page
    public function checkout()
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();
        $cartItems = [];
        $subtotal = 0;
        foreach ($products as $product) {
            $qty = $cart[$product->id]['quantity'];
            $cartItems[] = (object) [
                'product' => $product,
                'quantity' => $qty,
            ];
            $subtotal += $qty * $product->price;
        }
        $deliveryFee = 2.00; // Flat delivery fee, adjust as needed
        $total = $subtotal + $deliveryFee;
        return view('checkout', compact('cartItems', 'subtotal', 'deliveryFee', 'total'));
    }

    public function remove(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session(['cart' => $cart]);
            return redirect()->route('cart.show')->with('success', 'Product removed from cart.');
        }
        return redirect()->route('cart.show')->with('info', 'Product not found in cart.');
    }

    public function checkoutSubmit(Request $request)
    {
        $cart = session('cart', []);
        if (!count($cart)) {
            return redirect()->route('cart.show')->with('info', 'Your cart is empty.');
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
        ]);
        // Create order
        $order = new \App\Models\Order();
        if (auth()->check()) {
            $order->user_id = auth()->id();
        }
        $order->type = 'online'; // Always set type to online for online checkout
        $order->order_number = 'ORD-' . strtoupper(uniqid());
        $order->total_amount = $total;
        $order->tax_amount = $total * 0.13;
        $order->grand_total = $order->total_amount + $order->tax_amount;
        $order->status = 'pending';
        $order->shipping_address = $validated['address'];
        $order->billing_address = $validated['address'];
        $order->payment_method = 'cash';
        $order->payment_status = 'pending';
        $order->save();
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
        return redirect()->route('checkout.confirmation', $order)->with('success', 'Order placed successfully!');
    }

    public function confirmation(\App\Models\Order $order)
    {
        return view('cart.confirmation', compact('order'));
    }
} 