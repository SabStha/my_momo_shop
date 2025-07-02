<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserBadgeController extends Controller
{
    // List all users and their badges
    public function index()
    {
        $users = User::with(['userBadges.badgeClass', 'userBadges.badgeRank', 'userBadges.badgeTier'])->get();
        return view('admin.badges.index', compact('users'));
    }

    // Show badge details for a specific user
    public function show($userId)
    {
        $user = User::with(['userBadges.badgeClass', 'userBadges.badgeRank', 'userBadges.badgeTier'])->findOrFail($userId);
        return view('admin.badges.show', compact('user'));
    }
} 