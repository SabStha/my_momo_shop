<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;

        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch_id' => $branchId]);
        }

        // Get users for the specific branch
        $users = User::when($branch, function($query) use ($branch) {
            return $query->whereHas('employee', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            });
        })->with(['roles', 'permissions'])->get();

        // Get roles and permissions
        $roles = Role::all();
        $permissions = Permission::all();

        return view('admin.roles.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'branch' => $branch
        ]);
    }

    public function update(Request $request, User $user)
    {
        $user->syncRoles($request->roles ?? []);
        $user->syncPermissions($request->permissions ?? []);
        
        $branchId = session('selected_branch_id');
        $redirectUrl = $branchId ? route('admin.roles.index', ['branch' => $branchId]) : route('admin.roles.index');
        
        return redirect($redirectUrl)->with('success', 'Roles and permissions updated.');
    }
} 