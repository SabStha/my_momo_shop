<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Branch;
use App\Models\PosAccessLog;
use Illuminate\Support\Facades\Hash;

class PaymentAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $branchId = $request->query('branch');
        
        if (!$branchId) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }

        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Invalid or inactive branch.');
        }

        return view('auth.payment-login', compact('branch'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required', // can be email or user_id
            'password' => 'required',
            'branch_id' => 'required|exists:branches,id'
        ]);

        // Verify branch is active
        $branch = Branch::where('id', $credentials['branch_id'])
            ->where('is_active', true)
            ->first();
        
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive branch'
            ], 400);
        }

        // Try to find user by email or ID
        $user = User::where('email', $credentials['identifier'])
            ->orWhere('id', $credentials['identifier'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if user has required role for payment management
            if ($user->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
                
                Auth::login($user);
                
                // Set payment session with branch ID
                session([
                    'payment_authenticated' => true,
                    'payment_user_id' => $user->id,
                    'selected_branch_id' => $credentials['branch_id']
                ]);
                
                // Log successful login
                PosAccessLog::create([
                    'user_id' => $user->id,
                    'access_type' => 'payment_management',
                    'action' => 'login',
                    'details' => [
                        'status' => 'success',
                        'branch_id' => $credentials['branch_id']
                    ],
                    'ip_address' => $request->ip()
                ]);

                // Generate token
                $token = $user->createToken('payment-token')->plainTextToken;

                // Return success response
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name')
                    ],
                    'branch' => $branch,
                    'redirect' => '/admin/payments?branch=' . $branch->id
                ]);
            }
            
            // Log failed login due to role
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'payment_management',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'insufficient_role'],
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You do not have payment management access permissions.'
            ], 403);
        }

        // Log failed login attempt
        PosAccessLog::create([
            'user_id' => null,
            'access_type' => 'payment_management',
            'action' => 'login',
            'details' => [
                'status' => 'failed', 
                'reason' => 'invalid_credentials',
                'identifier' => $credentials['identifier']
            ],
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        // Log logout
        PosAccessLog::create([
            'user_id' => auth()->id(),
            'access_type' => 'payment_management',
            'action' => 'logout',
            'details' => ['status' => 'success'],
            'ip_address' => $request->ip()
        ]);

        // Clear payment session
        session()->forget(['payment_authenticated', 'payment_user_id', 'selected_branch_id']);
        
        // Logout user
        Auth::logout();
        
        // Revoke token if exists
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return redirect()->route('admin.branches.index')
            ->with('success', 'Logged out from payment management successfully.');
    }
}
