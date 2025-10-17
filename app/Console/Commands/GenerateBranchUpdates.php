<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\BranchUpdate;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class GenerateBranchUpdates extends Command
{
    protected $signature = 'updates:generate-branch';
    protected $description = 'Automatically generate branch updates based on performance data';

    public function handle()
    {
        $this->info('🤖 AI Branch Update Generator Starting...');
        $this->info('');

        $branches = Branch::where('is_active', true)->get();
        $totalUpdates = 0;

        foreach ($branches as $branch) {
            $this->info("📍 Analyzing {$branch->name}...");
            
            // Check various metrics and generate updates
            $updates = 0;
            
            // 1. Check for sales milestones (weekly orders)
            $updates += $this->checkSalesMilestone($branch);
            
            // 2. Check for new popular products
            $updates += $this->checkPopularProducts($branch);
            
            // 3. Check for customer review highlights (simulated)
            $updates += $this->generateReviewHighlight($branch);
            
            // 4. Check for growth trends
            $updates += $this->checkGrowthTrend($branch);
            
            $this->info("   ✅ Generated {$updates} update(s)");
            $totalUpdates += $updates;
        }

        $this->info('');
        $this->info("🎉 Complete! Generated {$totalUpdates} total updates across all branches.");
        
        return 0;
    }

    private function checkSalesMilestone($branch)
    {
        // Get this week's orders
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $weeklyOrders = Order::where('branch_id', $branch->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        // Check for milestones
        $milestones = [150, 200, 250, 300, 500, 1000];
        
        foreach ($milestones as $milestone) {
            if ($weeklyOrders >= $milestone) {
                // Check if we already posted this milestone this week
                $exists = BranchUpdate::where('branch_id', $branch->id)
                    ->where('type', 'sales_update')
                    ->where('title', 'LIKE', "%{$milestone}+%")
                    ->whereBetween('published_at', [$weekStart, $weekEnd])
                    ->exists();

                if (!$exists) {
                    BranchUpdate::create([
                        'branch_id' => $branch->id,
                        'type' => 'sales_update',
                        'title' => "{$branch->name} hit {$milestone}+ orders this week! 🎉",
                        'content' => 'Strong performance with excellent customer feedback and repeat orders.',
                        'icon' => '📈',
                        'is_published' => true,
                        'published_at' => now(),
                        'created_by' => 1,
                    ]);
                    
                    return 1;
                }
                break; // Only post highest milestone reached
            }
        }

        return 0;
    }

    private function checkPopularProducts($branch)
    {
        // Get this week's top selling products
        $weekStart = now()->startOfWeek();
        
        $topProducts = Order::where('branch_id', $branch->id)
            ->where('created_at', '>=', $weekStart)
            ->with('items.product')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'product' => $items->first()->product,
                    'quantity' => $items->sum('quantity')
                ];
            })
            ->sortByDesc('quantity')
            ->take(3);

        if ($topProducts->isNotEmpty()) {
            $topProduct = $topProducts->first();
            
            // Only post if sold more than 50 units this week
            if ($topProduct['quantity'] > 50) {
                // Check if not posted recently
                $exists = BranchUpdate::where('branch_id', $branch->id)
                    ->where('type', 'promo_update')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$exists) {
                    $productName = $topProduct['product']->name ?? 'menu items';
                    
                    BranchUpdate::create([
                        'branch_id' => $branch->id,
                        'type' => 'promo_update',
                        'title' => "🔥 {$productName} flying off the shelves!",
                        'content' => "Our customers can't get enough! {$topProduct['quantity']}+ orders this week. Limited availability, order now!",
                        'icon' => '🔥',
                        'is_published' => true,
                        'published_at' => now(),
                        'created_by' => 1,
                    ]);
                    
                    return 1;
                }
            }
        }

        return 0;
    }

    private function generateReviewHighlight($branch)
    {
        // Simulated positive reviews (replace with real review system later)
        $reviews = [
            '"Best momos in town! The service is amazing and the food is always fresh."',
            '"Absolutely loved the food! Will definitely be coming back again."',
            '"Great atmosphere and friendly staff. The momos are delicious!"',
            '"Amazing quality and portions. Best value for money in Kathmandu!"',
            '"These are the most authentic momos I\'ve tasted outside of Tibet!"',
            '"Five stars! The spicy sauce is to die for!"',
            '"Perfect spot for a quick lunch. Fast service and great taste."',
            '"My family\'s favorite momo place. Never disappoints!"',
        ];

        // Only post review once a week per branch
        $lastReview = BranchUpdate::where('branch_id', $branch->id)
            ->where('type', 'review_highlight')
            ->where('created_at', '>=', now()->subWeek())
            ->exists();

        if (!$lastReview && rand(1, 2) === 1) { // 50% chance
            BranchUpdate::create([
                'branch_id' => $branch->id,
                'type' => 'review_highlight',
                'title' => 'Customer Review Highlight',
                'content' => $reviews[array_rand($reviews)],
                'icon' => '⭐',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => 1,
            ]);
            
            return 1;
        }

        return 0;
    }

    private function checkGrowthTrend($branch)
    {
        // Compare this week vs last week
        $thisWeekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $thisWeekOrders = Order::where('branch_id', $branch->id)
            ->where('created_at', '>=', $thisWeekStart)
            ->count();

        $lastWeekOrders = Order::where('branch_id', $branch->id)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        if ($lastWeekOrders > 0) {
            $growth = (($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100;

            // Significant growth (>30%)
            if ($growth > 30) {
                // Check if not posted recently
                $exists = BranchUpdate::where('branch_id', $branch->id)
                    ->where('type', 'milestone')
                    ->where('title', 'LIKE', '%growth%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$exists) {
                    BranchUpdate::create([
                        'branch_id' => $branch->id,
                        'type' => 'milestone',
                        'title' => "📊 {$branch->name} experiencing rapid growth!",
                        'content' => sprintf(
                            'Orders increased by %d%% this week! Our customers love what we\'re serving.',
                            round($growth)
                        ),
                        'icon' => '📊',
                        'is_published' => true,
                        'published_at' => now(),
                        'created_by' => 1,
                    ]);
                    
                    return 1;
                }
            }
        }

        return 0;
    }
}
