<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Combo;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function showMenu()
    {
        // Get all active products
        $products = Product::where('is_active', true)->get();
        
        // Group products by tag (correct tags from seeder)
        $combos = $products->where('tag', 'combos');
        $desserts = $products->where('tag', 'desserts');
        $featured = $products->where('is_featured', true);
        
        // Group foods by tag (buff, chicken, veg, others)
        $buffItems = $products->where('tag', 'buff');
        $chickenItems = $products->where('tag', 'chicken');
        $vegItems = $products->where('tag', 'veg');
        $sideSnacks = $products->where('tag', 'others'); // sides
        
        // Combine all food items
        $foods = $buffItems->merge($chickenItems)->merge($vegItems)->merge($sideSnacks);
        $mainItems = collect(); // No main items for now
        
        // Group drinks by tag (hot, cold, boba)
        $hotDrinks = $products->where('tag', 'hot');
        $coldDrinks = $products->where('tag', 'cold');
        $bobaDrinks = $products->where('tag', 'boba');
        
        // Combine all drinks
        $drinks = $hotDrinks->merge($coldDrinks)->merge($bobaDrinks);

        return view('pages.menu', compact(
            'featured', 
            'combos', 
            'foods',
            'drinks',
            'desserts',
            'buffItems',
            'chickenItems',
            'vegItems',
            'mainItems',
            'sideSnacks',
            'hotDrinks',
            'coldDrinks',
            'bobaDrinks'
        ));
    }

    public function showFood()
    {
        // Get all active food products by tag
        $buffItems = Product::where('is_active', true)->where('tag', 'buff')->get();
        $chickenItems = Product::where('is_active', true)->where('tag', 'chicken')->get();
        $vegItems = Product::where('is_active', true)->where('tag', 'veg')->get();
        $sideSnacks = Product::where('is_active', true)->where('tag', 'others')->get();
        $mainItems = collect(); // No main items
        
        // Combine all foods
        $foods = $buffItems->merge($chickenItems)->merge($vegItems)->merge($sideSnacks);

        return view('menu.food', compact(
            'buffItems',
            'chickenItems',
            'vegItems', 
            'mainItems',
            'sideSnacks',
            'foods'
        ));
    }

    public function showDrinks()
    {
        // Get all active drink products by tag
        $hotDrinks = Product::where('is_active', true)->where('tag', 'hot')->get();
        $coldDrinks = Product::where('is_active', true)->where('tag', 'cold')->get();
        $bobaDrinks = Product::where('is_active', true)->where('tag', 'boba')->get();
        
        // Combine all drinks
        $drinks = $hotDrinks->merge($coldDrinks)->merge($bobaDrinks);

        return view('menu.drinks', compact(
            'hotDrinks',
            'coldDrinks',
            'bobaDrinks',
            'drinks'
        ));
    }

    public function showDesserts()
    {
        // Get all active dessert products
        $desserts = Product::where('is_active', true)->where('tag', 'desserts')->get();

        return view('menu.desserts', compact('desserts'));
    }

    public function showCombos()
    {
        // Get all active combo products
        $combos = Product::where('is_active', true)->where('tag', 'combos')->get();

        return view('menu.combo', compact('combos'));
    }

    public function featured()
    {
        $featuredProducts = Product::where('is_featured', true)
                                   ->where('is_active', true)
                                   ->orderBy('name')
                                   ->get();

        return view('menu.featured', compact('featuredProducts'));
    }
}
