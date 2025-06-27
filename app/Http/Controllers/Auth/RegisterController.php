<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Creator;
use App\Models\Referral;
use App\Services\ReferralService;
use App\Providers\RouteServiceProvider;

class RegisterController extends Controller
{
    public function __construct()
    {
        Log::info('RegisterController constructed');
    }

    public function showRegistrationForm()
    {
        Log::info('Regular user registration form accessed', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'method' => request()->method()
        ]);
        return view('auth.register');
    }

    public function register(Request $request)
    {
        Log::info('Registration attempt', [
            'email' => $request->email,
            'name' => $request->name,
            'has_referral' => $request->has('referral_code')
        ]);

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'referral_code' => ['nullable', 'string', 'exists:creators,code']
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create wallet for the user (universal, no branch_id)
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'is_active' => true,
            ]);

            Log::info('Created wallet for user', [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'branch_id' => $wallet->branch_id
            ]);

            // Handle referral if provided
            if ($request->referral_code) {
                Log::info('Processing referral code', ['code' => $request->referral_code]);
                
                $creator = Creator::where('code', $request->referral_code)->first();
                if ($creator) {
                    Log::info('Found creator for referral', [
                        'creator_id' => $creator->id,
                        'user_id' => $creator->user_id
                    ]);

                    $referral = Referral::create([
                        'creator_id' => $creator->id,
                        'referred_user_id' => $user->id,
                        'status' => 'signed_up',
                        'order_count' => 0,
                        'code' => Referral::generateUniqueCode()
                    ]);

                    Log::info('Created referral record', [
                        'referral_id' => $referral->id,
                        'creator_id' => $creator->id,
                        'referred_id' => $user->id
                    ]);

                    // Process referral using the service
                    $referralService = new ReferralService();
                    $referralService->processNewReferral($user, $creator);

                    Log::info('Completed referral processing', [
                        'user_id' => $user->id,
                        'creator_id' => $creator->id
                    ]);
                } else {
                    Log::warning('Creator not found for referral code', [
                        'code' => $request->referral_code
                    ]);
                }
            }

            event(new Registered($user));

            Auth::login($user);

            // Redirect based on user role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('creator')) {
                return redirect()->route('creator.dashboard');
            } elseif ($user->hasRole('employee')) {
                return redirect()->route('employee.dashboard');
            } else {
                return redirect()->route('home');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Registration failed. Please try again.')
                ->withInput();
        }
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'contact' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Check if it's a valid email
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        // Check if email is unique
                        if (User::where('email', $value)->exists()) {
                            $fail('This email is already registered.');
                        }
                    }
                    // Check if it's a valid phone number
                    elseif (preg_match('/^[0-9]{10}$/', $value)) {
                        // Check if phone is unique
                        if (User::where('phone', $value)->exists()) {
                            $fail('This phone number is already registered.');
                        }
                    } else {
                        $fail('Please enter a valid email or 10-digit phone number.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['required', 'accepted'],
        ], [
            'terms.required' => 'You must accept the terms and conditions.',
            'terms.accepted' => 'You must accept the terms and conditions.',
        ]);
    }

    protected function create(array $data)
    {
        $isEmail = filter_var($data['contact'], FILTER_VALIDATE_EMAIL);
        
        Log::info('Creating new user', [
            'name' => $data['name'],
            'contact' => $data['contact'],
            'is_email' => $isEmail
        ]);

        return User::create([
            'name' => $data['name'],
            'email' => $isEmail ? $data['contact'] : null,
            'phone' => !$isEmail ? $data['contact'] : null,
            'password' => Hash::make($data['password']),
        ]);
    }
}
