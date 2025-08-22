<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Offer;
use App\Services\StatisticsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

            // Get active offers - include AI-generated and personalized offers
            $activeOffers = Offer::active()->latest()->take(6)->get();
            
            // If user is logged in, get personalized offers and claimed offers
            if (auth()->check()) {
                $user = auth()->user();
                
                // Get personalized offers
                $personalizedOffers = Offer::active()
                    ->personalized()
                    ->forUser($user->id)
                    ->latest()
                    ->take(3)
                    ->get();
                
                // Get user's claimed offers (both active and used)
                $claimedOffers = $user->offerClaims()
                    ->with(['offer'])
                    ->orderBy('claimed_at', 'desc')
                    ->get()
                    ->map(function($claim) {
                        return $claim->offer;
                    })
                    ->filter(function($offer) {
                        return $offer && $offer->is_active;
                    });
                
                // Merge all offers: personalized + claimed + general offers
                $allOffers = $personalizedOffers->merge($claimedOffers)->merge($activeOffers);
                
                // Remove duplicates and take the latest 8 offers
                $activeOffers = $allOffers->unique('id')->take(8);
            }

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

            // Get real customer testimonials
            $testimonials = $this->statisticsService->getCustomerTestimonials(6);

            // If no products found, log it but don't throw an error
            if ($products->isEmpty()) {
                Log::info('No products found in the database');
            }

            return view('home', compact('products', 'tags', 'featuredProducts', 'menuHighlights', 'activeOffers', 'statistics', 'testimonials'));
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            // Return view with empty collection if there's an error
            return view('home', [
                'products' => collect(), 
                'tags' => collect(), 
                'featuredProducts' => collect(), 
                'menuHighlights' => collect(),
                'activeOffers' => collect(),
                'testimonials' => collect(),
                'statistics' => [
                    'happy_customers' => '500+',
                    'momo_varieties' => '15+',
                    'customer_rating' => '5.0',
                    'total_orders' => '1000+',
                    'total_revenue' => 'Rs.50,000+',
                    'orders_delivered' => '1500+',
                    'years_in_business' => '3+',
                    'growth_percentage' => '15',
                    'satisfaction_rate' => '98',
                    'average_delivery_time' => '25'
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
            // Check if database is accessible
            DB::connection()->getPdo();
            
            $statistics = $this->statisticsService->getFormattedStatistics();
            return response()->json($statistics);
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage());
            
            // Return fallback statistics instead of throwing an error
            return response()->json([
                'happy_customers' => '500+',
                'momo_varieties' => '15+',
                'customer_rating' => '5.0⭐',
                'total_orders' => '1000+',
                'total_revenue' => 'Rs.50,000+',
                'orders_delivered' => '1500+',
                'years_in_business' => '3+',
                'growth_percentage' => '15',
                'satisfaction_rate' => '98',
                'average_delivery_time' => '25'
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

    /**
     * Show the help/guide page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function help()
    {
        try {
            return view('pages.help');
        } catch (\Exception $e) {
            Log::error('Error loading help page: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Unable to load help page');
        }
    }

    /**
     * Show the new user guide page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function newUserGuide()
    {
        try {
            return view('pages.new-user-guide');
        } catch (\Exception $e) {
            Log::error('Error loading new user guide page: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Unable to load new user guide page');
        }
    }
}
