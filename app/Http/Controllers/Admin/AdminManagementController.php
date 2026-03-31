<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::with('roles');
        
        if ($search = $request->get('search')) {
            $query->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
        
        $admins = $query->latest()->paginate(20)->withQueryString();
        
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.admins.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:admins',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'is_super_admin' => 'nullable|boolean',
        ]);

        $currentAdmin = auth('admin')->user();
        if (!$currentAdmin->is_super_admin) {
            if ($request->has('is_super_admin') && $request->is_super_admin) {
                return back()->with('error', 'Privilege Escalation: You cannot create a super admin.');
            }
            
            if ($request->has('roles')) {
                $requestedRoles = Role::whereIn('name', $request->roles)->with('permissions')->get();
                foreach ($requestedRoles as $role) {
                    foreach ($role->permissions as $perm) {
                        if (!$currentAdmin->hasPermissionTo($perm->name)) {
                            return back()->with('error', "Privilege Escalation: Role {$role->name} contains permission {$perm->name} which you do not possess.");
                        }
                    }
                }
            }
        }

        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_super_admin' => $request->has('is_super_admin') ? true : false,
        ]);

        if ($request->has('roles')) {
            $admin->syncRoles($request->roles);
        }

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully.');
    }

    public function edit(Admin $admin)
    {
        $roles = Role::all();
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'is_super_admin' => 'nullable|boolean',
        ]);

        $currentAdmin = auth('admin')->user();
        
        if (!$currentAdmin->is_super_admin) {
            if ($admin->is_super_admin) {
                return back()->with('error', 'You cannot modify a super admin account.');
            }
            if ($request->has('is_super_admin') && $request->is_super_admin) {
                return back()->with('error', 'Privilege Escalation: You cannot promote an admin to super admin.');
            }
            
            if ($request->has('roles')) {
                $requestedRoles = Role::whereIn('name', $request->roles)->with('permissions')->get();
                foreach ($requestedRoles as $role) {
                    foreach ($role->permissions as $perm) {
                        if (!$currentAdmin->hasPermissionTo($perm->name)) {
                            return back()->with('error', "Privilege Escalation: Role {$role->name} contains permission {$perm->name} which you do not possess.");
                        }
                    }
                }
            }
        }

        $admin->username = $request->username;
        $admin->email = $request->email;
        
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->is_super_admin = $request->has('is_super_admin') ? true : false;
        
        $admin->save();

        if ($request->has('roles')) {
            $admin->syncRoles($request->roles);
        } else {
            $admin->syncRoles([]);
        }

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        // Prevent deleting oneself
        if (auth()->guard('admin')->id() === $admin->id) {
            return redirect()->route('admin.admins.index')->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Admin deleted successfully.');
    }
}
