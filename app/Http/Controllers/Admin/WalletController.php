<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function index()
    {
        $users = User::with(['wallet', 'wallet.transactions' => function($query) {
            $query->latest();
        }])->get();

        return view('desktop.admin.wallet.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Create wallet if it doesn't exist
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance' => 0]
            );

            // If initial amount is provided, create a credit transaction
            if ($request->amount > 0) {
                $wallet->transactions()->create([
                    'type' => 'credit',
                    'amount' => $request->amount,
                    'description' => $request->description ?? 'Initial wallet creation'
                ]);

                $wallet->increment('balance', $request->amount);
            }

            DB::commit();
            return redirect()->route('admin.wallet.index')
                           ->with('success', 'Wallet created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create wallet. Please try again.');
        }
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $wallet = Wallet::where('user_id', $request->user_id)->firstOrFail();

            $wallet->transactions()->create([
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet top-up'
            ]);

            $wallet->increment('balance', $request->amount);

            DB::commit();
            return redirect()->route('admin.wallet.index')
                           ->with('success', 'Wallet topped up successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet top-up failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to top up wallet. Please try again.');
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $wallet = Wallet::where('user_id', $request->user_id)->firstOrFail();

            if ($wallet->balance < $request->amount) {
                throw new \Exception('Insufficient wallet balance.');
            }

            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet withdrawal'
            ]);

            $wallet->decrement('balance', $request->amount);

            DB::commit();
            return redirect()->route('admin.wallet.index')
                           ->with('success', 'Amount withdrawn successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet withdrawal failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?? 'Failed to withdraw amount. Please try again.');
        }
    }

    public function manage(Request $request)
    {
        $user = User::with(['wallet.transactions' => function($query) {
            $query->latest();
        }])->findOrFail($request->user);

        $transactions = $user->wallet->transactions ?? collect();
        $wallet = $user->wallet;

        return view('desktop.admin.wallet.manage', compact('user', 'transactions', 'wallet'));
    }
} 