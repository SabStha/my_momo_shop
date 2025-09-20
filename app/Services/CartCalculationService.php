<?php

namespace App\Services;

use App\Models\Product;
use App\Models\BulkPackage;
use Illuminate\Support\Facades\Log;

class CartCalculationService
{
    /**
     * Calculate cart totals server-side for security and consistency
     * Returns canonical structure with unavailable items for proper error handling
     */
    public function calculate(array $params): array
    {
        $cartItems = $params['items'] ?? [];
        $branchId = (int) ($params['branch_id'] ?? 1);
        
        $items = [];
        $unavailable = [];
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];
            $type = $item['type'] ?? 'product';
            $variantId = $item['variant_id'] ?? null;
            $optionIds = $item['option_ids'] ?? [];

            try {
                if ($type === 'bulk' && str_starts_with($productId, 'bulk-')) {
                    // Handle bulk packages
                    $bulkPackageId = str_replace('bulk-', '', $productId);
                    $bulkPackage = BulkPackage::find($bulkPackageId);
                    
                    if (!$bulkPackage) {
                        $unavailable[] = [
                            'product_id' => $productId,
                            'reason' => 'bulk_package_not_found'
                        ];
                        continue;
                    }
                    
                    $price = $bulkPackage->bulk_price ?? $bulkPackage->total_price;
                    $itemTotal = $price * $quantity;
                    $subtotal += $itemTotal;
                    
                    $items[] = [
                        'product_id' => $productId,
                        'variant_id' => null,
                        'option_ids' => [],
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'type' => 'bulk',
                        'bulk_package_id' => $bulkPackageId
                    ];
                } else {
                    // Handle regular products with branch scoping
                    $product = Product::withTrashed()->find($productId);
                    
                    if (!$product) {
                        $unavailable[] = [
                            'product_id' => $productId,
                            'reason' => 'product_not_found'
                        ];
                        continue;
                    }
                    
                    if ($product->trashed()) {
                        $unavailable[] = [
                            'product_id' => $productId,
                            'reason' => 'soft_deleted'
                        ];
                        continue;
                    }
                    
                    if (!$product->is_active) {
                        $unavailable[] = [
                            'product_id' => $productId,
                            'reason' => 'inactive'
                        ];
                        continue;
                    }
                    
                    // For now, skip branch-specific checks since branch_product table doesn't exist
                    // TODO: Implement branch-product relationships when needed
                    // For now, assume all products are available in all branches
                    
                    $itemTotal = $product->price * $quantity;
                    $subtotal += $itemTotal;
                    
                    $items[] = [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'option_ids' => $optionIds,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'type' => 'product'
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error processing cart item', [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
                $unavailable[] = [
                    'product_id' => $productId,
                    'reason' => 'processing_error'
                ];
            }
        }

        // Calculate delivery fee based on branch or business rule
        $deliveryFee = 0;
        if ($branchId) {
            // Use branch-specific delivery fee
            $branch = \App\Models\Branch::find($branchId);
            if ($branch) {
                $deliveryFee = $branch->delivery_fee ?? 0;
            } else {
                // Fallback to business rule if branch not found
                $deliveryFee = $subtotal >= 25 ? 0 : 5.00;
            }
        } else {
            // No branch specified, use business rule
            $deliveryFee = $subtotal >= 25 ? 0 : 5.00;
        }
        
        // Calculate taxes and totals
        $taxRate = 0.13; // 13% tax
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount + $deliveryFee;

        return [
            'items' => $items,
            'unavailable' => $unavailable,
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'tax_rate' => $taxRate,
            'tax' => round($taxAmount, 2),
            'discount' => 0, // Can be extended for discount support
            'total' => round($total, 2)
        ];
    }

    /**
     * Legacy method for backward compatibility
     */
    public function calculateCartTotals(array $cartItems, $branchId = null): array
    {
        $result = $this->calculate([
            'items' => $cartItems,
            'branch_id' => $branchId
        ]);
        
        // Convert to legacy format
        return [
            'items' => $result['items'],
            'subtotal' => $result['subtotal'],
            'delivery_fee' => $result['delivery_fee'],
            'tax_rate' => $result['tax_rate'],
            'tax_amount' => $result['tax'],
            'grand_total' => $result['total'],
            'errors' => array_map(function($item) {
                return "Product {$item['product_id']}: {$item['reason']}";
            }, $result['unavailable']),
            'has_errors' => !empty($result['unavailable'])
        ];
    }

    /**
     * Validate cart items and return server-calculated totals
     */
    public function validateAndCalculate(array $cartItems): array
    {
        $result = $this->calculateCartTotals($cartItems);
        
        if ($result['has_errors']) {
            return [
                'success' => false,
                'errors' => $result['errors'],
                'message' => 'Some items in your cart are no longer available'
            ];
        }

        return [
            'success' => true,
            'cart' => $result
        ];
    }
}
