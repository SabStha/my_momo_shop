<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Offer;
use App\Services\StatisticsService;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Show the application home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        try {
            // Get all unique tags from products
            $tags = Product::whereNotNull('tag')
                          ->where('stock', '>', 0)
                          ->distinct()
                          ->pluck('tag');

            // Get featured products
            $featuredProducts = Product::where('is_featured', 1)->get();

            // Menu highlights
            $menuHighlights = Product::where('is_menu_highlight', 1)->get();

            // Get active offers
            $activeOffers = Offer::active()->latest()->take(6)->get();

            // Get products based on selected tag or all if none selected
            $query = Product::where('stock', '>', 0);
            
            if ($request->has('tag') && $request->tag !== 'all') {
                $query->where('tag', $request->tag);
            }

            $products = $query->orderBy('is_featured', 'desc')
                            ->latest()
                            ->get();

            // Get dynamic statistics
            $statistics = $this->statisticsService->getFormattedStatistics();

            // If no products found, log it but don't throw an error
            if ($products->isEmpty()) {
                Log::info('No products found in the database');
            }

            return view('home', compact('products', 'tags', 'featuredProducts', 'menuHighlights', 'activeOffers', 'statistics'));
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            // Return view with empty collection if there's an error
            return view('home', [
                'products' => collect(), 
                'tags' => collect(), 
                'featuredProducts' => collect(), 
                'menuHighlights' => collect(),
                'activeOffers' => collect(),
                'statistics' => [
                    'happy_customers' => '500+',
                    'momo_varieties' => '15+',
                    'customer_rating' => '5.0',
                    'total_orders' => '1000+',
                    'total_revenue' => '$50,000+'
                ]
            ]);
        }
    }

    /**
     * Get statistics via AJAX for real-time updates
     */
    public function getStatistics()
    {
        try {
            $statistics = $this->statisticsService->getFormattedStatistics();
            return response()->json($statistics);
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage());
            return response()->json([
                'happy_customers' => '500+',
                'momo_varieties' => '15+',
                'customer_rating' => '5.0',
                'total_orders' => '1000+',
                'total_revenue' => '$50,000+'
            ]);
        }
    }

    /**
     * Clear the referral discount session variable.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearReferralDiscount()
    {
        session()->forget('referral_discount');
        return response()->json(['success' => true]);
    }

    /**
     * Show the offers page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function offers()
    {
        try {
            // Get products with discounts
            $products = Product::where('active', true)
                             ->whereNotNull('discount_price')
                             ->latest()
                             ->get();

            return view('offers', compact('products'));
        } catch (\Exception $e) {
            Log::error('Error fetching offers: ' . $e->getMessage());
            return view('offers', ['products' => collect()]);
        }
    }

    /**
     * Show the bulk orders page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function bulkOrders()
    {
        try {
            // Get all active products for bulk ordering
            $products = Product::where('active', true)
                             ->where('stock', '>', 0)
                             ->orderBy('name')
                             ->get();

            return view('bulk-orders', compact('products'));
        } catch (\Exception $e) {
            Log::error('Error fetching bulk orders page: ' . $e->getMessage());
            return view('bulk-orders', ['products' => collect()]);
        }
    }

    /**
     * Show the search page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q');
            $products = collect();

            if ($query) {
                $products = Product::where('active', true)
                                 ->where(function($q) use ($query) {
                                     $q->where('name', 'like', "%{$query}%")
                                       ->orWhere('description', 'like', "%{$query}%");
                                 })
                                 ->where('stock', '>', 0)
                                 ->latest()
                                 ->get();
            }

            return view('search', compact('products', 'query'));
        } catch (\Exception $e) {
            Log::error('Error in search: ' . $e->getMessage());
            return view('search', ['products' => collect(), 'query' => '']);
        }
    }

    /**
     * Show the account page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function account()
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }

            $user = auth()->user();
            return view('account', compact('user'));
        } catch (\Exception $e) {
            Log::error('Error loading account page: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Unable to load account page');
        }
    }
}
