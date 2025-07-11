<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;

class PosController extends Controller
{
    public function showLoginForm(Request $request) {
        $branchId = $request->query('branch');
        
        if (!$branchId) {
            return redirect('/admin/branches')->with('error', 'Branch ID is required');
        }
        
        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();
            
        if (!$branch) {
            return redirect('/admin/branches')->with('error', 'Invalid or inactive branch');
        }
        
        return view('auth.pos-login', compact('branch'))->with('layout', 'layouts.pos');
    }
    
    public function login(Request $request) {
        $request->validate([
            'identifier' => 'required',
            'password' => 'required',
            'branch_id' => 'required|exists:branches,id'
        ]);
        
        $branch = Branch::findOrFail($request->branch_id);
        
        // Try to authenticate with email or ID
        $credentials = [
            'password' => $request->password
        ];
        
        if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $request->identifier;
        } else {
            $credentials['id'] = $request->identifier;
        }
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user has access to this branch
            if (!$user->hasBranchAccess($branch->id)) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this branch'
                ], 403);
            }
            
            // Generate token
            $token = $user->createToken('pos-token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'branch' => $branch,
                'redirect' => '/pos?branch=' . $branch->id
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch_id' => $branchId]);
        }

        // Get active categories
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get unique tags from products
        $tags = Product::where('is_active', true)
            ->whereNotNull('tag')
            ->distinct()
            ->pluck('tag')
            ->map(fn($tag) => strtolower($tag))
            ->unique()
            ->values();

        return view('admin.pos', compact('branch', 'categories', 'tags'));
    }
    
    public function tables() {
        return response()->json(['message' => 'POS tables']);
    }
    
    public function orders() {
        return response()->json(['message' => 'POS orders']);
    }
    
    public function payments() {
        return response()->json(['message' => 'POS payments']);
    }
    
    public function accessLogs() {
        return response()->json(['message' => 'POS access logs']);
    }
} 