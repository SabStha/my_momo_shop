<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerLifetimeValueService
{
    /**
     * Calculate CLV for a specific segment
     */
    public function getSegmentCLV(string $segment, string $startDate, string $endDate, int $branchId)
    {
        $segmentUsers = $this->getUsersBySegment($segment, $startDate, $endDate, $branchId);
        
        if ($segmentUsers->isEmpty()) {
            return 0;
        }

        $totalRevenue = Order::whereIn('user_id', $segmentUsers->pluck('id'))
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->sum('total');

        $avgPurchaseValue = $totalRevenue / $segmentUsers->count();
        $purchaseFrequency = $this->getPurchaseFrequency($segmentUsers->pluck('id'), $startDate, $endDate, $branchId);
        $customerLifespan = $this->getCustomerLifespan($segmentUsers->pluck('id'), $branchId);

        return $avgPurchaseValue * $purchaseFrequency * $customerLifespan;
    }

    /**
     * Get users belonging to a specific segment
     */
    protected function getUsersBySegment(string $segment, string $startDate, string $endDate, int $branchId)
    {
        return match($segment) {
            'vip' => $this->getVIPUsers($startDate, $endDate, $branchId),
            'loyal' => $this->getLoyalUsers($startDate, $endDate, $branchId),
            'regular' => $this->getRegularUsers($startDate, $endDate, $branchId),
            'new' => $this->getNewUsers($startDate, $endDate, $branchId),
            default => collect()
        };
    }

    /**
     * Get VIP users (spent > 1000 in period)
     */
    protected function getVIPUsers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at');
        })
        ->whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('SUM(total) >= 1000');
        })
        ->get();
    }

    /**
     * Get loyal users (5+ orders in period)
     */
    protected function getLoyalUsers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= 5');
        })
        ->get();
    }

    /**
     * Get regular users (2-4 orders in period)
     */
    protected function getRegularUsers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) BETWEEN 2 AND 4');
        })
        ->get();
    }

    /**
     * Get new users (first order in period)
     */
    protected function getNewUsers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) = 1');
        })
        ->get();
    }

    /**
     * Calculate purchase frequency for a group of users
     */
    protected function getPurchaseFrequency($userIds, string $startDate, string $endDate, int $branchId)
    {
        $totalOrders = Order::whereIn('user_id', $userIds)
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->count();

        return $totalOrders / max(count($userIds), 1);
    }

    /**
     * Calculate average customer lifespan in months
     */
    protected function getCustomerLifespan($userIds, int $branchId)
    {
        $lifespans = Order::whereIn('user_id', $userIds)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select('user_id', DB::raw('DATEDIFF(MAX(created_at), MIN(created_at)) as lifespan'))
            ->groupBy('user_id')
            ->get()
            ->pluck('lifespan');

        return $lifespans->avg() / 30 ?? 0; // Convert days to months
    }
} 