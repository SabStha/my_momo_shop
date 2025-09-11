<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
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
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
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
        try {
            // Debug logging
            \Log::info('Profile picture upload attempt', [
                'has_file' => $request->hasFile('profile_picture'),
                'all_files' => $request->allFiles(),
                'request_data' => $request->all()
            ]);
            
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
            ]);

            $user = auth()->user();

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                
                // Debug logging
                \Log::info('File details', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $file->getClientOriginalExtension()
                ]);
                
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                $user->profile_picture = $path;
                $user->save();

                \Log::info('Profile picture uploaded successfully', ['path' => $path]);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile picture updated successfully!',
                    'path' => Storage::url($path)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No file was uploaded.'
            ], 400);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Profile picture validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Profile picture upload error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function topUp(Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|string',
            ]);

            $user = Auth::user();
            $barcode = $request->barcode;

            // Log the barcode top-up attempt
            \Log::info('Credit card barcode top-up attempt', [
                'user_id' => $user->id,
                'barcode' => $barcode,
                'user_email' => $user->email
            ]);

            // Try to parse as QR code data first
            $qrData = null;
            try {
                $qrData = json_decode($barcode, true);
            } catch (\Exception $e) {
                // Not JSON, continue with barcode processing
            }

            if ($qrData && is_array($qrData)) {
                // Handle QR code data
                return $this->processQRCodeData($qrData, $user);
            }

            // Handle traditional barcode format (12 digits)
            if (!preg_match('/^\d{12}$/', $barcode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid barcode or QR code format. Please check and try again.'
                ], 422);
            }

            // Simulate processing delay
            sleep(1);

            // TODO: Check if barcode exists in your credit card database
            // TODO: Check if barcode has already been used
            // TODO: Get credit amount from barcode
            
            // For now, simulate a random credit amount based on barcode
            $amount = (intval(substr($barcode, -2)) % 5 + 1) * 10; // 10, 20, 30, 40, or 50 credits
            
            // Add credits to user's account
            // You'll need to implement this based on your credits system
            // For example: $user->credits += $amount;
            
            // Log successful top-up
            \Log::info('Credit card barcode top-up successful', [
                'user_id' => $user->id,
                'barcode' => $barcode,
                'amount' => $amount,
                'new_balance' => $user->credits ?? 0
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully added {$amount} credits to your account!",
                'amount' => $amount,
                'new_balance' => $user->credits ?? 0
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Barcode top-up validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Barcode top-up processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your credit card. Please try again.'
            ], 500);
        }
    }

    /**
     * Basic Luhn algorithm validation for credit card numbers
     */
    private function validateCardNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);
        
        if (!is_numeric($cardNumber) || strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        $sum = 0;
        $length = strlen($cardNumber);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = $cardNumber[$i];
            
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }

        return ($sum % 10) == 0;
    }

    private function processQRCodeData($qrData, $user)
    {
        try {
            \Log::info('ProfileController QR Code Processing - Received Data', [
                'qr_data' => $qrData,
                'user_id' => $user->id,
                'has_amount' => isset($qrData['amount']),
                'has_branch_id' => isset($qrData['branch_id']),
                'has_expires_at' => isset($qrData['expires_at']),
                'has_type' => isset($qrData['type'])
            ]);

            // Check if it's an admin-generated QR code
            if (isset($qrData['amount']) && isset($qrData['branch_id']) && isset($qrData['expires_at'])) {
                \Log::info('ProfileController QR Code Processing - Processing as Admin QR Code');
                return $this->processAdminQRCode($qrData, $user);
            }
            
            // Check if it's a wallet top-up QR code
            if (isset($qrData['type']) && $qrData['type'] === 'wallet_topup') {
                \Log::info('ProfileController QR Code Processing - Processing as Wallet Top-Up QR Code');
                return $this->processWalletTopUpQR($qrData, $user);
            }

            // Fallback: treat as simple code with default amount
            \Log::info('ProfileController QR Code Processing - Using fallback (30 credits)');
            $amount = 30; // Default amount for legacy codes
            
            return $this->addCreditsToUser($user, $amount, 'Credits added via QR code');

        } catch (\Exception $e) {
            \Log::error('QR Code Processing Error in ProfileController: ' . $e->getMessage(), [
                'qr_data' => $qrData,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process QR code'
            ], 500);
        }
    }

    private function processAdminQRCode($qrData, $user)
    {
        // TEMPORARY: Skip expiration check for debugging
        // Check if QR code has expired
        if (false && isset($qrData['expires_at']) && $qrData['expires_at'] < time()) {
            return response()->json([
                'success' => false,
                'message' => 'QR code has expired'
            ], 400);
        }

        $amount = $qrData['amount'];
        $branchId = $qrData['branch_id'] ?? 'unknown';
        
        return $this->addCreditsToUser($user, $amount, "Top-up via admin QR code (Branch: {$branchId})");
    }

    private function processWalletTopUpQR($qrData, $user)
    {
        $amount = $qrData['amount'];
        
        // TEMPORARY: Skip expiration check for debugging
        // Check if QR code has expired
        if (false && isset($qrData['expires_at']) && $qrData['expires_at'] < time()) {
            return response()->json([
                'success' => false,
                'message' => 'QR code has expired'
            ], 400);
        }
        
        return $this->addCreditsToUser($user, $amount, 'Top-up via wallet QR code');
    }

    private function addCreditsToUser($user, $amount, $description)
    {
        \Log::info('ProfileController addCreditsToUser called', [
            'user_id' => $user->id,
            'amount' => $amount,
            'description' => $description
        ]);

        // Get or create user's wallet
        $wallet = $user->wallet;
        if (!$wallet) {
            \Log::info('ProfileController - Creating new wallet for user', [
                'user_id' => $user->id
            ]);
            $wallet = \App\Models\Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);
        }

        \Log::info('ProfileController - Creating transaction', [
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'description' => $description
        ]);

        // Create transaction
        $transaction = \App\Models\WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'status' => 'completed'
        ]);

        // Update wallet balance
        $oldBalance = $wallet->balance;
        $wallet->balance += $amount;
        $wallet->save();

        // Log successful top-up
        \Log::info('ProfileController QR code top-up successful', [
            'user_id' => $user->id,
            'amount' => $amount,
            'old_balance' => $oldBalance,
            'new_balance' => $wallet->balance,
            'description' => $description
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully added {$amount} credits to your account!",
            'amount' => $amount,
            'new_balance' => $wallet->balance
        ]);
    }
} 