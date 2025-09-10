<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulkPackage;
use App\Models\Product;

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

        return view('bulk.index', compact('packages', 'momoTypes', 'sideDishes', 'drinks', 'desserts', 'deliveryAreas'));
    }
} 