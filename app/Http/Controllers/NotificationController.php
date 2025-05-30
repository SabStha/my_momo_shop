<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // For now, we'll just return a view with some dummy notifications
        $notifications = [
            [
                'id' => 1,
                'title' => 'New Order',
                'message' => 'You have received a new order #123',
                'time' => '2 minutes ago',
                'read' => false
            ],
            [
                'id' => 2,
                'title' => 'Special Offer',
                'message' => 'Get 20% off on your next order!',
                'time' => '1 hour ago',
                'read' => true
            ],
            [
                'id' => 3,
                'title' => 'Order Status',
                'message' => 'Your order #456 has been delivered',
                'time' => '3 hours ago',
                'read' => true
            ]
        ];

        return view('desktop.notifications', compact('notifications'));
    }
} 