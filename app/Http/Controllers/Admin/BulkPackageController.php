<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkPackage;

class BulkPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = BulkPackage::ordered()->get();
        return view('admin.bulk-packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.bulk-packages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'emoji' => 'required|string|max:10',
            'description' => 'required|string',
            'type' => 'required|in:cooked,frozen',
            'package_key' => 'required|string|unique:bulk_packages,package_key',
            'total_price' => 'required|numeric|min:0',
            'sort_order' => 'integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric'
        ]);

        $package = BulkPackage::create($request->all());

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Bulk package created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(BulkPackage $bulkPackage)
    {
        return view('admin.bulk-packages.show', compact('bulkPackage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BulkPackage $bulkPackage)
    {
        return view('admin.bulk-packages.edit', compact('bulkPackage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BulkPackage $bulkPackage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'emoji' => 'required|string|max:10',
            'description' => 'required|string',
            'type' => 'required|in:cooked,frozen',
            'package_key' => 'required|string|unique:bulk_packages,package_key,' . $bulkPackage->id,
            'total_price' => 'required|numeric|min:0',
            'sort_order' => 'integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric'
        ]);

        $bulkPackage->update($request->all());

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Bulk package updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BulkPackage $bulkPackage)
    {
        $bulkPackage->delete();

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Bulk package deleted successfully!');
    }

    /**
     * Toggle package active status
     */
    public function toggleStatus(BulkPackage $bulkPackage)
    {
        $bulkPackage->update(['is_active' => !$bulkPackage->is_active]);

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Package status updated successfully!');
    }
}
