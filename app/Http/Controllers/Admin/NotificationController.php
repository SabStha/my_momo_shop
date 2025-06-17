<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ChurnRiskNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getChurnRisks(Request $request)
    {
        try {
            $service = new ChurnRiskNotificationService();
            return response()->json($service->getCachedNotifications());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }
} 