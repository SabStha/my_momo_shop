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
        $combos = Combo::all();
        $momoes = Product::where('tag', 'momo')->get();
        $drinks = Product::where('tag', 'drink')->get();
        // For specials, you can use another tag or a boolean column, or just leave as an empty array for now
         // or fetch as needed
    
        return view('pages.menu', compact('featured', 'combos', 'drinks', 'momoes'));
    }

    public function featured()
    {
    $featuredProducts = \App\Models\Product::where('is_featured', true)
                               ->where('is_active', true) // optional
                               ->orderBy('name')
                               ->get();
                               

    return view('menu.featured', compact('featuredProducts'));
    }

} 