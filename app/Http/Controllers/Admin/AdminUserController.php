<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        
        $users = User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->select('id', 'name', 'email')
                    ->limit(5)
                    ->get();
        
        return response()->json($users);
    }

    // Placeholder methods
    public function index() {
        return response()->json(['message' => 'AdminUser index']);
    }
} 