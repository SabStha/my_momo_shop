<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Coupon;
use App\Events\OrderPlaced;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Generate a unique order number in the format ORD-YYYYMMDD-XXXXX
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        do {
            $datePart = date('Ymd');
            $randomPart = strtoupper(Str::random(5));
            $orderNumber = "ORD-{$datePart}-{$randomPart}";
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Calculate order total based on items and optional coupon
     *
     * @param array $items Array of ['product_id' => id, 'quantity' => qty]
     * @param string|null $couponCode
     * @return array ['subtotal' => float, 'discount' => float, 'total' => float]
     */
    public function calculateOrderTotal(array $items, ?string $couponCode = null): array
    {
        $subtotal = 0;
        $productIds = collect($items)->map(fn($i) => $i['product_id'] ?? $i['id'])->filter()->unique()->toArray();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $product = $products[$item['product_id'] ?? $item['id']] ?? null;
            if ($product) {
                $subtotal += $product->price * $item['quantity'];
            }
        }

        $discount = 0;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($coupon) {
                $discount = $coupon->type === 'percentage' || $coupon->type === 'percent'
                    ? ($subtotal * $coupon->value / 100)
                    : $coupon->value;
            }
        }

        $total = max(0, $subtotal - $discount);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total'    => round($total, 2),
        ];
    }

    /**
     * Create order and order items within a transaction
     *
     * @param array $orderData
     * @param array $items Array of ['product_id' => id, 'quantity' => qty, 'price' => optional_price]
     * @return Order
     * @throws \Exception
     */
    public function createOrderWithItems(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            // Ensure order number is set
            if (!isset($orderData['order_number'])) {
                $orderData['order_number'] = $this->generateOrderNumber();
            }

            $order = Order::create($orderData);

            $productIds = collect($items)->map(fn($i) => $i['product_id'] ?? $i['id'])->filter()->unique()->toArray();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? $item['id'];
                $product = $products[$productId] ?? null;

                if (!$product) {
                    throw new \Exception("Product ID {$productId} not found");
                }
                
                $order->items()->create([
                    'product_id' => $product->id,
                    'item_name'  => $product->name,
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'] ?? $product->price,
                    'subtotal'   => ($item['price'] ?? $product->price) * $item['quantity'],
                ]);
            }

            return $order->load('items');
        });
    }

    /**
     * Fire OrderPlaced event safely
     *
     * @param Order $order
     * @return void
     */
    public function fireOrderPlacedEvent(Order $order): void
    {
        try {
            event(new OrderPlaced($order));
        } catch (\Exception $e) {
            Log::error('Failed to fire OrderPlaced event: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }
}
