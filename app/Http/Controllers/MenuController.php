<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Combo;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function showMenu()
    {
        $featured = Product::where('is_featured', true)->get();
        $combos = Product::where('tag', 'combos')->get();
        $momoes = Product::where('tag', 'momoes')->get();
        $drinks = Product::where('tag', 'drinks')->get();
        $desserts = Product::where('tag', 'desserts')->get();

        return view('pages.menu', compact('featured', 'combos', 'momoes', 'drinks', 'desserts'));
    }

    public function featured()
    {
        $featuredProducts = Product::where('is_featured', true)
                                   ->orderBy('name')
                                   ->get();

        return view('menu.featured', compact('featuredProducts'));
    }
}
