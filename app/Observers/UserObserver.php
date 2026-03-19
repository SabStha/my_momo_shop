<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Customer;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        Customer::create([
            'branch_id' => 1, // Default branch or modify if branch selection exists
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'city' => $user->city ?? null,
            'address' => $user->address ?? null,
            'total_spent' => 0,
            'total_orders' => 0,
            'loyalty_points' => 0,
            'customer_segment' => 'New',
            'is_active' => true,
        ]);
    }
}
