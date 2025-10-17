<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartSyncController extends Controller
{
    /**
     * Get user's cart data
     */
    public function getCart(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userCart = $user->getOrCreateCart();
            
            return response()->json([
                'success' => true,
                'cart' => [
                    'items' => $userCart->cart_data ?? [],
                    'item_count' => $userCart->getItemCount(),
                    'subtotal' => $userCart->getSubtotal(),
                    'last_updated' => $userCart->last_updated,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cart sync error (get): ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart data'
            ], 500);
        }
    }

    /**
     * Sync cart data from client to server
     */
    public function syncCart(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|string',
                'items.*.name' => 'required|string',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.image' => 'nullable|string',
                'items.*.type' => 'nullable|string',
            ]);

            $cartItems = $request->input('items', []);
            
            // Validate and clean cart data
            $validatedItems = [];
            foreach ($cartItems as $item) {
                $validatedItems[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'image' => $item['image'] ?? null,
                    'type' => $item['type'] ?? 'product',
                ];
            }

            $userCart = $user->getOrCreateCart();
            $userCart->updateCart($validatedItems);

            Log::info('Cart synced successfully', [
                'user_id' => $user->id,
                'item_count' => count($validatedItems),
                'subtotal' => $userCart->getSubtotal(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart synchronized successfully',
                'cart' => [
                    'items' => $validatedItems,
                    'item_count' => $userCart->getItemCount(),
                    'subtotal' => $userCart->getSubtotal(),
                    'last_updated' => $userCart->last_updated,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid cart data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Cart sync error (sync): ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize cart'
            ], 500);
        }
    }

    /**
     * Clear user's cart
     */
    public function clearCart(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userCart = $user->getOrCreateCart();
            $userCart->updateCart([]);

            Log::info('Cart cleared', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'cart' => [
                    'items' => [],
                    'item_count' => 0,
                    'subtotal' => 0,
                    'last_updated' => $userCart->last_updated,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cart clear error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart'
            ], 500);
        }
    }

    /**
     * Add item to user's cart
     */
    public function addItem(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'id' => 'required|string',
                'name' => 'required|string',
                'price' => 'required|numeric|min:0',
                'quantity' => 'integer|min:1',
                'image' => 'nullable|string',
                'type' => 'nullable|string',
            ]);

            $newItem = [
                'id' => $request->input('id'),
                'name' => $request->input('name'),
                'price' => (float) $request->input('price'),
                'quantity' => (int) ($request->input('quantity', 1)),
                'image' => $request->input('image'),
                'type' => $request->input('type', 'product'),
            ];

            $userCart = $user->getOrCreateCart();
            $cartItems = $userCart->cart_data ?? [];

            // Check if item already exists
            $existingItemIndex = null;
            foreach ($cartItems as $index => $item) {
                if ($item['id'] === $newItem['id']) {
                    $existingItemIndex = $index;
                    break;
                }
            }

            if ($existingItemIndex !== null) {
                // Update quantity of existing item
                $cartItems[$existingItemIndex]['quantity'] += $newItem['quantity'];
            } else {
                // Add new item
                $cartItems[] = $newItem;
            }

            $userCart->updateCart($cartItems);

            return response()->json([
                'success' => true,
                'message' => $newItem['name'] . ' added to cart!',
                'cart' => [
                    'items' => $cartItems,
                    'item_count' => $userCart->getItemCount(),
                    'subtotal' => $userCart->getSubtotal(),
                    'last_updated' => $userCart->last_updated,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Cart add item error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart'
            ], 500);
        }
    }

    /**
     * Remove item from user's cart
     */
    public function removeItem(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'item_id' => 'required|string',
            ]);

            $itemId = $request->input('item_id');
            $userCart = $user->getOrCreateCart();
            $cartItems = $userCart->cart_data ?? [];

            // Remove item
            $cartItems = array_filter($cartItems, function ($item) use ($itemId) {
                return $item['id'] !== $itemId;
            });

            // Re-index array
            $cartItems = array_values($cartItems);

            $userCart->updateCart($cartItems);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => [
                    'items' => $cartItems,
                    'item_count' => $userCart->getItemCount(),
                    'subtotal' => $userCart->getSubtotal(),
                    'last_updated' => $userCart->last_updated,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cart remove item error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart'
            ], 500);
        }
    }

    /**
     * Update item quantity in user's cart
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'item_id' => 'required|string',
                'quantity' => 'required|integer|min:1',
            ]);

            $itemId = $request->input('item_id');
            $quantity = (int) $request->input('quantity');
            $userCart = $user->getOrCreateCart();
            $cartItems = $userCart->cart_data ?? [];

            // Update quantity
            foreach ($cartItems as &$item) {
                if ($item['id'] === $itemId) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }

            $userCart->updateCart($cartItems);

            return response()->json([
                'success' => true,
                'message' => 'Item quantity updated',
                'cart' => [
                    'items' => $cartItems,
                    'item_count' => $userCart->getItemCount(),
                    'subtotal' => $userCart->getSubtotal(),
                    'last_updated' => $userCart->last_updated,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cart update quantity error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item quantity'
            ], 500);
        }
    }
}