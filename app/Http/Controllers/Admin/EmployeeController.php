<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->get();
        return view('desktop.admin.employees.index', compact('employees'));
    }

    public function create()
    {
        try {
            return view('desktop.admin.employees.create');
        } catch (\Throwable $e) {
            Log::error('Error rendering create employee view', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'An error occurred while loading the employee creation page.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        try {
            DB::beginTransaction();

            // Create user with employee role in users table
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'employee' // Set the role column to 'employee'
            ]);

            // Log the user creation
            Log::info('Created new user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            // Assign both employee and cashier roles using Spatie
            $user->syncRoles(['employee', 'cashier']);

            // Log the role assignment
            Log::info('Assigned roles to user', [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames()
            ]);

            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'position' => $validated['position'],
                'salary' => $validated['salary'],
                'hire_date' => $validated['hire_date'],
                'status' => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // 🔥 Log the full error details
            Log::error('Employee creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create employee. Check logs for details.');
        }
    }

    public function show(Employee $employee)
    {
        return view('desktop.admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('desktop.admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($employee->user_id)],
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $employee->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Update employee
            $employee->update([
                'position' => $validated['position'],
                'salary' => $validated['salary'],
                'hire_date' => $validated['hire_date'],
                'status' => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update employee. Please try again.');
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();
            
            // Delete employee (this will cascade delete the user due to foreign key constraint)
            $employee->delete();
            
            DB::commit();
            
            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete employee. Please try again.');
        }
    }
} 