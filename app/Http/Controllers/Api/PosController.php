<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function products(Request $request)
    {
        try {
            $branchId = session('selected_branch_id');
            if (!$branchId) {
                return response()->json(['error' => 'No branch selected'], 400);
            }

            $products = Product::where('is_active', true)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => (float) $product->price,
                        'category' => $product->category,
                        'tag' => strtolower(str_replace(' ', '-', $product->tag)),
                        'image' => $product->image
                    ];
                });

            // Log products and their tags for debugging
            \Log::info('Products being sent to frontend:', [
                'products' => $products->map(function($p) {
                    return [
                        'name' => $p['name'],
                        'tag' => $p['tag']
                    ];
                })->toArray()
            ]);

            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    public function tables(Request $request)
    {
        // Get branch ID from session or request header
        $branchId = session('selected_branch_id') ?? $request->header('X-Branch-ID');
        
        \Log::info('Loading tables for branch', [
            'branch_id' => $branchId,
            'session_branch' => session('selected_branch_id'),
            'header_branch' => $request->header('X-Branch-ID')
        ]);
        
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $tables = Table::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        \Log::info('Found tables', [
            'branch_id' => $branchId,
            'count' => $tables->count(),
            'table_ids' => $tables->pluck('id')->toArray()
        ]);

        return response()->json($tables);
    }

    public function verifyToken(Request $request)
    {
        // Temporarily bypass authentication
        return response()->json([
            'success' => true,
            'message' => 'Token verification bypassed',
            'user' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'roles' => ['admin']
            ],
            'branch' => [
                'id' => $request->branch_id,
                'name' => 'Test Branch'
            ]
        ]);

        // Original authentication code commented out for now
        /*
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $branchId = $request->branch_id;

        if (!$branchId) {
            return response()->json(['message' => 'Branch ID is required'], 400);
        }

        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            return response()->json(['message' => 'Invalid or inactive branch'], 400);
        }

        if (!$user->hasBranchAccess($branchId)) {
            return response()->json(['message' => 'You do not have access to this branch'], 403);
        }

        session(['selected_branch_id' => $branchId]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'branch' => $branch
        ]);
        */
    }

    /**
     * Get current user and branch information
     */
    public function userInfo(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $user = Auth::user();
            $branchId = session('selected_branch_id');
            
            if (!$branchId) {
                return response()->json([
                    'message' => 'No branch selected'
                ], 400);
            }

            $branch = Branch::find($branchId);
            
            if (!$branch) {
                return response()->json([
                    'message' => 'Branch not found'
                ], 404);
            }

            // Get active order for the current user and branch
            $activeOrder = Order::where('user_id', $user->id)
                ->where('branch_id', $branchId)
                ->where('status', 'pending')
                ->with(['items.product'])
                ->latest()
                ->first();

            return response()->json([
                'user_name' => $user->name,
                'branch_name' => $branch->name,
                'branch_id' => $branch->id,
                'active_order' => $activeOrder ? [
                    'id' => $activeOrder->id,
                    'created_at' => $activeOrder->created_at->format('Y-m-d H:i:s'),
                    'items' => $activeOrder->items->map(function($item) {
                        return [
                            'name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price
                        ];
                    }),
                    'total' => $activeOrder->total
                ] : null
            ]);
        } catch (\Exception $e) {
            \Log::error('User info error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load user info'
            ], 500);
        }
    }
} 