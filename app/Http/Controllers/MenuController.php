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
        $drinks = Product::where('tag', 'drink')->get();
        // For specials, you can use another tag or a boolean column, or just leave as an empty array for now
        $specials = []; // or fetch as needed
    
        return view('menu', compact('featured', 'combos', 'drinks', 'specials'));
    }
} 