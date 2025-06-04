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
} 