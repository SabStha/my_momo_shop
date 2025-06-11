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
    public function products()
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $products = Product::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        return response()->json($products);
    }

    public function tables()
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $tables = Table::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        return response()->json($tables);
    }

    public function verifyToken(Request $request)
    {
        $user = Auth::user();
        $branchId = $request->input('branch_id');

        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            return response()->json(['error' => 'Invalid or inactive branch'], 400);
        }

        // Set branch in session
        session(['selected_branch_id' => $branchId]);

        return response()->json([
            'user' => $user,
            'branch' => $branch
        ]);
    }
} 