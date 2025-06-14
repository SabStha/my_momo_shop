<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class PosAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('pos-token')->plainTextToken;

            // Log successful login
            ActivityLogService::logPosActivity(
                'login',
                'User logged into POS',
                [
                    'user_id' => $user->id,
                    'branch_id' => session('selected_branch_id'),
                    'email' => $user->email
                ]
            );

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);
        }

        // Log failed login attempt
        ActivityLogService::logPosActivity(
            'login',
            'Failed login attempt',
            [
                'email' => $request->email,
                'branch_id' => session('selected_branch_id'),
                'ip_address' => $request->ip()
            ]
        );

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function verifyToken(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            ActivityLogService::logPosActivity(
                'verify',
                'User verified POS token',
                [
                    'user_id' => $user->id,
                    'branch_id' => session('selected_branch_id'),
                    'email' => $user->email
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
            'user' => $user
        ]);
    }
} 