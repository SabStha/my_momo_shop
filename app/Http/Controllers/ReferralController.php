<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReferralController extends Controller
{
    public function showInstallPrompt($code)
    {
        // Validate the referral code
        $creator = Creator::where('code', $code)->first();
        
        if (!$creator) {
            return redirect()->route('register')->with('error', 'Invalid referral code.');
        }

        // Store the referral code in session
        Session::put('referral_code', $code);
        
        // Store PWA installation status
        Session::put('pwa_install_pending', true);

        return view('referral.install', [
            'creator' => $creator
        ]);
    }

    public function markAsInstalled(Request $request)
    {
        // Mark that the user has installed the PWA
        Session::put('pwa_installed', true);
        
        return response()->json(['success' => true]);
    }
} 