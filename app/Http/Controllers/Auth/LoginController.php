<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (auth()->user()->isAdmin()) {
            return '/admin/dashboard';
        } elseif (auth()->user()->hasRole('employee')) {
            return '/employee/dashboard';
        } elseif (auth()->user()->hasRole('cashier')) {
            return '/pos';
        }
        
        return '/dashboard';
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->is_creator) {
            return redirect()->route('creator-dashboard.index');
        }
        if ($request->has('ref')) {
            $referralCode = $request->ref;
            $creator = \App\Models\Creator::where('code', $referralCode)->first();
            if ($creator) {
                session(['referral_discount' => 50]); // Set the discount amount
            }
        }
        return redirect('/');
    }
}
