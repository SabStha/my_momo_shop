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
        return view('creator.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ]);

        DB::beginTransaction();
        try {
            // Ensure creator role exists
            Role::firstOrCreate(['name' => 'creator']);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_creator' => true,
            ]);

            // Assign Spatie role
            $user->assignRole('creator');

            // Create a Creator record for the user
            $code = Str::random(8);
            while (Creator::where('code', $code)->exists()) {
                $code = Str::random(8);
            }

            Creator::create([
                'user_id' => $user->id,
                'code' => $code,
                'bio' => 'Welcome!',
            ]);

            Auth::login($user);
            DB::commit();

            return redirect()->route('creator-dashboard.index')
                ->with('success', 'Welcome! Your creator account has been created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Creator registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to register. Please try again. Error: ' . $e->getMessage()]);
        }
    }
} 