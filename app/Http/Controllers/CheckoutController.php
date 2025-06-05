<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $productIds = collect($cart)->pluck('product_id')->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

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

    public function process(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock
        ]);

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total_amount' => $product->price * $request->quantity
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);

            // Update product stock
            $product->decrement('stock', $request->quantity);

            DB::commit();

            return redirect()->route('checkout.complete', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process order. Please try again.');
        }
    }

    public function complete(Order $order)
    {
        return view('checkout.complete', compact('order'));
    }

    public function thankyou()
    {
        return view('checkout.thankyou');
    }

    public function submit(Request $request)
    {
        $cart = session('cart', []);
        if (!count($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'delivery_method' => 'required|in:delivery,pickup',
            'payment_method' => 'required|in:cod,esewa,wallet',
            'wallet_payment_type' => 'required_if:payment_method,wallet|in:max,custom',
            'wallet_amount' => 'required_if:payment_method,wallet|numeric|min:0.01|max:' . (auth()->user()->wallet ? auth()->user()->wallet->balance : 0),
            'remaining_payment_method' => 'required_if:payment_method,wallet|in:cod,esewa',
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $productIds = collect($cart)->pluck('product_id')->all();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            
            $subtotal = 0;
            foreach ($cart as $item) {
                $product = $products[$item['product_id']] ?? null;
                if ($product) {
                    $subtotal += $product->price * $item['quantity'];
                }
            }

            $deliveryFee = 5.00;
            $discountAmount = session('coupon.discount_amount', 0);
            $total = $subtotal + $deliveryFee - $discountAmount;

            // Handle wallet payment
            $walletAmount = 0;
            if ($validated['payment_method'] === 'wallet' && auth()->check()) {
                $wallet = auth()->user()->wallet;
                if ($wallet) {
                    $walletAmount = $validated['wallet_payment_type'] === 'max' 
                        ? min($wallet->balance, $total)
                        : min($validated['wallet_amount'], $wallet->balance, $total);
                    
                    if ($walletAmount > 0) {
                        $wallet->decrement('balance', $walletAmount);
                    }
                }
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total_amount' => $total,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'wallet_amount' => $walletAmount,
                'payment_method' => $validated['payment_method'],
                'delivery_method' => $validated['delivery_method'],
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone'],
                'delivery_address' => $validated['address'],
            ]);

            // Create order items
            foreach ($cart as $item) {
                $product = $products[$item['product_id']] ?? null;
                if ($product) {
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'item_name' => $product->name,
                        'subtotal' => $product->price * $item['quantity'],
                    ]);

                    // Update product stock
                    $product->decrement('stock', $item['quantity']);
                }
            }

            // Clear cart and coupon session
            session()->forget(['cart', 'coupon', 'discount_amount']);

            DB::commit();

            return redirect()->route('checkout.complete', $order)
                           ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process order. Please try again.');
        }
    }

    public function quickCheckout(Request $request, Product $product)
    {
        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total_amount' => $product->price * $request->quantity,
                'delivery_fee' => 5.00,
                'customer_name' => auth()->user()->name,
                'customer_email' => auth()->user()->email,
                'customer_phone' => auth()->user()->phone,
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'item_name' => $product->name,
                'subtotal' => $product->price * $request->quantity,
            ]);

            // Update product stock
            $product->decrement('stock', $request->quantity);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'redirect_url' => route('products.show', $product)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }
} 