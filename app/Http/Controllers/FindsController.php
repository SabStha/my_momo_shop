<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchandise;

class FindsController extends Controller
{
    public function index(Request $request)
    {
        $selectedModel = $request->get('model', 'all');
        
        // Fetch merchandise data from database grouped by category and filtered by model
        $merchandise = [
            'tshirts' => Merchandise::active()->byCategory('tshirts')->byModel($selectedModel)->get(),
            'accessories' => Merchandise::active()->byCategory('accessories')->byModel($selectedModel)->get(),
            'toys' => Merchandise::active()->byCategory('toys')->byModel($selectedModel)->get(),
            'limited' => Merchandise::active()->byCategory('limited')->byModel($selectedModel)->get(),
        ];

        return view('finds.index', compact('merchandise', 'selectedModel'));
    }

    public function data(Request $request)
    {
        $selectedModel = $request->get('model', 'all');
        $merchandise = [
            'tshirts' => Merchandise::active()->byCategory('tshirts')->byModel($selectedModel)->get(),
            'accessories' => Merchandise::active()->byCategory('accessories')->byModel($selectedModel)->get(),
            'toys' => Merchandise::active()->byCategory('toys')->byModel($selectedModel)->get(),
            'limited' => Merchandise::active()->byCategory('limited')->byModel($selectedModel)->get(),
        ];
        return response()->json($merchandise);
    }
} 