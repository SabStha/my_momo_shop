<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LoyaltyController extends Controller
{
    /**
     * Get loyalty summary for the authenticated user.
     */
    public function summary(Request $request): JsonResponse
    {
        // TODO: In Sprint 3, compute real values from user's activity
        // For now, return stub data
        
        return response()->json([
            'credits' => 1250, // integer NPR credits (stub for now)
            'tier' => 'Silver',
            'badges' => [
                [
                    'id' => 'try-new',
                    'name' => 'Explorer',
                    'tier' => 'Silver'
                ],
                [
                    'id' => 'streak-7',
                    'name' => '7-Day Streak',
                    'tier' => 'Bronze'
                ],
                [
                    'id' => 'first-order',
                    'name' => 'First Order',
                    'tier' => 'Gold'
                ],
                [
                    'id' => 'loyal-customer',
                    'name' => 'Loyal Customer',
                    'tier' => 'Silver'
                ],
            ],
        ]);
    }
}
