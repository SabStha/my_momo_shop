<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class PosTableController extends Controller
{
    public function index()
    {
        $tables = Table::all();
        return response()->json($tables);
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,reserved',
            'is_occupied' => 'boolean'
        ]);

        try {
            $updated = $table->updateStatus($request->status, $request->is_occupied);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Table status updated successfully',
                    'table' => $table
                ]);
            } else {
                throw new \Exception('Failed to update table status');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update table status via API', [
                'table_id' => $table->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update table status'
            ], 500);
        }
    }
} 