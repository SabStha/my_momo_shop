<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MobileNotificationService;
use App\Services\OrderNotificationService;
use App\Models\User;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Test controller for sending notifications to mobile app
 * For testing purposes only
 */
class TestNotificationController extends Controller
{
    protected $mobileNotificationService;
    protected $orderNotificationService;

    public function __construct(
        MobileNotificationService $mobileNotificationService,
        OrderNotificationService $orderNotificationService
    ) {
        $this->mobileNotificationService = $mobileNotificationService;
        $this->orderNotificationService = $orderNotificationService;
    }

    /**
     * Test offer notification
     * POST /api/test/notification/offer
     */
    public function testOfferNotification(Request $request)
    {
        try {
            $user = auth()->user() ?? User::first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found. Please login first.'
                ], 404);
            }

            // Create or get a test offer
            $offer = Offer::where('code', 'TEST20')->first();
            
            if (!$offer) {
                $offer = Offer::create([
                    'code' => 'TEST20',
                    'title' => '🎉 20% Off Special!',
                    'description' => 'Limited time offer! Get 20% off on all momos. Valid for next 24 hours!',
                    'discount' => 20,
                    'type' => 'percentage',
                    'min_purchase' => 500,
                    'max_discount' => 200,
                    'valid_from' => now(),
                    'valid_until' => now()->addDays(1),
                    'is_active' => true,
                    'usage_limit' => 100,
                    'used_count' => 0,
                ]);
            }

            // Send the notification
            $result = $this->mobileNotificationService->sendOfferNotification($user, $offer);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Offer notification sent successfully!',
                    'notification' => [
                        'title' => $offer->title,
                        'message' => $offer->description,
                        'code' => $offer->code,
                        'discount' => $offer->discount . '%'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Test notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test delivery notification
     * POST /api/test/notification/delivery
     */
    public function testDeliveryNotification(Request $request)
    {
        try {
            $user = auth()->user() ?? User::first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found. Please login first.'
                ], 404);
            }

            // Get or create a test order
            $order = $user->orders()->latest()->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found. Place an order first to test delivery notifications.'
                ], 404);
            }

            // Test different statuses
            $status = $request->input('status', 'out_for_delivery');
            $oldStatus = 'ready';

            // Set test rider info if testing delivery
            if (in_array($status, ['out_for_delivery', 'ready', 'arriving'])) {
                // Try to assign a delivery driver if not already assigned
                if (!$order->driver_id) {
                    $driver = User::whereHas('roles', function($q) {
                        $q->where('name', 'delivery_driver');
                    })->first();
                    
                    if ($driver) {
                        $order->driver_id = $driver->id;
                        $order->estimated_delivery_time = now()->addMinutes(20);
                        $order->save();
                    }
                }
            }

            // Send the notification
            $this->orderNotificationService->sendOrderStatusNotification($order, $status, $oldStatus);

            return response()->json([
                'success' => true,
                'message' => 'Delivery notification sent successfully!',
                'notification' => [
                    'order_number' => $order->order_number ?? $order->code ?? 'ORD-' . $order->id,
                    'status' => $status,
                    'has_rider' => !is_null($order->driver_id),
                    'eta' => $order->estimated_delivery_time ? $order->estimated_delivery_time->format('g:i A') : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Test delivery notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test flash sale notification
     * POST /api/test/notification/flash-sale
     */
    public function testFlashSaleNotification(Request $request)
    {
        try {
            $result = $this->mobileNotificationService->sendFlashSaleNotification(
                'Flash Sale!',
                '⚡ Limited time only! Get 30% off on all momos for the next 2 hours!',
                [
                    'discount' => 30,
                    'duration_minutes' => 120,
                    'expires_at' => now()->addHours(2)->toIso8601String()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Flash sale notification sent!',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Test flash sale notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test system notification
     * POST /api/test/notification/system
     */
    public function testSystemNotification(Request $request)
    {
        try {
            $result = $this->mobileNotificationService->sendSystemNotification(
                '📢 App Update Available',
                'A new version of Amako Momo app is available. Update now for the best experience!',
                'system'
            );

            return response()->json([
                'success' => true,
                'message' => 'System notification sent!',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Test system notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test all notifications
     * POST /api/test/notification/all
     */
    public function testAllNotifications(Request $request)
    {
        try {
            $results = [];

            // 1. Offer notification
            try {
                $offerResult = $this->testOfferNotification($request);
                $results['offer'] = json_decode($offerResult->getContent(), true);
            } catch (\Exception $e) {
                $results['offer'] = ['success' => false, 'error' => $e->getMessage()];
            }

            // Wait 2 seconds
            sleep(2);

            // 2. Delivery notification
            try {
                $deliveryResult = $this->testDeliveryNotification($request);
                $results['delivery'] = json_decode($deliveryResult->getContent(), true);
            } catch (\Exception $e) {
                $results['delivery'] = ['success' => false, 'error' => $e->getMessage()];
            }

            return response()->json([
                'success' => true,
                'message' => 'All test notifications sent!',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Test all notifications error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending notifications: ' . $e->getMessage()
            ], 500);
        }
    }
}

