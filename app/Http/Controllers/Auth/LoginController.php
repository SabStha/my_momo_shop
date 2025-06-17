<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        Log::info('LoginController constructed');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'contact' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Determine if the contact is email or phone
        $isEmail = filter_var($credentials['contact'], FILTER_VALIDATE_EMAIL);
        
        // Prepare the credentials for authentication
        $authCredentials = [
            'password' => $credentials['password']
        ];
        
        if ($isEmail) {
            $authCredentials['email'] = $credentials['contact'];
        } else {
            $authCredentials['phone'] = $credentials['contact'];
        }

        if (Auth::attempt($authCredentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Create API token
            $user = Auth::user();
            
            try {
                // Delete any existing tokens
                $user->tokens()->delete();
                
                // Create new token with expiration
                $token = $user->createToken('payment-manager', ['*'], now()->addHours(24))->plainTextToken;
                
                // Store token in session
                $request->session()->put('api_token', $token);
                
                // Log the token creation
                \Log::info('Created API token for user', [
                    'user_id' => $user->id,
                    'token_expires_at' => now()->addHours(24)
                ]);
                
                // Role-based redirection
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.branches.index');
                } elseif ($user->hasRole('creator')) {
                    return redirect()->route('creator.dashboard');
                } elseif ($user->hasRole('cashier')) {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->hasRole('employee')) {
                    return redirect()->route('pos');
                } else {
                    return redirect()->route('home');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create API token', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Still allow login even if token creation fails
                return redirect()->route('admin.branches.index');
            }
        }

        return back()->withErrors([
            'contact' => 'The provided credentials do not match our records.',
        ])->onlyInput('contact');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Delete the API token
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
} 