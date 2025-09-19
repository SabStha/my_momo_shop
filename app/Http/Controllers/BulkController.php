<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulkPackage;
use App\Models\Product;
use App\Models\BulkSetting;

class BulkController extends Controller
{
    public function index()
    {
        // Fetch bulk packages from database
        $cookedPackages = BulkPackage::active()->byType('cooked')->ordered()->get();
        $frozenPackages = BulkPackage::active()->byType('frozen')->ordered()->get();

        $packages = [
            'cooked' => $cookedPackages->keyBy('package_key'),
            'frozen' => $frozenPackages->keyBy('package_key')
        ];

        // Fetch momo types from database
        $momoTypes = Product::where('is_active', true)
            ->where('category', 'Momo')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Fetch side dishes from database
        $sideDishes = Product::where('is_active', true)
            ->where('category', 'Side Dish')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Fetch drinks from database
        $drinks = Product::where('is_active', true)
            ->where('category', 'Drink')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Fetch desserts from database
        $desserts = Product::where('is_active', true)
            ->where('category', 'Dessert')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Delivery areas - these can be made dynamic from a settings table in the future
        $deliveryAreas = [
            'kathmandu' => 'Kathmandu',
            'lalitpur' => 'Lalitpur',
            'bhaktapur' => 'Bhaktapur',
            'other' => 'Other'
        ];

        // Fetch all products for the custom builder
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'price', 'image']);

        // Get bulk discount percentage
        $bulkDiscountPercentage = BulkSetting::getBulkDiscountPercentage();

        return view('bulk.index', compact('packages', 'momoTypes', 'sideDishes', 'drinks', 'desserts', 'deliveryAreas', 'products', 'bulkDiscountPercentage'));
    }

    public function customBuilder(Request $request)
    {
        // Get package data from URL parameters
        $packageId = $request->get('package_id');
        $packageKey = $request->get('package_key');
        $packageName = $request->get('package_name');
        $packageType = $request->get('package_type');
        $packagePrice = $request->get('package_price');

        // If package_id is provided, fetch from database
        $package = null;
        if ($packageId) {
            $package = BulkPackage::find($packageId);
        } elseif ($packageKey) {
            $package = BulkPackage::where('package_key', $packageKey)->first();
        }

        // If no package found, redirect back to bulk page
        if (!$package) {
            return redirect()->route('bulk')->with('error', 'Package not found.');
        }

        // Fetch the same data as the main bulk page
        $cookedPackages = BulkPackage::active()->byType('cooked')->ordered()->get();
        $frozenPackages = BulkPackage::active()->byType('frozen')->ordered()->get();

        $packages = [
            'cooked' => $cookedPackages->keyBy('package_key'),
            'frozen' => $frozenPackages->keyBy('package_key')
        ];

        // Fetch momo types from database
        $momoTypes = Product::where('is_active', true)
            ->where('category', 'Momo')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Fetch side dishes from database
        $sideDishes = Product::where('is_active', true)
            ->where('category', 'Side Dish')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Fetch drinks from database
        $drinks = Product::where('is_active', true)
            ->where('category', 'Drink')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Fetch desserts from database
        $desserts = Product::where('is_active', true)
            ->where('category', 'Dessert')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price
                ];
            })
            ->toArray();

        // Delivery areas
        $deliveryAreas = [
            'kathmandu' => 'Kathmandu',
            'lalitpur' => 'Lalitpur',
            'bhaktapur' => 'Bhaktapur',
            'other' => 'Other'
        ];

        // Fetch all products for the custom builder
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'price', 'image']);

        // Get bulk discount percentage
        $bulkDiscountPercentage = BulkSetting::getBulkDiscountPercentage();

        return view('bulk.custom-builder', compact(
            'package', 'packages', 'momoTypes', 'sideDishes', 'drinks', 'desserts', 'deliveryAreas', 'products', 'bulkDiscountPercentage'
        ));
    }

    public function getPackageByKey($packageKey)
    {
        $package = BulkPackage::where('package_key', $packageKey)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json($package);
    }
} 