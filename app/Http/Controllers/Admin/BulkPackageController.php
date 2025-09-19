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
        $products = \App\Models\Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'price']);
            
        return view('admin.bulk-packages.create', compact('products'));
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
            'bulk_price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
            'sort_order' => 'integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.category' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.bulk_price' => 'required|numeric'
        ]);

        try {
            $data = $request->all();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('bulk-packages', $imageName, 'public');
                $data['image'] = $imagePath;
            }
            
            $package = BulkPackage::create($data);
        } catch (\Exception $e) {
            \Log::error('Bulk Package Creation Error:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create package. Please try again.');
        }

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
        // Ensure the package exists and is accessible
        if (!$bulkPackage) {
            return redirect()->route('admin.bulk-packages.index')
                ->with('error', 'Package not found.');
        }

        $products = \App\Models\Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'price']);

        return view('admin.bulk-packages.edit', compact('bulkPackage', 'products'));
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
            'bulk_price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
            'sort_order' => 'integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.category' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.bulk_price' => 'required|numeric'
        ]);

        try {
            $data = $request->all();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($bulkPackage->image && \Storage::disk('public')->exists($bulkPackage->image)) {
                    \Storage::disk('public')->delete($bulkPackage->image);
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('bulk-packages', $imageName, 'public');
                $data['image'] = $imagePath;
            }
            
            $bulkPackage->update($data);
        } catch (\Exception $e) {
            \Log::error('Bulk Package Update Error:', [
                'package_id' => $bulkPackage->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update package. Please try again.');
        }

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Bulk package updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BulkPackage $bulkPackage)
    {
        try {
            $bulkPackage->delete();
        } catch (\Exception $e) {
            \Log::error('Bulk Package Deletion Error:', [
                'package_id' => $bulkPackage->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete package. Please try again.');
        }

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Bulk package deleted successfully!');
    }

    /**
     * Toggle package active status
     */
    public function toggleStatus(BulkPackage $bulkPackage)
    {
        try {
            $bulkPackage->update(['is_active' => !$bulkPackage->is_active]);
        } catch (\Exception $e) {
            \Log::error('Bulk Package Status Toggle Error:', [
                'package_id' => $bulkPackage->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update package status. Please try again.');
        }

        return redirect()->route('admin.bulk-packages.index')
            ->with('success', 'Package status updated successfully!');
    }
}
