<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'cashier', 'employee']);
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin and cashiers can view all orders
        if ($user->hasAnyRole(['admin', 'cashier'])) {
            return true;
        }

        // Employees can view orders they created
        if ($user->hasRole('employee') && $order->created_by === $user->id) {
            return true;
        }

        // Users can view their own orders
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'cashier', 'employee']);
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only admin and cashiers can update orders
        if ($user->hasAnyRole(['admin', 'cashier'])) {
            return true;
        }

        // Employees can only update pending orders they created
        if ($user->hasRole('employee') && 
            $order->created_by === $user->id && 
            $order->status === 'pending') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admin can delete orders
        if ($user->hasRole('admin')) {
            return true;
        }

        // Cashiers can delete pending orders
        if ($user->hasRole('cashier') && $order->status === 'pending') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can process payments for the order.
     */
    public function processPayment(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['admin', 'cashier']);
    }
}