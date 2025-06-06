<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', [
            'users' => User::with('roles', 'permissions')->get(),
            'roles' => Role::all(),
            'permissions' => Permission::all(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $user->syncRoles($request->roles ?? []);
        $user->syncPermissions($request->permissions ?? []);
        return redirect()->back()->with('success', 'Roles and permissions updated.');
    }
} 