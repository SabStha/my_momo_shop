<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        
        // Calculate profile completion percentage
        $profileFields = [
            'name', 'email', 'phone', 'city', 'ward_number', 
            'area_locality', 'building_name', 'detailed_directions', 'profile_picture'
        ];
        
        $completedFields = 0;
        foreach ($profileFields as $field) {
            if (!empty($user->$field)) {
                $completedFields++;
            }
        }
        
        $completionPercentage = round(($completedFields / count($profileFields)) * 100);
        
        // Get recent orders for the user
        $recentOrders = $user->orders()->latest()->take(5)->get();
        
        // Get user statistics
        $totalOrders = $user->orders()->count();
        $totalSpent = $user->orders()->sum('total');
        
        return view('user.profile.edit', compact(
            'user', 
            'completionPercentage', 
            'recentOrders', 
            'totalOrders', 
            'totalSpent'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'ward_number' => 'nullable|string|max:50',
            'area_locality' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'detailed_directions' => 'nullable|string|max:1000',
        ]);
        
        $user->update($request->only([
            'name', 'email', 'phone', 'city', 'ward_number', 
            'area_locality', 'building_name', 'detailed_directions'
        ]));
        
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],
        ]);
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated successfully.');
    }

    public function updatePicture(Request $request)
    {
        \Log::info('Profile picture upload request received', [
            'has_file' => $request->hasFile('profile_picture'),
            'file_size' => $request->file('profile_picture') ? $request->file('profile_picture')->getSize() : null,
            'file_name' => $request->file('profile_picture') ? $request->file('profile_picture')->getClientOriginalName() : null,
            'file_mime' => $request->file('profile_picture') ? $request->file('profile_picture')->getMimeType() : null,
            'file_extension' => $request->file('profile_picture') ? $request->file('profile_picture')->getClientOriginalExtension() : null,
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'x_requested_with' => $request->header('X-Requested-With'),
            'wants_json' => $request->wantsJson(),
            'all_input' => $request->all()
        ]);

        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
            ], [
                'profile_picture.required' => 'Please select a profile picture.',
                'profile_picture.image' => 'The file must be an image.',
                'profile_picture.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
                'profile_picture.max' => 'The image size must not exceed 5MB.'
            ]);

            $user = auth()->user();

            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                $user->profile_picture = $path;
                $user->save();

                \Log::info('Profile picture uploaded successfully', ['path' => $path]);

                // Return JSON response for AJAX requests, redirect for regular form submissions
                if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Profile picture updated successfully!',
                        'path' => Storage::url($path)
                    ]);
                } else {
                    return redirect()->back()->with('success', 'Profile picture updated successfully!');
                }
            }

            $errorMessage = 'No file was uploaded.';
            
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            } else {
                return redirect()->back()->with('error', $errorMessage);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Profile picture validation failed', ['errors' => $e->errors()]);
            
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            } else {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Profile picture upload error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            $errorMessage = 'An error occurred while uploading the image: ' . $e->getMessage();
            
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            } else {
                return redirect()->back()->with('error', $errorMessage);
            }
        }
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:DELETE'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        // Delete user's profile picture
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Logout user
        Auth::logout();

        // Delete user account
        $user->delete();

        return redirect()->route('home')->with('success', 'Your account has been permanently deleted.');
    }

    public function verifyEmail()
    {
        $user = Auth::user();
        
        // In a real application, you would send a verification email
        // For now, we'll just mark it as verified
        $user->email_verified_at = now();
        $user->save();
        
        return back()->with('success', 'Email verified successfully!');
    }

    public function verifyPhone()
    {
        $user = Auth::user();
        
        // In a real application, you would send an SMS verification
        // For now, we'll just mark it as verified
        $user->phone_verified_at = now();
        $user->save();
        
        return back()->with('success', 'Phone number verified successfully!');
    }
} 