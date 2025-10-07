<?php

/**
 * Add this method to your ProfileController or UserController
 * 
 * File location: app/Http/Controllers/ProfileController.php
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Update user's profile picture
     * 
     * Route: POST /api/profile/update-picture
     * Requires: auth:sanctum middleware
     */
    public function updateProfilePicture(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        try {
            $user = Auth::user();
            
            \Log::info('Profile picture upload started for user: ' . $user->id);
            
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                // Extract path from URL if it's a full URL
                $oldPath = str_replace(url('storage') . '/', '', $user->profile_picture);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    \Log::info('Old profile picture deleted: ' . $oldPath);
                }
            }
            
            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            \Log::info('New profile picture stored: ' . $path);
            
            // Update user record
            $user->profile_picture = url('storage/' . $path);
            $user->save();
            
            \Log::info('User record updated with new profile picture URL');
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'profile_picture_url' => $user->profile_picture,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Profile picture upload failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

