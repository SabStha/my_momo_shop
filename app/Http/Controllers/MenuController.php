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
        
        // Group products by tag/category
        $foods = $products->where('tag', 'foods');
        $drinks = $products->where('tag', 'drinks');
        $desserts = $products->where('tag', 'desserts');
        $combos = $products->where('tag', 'combos');
        $featured = $products->where('is_featured', true);
        
        // Group foods by subcategories
        $buffItems = $foods->where('category', 'buff');
        $chickenItems = $foods->where('category', 'chicken');
        $mainItems = $foods->where('category', 'main');
        $sideSnacks = $foods->where('category', 'side');
        
        // Group drinks by subcategories
        $hotDrinks = $drinks->where('category', 'hot');
        $coldDrinks = $drinks->where('category', 'cold');
        $bobaDrinks = $drinks->where('category', 'boba');

        return view('pages.menu', compact(
            'featured', 
            'combos', 
            'foods',
            'drinks',
            'desserts',
            'buffItems',
            'chickenItems',
            'mainItems',
            'sideSnacks',
            'hotDrinks',
            'coldDrinks',
            'bobaDrinks'
        ));
    }

    public function showFood()
    {
        // Get all active food products
        $products = Product::where('is_active', true)->where('tag', 'foods')->get();
        
        // Group foods by subcategories
        $buffItems = $products->where('category', 'buff');
        $chickenItems = $products->where('category', 'chicken');
        $mainItems = $products->where('category', 'main');
        $sideSnacks = $products->where('category', 'side');
        $foods = $products; // All foods for fallback

        return view('menu.food', compact(
            'buffItems',
            'chickenItems', 
            'mainItems',
            'sideSnacks',
            'foods'
        ));
    }

    public function showDrinks()
    {
        // Get all active drink products
        $products = Product::where('is_active', true)->where('tag', 'drinks')->get();
        
        // Group drinks by subcategories
        $hotDrinks = $products->where('category', 'hot');
        $coldDrinks = $products->where('category', 'cold');
        $bobaDrinks = $products->where('category', 'boba');
        $drinks = $products; // All drinks for fallback

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
