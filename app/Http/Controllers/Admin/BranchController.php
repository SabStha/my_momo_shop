<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount([
            'orders',
            'employees',
            'tables',
            'wallets'
        ])->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:branches',
                'address' => 'required|string',
                'contact_person' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'is_active' => 'required|boolean',
                'is_main' => 'required|boolean',
            ]);

            // Convert string values to boolean
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
            $validated['is_main'] = filter_var($validated['is_main'], FILTER_VALIDATE_BOOLEAN);

            // Ensure only one main branch exists
            if ($validated['is_main']) {
                Branch::where('is_main', true)->update(['is_main' => false]);
            }

            $branch = Branch::create($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Branch created successfully',
                    'branch' => $branch
                ]);
            }

            return redirect()->route('admin.branches.index')
                ->with('success', 'Branch created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Branch validation failed: ' . json_encode($e->errors()));
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Branch creation failed: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create branch: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to create branch: ' . $e->getMessage());
        }
    }

    public function show(Branch $branch)
    {
        return response()->json([
            'id' => $branch->id,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'contact_person' => $branch->contact_person,
            'email' => $branch->email,
            'phone' => $branch->phone,
            'is_active' => (bool)$branch->is_active,
            'requires_password' => (bool)$branch->requires_password,
            'is_main' => (bool)$branch->is_main,
            'access_password' => $branch->access_password ? true : false // Only send if exists, not the actual password
        ]);
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
                'address' => 'required|string',
                'contact_person' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'is_active' => 'boolean',
                'requires_password' => 'boolean',
                'access_password' => 'nullable|string|min:6'
            ]);

            // Debug logging for update attempt
            \Log::info('Branch update attempt', [
                'branch_id' => $branch->id,
                'requires_password' => $validated['requires_password'],
                'has_password' => !empty($validated['access_password']),
                'password_length' => !empty($validated['access_password']) ? strlen($validated['access_password']) : 0
            ]);

            // If password is required but not provided, return error
            if ($validated['requires_password'] && empty($validated['access_password'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required when enabling password protection'
                ], 422);
            }

            // Start a database transaction
            DB::beginTransaction();

            try {
                // Update branch data
                $branch->name = $validated['name'];
                $branch->code = $validated['code'];
                $branch->address = $validated['address'];
                $branch->contact_person = $validated['contact_person'];
                $branch->email = $validated['email'];
                $branch->phone = $validated['phone'];
                $branch->is_active = $validated['is_active'];
                $branch->requires_password = $validated['requires_password'];

                // Only update password if provided
                if (!empty($validated['access_password'])) {
                    $hashedPassword = Hash::make($validated['access_password']);
                    
                    \Log::info('Password hash details', [
                        'branch_id' => $branch->id,
                        'original_password_length' => strlen($validated['access_password']),
                        'hashed_password_length' => strlen($hashedPassword),
                        'hash_verification' => Hash::check($validated['access_password'], $hashedPassword)
                    ]);

                    // Update using query builder to ensure direct database update
                    DB::table('branches')
                        ->where('id', $branch->id)
                        ->update(['access_password' => $hashedPassword]);

                    // Verify the password was saved correctly
                    $savedBranch = DB::table('branches')->where('id', $branch->id)->first();
                    $verifyPassword = Hash::check($validated['access_password'], $savedBranch->access_password);
                    
                    \Log::info('Password verification after save', [
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'verification_success' => $verifyPassword,
                        'stored_hash' => $savedBranch->access_password
                    ]);

                    if (!$verifyPassword) {
                        throw new \Exception('Password verification failed after save');
                    }
                }

                // Save other branch data
                $branch->save();

                // Commit the transaction
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Branch updated successfully'
                ]);
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Branch update error', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update branch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Branch $branch)
    {
        // Prevent deletion of main branch
        if ($branch->is_main) {
            return back()->with('error', 'Cannot delete the main branch.');
        }

        // Check for associated data
        $hasData = DB::table('products')->where('branch_id', $branch->id)->exists() ||
            DB::table('orders')->where('branch_id', $branch->id)->exists() ||
            DB::table('employees')->where('branch_id', $branch->id)->exists() ||
            DB::table('tables')->where('branch_id', $branch->id)->exists() ||
            DB::table('wallets')->where('branch_id', $branch->id)->exists();

        if ($hasData) {
            return back()->with('error', 'Cannot delete branch with associated data.');
        }

        try {
            $branch->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Branch deleted successfully'
                ]);
            }

            return redirect()->route('admin.branches.index')
                ->with('success', 'Branch deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Branch deletion failed: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete branch: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete branch: ' . $e->getMessage());
        }
    }

    public function switch(Branch $branch)
    {
        if (!$branch->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This branch is currently inactive.'
            ], 400);
        }

        // Store the entire branch object in session
        session(['selected_branch' => $branch]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully switched to ' . $branch->name,
            'branch' => $branch
        ]);
    }

    public function verify(Request $request, Branch $branch)
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string'
            ]);

            // Debug logging
            \Log::info('Password verification attempt', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'requires_password' => $branch->requires_password,
                'has_password' => !empty($branch->access_password),
                'input_password_length' => strlen($validated['password']),
                'stored_password_length' => !empty($branch->access_password) ? strlen($branch->access_password) : 0,
                'stored_hash' => $branch->access_password // Log the stored hash for debugging
            ]);

            // Check if branch requires password
            if (!$branch->requires_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'This branch does not require password verification'
                ], 400);
            }

            // Check if branch has a password set
            if (empty($branch->access_password)) {
                \Log::warning('Branch has no password set', [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Branch password not set'
                ], 400);
            }

            // Verify password
            $isValid = Hash::check($validated['password'], $branch->access_password);
            
            \Log::info('Password verification result', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'is_valid' => $isValid,
                'input_password' => $validated['password'], // Log the input password for debugging
                'stored_hash' => $branch->access_password // Log the stored hash again
            ]);

            if (!$isValid) {
                \Log::warning('Invalid password attempt', [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'input_password' => $validated['password']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ], 401);
            }

            // Store verification in session
            session(['branch_verified_' . $branch->id => true]);

            \Log::info('Password verified successfully', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password verified successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Password verification error', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request, Branch $branch)
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string|min:6'
            ]);

            // Hash the new password
            $hashedPassword = Hash::make($validated['password']);
            
            // Update the branch password
            $branch->access_password = $hashedPassword;
            $branch->requires_password = true;
            $branch->save();

            // Verify the new password
            $verifyHash = Hash::check($validated['password'], $hashedPassword);
            
            \Log::info('Password reset details', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'password_length' => strlen($validated['password']),
                'hashed_password_length' => strlen($hashedPassword),
                'hash_verification' => $verifyHash,
                'stored_hash' => $hashedPassword
            ]);

            if (!$verifyHash) {
                throw new \Exception('Password hash verification failed');
            }

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Password reset error', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }
} 