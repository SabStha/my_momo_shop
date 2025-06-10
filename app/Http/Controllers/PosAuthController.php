<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PosAccessLog;
use App\Models\Branch;

class PosAuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Get branch from URL parameter
        $branchId = $request->query('branch');
        
        if (!$branchId) {
            return redirect('/admin/branches')->with('error', 'Branch ID is required');
        }
        
        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();
            
        if (!$branch) {
            return redirect('/admin/branches')->with('error', 'Invalid or inactive branch');
        }
        
        return view('auth.pos-login', compact('branch'));
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

        if ($user && \Hash::check($credentials['password'], $user->password)) {
            // Check if user has required role
            if ($user->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
                // Set POS session
                session(['pos_authenticated' => true]);
                session(['pos_user_id' => $user->id]);
                session(['selected_branch_id' => $credentials['branch_id']]);
                
                // Create API token for POS access
                $token = $user->createToken('pos-token', ['pos-access'])->plainTextToken;
                
                // Log successful login
                PosAccessLog::create([
                    'user_id' => $user->id,
                    'access_type' => 'pos',
                    'action' => 'login',
                    'details' => [
                        'status' => 'success',
                        'branch_id' => $credentials['branch_id']
                    ],
                    'ip_address' => $request->ip()
                ]);

                // Return success response with token
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
                    'redirect' => '/pos?branch=' . $branch->id
                ]);
            }
            
            // Log failed login due to role
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'pos',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'insufficient_role'],
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access POS.'
            ], 403);
        }

        // Log failed login attempt
        PosAccessLog::create([
            'user_id' => $user ? $user->id : null,
            'access_type' => 'pos',
            'action' => 'login',
            'details' => ['status' => 'failed', 'reason' => 'invalid_credentials'],
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.'
        ], 401);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            // Revoke the token
            $request->user()->currentAccessToken()->delete();
            
            // Clear POS session
            session()->forget(['pos_authenticated', 'pos_user_id']);
            
            // Log logout
            PosAccessLog::create([
                'user_id' => Auth::id(),
                'access_type' => 'pos',
                'action' => 'logout',
                'details' => ['status' => 'success'],
                'ip_address' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.'
        ]);
    }

    public function verifyToken(Request $request)
    {
        // Check both session and token authentication
        if (!$request->user() || !session('pos_authenticated')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session'
            ], 401);
        }

        // Verify session user matches token user
        if (session('pos_user_id') !== $request->user()->id) {
            session()->forget(['pos_authenticated', 'pos_user_id']);
            return response()->json([
                'success' => false,
                'message' => 'Session mismatch'
            ], 401);
        }

        // Check if user has required role
        if (!$request->user()->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access POS'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'roles' => $request->user()->roles->pluck('name')
            ]
        ]);
    }
} 