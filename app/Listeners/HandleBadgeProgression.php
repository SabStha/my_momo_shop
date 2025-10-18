<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\BadgeProgressionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleBadgeProgression
{
    use InteractsWithQueue;

    protected $badgeService;

    /**
     * Create the event listener.
     */
    public function __construct(BadgeProgressionService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        
        // Process badge progression for completed, delivered, and pending orders
        if (!in_array($order->status, ['completed', 'delivered', 'pending'])) {
            Log::info('Badge progression skipped - order status not eligible', [
                'order_id' => $order->id,
                'status' => $order->status
            ]);
            return;
        }

        try {
            Log::info('Processing badge progression for order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total' => $order->total_amount
            ]);

            // Process badge progression
            $this->badgeService->processOrderCompletion($order);

            Log::info('Badge progression completed successfully', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing badge progression', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 