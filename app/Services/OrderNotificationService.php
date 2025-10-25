<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Centralized service for sending order status notifications
 * Ensures consistent push notifications and in-app notifications
 */
class OrderNotificationService
{
    protected $expoPushService;

    public function __construct(ExpoPushService $expoPushService)
    {
        $this->expoPushService = $expoPushService;
    }

    /**
     * Send notification when order status changes
     */
    public function sendOrderStatusNotification(Order $order, string $newStatus, string $oldStatus)
    {
        if (!$order->user_id) {
            Log::info('Order has no user, skipping notification', ['order_id' => $order->id]);
            return;
        }

        try {
            $orderNumber = $order->order_number ?: $order->code ?: 'ORD-' . $order->id;
            
            // Get user-friendly messages with delivery context
            $message = $this->getStatusMessage($newStatus, $order);
            
            // Get rider info if available
            $riderName = $order->deliveryDriver->name ?? null;
            $riderPhone = $order->deliveryDriver->phone ?? null;
            
            // Calculate ETA based on status
            $etaMinutes = $this->calculateETA($newStatus, $order);
            
            // Save in-app notification
            $user = User::find($order->user_id);
            if ($user) {
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\Notifications\OrderStatusNotification',
                    'data' => [
                        'id' => (string) Str::uuid(),
                        'type' => 'order',
                        'title' => $message['title'],
                        'message' => $message['body'],
                        'data' => [
                            'order_id' => $order->id,
                            'order_number' => $orderNumber,
                            'status' => $newStatus,
                            'action' => 'view_order',
                            'navigation' => "/order/{$order->id}",
                            'rider_name' => $riderName,
                            'rider_phone' => $riderPhone,
                            'eta_min' => $etaMinutes['min'] ?? null,
                            'eta_max' => $etaMinutes['max'] ?? null,
                            'progress' => $this->getProgressPercent($newStatus),
                        ],
                        'created_at' => now(),
                        'read_at' => null,
                    ],
                    'read_at' => null,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                ]);
                
                Log::info('In-app notification created for order status', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'status' => $newStatus
                ]);
            }
            
            // Send push notification to all user's devices with enhanced data
            $tokens = Device::where('user_id', $order->user_id)
                ->pluck('token')
                ->toArray();
            
            if (!empty($tokens)) {
                $pushData = [
                    'orderId' => $order->id,
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'code' => $orderNumber,
                    'status' => $newStatus,
                    'action' => 'view_order',
                    'navigation' => "/order/{$order->id}",
                    'rider_name' => $riderName,
                    'rider_phone' => $riderPhone,
                    'progress' => $this->getProgressPercent($newStatus),
                ];
                
                // Add ETA if available
                if (!empty($etaMinutes)) {
                    $pushData['eta_min'] = $etaMinutes['min'];
                    $pushData['eta_max'] = $etaMinutes['max'];
                }
                
                $this->expoPushService->send(
                    $tokens,
                    $message['title'],
                    $message['body'],
                    $pushData
                );
                
                Log::info('Push notification sent for order status update', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'tokens_count' => count($tokens),
                    'has_rider' => !is_null($riderName),
                    'has_eta' => !empty($etaMinutes)
                ]);
            } else {
                Log::info('No device tokens found for user', [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Calculate ETA based on order status
     */
    protected function calculateETA(string $status, Order $order): array
    {
        // If order has estimated delivery time, use it
        if ($order->estimated_delivery_time) {
            $now = now();
            $estimatedTime = \Carbon\Carbon::parse($order->estimated_delivery_time);
            $minutesLeft = max(0, $now->diffInMinutes($estimatedTime, false));
            
            if ($minutesLeft > 0) {
                return [
                    'min' => max(1, $minutesLeft - 5),  // Buffer of 5 minutes
                    'max' => $minutesLeft + 5
                ];
            }
        }
        
        // Fallback: status-based estimates
        $estimates = [
            'out_for_delivery' => ['min' => 15, 'max' => 25],
            'ready' => ['min' => 20, 'max' => 30],
            'preparing' => ['min' => 25, 'max' => 35],
            'confirmed' => ['min' => 30, 'max' => 40],
        ];
        
        return $estimates[$status] ?? [];
    }
    
    /**
     * Get progress percentage based on status
     */
    protected function getProgressPercent(string $status): int
    {
        $progressMap = [
            'pending' => 10,
            'confirmed' => 20,
            'preparing' => 40,
            'processing' => 40,
            'ready' => 60,
            'out_for_delivery' => 80,
            'delivered' => 100,
            'completed' => 100,
        ];
        
        return $progressMap[$status] ?? 50;
    }

    /**
     * Get user-friendly status messages with delivery context
     */
    protected function getStatusMessage(string $status, Order $order = null): array
    {
        $orderNumber = $order ? ($order->order_number ?: $order->code ?: 'ORD-' . $order->id) : '';
        $riderName = $order && $order->deliveryDriver ? $order->deliveryDriver->name : null;
        $etaMinutes = $this->calculateETA($status, $order);
        
        // Format ETA string
        $etaStr = '';
        if (!empty($etaMinutes)) {
            $etaStr = " — ETA {$etaMinutes['min']}-{$etaMinutes['max']} min";
        }
        
        // Format rider info
        $riderInfo = $riderName ? "Rider {$riderName} " : '';
        
        $messages = [
            'pending' => [
                'title' => '📝 Order received',
                'body' => "Order {$orderNumber} • We're processing your order"
            ],
            'confirmed' => [
                'title' => '✅ Order confirmed',
                'body' => "Order {$orderNumber} • Kitchen preparing your momos"
            ],
            'processing' => [
                'title' => '👨‍🍳 Preparing your order',
                'body' => "Order {$orderNumber} • Fresh momos being made"
            ],
            'preparing' => [
                'title' => '👨‍🍳 Preparing your order',
                'body' => "Order {$orderNumber} • Fresh momos being made"
            ],
            'ready' => [
                'title' => '📦 Order ready',
                'body' => "{$riderInfo}will pick up soon • {$orderNumber}"
            ],
            'out_for_delivery' => [
                'title' => "🛵 Delivery started{$etaStr}",
                'body' => "{$riderInfo}picked up • {$orderNumber}"
            ],
            'delivered' => [
                'title' => '✅ Delivered! Enjoy your momos',
                'body' => "Order {$orderNumber} • Rate your experience"
            ],
            'completed' => [
                'title' => '✨ Order completed',
                'body' => "Order {$orderNumber} • Thank you for ordering!"
            ],
            'cancelled' => [
                'title' => '❌ Order cancelled',
                'body' => "Order {$orderNumber} • Refund will be processed"
            ],
        ];
        
        return $messages[$status] ?? [
            'title' => '📦 Order updated',
            'body' => "Order {$orderNumber} • Status: " . str_replace('_', ' ', $status)
        ];
    }
}

