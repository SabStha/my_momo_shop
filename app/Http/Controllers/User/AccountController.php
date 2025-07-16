<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // Ensure referral code exists
        if (empty($user->referral_code)) {
            $user->referral_code = strtoupper(substr(md5($user->id . time()), 0, 8));
            $user->save();
        }
        $wallet = $user->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->take(20)->get() : collect();
        $orders = $user->orders()->latest()->get();
        $offers = collect(); // Add offers if needed
        $settings = collect(); // Add settings if needed
        
        return view('user.profile.edit', compact('user', 'wallet', 'transactions', 'orders', 'offers', 'settings'));
    }

    public function show()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->take(20)->get() : collect();
        return view('user.my-account', compact('user', 'wallet', 'transactions'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Account updated!');
    }

    // Admin/manual top-up
    public function topUp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user_id]);
        $wallet->balance += $request->amount;
        $wallet->save();
        $wallet->transactions()->create([
            'amount' => $request->amount,
            'type' => 'credit',
            'description' => $request->description ?? 'Manual top-up',
        ]);
        return back()->with('success', 'Wallet topped up!');
    }

    // Admin/manual withdrawal
    public function withdraw(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user_id]);
        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient wallet balance.');
        }
        $wallet->balance -= $request->amount;
        $wallet->save();
        $wallet->transactions()->create([
            'amount' => $request->amount,
            'type' => 'debit',
            'description' => $request->description ?? 'Manual withdrawal',
        ]);
        return back()->with('success', 'Wallet withdrawn!');
    }

    // Example: credit wallet
    public function credit($userId, $amount, $description = null)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $userId]);
        $wallet->balance += $amount;
        $wallet->save();
        $wallet->transactions()->create([
            'amount' => $amount,
            'type' => 'credit',
            'description' => $description ?? 'Credit',
        ]);
    }

    // Example: debit wallet
    public function debit($userId, $amount, $description = null)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $userId]);
        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }
        $wallet->balance -= $amount;
        $wallet->save();
        $wallet->transactions()->create([
            'amount' => $amount,
            'type' => 'debit',
            'description' => $description ?? 'Debit',
        ]);
    }
} 