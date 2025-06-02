<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('items')
            ->orderBy('name')
            ->paginate(10);
            
        return view('desktop.admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('desktop.admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string'
        ]);

        try {
            Supplier::create($validated);
            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return back()->with('error', 'Error creating supplier. Please try again.');
        }
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('items');
        return view('desktop.admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('desktop.admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string'
        ]);

        try {
            $supplier->update($validated);
            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return back()->with('error', 'Error updating supplier. Please try again.');
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return back()->with('error', 'Error deleting supplier. Please try again.');
        }
    }
} 