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

        return view('finds.index', compact('merchandise', 'selectedModel', 'bulkPackages'));
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