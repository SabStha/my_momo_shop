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
        \Log::info('📄 LOGIN FORM DISPLAYED', [
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'all_request_data' => request()->all(),
            'headers' => request()->headers->all(),
            'referer' => request()->header('referer'),
            'content_type' => request()->header('content-type'),
            'timestamp' => now()
        ]);
        
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
        \Log::info('=== LOGIN CONTROLLER STARTED ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'email' => $request->email,
            'password_provided' => $request->has('password'),
            'csrf_token_provided' => $request->has('_token'),
            'csrf_token' => $request->input('_token'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'all_input' => $request->all(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('content-type'),
            'referer' => $request->header('referer')
        ]);

        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            \Log::info('Validation passed', [
                'credentials' => array_merge($credentials, ['password' => '[HIDDEN]'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;
        }

        \Log::info('Attempting authentication', [
            'email' => $credentials['email'],
            'password_length' => strlen($credentials['password'])
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            \Log::info('✅ AUTHENTICATION SUCCESSFUL', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
                'authenticated' => Auth::check()
            ]);
            
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
                $redirectUrl = null;
                if ($user->hasRole('admin')) {
                    $redirectUrl = url('/'); // Redirect to home page instead
                    \Log::info('🔄 Redirecting admin user', ['url' => $redirectUrl]);
                    return redirect()->to($redirectUrl);
                } elseif ($user->hasRole('creator')) {
                    $redirectUrl = route('creator.dashboard');
                    \Log::info('🔄 Redirecting creator user', ['url' => $redirectUrl]);
                    return redirect()->to($redirectUrl);
                } elseif ($user->hasRole('cashier')) {
                    $redirectUrl = route('admin.branches.index');
                    \Log::info('🔄 Redirecting cashier user', ['url' => $redirectUrl]);
                    return redirect()->to($redirectUrl);
                } elseif ($user->hasRole('employee')) {
                    $redirectUrl = route('pos');
                    \Log::info('🔄 Redirecting employee user', ['url' => $redirectUrl]);
                    return redirect()->to($redirectUrl);
                } elseif ($user->hasRole('investor')) {
                    $redirectUrl = route('investor.dashboard');
                    \Log::info('🔄 Redirecting investor user', ['url' => $redirectUrl]);
                    return redirect()->to($redirectUrl);
                } else {
                    $redirectUrl = url('/');
                    \Log::info('🔄 Redirecting user with no specific role to home', ['url' => $redirectUrl]);
                    return redirect()->to($redirectUrl);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create API token', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Still allow login even if token creation fails, but redirect based on role
                if ($user->hasRole('admin') || $user->hasRole('cashier')) {
                    return redirect()->route('admin.branches.index');
                } elseif ($user->hasRole('creator')) {
                    return redirect()->route('creator.dashboard');
                } elseif ($user->hasRole('employee')) {
                    return redirect()->route('pos');
                } elseif ($user->hasRole('investor')) {
                    return redirect()->route('investor.dashboard');
                } else {
                    return redirect()->to('/');
                }
            }
        }

        \Log::warning('❌ AUTHENTICATION FAILED', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'credentials_provided' => $credentials,
            'user_exists' => \App\Models\User::where('email', $request->email)->exists()
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
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