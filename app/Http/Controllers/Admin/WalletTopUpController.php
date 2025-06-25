<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WalletTopUpController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showLogin()
    {
        // If already authenticated for wallet, redirect to index
        if (Session::has('wallet_authenticated')) {
            return redirect()->route('wallet.index');
        }

        // Check if user has proper role
        if (!Auth::user()->hasRole(['admin', 'employee'])) {
            return redirect()->back()->with('error', 'You do not have permission to access this feature.');
        }

        return view('admin.wallet.topup-login');
    }

    public function login(Request $request)
    {
        Log::info('WalletTopUpController: Starting login process', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'ip' => $request->ip()
        ]);

        $request->validate([
            'password' => 'required',
        ]);

        // Verify the password matches the currently logged-in user
        if (!Hash::check($request->password, Auth::user()->password)) {
            Log::warning('WalletTopUpController: Failed login attempt', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        // Set wallet authentication session
        Session::put('wallet_authenticated', true);
        Session::put('wallet_auth_time', now());
        Session::put('wallet_last_activity', now());
        
        Log::info('WalletTopUpController: Login successful', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);

        return redirect()->route('wallet.index')
                        ->with('success', 'Wallet access granted successfully.');
    }

    public function logout()
    {
        Log::info('WalletTopUpController: Starting logout process', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name
        ]);

        Session::forget(['wallet_authenticated', 'wallet_auth_time', 'wallet_last_activity']);
            
            Log::info('WalletTopUpController: Logout successful', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'timestamp' => now()
            ]);

        return redirect()->route('wallet.topup.login')
                        ->with('success', 'Wallet access has been terminated.');
    }

    public function showTopUpForm()
    {
        try {
            $users = User::with('wallet')->get();
            return view('admin.wallet.topup-form', compact('users'));
        } catch (\Exception $e) {
            Log::error('Failed to show top-up form: ' . $e->getMessage());
            return redirect()->route('wallet.index')
                           ->with('error', 'Failed to load top-up form. Please try again.');
        }
    }

    public function processTopUp(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:255'
            ]);

            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $amount = $request->amount;
            $description = $request->description ?? 'Manual top-up by admin';

            // Get or create wallet
            $wallet = $user->wallet ?? new Wallet(['user_id' => $user->id]);
            $wallet->balance = ($wallet->balance ?? 0) + $amount;
            $wallet->save();

            // Create transaction record
            $transaction = new WalletTransaction([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'credit',
                'description' => $description,
                'status' => 'completed'
            ]);
            $transaction->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Top-up successful',
                'new_balance' => number_format($wallet->balance, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet top-up failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'amount' => $request->amount
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process top-up: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateQR(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01'
            ]);

            $user = User::findOrFail($request->user_id);
            $amount = $request->amount;

            // Generate QR code data
            $qrData = [
                'user_id' => $user->id,
                'amount' => $amount,
                'timestamp' => now()->timestamp
            ];

            // Generate QR code
            $qrCode = $this->qrCodeService->generateQRCode(json_encode($qrData));

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
                'user' => $user->name,
                'amount' => number_format($amount, 2)
            ]);

        } catch (\Exception $e) {
            Log::error('QR code generation failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'amount' => $request->amount
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }
} 