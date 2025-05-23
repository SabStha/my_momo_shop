<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockItem;
use App\Models\InventoryCount;

class InventoryController extends Controller
{
    public function dashboard()
    {
        $items = \App\Models\StockItem::all();
        $forecast = [];
        foreach ($items as $item) {
            // Get the last 7 days of inventory counts
            $counts = \App\Models\InventoryCount::where('stock_item_id', $item->id)
                ->orderByDesc('created_at')
                ->take(7)
                ->get();

            // Calculate average daily usage
            $avg = $counts->avg(function($count) {
                return abs($count->expected_quantity - $count->actual_quantity);
            }) ?? 0;

            // Calculate trend (positive or negative)
            $trend = 0;
            if ($counts->count() >= 2) {
                $firstCount = $counts->last();
                $lastCount = $counts->first();
                $trend = ($lastCount->actual_quantity - $firstCount->actual_quantity) / $counts->count();
            }

            // Calculate standard deviation for safety stock
            $deviations = $counts->map(function($count) use ($avg) {
                return pow(abs($count->expected_quantity - $count->actual_quantity) - $avg, 2);
            });
            $stdDev = sqrt($deviations->avg() ?? 0);

            // Calculate safety stock (2 standard deviations)
            $safetyStock = 2 * $stdDev;

            // Calculate reorder point (average daily usage * lead time + safety stock)
            $leadTime = 2; // Assuming 2 days lead time
            $reorderPoint = ($avg * $leadTime) + $safetyStock;

            // Calculate needed quantity for 2 days
            $needed = $avg * 2;

            // Calculate suggested order quantity
            $suggested = max(0, $needed - $item->quantity);

            // Add buffer based on trend
            if ($trend > 0) {
                $suggested += $trend * 2; // Add extra if trend is increasing
            }

            $forecast[] = [
                'id' => $item->id,
                'name' => $item->name,
                'current' => $item->quantity,
                'avg' => round($avg, 2),
                'needed' => round($needed, 2),
                'suggested' => round($suggested, 2),
                'unit' => $item->unit,
                'trend' => round($trend, 2),
                'safety_stock' => round($safetyStock, 2),
                'reorder_point' => round($reorderPoint, 2),
                'status' => $item->quantity <= $reorderPoint ? 'Reorder' : 'OK',
                'last_count' => $counts->first() ? $counts->first()->created_at->format('Y-m-d') : 'Never',
                'count_frequency' => $counts->count() . '/7 days'
            ];
        }
        return view('admin.inventory.dashboard', compact('items', 'forecast'));
    }

    public function count()
    {
        $items = \App\Models\StockItem::all();
        return view('admin.inventory.count', compact('items'));
    }

    public function forecast()
    {
        $items = \App\Models\StockItem::all();
        $forecast = [];

        foreach ($items as $item) {
            // Get the last 7 days of inventory counts
            $counts = \App\Models\InventoryCount::where('stock_item_id', $item->id)
                ->orderByDesc('created_at')
                ->take(7)
                ->get();

            // Calculate average daily usage
            $avg = $counts->avg(function($count) {
                return abs($count->expected_quantity - $count->actual_quantity);
            }) ?? 0;

            // Calculate trend (positive or negative)
            $trend = 0;
            if ($counts->count() >= 2) {
                $firstCount = $counts->last();
                $lastCount = $counts->first();
                $trend = ($lastCount->actual_quantity - $firstCount->actual_quantity) / $counts->count();
            }

            // Calculate standard deviation for safety stock
            $deviations = $counts->map(function($count) use ($avg) {
                return pow(abs($count->expected_quantity - $count->actual_quantity) - $avg, 2);
            });
            $stdDev = sqrt($deviations->avg() ?? 0);

            // Calculate safety stock (2 standard deviations)
            $safetyStock = 2 * $stdDev;

            // Calculate reorder point (average daily usage * lead time + safety stock)
            $leadTime = 2; // Assuming 2 days lead time
            $reorderPoint = ($avg * $leadTime) + $safetyStock;

            // Calculate needed quantity for 2 days
            $needed = $avg * 2;

            // Calculate suggested order quantity
            $suggested = max(0, $needed - $item->quantity);

            // Add buffer based on trend
            if ($trend > 0) {
                $suggested += $trend * 2; // Add extra if trend is increasing
            }

            $forecast[] = [
                'id' => $item->id,
                'name' => $item->name,
                'current' => $item->quantity,
                'avg' => round($avg, 2),
                'needed' => round($needed, 2),
                'suggested' => round($suggested, 2),
                'unit' => $item->unit,
                'trend' => round($trend, 2),
                'safety_stock' => round($safetyStock, 2),
                'reorder_point' => round($reorderPoint, 2),
                'status' => $item->quantity <= $reorderPoint ? 'Reorder' : 'OK',
                'last_count' => $counts->first() ? $counts->first()->created_at->format('Y-m-d') : 'Never',
                'count_frequency' => $counts->count() . '/7 days'
            ];
        }

        return view('admin.inventory.forecast-partial', compact('forecast'));
    }

    public function add()
    {
        return view('admin.inventory.add');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'expiry' => 'required|date',
        ]);

        StockItem::create($validated);

        return redirect()->route('admin.inventory.count')->with('success', 'Item added successfully.');
    }

    public function edit($id)
    {
        $item = StockItem::findOrFail($id);
        return view('admin.inventory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'expiry' => 'required|date',
        ]);

        $item = StockItem::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.inventory.count')->with('success', 'Item updated successfully.');
    }

    public function updateForecast(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:stock_items,id',
                'current_quantity' => 'required|numeric|min:0',
                'avg_usage' => 'required|numeric|min:0',
                'trend' => 'required|numeric',
                'safety_stock' => 'required|numeric|min:0',
                'reorder_point' => 'required|numeric|min:0',
                'suggested' => 'required|numeric|min:0',
                'status' => 'required|in:OK,Reorder'
            ]);

            $item = StockItem::findOrFail($validated['id']);
            
            // Update the stock item
            $item->update([
                'quantity' => $validated['current_quantity']
            ]);

            // Create a new inventory count record
            InventoryCount::create([
                'stock_item_id' => $item->id,
                'expected_quantity' => $validated['current_quantity'] + $validated['avg_usage'],
                'actual_quantity' => $validated['current_quantity'],
                'notes' => 'Manual forecast adjustment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Forecast updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating forecast: ' . $e->getMessage()
            ], 500);
        }
    }
} 