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
                    'preferred_branch_id' => $user->preferred_branch_id,
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
                'preferred_branch_id' => 'sometimes|nullable|exists:branches,id',
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
                'area_locality', 'building_name', 'detailed_directions', 'preferred_branch_id'
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
                    'preferred_branch_id' => $user->preferred_branch_id,
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
            \Log::info('getWalletBalance called (GET method)', [
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'authenticated' => Auth::check(),
                'sanctum_authenticated' => Auth::guard('sanctum')->check(),
                'method' => request()->method(),
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
                    'credits_balance' => 0,
                    'is_active' => true
                ]);
            }
            
            $balance = $wallet->credits_balance;
            
            \Log::info('Wallet balance retrieved', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'wallet_id' => $wallet->id,
                'has_wallet' => $wallet ? true : false,
                'credits_balance' => $wallet->credits_balance,
                'balance' => $balance,
                'wallet_number' => $wallet->wallet_number ?? $wallet->account_number
            ]);
            
            return response()->json([
                'success' => true,
                'balance' => $balance,
                'wallet_number' => $wallet ? $wallet->wallet_number : null,
                'timestamp' => now()->toISOString(),
                'debug_info' => [
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'credits_balance_raw' => $wallet->credits_balance
                ]
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
              ->header('Pragma', 'no-cache')
              ->header('Expires', '0');
            
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

    /**
     * Get authenticated user's wallet balance via POST (to avoid caching)
     */
    public function getWalletBalancePost()
    {
        \Log::info('getWalletBalancePost called (POST method)', [
            'session_id' => session()->getId(),
            'user_id' => Auth::id(),
            'authenticated' => Auth::check(),
            'method' => request()->method()
        ]);
        return $this->getWalletBalance();
    }

    /**
     * Get authenticated user's credits balance
     */
    public function getCreditsBalance()
    {
        try {
            \Log::info('getCreditsBalance called', [
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
            
            // Get or create credits account for the user
            $creditsAccount = $user->creditsAccount;
            if (!$creditsAccount) {
                \Log::info('Creating credits account for user', ['user_id' => $user->id]);
                $creditsAccount = \App\Models\CreditsAccount::create([
                    'user_id' => $user->id,
                    'credits_balance' => 0,
                    'total_credits_earned' => 0,
                    'total_credits_spent' => 0,
                    'is_active' => true
                ]);
            }
            
            $creditsBalance = $creditsAccount->credits_balance;
            
            \Log::info('Credits balance retrieved', [
                'user_id' => $user->id,
                'has_credits_account' => $creditsAccount ? true : false,
                'credits_balance' => $creditsBalance
            ]);
            
            return response()->json([
                'success' => true,
                'credits_balance' => $creditsBalance,
                'display_credits' => $creditsAccount->display_credits,
                'account_number' => $creditsAccount->account_number
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching credits balance: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch credits balance: ' . $e->getMessage()
            ], 500);
        }
    }
}
