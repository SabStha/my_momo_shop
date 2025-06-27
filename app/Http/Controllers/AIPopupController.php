<?php

namespace App\Http\Controllers;

use App\Services\AIPopupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIPopupController extends Controller
{
    protected $aiPopupService;

    public function __construct(AIPopupService $aiPopupService)
    {
        $this->aiPopupService = $aiPopupService;
    }

    /**
     * Get AI popup decision for current user/context
     */
    public function getPopupDecision(Request $request)
    {
        try {
            $context = $request->input('context', 'homepage');
            $user = auth()->user();
            
            $popupData = $this->aiPopupService->shouldShowPopup($user, $context);
            
            if ($popupData['show_popup']) {
                // Track popup shown
                $this->aiPopupService->trackPopupInteraction(
                    $popupData['offer']->id, 
                    'shown', 
                    $user
                );
                
                return response()->json([
                    'success' => true,
                    'show_popup' => true,
                    'offer' => [
                        'id' => $popupData['offer']->id,
                        'title' => $popupData['offer']->title,
                        'description' => $popupData['offer']->description,
                        'discount' => $popupData['offer']->discount,
                        'code' => $popupData['offer']->code,
                        'min_purchase' => $popupData['offer']->min_purchase,
                        'max_discount' => $popupData['offer']->max_discount,
                        'valid_until' => $popupData['offer']->valid_until,
                        'type' => $popupData['offer']->type,
                        'ai_generated' => $popupData['offer']->ai_generated,
                    ],
                    'timing' => $popupData['timing'],
                    'urgency' => $popupData['urgency'],
                    'reasoning' => $popupData['reasoning']
                ]);
            }
            
            return response()->json([
                'success' => true,
                'show_popup' => false,
                'reason' => $popupData['reason'] ?? 'ai_decision',
                'debug_info' => [
                    'user_logged_in' => $user ? true : false,
                    'context' => $context,
                    'session_popup_shown' => session('popup_shown_session', false),
                    'user_popup_shown' => $user ? session('popup_shown_user_' . $user->id, false) : false,
                    'anonymous_popup_shown' => session('popup_shown_anonymous', false),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('AI Popup Decision Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'show_popup' => false,
                'message' => 'Failed to get popup decision'
            ], 500);
        }
    }

    /**
     * Track popup interaction
     */
    public function trackInteraction(Request $request)
    {
        try {
            $offerId = $request->input('offer_id');
            $action = $request->input('action'); // 'clicked', 'converted', 'dismissed'
            $user = auth()->user();
            
            $this->aiPopupService->trackPopupInteraction($offerId, $action, $user);
            
            return response()->json([
                'success' => true,
                'message' => 'Interaction tracked successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Popup Interaction Tracking Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to track interaction'
            ], 500);
        }
    }

    /**
     * Get popup analytics (for admin)
     */
    public function getAnalytics(Request $request)
    {
        try {
            // This would return popup performance analytics
            // For now, return basic structure
            $analytics = [
                'total_popups_shown' => 0,
                'total_clicks' => 0,
                'total_conversions' => 0,
                'click_through_rate' => 0,
                'conversion_rate' => 0,
                'best_performing_offers' => [],
                'timing_analysis' => [],
            ];
            
            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);
            
        } catch (\Exception $e) {
            Log::error('Popup Analytics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics'
            ], 500);
        }
    }

    /**
     * Reset popup state for testing (temporary - remove in production)
     */
    public function resetPopupState(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Clear all popup-related session data
            session()->forget('popup_shown_session');
            session()->forget('popup_shown_anonymous');
            
            if ($user) {
                session()->forget('popup_shown_user_' . $user->id);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Popup state reset successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Popup Reset Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset popup state'
            ], 500);
        }
    }

    /**
     * Reset popup frequency limits for testing
     */
    public function resetFrequency(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Use the service method to reset frequency
            $this->aiPopupService->resetPopupFrequency($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Popup frequency limits reset successfully',
                'user_id' => $user ? $user->id : 'anonymous'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Popup Frequency Reset Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset popup frequency'
            ], 500);
        }
    }
} 