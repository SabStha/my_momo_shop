<?php

namespace App\Services;

use App\Models\User;
use App\Models\Offer;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service for sending notifications to mobile app users
 */
class MobileNotificationService
{
    /**
     * Send AI offer notification to a specific user
     */
    public function sendOfferNotification(User $user, Offer $offer)
    {
        try {
            $notification = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'promotion',
                'title' => $offer->title,
                'message' => $offer->description,
                'data' => [
                    'offer_id' => $offer->id,
                    'offer_code' => $offer->code,
                    'offer_title' => $offer->title,
                    'discount' => $offer->discount,
                    'type' => $offer->type,
                    'min_purchase' => $offer->min_purchase,
                    'max_discount' => $offer->max_discount,
                    'valid_until' => $offer->valid_until->toIso8601String(),
                    'action' => 'view_offer',
                    'navigation' => '/menu', // Navigate to menu to use offer
                ],
                'created_at' => now(),
                'read_at' => null,
            ];

            // Save to user's notifications using Laravel's notification system
            $user->notifications()->create([
                'id' => $notification['id'],
                'type' => 'App\Notifications\OfferNotification',
                'data' => $notification,
                'read_at' => null,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ]);

            // Send push notification to user's devices
            $this->sendPushNotification($user, $offer->title, $offer->description, $notification['data']);

            Log::info('Mobile notification sent', [
                'user_id' => $user->id,
                'offer_id' => $offer->id,
                'notification_id' => $notification['id']
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send mobile notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'offer_id' => $offer->id,
            ]);
            return false;
        }
    }
    
    /**
     * Send push notification to user's registered devices
     */
    protected function sendPushNotification(User $user, string $title, string $body, array $data = [])
    {
        try {
            // Get user's device tokens
            $tokens = \App\Models\Device::where('user_id', $user->id)
                ->pluck('token')
                ->toArray();
            
            if (empty($tokens)) {
                Log::info('No device tokens found for user', ['user_id' => $user->id]);
                return;
            }
            
            // Send via Expo Push Service
            $pushService = app(ExpoPushService::class);
            $pushService->send($tokens, $title, $body, $data);
            
            Log::info('Push notification sent to devices', [
                'user_id' => $user->id,
                'device_count' => count($tokens)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send push notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'title' => $title
            ]);
        }
    }

    /**
     * Send AI offer to all active users
     */
    public function broadcastOfferToAllUsers(Offer $offer, $targetAudience = 'all')
    {
        $query = User::query();

        // Filter by target audience
        if ($targetAudience === 'new_customers') {
            $query->whereDoesntHave('orders');
        } elseif ($targetAudience === 'returning_customers') {
            $query->whereHas('orders', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            });
        }

        $users = $query->limit(50)->get(); // Limit to 50 users to prevent overload
        $successCount = 0;

        foreach ($users as $user) {
            if ($this->sendOfferNotification($user, $offer)) {
                $successCount++;
            }
        }

        Log::info('Broadcast AI offer completed', [
            'offer_id' => $offer->id,
            'total_users' => $users->count(),
            'successful_sends' => $successCount,
        ]);

        return [
            'success' => true,
            'total_users' => $users->count(),
            'notifications_sent' => $successCount,
        ];
    }

    /**
     * Send personalized offer notification
     */
    public function sendPersonalizedOffer(User $user, array $offerData)
    {
        try {
            $notification = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'promotion',
                'title' => $offerData['title'] ?? '🎁 Special Offer Just For You!',
                'message' => $offerData['description'] ?? 'We have a personalized offer based on your preferences!',
                'data' => array_merge([
                    'action' => 'view_offer',
                    'navigation' => '/menu',
                    'personalized' => true,
                ], $offerData),
                'created_at' => now(),
                'read_at' => null,
            ];

            $user->notifications()->create([
                'id' => $notification['id'],
                'type' => 'App\Notifications\PersonalizedOfferNotification',
                'data' => $notification,
                'read_at' => null,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send personalized offer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order update notification
     */
    public function sendOrderUpdate(User $user, $order, $status)
    {
        try {
            $statusMessages = [
                'pending' => 'Your order has been received and is being processed.',
                'confirmed' => 'Your order has been confirmed and will be prepared soon!',
                'processing' => 'Your order is being prepared! It will be ready soon.',
                'preparing' => 'Our chefs are preparing your delicious momos!',
                'ready' => 'Your order is ready for pickup or delivery!',
                'out_for_delivery' => 'Your order is on the way! The delivery driver is heading to you.',
                'delivered' => 'Your order has been delivered! Hope you enjoy your meal! 😊',
                'completed' => 'Your order has been delivered. Enjoy your meal!',
                'cancelled' => 'Your order has been cancelled.',
            ];

            $statusIcons = [
                'pending' => '🛒',
                'confirmed' => '✓',
                'processing' => '👨‍🍳',
                'preparing' => '👨‍🍳',
                'ready' => '✅',
                'out_for_delivery' => '🚗',
                'delivered' => '🎉',
                'completed' => '🎉',
                'cancelled' => '❌',
            ];

            $notification = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'order',
                'title' => $statusIcons[$status] . ' Order ' . ucfirst($status),
                'message' => $statusMessages[$status] ?? 'Your order status has been updated.',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? $order->order_code,
                    'status' => $status,
                    'action' => $status === 'delivered' ? 'order_delivered_review' : 'view_order',
                    'navigation' => '/orders/' . $order->id,
                    'show_review_prompt' => $status === 'delivered', // Special flag for delivered orders
                ],
                'created_at' => now(),
                'read_at' => null,
            ];

            $user->notifications()->create([
                'id' => $notification['id'],
                'type' => 'App\Notifications\OrderUpdateNotification',
                'data' => $notification,
                'read_at' => null,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send flash sale notification
     */
    public function sendFlashSaleNotification($title, $message, $data = [])
    {
        try {
            $users = User::limit(50)->get();
            $successCount = 0;

            foreach ($users as $user) {
                $notification = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'promotion',
                    'title' => '⚡ ' . $title,
                    'message' => $message,
                    'data' => array_merge([
                        'action' => 'view_flash_sale',
                        'navigation' => '/menu',
                        'flash_sale' => true,
                    ], $data),
                    'created_at' => now(),
                    'read_at' => null,
                ];

                $user->notifications()->create([
                    'id' => $notification['id'],
                    'type' => 'App\Notifications\FlashSaleNotification',
                    'data' => $notification,
                    'read_at' => null,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                ]);

                $successCount++;
            }

            return [
                'success' => true,
                'notifications_sent' => $successCount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send flash sale notification: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send system notification to all users
     */
    public function sendSystemNotification($title, $message, $type = 'system')
    {
        try {
            $users = User::limit(50)->get();
            $successCount = 0;

            foreach ($users as $user) {
                $notification = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => [
                        'system' => true,
                    ],
                    'created_at' => now(),
                    'read_at' => null,
                ];

                $user->notifications()->create([
                    'id' => $notification['id'],
                    'type' => 'App\Notifications\SystemNotification',
                    'data' => $notification,
                    'read_at' => null,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                ]);

                $successCount++;
            }

            return [
                'success' => true,
                'notifications_sent' => $successCount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send system notification: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clean up old notifications (older than 30 days)
     */
    public function cleanupOldNotifications()
    {
        try {
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            
            $deletedCount = DatabaseNotification::where('created_at', '<', $thirtyDaysAgo)
                ->where('read_at', '!=', null)
                ->delete();

            Log::info('Cleaned up old notifications', ['deleted_count' => $deletedCount]);

            return [
                'success' => true,
                'deleted_count' => $deletedCount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to cleanup notifications: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

