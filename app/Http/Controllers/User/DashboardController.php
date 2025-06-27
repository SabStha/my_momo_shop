<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        Log::info('Dashboard accessed', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_roles' => $user->getRoleNames()->toArray(),
            'route' => request()->route()->getName()
        ]);
        
        if ($user->hasRole('admin')) {
            // Redirect admin users to the admin branches index
            Log::info('Redirecting admin user to branches index', ['user_id' => $user->id]);
            return redirect()->route('admin.branches.index');
        }
        
        // For regular users and customers, redirect to home page (which has all the required data)
        if ($user->hasRole('user') || $user->hasRole('customer')) {
            Log::info('Redirecting regular user to home page', ['user_id' => $user->id, 'roles' => $user->getRoleNames()->toArray()]);
            return redirect()->route('home');
        }
        
        // For any other roles (creator, employee, etc.), also redirect to home page
        Log::info('Redirecting other role user to home page', ['user_id' => $user->id, 'roles' => $user->getRoleNames()->toArray()]);
        return redirect()->route('home');
    }
} 