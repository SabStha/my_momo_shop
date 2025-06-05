<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Order;
use App\Models\Product;

class PosController extends Controller
{
    public function tables()
    {
        $tables = Table::all()->map(function ($table) {
            return [
                'id' => $table->id,
                'name' => $table->name,
                'status' => $table->status,
                'capacity' => $table->capacity
            ];
        });

        return response()->json($tables);
    }

    public function index()
    {
        return response()->json([
            'tables' => Table::all(),
            'products' => Product::where('is_active', true)->get(),
            'orders' => Order::with(['items', 'table'])->where('status', '!=', 'completed')->get()
        ]);
    }

    public function products()
    {
        $products = Product::where('is_active', true)->get();
        return response()->json($products);
    }
} 