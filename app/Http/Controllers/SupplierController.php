<?php

namespace App\Http\Controllers;

use App\Models\InventoryOrder;
use App\Models\InventorySupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Confirm order received by supplier
     */
    public function confirmOrder(Request $request, InventoryOrder $order)
    {
        $token = $request->query('token');
        
        // Validate the confirmation token
        if (!$this->validateConfirmationToken($order, $token)) {
            return view('supplier.invalid-token', [
                'message' => 'Invalid or expired confirmation link.'
            ]);
        }

        // Check if order is already confirmed
        if ($order->status === 'received') {
            return view('supplier.order-confirmed', [
                'order' => $order,
                'message' => 'This order has already been confirmed as received.'
            ]);
        }

        try {
            // Update order status to supplier_confirmed (not received yet)
            $order->update([
                'status' => 'supplier_confirmed',
                'supplier_confirmed_at' => now() // New field to track supplier confirmation
            ]);

            // Log the confirmation
            Log::info('Supplier confirmed order delivery:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'supplier_id' => $order->supplier_id,
                'confirmed_at' => now()
            ]);

            // Send email to admin/branch
            try {
                $adminEmail = config('mail.from.address');
                \Mail::to($adminEmail)->send(new \App\Mail\SupplierOrderConfirmationToAdmin($order));
                Log::info('Admin notified of supplier confirmation:', [
                    'order_id' => $order->id,
                    'admin_email' => $adminEmail
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send admin supplier confirmation email:', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            return view('supplier.order-confirmed', [
                'order' => $order,
                'message' => 'Order delivery has been successfully confirmed! The admin will review and mark as received.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming supplier order:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return view('supplier.error', [
                'message' => 'An error occurred while confirming the order. Please try again or contact support.'
            ]);
        }
    }

    /**
     * View order details (for suppliers)
     */
    public function viewOrder(Request $request, InventoryOrder $order)
    {
        $token = $request->query('token');
        
        // Validate the token
        if (!$this->validateConfirmationToken($order, $token)) {
            return view('supplier.invalid-token', [
                'message' => 'Invalid or expired link.'
            ]);
        }

        return view('supplier.order-details', [
            'order' => $order,
            'supplier' => $order->supplier
        ]);
    }

    /**
     * Validate the confirmation token
     */
    private function validateConfirmationToken(InventoryOrder $order, $token): bool
    {
        if (!$token) {
            return false;
        }

        $expectedToken = hash('sha256', $order->id . $order->created_at . config('app.key'));
        
        return hash_equals($expectedToken, $token);
    }

    /**
     * Confirm full order (AJAX)
     */
    public function confirmFullOrder(Request $request, InventoryOrder $order)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'delivery_date' => 'required|date|after:today'
        ]);

        try {
            // Update order status to supplier_confirmed
            $order->update([
                'status' => 'supplier_confirmed',
                'supplier_confirmed_at' => now(),
                'notes' => $order->notes . "\n\nSupplier Notes: " . ($request->notes ?? 'No additional notes'),
                'expected_delivery' => $request->delivery_date
            ]);

            // Log the confirmation
            Log::info('Supplier confirmed full order:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'supplier_id' => $order->supplier_id,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes
            ]);

            // Send email to admin/branch
            try {
                $adminEmail = config('mail.from.address');
                \Mail::to($adminEmail)->send(new \App\Mail\SupplierOrderConfirmationToAdmin($order));
                Log::info('Admin notified of supplier full confirmation:', [
                    'order_id' => $order->id,
                    'admin_email' => $adminEmail
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send admin supplier full confirmation email:', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed successfully! Expected delivery: ' . $request->delivery_date
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming full order:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while confirming the order.'
            ], 500);
        }
    }

    /**
     * Confirm partial order (AJAX)
     */
    public function confirmPartialOrder(Request $request, InventoryOrder $order)
    {
        $request->validate([
            'available_quantities' => 'required|array',
            'available_quantities.*' => 'required|integer|min:0',
            'notes' => 'required|string|max:1000',
            'delivery_date' => 'required|date|after:today'
        ]);

        try {
            // Update order items with available quantities
            foreach ($request->available_quantities as $itemId => $availableQty) {
                $orderItem = $order->items()->find($itemId);
                if ($orderItem) {
                    $orderItem->update([
                        'quantity' => $availableQty,
                        'total_price' => $availableQty * $orderItem->unit_price
                    ]);
                }
            }

            // Recalculate total amount
            $totalAmount = $order->items->sum('total_price');

            // Update order status and details
            $order->update([
                'status' => 'supplier_confirmed',
                'supplier_confirmed_at' => now(),
                'total_amount' => $totalAmount,
                'notes' => $order->notes . "\n\nPartial Confirmation Notes: " . $request->notes,
                'expected_delivery' => $request->delivery_date
            ]);

            // Log the partial confirmation
            Log::info('Supplier confirmed partial order:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'supplier_id' => $order->supplier_id,
                'available_quantities' => $request->available_quantities,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes
            ]);

            // Send email to admin/branch
            try {
                $adminEmail = config('mail.from.address');
                \Mail::to($adminEmail)->send(new \App\Mail\SupplierOrderConfirmationToAdmin($order));
                Log::info('Admin notified of supplier partial confirmation:', [
                    'order_id' => $order->id,
                    'admin_email' => $adminEmail
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send admin supplier partial confirmation email:', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Partial order confirmed successfully! Expected delivery: ' . $request->delivery_date
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming partial order:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while confirming the partial order.'
            ], 500);
        }
    }

    /**
     * Reject order (AJAX)
     */
    public function rejectOrder(Request $request, InventoryOrder $order)
    {
        $request->validate([
            'reason' => 'required|in:out_of_stock,discontinued,price_change,delivery_issue,other',
            'notes' => 'required|string|max:1000'
        ]);

        try {
            // Update order status to rejected
            $order->update([
                'status' => 'rejected',
                'notes' => $order->notes . "\n\nRejection Reason: " . $request->reason . "\nRejection Notes: " . $request->notes
            ]);

            // Log the rejection
            Log::info('Supplier rejected order:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'supplier_id' => $order->supplier_id,
                'reason' => $request->reason,
                'notes' => $request->notes
            ]);

            // Send email to admin/branch
            try {
                $adminEmail = config('mail.from.address');
                \Mail::to($adminEmail)->send(new \App\Mail\SupplierOrderConfirmationToAdmin($order));
                Log::info('Admin notified of supplier rejection:', [
                    'order_id' => $order->id,
                    'admin_email' => $adminEmail
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send admin supplier rejection email:', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order rejected successfully. The branch has been notified.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting order:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the order.'
            ], 500);
        }
    }
}
