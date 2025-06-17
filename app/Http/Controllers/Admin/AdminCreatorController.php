<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminCreatorController extends Controller
{
    public function index()
    {
        $creators = Creator::with(['user', 'referrals.referredUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        $topCreators = Creator::with('user')
            ->orderBy('referral_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.creators.index', compact('creators', 'topCreators'));
    }

    public function show(Creator $creator)
    {
        $creator->load(['user', 'referrals.referredUser', 'rewards']);
        return view('admin.creators.show', compact('creator'));
    }

    public function create()
    {
        return view('admin.creators.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'bio' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $user->assignRole('creator');

            $creator = Creator::create([
                'user_id' => $user->id,
                'bio' => $validated['bio'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.creators.index')
                ->with('success', 'Creator created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create creator. Please try again.']);
        }
    }

    public function edit(Creator $creator)
    {
        return view('admin.creators.edit', compact('creator'));
    }

    public function update(Request $request, Creator $creator)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $creator->user_id,
            'bio' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $creator->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $creator->update([
                'bio' => $validated['bio'],
            ]);

            DB::commit();

            return redirect()->route('admin.creators.index')
                ->with('success', 'Creator updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update creator. Please try again.']);
        }
    }

    public function destroy(Creator $creator)
    {
        DB::beginTransaction();
        try {
            $creator->user->delete();
            $creator->delete();

            DB::commit();

            return redirect()->route('admin.creators.index')
                ->with('success', 'Creator deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete creator. Please try again.']);
        }
    }
} 