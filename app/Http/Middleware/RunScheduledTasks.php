<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RunScheduledTasks
{
    /**
     * Handle an incoming request and run scheduled tasks if needed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run on web requests, not API or admin requests
        if (!$request->is('api/*') && !$request->is('admin/*')) {
            $this->runSchedulerIfNeeded();
        }

        return $next($request);
    }

    /**
     * Run the Laravel scheduler if it hasn't run recently
     */
    private function runSchedulerIfNeeded(): void
    {
        $cacheKey = 'scheduler_last_run';
        $lastRun = Cache::get($cacheKey);

        // Only run if it hasn't run in the last minute
        if (!$lastRun || now()->diffInSeconds($lastRun) >= 60) {
            try {
                // Run scheduler in background (non-blocking)
                Artisan::call('schedule:run');
                
                // Update last run time
                Cache::put($cacheKey, now(), now()->addMinutes(5));
            } catch (\Exception $e) {
                // Silently fail - don't interrupt user experience
                \Log::error('Scheduler failed: ' . $e->getMessage());
            }
        }
    }
}

