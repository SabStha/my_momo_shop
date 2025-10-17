<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MobileNotificationService;
use App\Services\AIOfferService;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileNotificationController extends Controller
{
    protected $mobileNotificationService;

    public function __construct(MobileNotificationService $mobileNotificationService)
    {
        $this->mobileNotificationService = $mobileNotificationService;
    }

    /**
     * Send test notification to all users
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $result = $this->mobileNotificationService->sendSystemNotification(
                '🎉 Welcome to Amako Momo!',
                'Thank you for using our mobile app. Check out our special offers!',
                'system'
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and send AI offers manually
     */
    public function generateAndSendAIOffers(Request $request)
    {
        try {
            $branchId = $request->input('branch_id', 1);
            
            $aiOfferService = app(AIOfferService::class);
            $result = $aiOfferService->generateAIOffers($branchId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully generated {$result['offers_created']} AI offers and sent notifications to mobile users",
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate AI offers',
                    'error' => $result['error'] ?? 'Unknown error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Manual AI offer generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate AI offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send flash sale notification
     */
    public function sendFlashSale(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $result = $this->mobileNotificationService->sendFlashSaleNotification(
                $request->input('title'),
                $request->input('message'),
                $request->input('data', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Flash sale notification sent successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send flash sale notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Broadcast existing offer to users
     */
    public function broadcastOffer(Request $request, $offerId)
    {
        try {
            $offer = Offer::findOrFail($offerId);
            $targetAudience = $request->input('target_audience', 'all');

            $result = $this->mobileNotificationService->broadcastOfferToAllUsers($offer, $targetAudience);

            return response()->json([
                'success' => true,
                'message' => 'Offer broadcast successfully to mobile users',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to broadcast offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function getStatistics()
    {
        try {
            $totalNotifications = \DB::table('notifications')->count();
            $unreadNotifications = \DB::table('notifications')
                ->whereNull('read_at')
                ->count();
            $readNotifications = $totalNotifications - $unreadNotifications;

            $notificationsByType = \DB::table('notifications')
                ->select(
                    \DB::raw("JSON_EXTRACT(data, '$.type') as type"),
                    \DB::raw('COUNT(*) as count')
                )
                ->groupBy('type')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalNotifications,
                    'unread' => $unreadNotifications,
                    'read' => $readNotifications,
                    'by_type' => $notificationsByType,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

