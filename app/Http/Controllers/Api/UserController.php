<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get authenticated user's profile data
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'ward_number' => $user->ward_number,
                    'area_locality' => $user->area_locality,
                    'building_name' => $user->building_name,
                    'detailed_directions' => $user->detailed_directions,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile data'
            ], 500);
        }
    }
    
    /**
     * Update authenticated user's profile data
     */
    public function updateProfile(Request $request)
    {
        try {
            \Log::info('updateProfile called', [
                'user_id' => Auth::id(),
                'authenticated' => Auth::check(),
                'request_data' => $request->all(),
                'method' => $request->method(),
                'url' => $request->url()
            ]);
            
            $user = Auth::user();
            
            if (!$user) {
                \Log::error('User not authenticated in updateProfile');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone' => 'sometimes|string|max:20',
                'city' => 'sometimes|string|max:255',
                'ward_number' => 'sometimes|string|max:50',
                'area_locality' => 'sometimes|string|max:255',
                'building_name' => 'sometimes|string|max:255',
                'detailed_directions' => 'sometimes|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                \Log::error('Validation failed in updateProfile', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user->update($request->only([
                'name', 'email', 'phone', 'city', 'ward_number', 
                'area_locality', 'building_name', 'detailed_directions'
            ]));
            
            \Log::info('Profile updated successfully', ['user_id' => $user->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'ward_number' => $user->ward_number,
                    'area_locality' => $user->area_locality,
                    'building_name' => $user->building_name,
                    'detailed_directions' => $user->detailed_directions,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Exception in updateProfile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Get authenticated user's wallet balance
     */
    public function getWalletBalance()
    {
        try {
            \Log::info('getWalletBalance called', [
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'authenticated' => Auth::check(),
                'sanctum_authenticated' => Auth::guard('sanctum')->check(),
                'request_headers' => request()->headers->all()
            ]);
            
            // Try to get user from API token first, then from session
            $user = null;
            
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                \Log::info('User authenticated via Sanctum', ['user_id' => $user->id]);
            } elseif (Auth::check()) {
                $user = Auth::user();
                \Log::info('User authenticated via session', ['user_id' => $user->id]);
            } else {
                \Log::warning('No user authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Get or create wallet for the user
            $wallet = $user->wallet;
            if (!$wallet) {
                \Log::info('Creating wallet for user', ['user_id' => $user->id]);
                $wallet = \App\Models\Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'is_active' => true
                ]);
            }
            
            $balance = $wallet->balance;
            
            \Log::info('Wallet balance retrieved', [
                'user_id' => $user->id,
                'has_wallet' => $wallet ? true : false,
                'balance' => $balance
            ]);
            
            return response()->json([
                'success' => true,
                'balance' => $balance,
                'wallet_number' => $wallet ? $wallet->wallet_number : null
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching wallet balance: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wallet balance: ' . $e->getMessage()
            ], 500);
        }
    }
}
