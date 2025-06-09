<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchPasswordController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('admin.branches.passwords', compact('branches'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'password' => 'required|string|min:6',
            'requires_password' => 'boolean'
        ]);

        $branch->update([
            'access_password' => $request->password,
            'requires_password' => $request->boolean('requires_password')
        ]);

        return redirect()->route('admin.branches.passwords')
            ->with('success', 'Branch password updated successfully.');
    }

    public function verify(Request $request, Branch $branch)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        if ($branch->verifyPassword($request->password)) {
            session(['branch_verified_' . $branch->id => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 401);
    }
} 