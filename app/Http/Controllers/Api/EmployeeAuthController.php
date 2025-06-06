<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PosAccessLog;
use Illuminate\Support\Facades\Hash;

class EmployeeAuthController extends Controller
{
    public function verify(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required',
            'password' => 'required'
        ]);

        // Try to find user by email, id, or employee number
        $user = User::where('email', $credentials['identifier'])
                    ->orWhere('id', $credentials['identifier'])
                    ->orWhereHas('employee', function($query) use ($credentials) {
                        $query->where('employee_number', $credentials['identifier']);
                    })
                    ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if user has required role
            if ($user->hasRole(['admin', 'cashier'])) {
                // Log successful login
                PosAccessLog::create([
                    'user_id' => $user->id,
                    'access_type' => 'payment_manager',
                    'action' => 'login',
                    'details' => ['status' => 'success'],
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'name' => $user->name,
                    'is_admin' => $user->hasRole('admin'),
                    'is_cashier' => $user->hasRole('cashier')
                ]);
            }

            // Log failed login due to role
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'payment_manager',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'insufficient_role'],
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access Payment Manager.'
            ], 403);
        }

        // Log failed login attempt
        if ($user) {
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'payment_manager',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'invalid_credentials'],
                'ip_address' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.'
        ], 401);
    }
} 