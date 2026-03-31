<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\AdminAuditLog;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $admin = auth('admin')->user();
        $permissions = $request->permissions ?? [];

        if (!$admin->is_super_admin) {
            foreach ($permissions as $perm) {
                if (!$admin->hasPermissionTo($perm)) {
                    return back()->with('error', 'Privilege Escalation: You cannot assign a permission you do not possess (' . $perm . ').');
                }
            }
        }

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);
            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            AdminAuditLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'Created Role',
                'meta' => ['details' => "Role {$role->name} was created."],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating role: ' . $e->getMessage());
        }
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($role->name === 'super-admin' && !auth('admin')->user()->is_super_admin) {
            return back()->with('error', 'Only a super admin can modify the super-admin role.');
        }

        $admin = auth('admin')->user();
        $permissions = $request->permissions ?? [];

        if (!$admin->is_super_admin) {
            foreach ($permissions as $perm) {
                if (!$admin->hasPermissionTo($perm)) {
                    return back()->with('error', 'Privilege Escalation: You cannot assign a permission you do not possess (' . $perm . ').');
                }
            }
        }

        DB::beginTransaction();
        try {
            $role->update(['name' => $request->name]);
            
            $permissions = $request->permissions ?? [];
            $role->syncPermissions($permissions);

            AdminAuditLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'Updated Role',
                'meta' => ['details' => "Role {$role->name} was updated."],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Cannot delete super-admin role.');
        }

        $roleName = $role->name;
        $role->delete();

        AdminAuditLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'Deleted Role',
            'meta' => ['details' => "Role {$roleName} was deleted."],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
