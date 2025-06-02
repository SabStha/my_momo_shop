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
            'password' => 'required'
        ]);

        $user = Auth::user();

        if ($user && \Hash::check($credentials['password'], $user->password)) {
            // Check if user has required role
            if ($user->hasRole(['admin', 'cashier', 'employee'])) {
                // Set POS verification in session with expiration
                session([
                    'pos_verified' => true,
                    'pos_verified_at' => now()->timestamp,
                    'pos_verified_user_id' => $user->id
                ]);
                
                // Log successful login
                PosAccessLog::create([
                    'user_id' => $user->id,
                    'access_type' => 'pos',
                    'action' => 'login',
                    'details' => ['status' => 'success'],
                    'ip_address' => $request->ip()
                ]);

                // Redirect to POS dashboard
                return redirect()->route('pos');
            }
            
            // Log failed login due to role
            PosAccessLog::create([
                'user_id' => $user->id,
                'access_type' => 'pos',
                'action' => 'login',
                'details' => ['status' => 'failed', 'reason' => 'insufficient_role'],
                'ip_address' => $request->ip()
            ]);

            return back()->withErrors([
                'password' => 'You do not have permission to access POS.',
            ]);
        }

        // Log failed login attempt
        PosAccessLog::create([
            'user_id' => $user->id,
            'access_type' => 'pos',
            'action' => 'login',
            'details' => ['status' => 'failed', 'reason' => 'invalid_credentials'],
            'ip_address' => $request->ip()
        ]);

        return back()->withErrors([
            'password' => 'The provided password is incorrect.',
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