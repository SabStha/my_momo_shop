<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        // Get user data if logged in
        $userData = null;
        if (Auth::check()) {
            $user = Auth::user();
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'ward_number' => $user->ward_number,
                'area_locality' => $user->area_locality,
                'building_name' => $user->building_name,
                'detailed_directions' => $user->detailed_directions,
                'preferred_branch_id' => $user->preferred_branch_id,
            ];
        }

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

        return view('checkout', compact('cartItems', 'subtotal', 'deliveryFee', 'total', 'userData'));
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
            'branch_id' => 'required|exists:branches,id',
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
                        // Use the WalletPaymentProcessor to properly handle the payment
                        $payment = \App\Models\Payment::create([
                            'order_id' => null, // Will be set after order creation
                            'user_id' => auth()->id(),
                            'amount' => $walletAmount,
                            'payment_method' => 'wallet',
                            'status' => 'pending',
                            'branch_id' => $validated['branch_id'],
                        ]);
                        
                        // Process the payment using the WalletPaymentProcessor
                        $walletProcessor = new \App\Services\Payment\Processors\WalletPaymentProcessor();
                        $result = $walletProcessor->process(['payment_id' => $payment->id]);
                        if (!$result['success']) {
                            throw new \Exception('Failed to process wallet payment: ' . $result['message']);
                        }
                    }
                }
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'branch_id' => $validated['branch_id'],
                'status' => 'pending',
                'order_type' => 'online', // Set order type for online orders
                'total_amount' => $total,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'wallet_payment' => $walletAmount,
                'payment_method' => $validated['payment_method'],
                'delivery_method' => $validated['delivery_method'],
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone'],
                'delivery_address' => $validated['address'],
            ]);

            // Update payment record with order ID if wallet payment was processed
            if (isset($payment) && $payment) {
                $payment->update(['order_id' => $order->id]);
                
                // Update order payment status if wallet payment was successful
                if ($walletAmount > 0) {
                    $order->update([
                        'payment_status' => 'paid',
                        'wallet_payment' => $walletAmount
                    ]);
                }
            }

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

    /**
     * Get available branches based on user location
     */
    public function getAvailableBranches(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;

        // Get all active branches (excluding main branch)
        $branches = Branch::where('is_active', true)
                         ->where('is_main', false)
                         ->whereNotNull('latitude')
                         ->whereNotNull('longitude')
                         ->get();

        $availableBranches = [];
        $nearestBranch = null;
        $shortestDistance = PHP_FLOAT_MAX;

        foreach ($branches as $branch) {
            $distance = $branch->distanceTo($lat, $lng);
            
            if ($distance !== null) {
                $isWithinRadius = $branch->isWithinDeliveryRadius($lat, $lng);
                $deliveryFee = $branch->getDeliveryFee($lat, $lng);
                
                $branchData = [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'distance' => round($distance, 2),
                    'delivery_radius' => $branch->delivery_radius_km ?? 5,
                    'is_within_radius' => $isWithinRadius,
                    'delivery_fee' => $deliveryFee,
                    'estimated_delivery_time' => $this->getEstimatedDeliveryTime($distance),
                    'contact_phone' => $branch->phone
                ];

                $availableBranches[] = $branchData;

                // Track nearest branch
                if ($distance < $shortestDistance) {
                    $shortestDistance = $distance;
                    $nearestBranch = $branchData;
                }
            }
        }

        // Sort by distance
        usort($availableBranches, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return response()->json([
            'success' => true,
            'branches' => $availableBranches,
            'nearest_branch' => $nearestBranch,
            'user_location' => [
                'latitude' => $lat,
                'longitude' => $lng
            ]
        ]);
    }

    /**
     * Get all available branches (without location requirements)
     */
    public function getAllBranches(Request $request)
    {
        // Get all active branches (excluding main branch)
        $branches = Branch::where('is_active', true)
                         ->where('is_main', false)
                         ->get();

        $allBranches = [];

        foreach ($branches as $branch) {
            $branchData = [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'delivery_fee' => $branch->delivery_fee ?? 0,
                'delivery_radius' => $branch->delivery_radius_km ?? 5,
                'contact_phone' => $branch->phone,
                'has_location' => !is_null($branch->latitude) && !is_null($branch->longitude)
            ];

            // If branch has location data and user provided coordinates, include distance info
            if ($branchData['has_location'] && $request->has('latitude') && $request->has('longitude')) {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $distance = $branch->distanceTo($lat, $lng);
                
                if ($distance !== null) {
                    $branchData['distance'] = round($distance, 2);
                    $branchData['is_within_radius'] = $branch->isWithinDeliveryRadius($lat, $lng);
                    $branchData['estimated_delivery_time'] = $this->getEstimatedDeliveryTime($distance);
                }
            } else {
                $branchData['distance'] = null;
                $branchData['is_within_radius'] = null;
                $branchData['estimated_delivery_time'] = 'Contact branch';
            }

            $allBranches[] = $branchData;
        }

        // Sort by name if no location data, otherwise by distance
        if (!$request->has('latitude') || !$request->has('longitude')) {
            usort($allBranches, function($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        } else {
            usort($allBranches, function($a, $b) {
                if ($a['distance'] === null && $b['distance'] === null) {
                    return $a['name'] <=> $b['name'];
                }
                if ($a['distance'] === null) return 1;
                if ($b['distance'] === null) return -1;
                return $a['distance'] <=> $b['distance'];
            });
        }

        return response()->json([
            'success' => true,
            'branches' => $allBranches,
            'total_branches' => count($allBranches)
        ]);
    }

    /**
     * Get estimated delivery time based on distance
     */
    private function getEstimatedDeliveryTime($distance)
    {
        if ($distance <= 2) {
            return '20-30 minutes';
        } elseif ($distance <= 5) {
            return '30-45 minutes';
        } elseif ($distance <= 10) {
            return '45-60 minutes';
        } else {
            return '60-90 minutes';
        }
    }
} 