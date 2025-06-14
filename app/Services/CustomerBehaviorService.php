<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\CustomerSegment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerBehaviorService
{
    /**
     * Check for churn risk triggers
     */
    public function checkChurnTriggers(int $branchId)
    {
        $churnWindow = now()->subDays(90);
        
        return User::whereHas('orders', function ($query) use ($churnWindow, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '<', $churnWindow)
                ->whereNull('deleted_at');
        })
        ->whereDoesntHave('orders', function ($query) use ($churnWindow, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '>=', $churnWindow)
                ->whereNull('deleted_at');
        })
        ->get()
        ->map(function ($user) {
            return [
                'user_id' => $user->id,
                'last_order' => $user->orders()->latest()->first()?->created_at,
                'total_spent' => $user->orders()->sum('total'),
                'trigger_type' => 'churn_risk',
                'risk_level' => $this->calculateChurnRisk($user)
            ];
        });
    }

    /**
     * Check for VIP triggers
     */
    public function checkVIPTriggers(int $branchId)
    {
        $weekAgo = now()->subWeek();
        
        return User::whereHas('orders', function ($query) use ($weekAgo, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '>=', $weekAgo)
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= 3');
        })
        ->get()
        ->map(function ($user) {
            return [
                'user_id' => $user->id,
                'order_count' => $user->orders()->count(),
                'total_spent' => $user->orders()->sum('total'),
                'trigger_type' => 'vip_candidate',
                'last_order' => $user->orders()->latest()->first()?->created_at
            ];
        });
    }

    /**
     * Calculate churn risk score for a user
     */
    protected function calculateChurnRisk(User $user)
    {
        $lastOrder = $user->orders()->latest()->first();
        if (!$lastOrder) return 'high';

        $daysSinceLastOrder = now()->diffInDays($lastOrder->created_at);
        $orderFrequency = $this->getOrderFrequency($user);
        $avgOrderValue = $user->orders()->avg('total');

        if ($daysSinceLastOrder > 90) return 'high';
        if ($daysSinceLastOrder > 60) return 'medium';
        if ($daysSinceLastOrder > 30) return 'low';
        return 'safe';
    }

    /**
     * Get average days between orders for a user
     */
    protected function getOrderFrequency(User $user)
    {
        $orders = $user->orders()
            ->orderBy('created_at')
            ->get();

        if ($orders->count() < 2) return 0;

        $totalDays = 0;
        for ($i = 1; $i < $orders->count(); $i++) {
            $totalDays += $orders[$i]->created_at->diffInDays($orders[$i-1]->created_at);
        }

        return $totalDays / ($orders->count() - 1);
    }

    /**
     * Get behavioral patterns for a user
     */
    public function getUserBehaviorPatterns(int $userId, int $branchId)
    {
        $user = User::findOrFail($userId);
        
        return [
            'purchase_frequency' => $this->getOrderFrequency($user),
            'preferred_categories' => $this->getPreferredCategories($user, $branchId),
            'average_order_value' => $user->orders()->where('branch_id', $branchId)->avg('total'),
            'peak_purchase_hours' => $this->getPeakPurchaseHours($user, $branchId),
            'preferred_payment_method' => $this->getPreferredPaymentMethod($user, $branchId)
        ];
    }

    /**
     * Get user's preferred product categories
     */
    protected function getPreferredCategories(User $user, int $branchId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.branch_id', $branchId)
            ->whereNull('orders.deleted_at')
            ->select('products.category', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category')
            ->orderByDesc('count')
            ->limit(3)
            ->get();
    }

    /**
     * Get user's peak purchase hours
     */
    protected function getPeakPurchaseHours(User $user, int $branchId)
    {
        return DB::table('orders')
            ->where('user_id', $user->id)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(3)
            ->get();
    }

    /**
     * Get user's preferred payment method
     */
    protected function getPreferredPaymentMethod(User $user, int $branchId)
    {
        return DB::table('orders')
            ->where('user_id', $user->id)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->first();
    }
} 