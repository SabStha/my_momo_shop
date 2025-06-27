<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulkPackage;

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

        $momoTypes = [
            ['name' => 'Buff Steamed', 'price' => 30],
            ['name' => 'Chicken Fried', 'price' => 35],
            ['name' => 'Veg C-Momo', 'price' => 25],
            ['name' => 'Pork Steamed', 'price' => 40],
            ['name' => 'Mixed Platter', 'price' => 45]
        ];

        $sideDishes = [
            ['name' => 'Sausage', 'price' => 150],
            ['name' => 'Fries', 'price' => 100],
            ['name' => 'Globe', 'price' => 120],
            ['name' => 'Karaage', 'price' => 180],
            ['name' => 'Spring Rolls', 'price' => 130],
            ['name' => 'Noodles', 'price' => 200]
        ];

        $deliveryAreas = [
            'kathmandu' => 'Kathmandu',
            'lalitpur' => 'Lalitpur',
            'bhaktapur' => 'Bhaktapur',
            'other' => 'Other'
        ];

        return view('bulk.index', compact('packages', 'momoTypes', 'sideDishes', 'deliveryAreas'));
    }
} 