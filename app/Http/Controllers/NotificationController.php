<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(10);
        return view('notifications', compact('notifications'));
    }

    public function markAsRead(Request $request)
    {
        $notification = auth()->user()->notifications()->findOrFail($request->notification_id);
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }

    public function getChurnRisks()
    {
        try {
            $churnService = new \App\Services\ChurnRiskNotificationService();
            $notifications = $churnService->getCachedNotifications();
            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 