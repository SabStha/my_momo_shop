<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DeliveryTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    /**
     * Show delivery dashboard for drivers
     */
    public function index()
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                \Log::warning('Delivery dashboard accessed without authentication');
                return redirect()->route('login')->with('error', 'Please log in to access the delivery dashboard');
            }
            
            $driverId = auth()->id();
            \Log::info('Delivery dashboard accessed', ['driver_id' => $driverId]);
            
            // Get assigned orders with error handling
            try {
                $assignedOrders = Order::where('assigned_driver_id', $driverId)
                    ->whereIn('status', ['ready', 'out_for_delivery'])
                    ->with(['items', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                \Log::error('Failed to fetch assigned orders: ' . $e->getMessage());
                $assignedOrders = collect(); // Empty collection
            }
            
            // Get available orders (ready but not assigned)
            try {
                $availableOrders = Order::where('status', 'ready')
                    ->whereNull('assigned_driver_id')
                    ->with(['items', 'user'])
                    ->orderBy('created_at', 'asc')
                    ->get();
            } catch (\Exception $e) {
                \Log::error('Failed to fetch available orders: ' . $e->getMessage());
                $availableOrders = collect(); // Empty collection
            }
            
            \Log::info('Delivery dashboard data loaded', [
                'assigned_count' => $assignedOrders->count(),
                'available_count' => $availableOrders->count()
            ]);
            
            // Separate assigned orders into active deliveries and other statuses
            $activeDeliveries = $assignedOrders->where('status', 'out_for_delivery');
            $readyOrders = $assignedOrders->where('status', 'ready');
            
            return view('delivery.dashboard', compact('assignedOrders', 'availableOrders', 'activeDeliveries', 'readyOrders'));
            
        } catch (\Exception $e) {
            \Log::error('Delivery dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('errors.500', [
                'message' => 'Failed to load delivery dashboard',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Start delivery (change status from ready to out_for_delivery)
     */
    public function startDelivery(Request $request, $orderId)
    {
        try {
            $request->validate([
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);
            
            DB::beginTransaction();
            
            $order = Order::findOrFail($orderId);
            
            // Verify driver is assigned to this order
            if ($order->assigned_driver_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Verify order is ready
            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not ready for delivery'
                ], 400);
            }
            
            // Update order status
            $order->status = 'out_for_delivery';
            $order->save();
            
            // Create tracking record
            DeliveryTracking::create([
                'order_id' => $order->id,
                'driver_id' => auth()->id(),
                'status' => 'out_for_delivery',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            
            DB::commit();
            
            \Log::info('Delivery started', [
                'order_id' => $orderId,
                'driver_id' => auth()->id(),
                'location' => $request->latitude ? "{$request->latitude}, {$request->longitude}" : 'not provided'
            ]);
            
            // Notify customer
            if ($order->user_id) {
                try {
                    $mobileNotificationService = app(\App\Services\MobileNotificationService::class);
                    $mobileNotificationService->sendOrderUpdate(
                        $order->user,
                        $order,
                        'out_for_delivery'
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send notification: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Delivery started successfully',
                'order' => $order->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to start delivery: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start delivery'
            ], 500);
        }
    }

    /**
     * Accept an order for delivery
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();
            
            $order = Order::findOrFail($orderId);
            
            // Check if order is ready and not assigned
            if ($order->status !== 'ready' || $order->assigned_driver_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not available for delivery'
                ], 400);
            }
            
            // Assign to driver
            $order->assigned_driver_id = auth()->id();
            $order->status = 'out_for_delivery';
            $order->out_for_delivery_at = now();
            $order->save();
            
            // Create tracking record
            DeliveryTracking::create([
                'order_id' => $order->id,
                'driver_id' => auth()->id(),
                'status' => 'accepted',
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ]);
            
            DB::commit();
            
            \Log::info('Order accepted by delivery driver', [
                'order_id' => $orderId,
                'driver_id' => auth()->id()
            ]);
            
            // Notify customer
            if ($order->user_id) {
                try {
                    $mobileNotificationService = app(\App\Services\MobileNotificationService::class);
                    $mobileNotificationService->sendOrderUpdate(
                        $order->user,
                        $order,
                        'out_for_delivery'
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send notification: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order accepted for delivery',
                'order' => $order->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to accept order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept order'
            ], 500);
        }
    }
    
    /**
     * Update delivery location (GPS tracking)
     */
    public function updateLocation(Request $request, $orderId)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        try {
            $order = Order::findOrFail($orderId);
            
            // Verify driver is assigned to this order
            if ($order->assigned_driver_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Update tracking
            DeliveryTracking::create([
                'order_id' => $order->id,
                'driver_id' => auth()->id(),
                'status' => 'location_update',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Location updated'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to update location: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location'
            ], 500);
        }
    }
    
    /**
     * Mark order as delivered with photo proof
     */
    public function markAsDelivered(Request $request, $orderId)
    {
        $request->validate([
            'delivery_photo' => 'required|image|max:5120', // 5MB max
            'notes' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        
        try {
            DB::beginTransaction();
            
            $order = Order::findOrFail($orderId);
            
            // Verify driver is assigned to this order
            if ($order->assigned_driver_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Upload delivery photo
            $photoPath = null;
            if ($request->hasFile('delivery_photo')) {
                $photoPath = $request->file('delivery_photo')->store('delivery-proofs', 'public');
            }
            
            // Update order
            $order->status = 'delivered';
            $order->delivered_at = now();
            $order->delivery_photo = $photoPath;
            $order->delivery_notes = $request->notes;
            $order->save();
            
            // Create final tracking record
            DeliveryTracking::create([
                'order_id' => $order->id,
                'driver_id' => auth()->id(),
                'status' => 'delivered',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'photo_url' => $photoPath,
                'notes' => $request->notes,
            ]);
            
            DB::commit();
            
            \Log::info('Order delivered', [
                'order_id' => $orderId,
                'driver_id' => auth()->id(),
                'photo_uploaded' => $photoPath ? 'yes' : 'no'
            ]);
            
            // Notify customer
            if ($order->user_id) {
                try {
                    $mobileNotificationService = app(\App\Services\MobileNotificationService::class);
                    $mobileNotificationService->sendOrderUpdate(
                        $order->user,
                        $order,
                        'delivered'
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send notification: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order marked as delivered',
                'order' => $order->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to mark as delivered: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as delivered'
            ], 500);
        }
    }
    
    /**
     * Get delivery tracking history for an order
     */
    public function getTracking($orderId)
    {
        try {
            $tracking = DeliveryTracking::where('order_id', $orderId)
                ->with('driver')
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'tracking' => $tracking
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to get tracking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tracking'
            ], 500);
        }
    }
}

