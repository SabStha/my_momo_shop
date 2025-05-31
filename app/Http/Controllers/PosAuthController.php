<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PosAccessLog;

class PosAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.pos-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'id' => 'required',
            'password' => 'required'
        ]);

        // Try to find user by email or id
        $user = User::where('email', $credentials['id'])
                    ->orWhere('id', $credentials['id'])
                    ->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $credentials['password']])) {
            // Check if user has required role
            if ($user->hasRole(['admin', 'cashier', 'employee'])) {
                session(['pos_verified' => true]);
                
                // Log successful login
                PosAccessLog::create([
                    'user_id' => $user->id,
                    'access_type' => 'pos',
                    'action' => 'login',
                    'details' => ['status' => 'success'],
                    'ip_address' => $request->ip()
                ]);

                return redirect()->intended(route('pos'));
            }
            
            // Log failed login due to role
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'pos',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'insufficient_role'],
                'ip_address' => $request->ip()
            ]);

            // If user doesn't have required role, logout and return error
            Auth::logout();
            return back()->withErrors([
                'id' => 'You do not have permission to access POS.',
            ]);
        }

        // Log failed login attempt
        if ($user) {
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'pos',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'invalid_credentials'],
                'ip_address' => $request->ip()
            ]);
        }

        return back()->withErrors([
            'id' => 'The provided credentials are incorrect.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            // Log logout
            PosAccessLog::create([
                'user_id' => Auth::id(),
                'access_type' => 'pos',
                'action' => 'logout',
                'details' => ['status' => 'success'],
                'ip_address' => $request->ip()
            ]);
        }

        session()->forget('pos_verified');
        return redirect()->route('home');
    }
} 