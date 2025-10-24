<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Determines optimal times to send offers to each user
 */
class SmartTimingEngine
{
    /**
     * Get optimal time to send notification to user
     */
    public function getOptimalSendTime(User $user): Carbon
    {
        $cacheKey = "optimal_time_{$user->id}";
        
        $optimalHour = Cache::remember($cacheKey, 3600, function() use ($user) {
            return $this->calculateOptimalHour($user);
        });
        
        $sendTime = Carbon::now();
        
        // If we've passed the optimal hour today, schedule for tomorrow
        if ($sendTime->hour >= $optimalHour) {
            $sendTime->addDay();
        }
        
        $sendTime->setTime($optimalHour, 0, 0);
        
        // Apply quiet hours if user has preferences
        $sendTime = $this->respectQuietHours($user, $sendTime);
        
        // Avoid sending during business off-hours (before 9 AM or after 9 PM)
        if ($sendTime->hour < 9) {
            $sendTime->setTime(9, 0, 0);
        } elseif ($sendTime->hour >= 21) {
            $sendTime->addDay()->setTime(9, 0, 0);
        }
        
        return $sendTime;
    }

    /**
     * Calculate optimal hour based on user's order history
     */
    protected function calculateOptimalHour(User $user): int
    {
        $orders = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(90))
            ->get();
        
        if ($orders->isEmpty()) {
            return 11; // Default to 11 AM
        }
        
        // Group orders by hour
        $hourCounts = $orders->groupBy(function($order) {
            return $order->created_at->hour;
        })->map->count();
        
        // Get most common hour
        $mostCommonHour = $hourCounts->sortDesc()->keys()->first();
        
        // Send notification 2 hours before most common order time
        $notificationHour = max(9, $mostCommonHour - 2);
        
        return min(18, $notificationHour); // Cap at 6 PM
    }

    /**
     * Respect user's quiet hours
     */
    protected function respectQuietHours(User $user, Carbon $sendTime): Carbon
    {
        $preferences = $user->offerPreferences()->first();
        
        if (!$preferences || !$preferences->quiet_hours_start || !$preferences->quiet_hours_end) {
            return $sendTime;
        }
        
        $quietStart = Carbon::createFromTimeString($preferences->quiet_hours_start);
        $quietEnd = Carbon::createFromTimeString($preferences->quiet_hours_end);
        
        // If send time falls in quiet hours, move to after quiet hours end
        if ($sendTime->between($quietStart, $quietEnd)) {
            $sendTime->setTime($quietEnd->hour, $quietEnd->minute, 0);
        }
        
        return $sendTime;
    }

    /**
     * Check if user prefers notifications on weekends
     */
    public function isWeekendPreferred(User $user): bool
    {
        $orders = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(90))
            ->get();
        
        if ($orders->isEmpty()) {
            return false;
        }
        
        $weekendOrders = $orders->filter(function($order) {
            return $order->created_at->isWeekend();
        })->count();
        
        $weekdayOrders = $orders->count() - $weekendOrders;
        
        return $weekendOrders > $weekdayOrders;
    }

    /**
     * Get best day of week to send offer
     */
    public function getOptimalDayOfWeek(User $user): int
    {
        $orders = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(90))
            ->get();
        
        if ($orders->isEmpty()) {
            return Carbon::FRIDAY; // Default to Friday
        }
        
        $dayCounts = $orders->groupBy(function($order) {
            return $order->created_at->dayOfWeek;
        })->map->count();
        
        return $dayCounts->sortDesc()->keys()->first() ?? Carbon::FRIDAY;
    }

    /**
     * Schedule offer send considering all factors
     */
    public function scheduleOptimalSend(User $user, string $offerType = 'general'): Carbon
    {
        $baseTime = $this->getOptimalSendTime($user);
        
        // For flash sales, send immediately during business hours
        if ($offerType === 'flash') {
            $now = Carbon::now();
            if ($now->hour >= 9 && $now->hour < 21) {
                return $now->addMinutes(5); // Send in 5 minutes
            }
            return $now->setTime(9, 0, 0); // Wait until 9 AM
        }
        
        // For weekend shoppers, schedule for Friday/Saturday
        if ($this->isWeekendPreferred($user) && !$baseTime->isWeekend()) {
            $baseTime = $baseTime->next(Carbon::FRIDAY);
        }
        
        // For birthday offers, send at 9 AM on birthday
        if ($offerType === 'birthday') {
            return $baseTime->setTime(9, 0, 0);
        }
        
        return $baseTime;
    }

    /**
     * Check if it's a good time to send notifications now
     */
    public function isGoodTimeToSendNow(): bool
    {
        $now = Carbon::now();
        
        // Not too early or too late
        if ($now->hour < 9 || $now->hour >= 21) {
            return false;
        }
        
        // Avoid lunch rush (12-2 PM) and dinner rush (6-8 PM)
        if (($now->hour >= 12 && $now->hour < 14) || ($now->hour >= 18 && $now->hour < 20)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get frequency limit for user (avoid spam)
     */
    public function canSendToUser(User $user): bool
    {
        $preferences = $user->offerPreferences()->first();
        $frequency = $preferences?->frequency_preference ?? 'weekly';
        
        // Check last notification sent
        $lastSent = \DB::table('offer_analytics')
            ->where('user_id', $user->id)
            ->where('action', 'received')
            ->latest('timestamp')
            ->first();
        
        if (!$lastSent) {
            return true; // Never sent before
        }
        
        $lastSentDate = Carbon::parse($lastSent->timestamp);
        
        switch ($frequency) {
            case 'daily':
                return $lastSentDate->diffInHours(now()) >= 24;
            case 'weekly':
                return $lastSentDate->diffInDays(now()) >= 7;
            case 'monthly':
                return $lastSentDate->diffInDays(now()) >= 30;
            default:
                return $lastSentDate->diffInDays(now()) >= 7;
        }
    }

    /**
     * Calculate best time for specific trigger type
     */
    public function getBestTimeForTrigger(string $triggerType): array
    {
        return match($triggerType) {
            'new_user_welcome' => ['hour' => 10, 'delay_hours' => 24],
            'inactive_user' => ['hour' => 11, 'delay_hours' => 0],
            'birthday' => ['hour' => 9, 'delay_hours' => 0],
            'high_value_vip' => ['hour' => 14, 'delay_hours' => 0],
            'abandoned_cart' => ['hour' => null, 'delay_hours' => 2], // 2 hours after abandonment
            default => ['hour' => 11, 'delay_hours' => 0],
        };
    }
}

