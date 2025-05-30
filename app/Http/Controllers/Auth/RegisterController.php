<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\Creator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function showRegistrationForm(Request $request)
    {
        if ($request->has('ref')) {
            session(['referral_code' => $request->ref]);
        }
        return view('auth.register');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Handle referral if exists
        if ($referralCode = session('referral_code')) {
            $creator = Creator::where('code', $referralCode)->first();
            
            if ($creator) {
                // Check if this user has already been referred
                $existingReferral = Referral::where('referred_user_id', $user->id)->first();
                
                if (!$existingReferral) {
                    // Create new referral record
                    $referral = Referral::create([
                        'creator_id' => $creator->user_id,
                        'referred_user_id' => $user->id,
                        'code' => $referralCode,
                        'status' => 'signed_up'
                    ]);

                    // Award points to creator
                    $creator->points += 10;
                    $creator->referral_count += 1;
                    $creator->save();

                    // Create welcome discount coupon for user
                    $coupon = \App\Models\Coupon::create([
                        'code' => 'WELCOME' . strtoupper(uniqid()),
                        'type' => 'fixed',
                        'value' => 50,
                        'min_order_amount' => 100,
                        'max_uses' => 1,
                        'expires_at' => now()->addMonths(3),
                        'is_active' => true,
                        'description' => 'Welcome discount for signing up through referral'
                    ]);

                    // Assign coupon to user
                    \App\Models\UserCoupon::create([
                        'user_id' => $user->id,
                        'coupon_id' => $coupon->id,
                        'used' => false
                    ]);

                    // Set session variable for the discount popup
                    session(['referral_discount' => 50]);

                    Log::info('Referral signup processed', [
                        'creator_id' => $creator->id,
                        'user_id' => $user->id,
                        'creator_points' => $creator->points,
                        'coupon_created' => $coupon->code
                    ]);
                } else {
                    Log::info('User already has a referral', [
                        'user_id' => $user->id,
                        'referral_id' => $existingReferral->id
                    ]);
                }
            }
        }

        Auth::login($user);

        // Ensure the session is saved before redirecting
        session()->save();

        return redirect('/');
    }

    /**
     * Get the post registration redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (session()->has('referral_code')) {
            return '/';
        }
        return '/';
    }
}
