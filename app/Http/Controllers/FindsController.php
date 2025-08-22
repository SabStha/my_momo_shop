<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchandise;
use App\Models\BulkPackage;
use App\Http\Resources\MerchandiseResource;

class FindsController extends Controller
{
    public function index(Request $request)
    {
        $selectedModel = $request->get('model', 'all');
        
        // Fetch merchandise data from database grouped by category and filtered by model
        $merchandise = [
            'tshirts' => MerchandiseResource::collection(Merchandise::active()->byCategory('tshirts')->byModel($selectedModel)->get()),
            'accessories' => MerchandiseResource::collection(Merchandise::active()->byCategory('accessories')->byModel($selectedModel)->get()),
            'toys' => MerchandiseResource::collection(Merchandise::active()->byCategory('toys')->byModel($selectedModel)->get()),
            'limited' => MerchandiseResource::collection(Merchandise::active()->byCategory('limited')->byModel($selectedModel)->get()),
        ];

        // Fetch bulk packages
        $bulkPackages = BulkPackage::active()->ordered()->get();

        // Dynamic categories configuration
        $categories = collect(config('finds.categories'))->map(function ($category, $key) {
            return [
                'key' => $key,
                'label' => $category['label'],
                'icon' => $category['icon'],
                'description' => $category['description']
            ];
        })->values()->toArray();

        // Dynamic configuration
        $config = [
            'finds_title' => config('finds.title'),
            'finds_subtitle' => config('finds.subtitle'),
            'add_to_cart_text' => config('finds.add_to_cart_text'),
            'unlockable_text' => config('finds.unlockable_text'),
            'progress_message' => config('finds.progress_message'),
            'earn_tooltip_message' => config('finds.earn_tooltip_message'),
            'urgency_badge_text' => config('finds.urgency_badge_text'),
            'earn_badge_text' => config('finds.earn_badge_text'),
        ];

        return view('finds.index', compact('merchandise', 'selectedModel', 'bulkPackages', 'categories', 'config'));
    }

    public function data(Request $request)
    {
        $selectedModel = $request->get('model', 'all');
        $merchandise = [
            'tshirts' => MerchandiseResource::collection(Merchandise::active()->byCategory('tshirts')->byModel($selectedModel)->get()),
            'accessories' => MerchandiseResource::collection(Merchandise::active()->byCategory('accessories')->byModel($selectedModel)->get()),
            'toys' => MerchandiseResource::collection(Merchandise::active()->byCategory('toys')->byModel($selectedModel)->get()),
            'limited' => MerchandiseResource::collection(Merchandise::active()->byCategory('limited')->byModel($selectedModel)->get()),
        ];
        return response()->json($merchandise);
    }
} 