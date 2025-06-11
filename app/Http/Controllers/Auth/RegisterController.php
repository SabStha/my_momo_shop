<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Wallet;

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
        Log::info('🚨 Regular user registration method was hit.', [
            'controller' => 'RegisterController',
            'method' => 'register',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->all(),
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'headers' => $request->headers->all()
        ]);
        
        // Log registration attempt with request data
        Log::info('Regular user registration attempt', [
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'has_csrf_token' => $request->has('_token'),
            'csrf_token' => $request->input('_token')
        ]);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['required', 'accepted'],
        ], [
            'phone.regex' => 'The phone number must be exactly 10 digits.',
            'terms.required' => 'You must accept the terms and conditions.',
            'terms.accepted' => 'You must accept the terms and conditions.',
        ]);

        if ($validator->fails()) {
            Log::warning('Registration validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->except('password')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'profile_picture' => null,
                'referral_code' => null,
                'referred_by' => null,
                'points' => 0,
                'role' => 'user',
                'is_admin' => false,
            ]);

            // Log successful user creation
            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Manually create wallet
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'is_active' => true,
            ]);

            // Log the user in
            Auth::login($user);

            // Log successful login
            Log::info('User logged in after registration', [
                'user_id' => $user->id
            ]);

            DB::commit();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to AmaKo MOMO.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
