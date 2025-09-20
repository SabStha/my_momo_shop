<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected $cartCalculationService;

    public function __construct(CartCalculationService $cartCalculationService)
    {
        $this->cartCalculationService = $cartCalculationService;
    }

    /**
     * Calculate cart totals server-side
     */
    public function calculateTotals(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.type' => 'nullable|string|in:product,bulk',
            'branch_id' => 'nullable|integer'
        ]);

        $result = $this->cartCalculationService->calculate([
            'items' => $request->items,
            'branch_id' => $request->input('branch_id', 1)
        ]);

        if (!empty($result['unavailable'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unavailable items',
                'unavailable' => $result['unavailable']
            ], 409); // Conflict
        }

        return response()->json([
            'success' => true,
            'items' => $result['items'],
            'subtotal' => $result['subtotal'],
            'delivery_fee' => $result['delivery_fee'],
            'tax' => $result['tax'],
            'total' => $result['total']
        ]);
    }

    /**
     * Validate cart items without calculating totals
     */
    public function validateCart(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.type' => 'nullable|string|in:product,bulk',
            'branch_id' => 'nullable|integer'
        ]);

        $result = $this->cartCalculationService->calculate([
            'items' => $request->items,
            'branch_id' => $request->input('branch_id', 1)
        ]);

        if (!empty($result['unavailable'])) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Unavailable items',
                'unavailable' => $result['unavailable']
            ], 409); // Conflict
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'items_count' => count($result['items']),
            'message' => 'Cart validation successful'
        ]);
    }
}
