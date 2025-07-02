<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserThemeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->syncThemesWithBadges(); // Ensure themes are up to date with badges
        
        $unlockedThemes = $user->unlockedThemes()->get();
        $activeTheme = $user->activeTheme;
        
        return view('user.themes.index', compact('unlockedThemes', 'activeTheme'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'theme_name' => 'required|string|in:bronze,silver,gold,elite'
        ]);

        $user = Auth::user();
        $success = $user->activateTheme($request->theme_name);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Theme activated successfully!',
                'theme' => $user->activeTheme
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Theme not available or not unlocked.'
        ], 400);
    }

    public function sync()
    {
        $user = Auth::user();
        $user->syncThemesWithBadges();

        return response()->json([
            'success' => true,
            'message' => 'Themes synchronized with badges!',
            'themes' => $user->unlockedThemes()->get(),
            'activeTheme' => $user->activeTheme
        ]);
    }
}
