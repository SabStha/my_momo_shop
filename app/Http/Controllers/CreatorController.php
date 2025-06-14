<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use App\Models\Payout;
use App\Models\Reward;
use Illuminate\Support\Facades\Schema;

class CreatorController extends Controller
{
    public function index()
    {
        $creators = Creator::with(['user', 'referrals.referredUser'])->get();
        $topCreators = Creator::with('user')
            ->orderBy('referral_count', 'desc')
            ->take(10)
            ->get();
        $pendingPayouts = Payout::with(['creator.user'])
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->take(5)
            ->get();
        $rewards = Reward::with(['creator.user'])
            ->orderBy('month', 'desc')
            ->take(10)
            ->get();

        return view('creator.index', compact('creators', 'topCreators', 'pendingPayouts', 'rewards'));
    }

    public function show($code)
    {
        $creator = Creator::where('code', $code)->with('user')->firstOrFail();
        return view('creators.show', compact('creator'));
    }

    public function create()
    {
        // Check if user already has a creator profile
        if (Auth::user()->creator) {
            return redirect()->route('creator-dashboard.index')
                ->with('error', 'You already have a creator profile.');
        }
        
        return view('creators.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bio' => 'required|string|max:500',
            'avatar' => 'nullable|image|max:2048'
        ]);

        // Generate a unique code for the creator
        $code = Str::random(8);
        while (Creator::where('code', $code)->exists()) {
            $code = Str::random(8);
        }

        $creator = new Creator();
        $creator->user_id = Auth::id();
        $creator->code = $code;
        $creator->bio = $request->bio;
        
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $creator->avatar = $path;
        }

        $creator->save();

        return redirect()->route('creator-dashboard.index')
            ->with('success', 'Creator profile created successfully! Your referral code is: ' . $code);
    }

    public function showRegistrationForm()
    {
        Log::info('Creator registration form accessed', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        return view('creator.register');
    }

    public function register(Request $request)
    {
        Log::info('🚨 Creator registration method was hit.', [
            'controller' => 'CreatorController',
            'method' => 'register',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        Log::info('Creator registration attempt', [
            'email' => $request->email,
            'name' => $request->name,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('creator');

            $creatorData = [
                'user_id' => $user->id,
                'code' => Str::random(8),
                'points' => 0
            ];

            // Only add bio if the column exists
            if (Schema::hasColumn('creators', 'bio')) {
                $creatorData['bio'] = 'Welcome!';
            }

            $creator = Creator::create($creatorData);

            DB::commit();

            Auth::login($user);

            return redirect()->route('creator.dashboard')
                ->with('success', 'Welcome to your creator dashboard! Your referral code is: ' . $creator->code);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to register. Please try again. Error: ' . $e->getMessage()]);
        }
    }
} 