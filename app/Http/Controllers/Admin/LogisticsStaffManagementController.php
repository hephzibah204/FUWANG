<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LogisticsStaffManagementController extends Controller
{
    public function impersonate(LogisticsStaff $logisticsStaff)
    {
        $admin = auth('admin')->user();
        if (! ($admin instanceof \App\Models\Admin) || ! $admin->is_super_admin) {
            abort(403);
        }

        session([
            'logistics_ops_impersonator_admin_id' => $admin->id,
            'logistics_ops_impersonator_admin_email' => $admin->email,
        ]);

        Auth::guard('logistics_staff')->login($logisticsStaff, true);

        $admin->logActivity('logistics_staff.impersonate', "Impersonated logistics staff {$logisticsStaff->email}");

        return redirect()->route('logistics.ops.dashboard');
    }

    public function index(Request $request)
    {
        $query = LogisticsStaff::query()->with('roles');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('fullname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        $staff = $query->latest()->paginate(20)->withQueryString();

        return view('admin.logistics_staff.index', compact('staff'));
    }

    public function create()
    {
        $roles = Role::query()
            ->where('guard_name', 'logistics_staff')
            ->orderBy('name')
            ->get();
        $permissions = Permission::query()
            ->where('guard_name', 'logistics_staff')
            ->orderBy('name')
            ->get();

        return view('admin.logistics_staff.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:logistics_staff,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'role' => ['required', 'string', Rule::in(['logistics_manager', 'logistics_officer'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $admin = auth('admin')->user();

        $staff = LogisticsStaff::query()->create([
            'fullname' => $request->input('fullname'),
            'email' => $request->input('email'),
            'password' => Hash::make((string) $request->input('password')),
            'is_active' => $request->boolean('is_active', true),
            'created_by_admin_id' => $admin?->id,
        ]);

        $roleName = (string) $request->input('role');
        $role = Role::query()->where('guard_name', 'logistics_staff')->where('name', $roleName)->first();
        if ($role) {
            $staff->assignRole($role);
        }

        if ($roleName === 'logistics_officer') {
            $allowed = Permission::query()
                ->where('guard_name', 'logistics_staff')
                ->whereIn('name', (array) $request->input('permissions', []))
                ->pluck('name')
                ->all();
            $staff->syncPermissions($allowed);
        } else {
            $staff->syncPermissions([]);
        }

        if ($admin instanceof \App\Models\Admin) {
            $admin->logActivity('logistics_staff.created', "Created logistics staff {$staff->email} ({$roleName})");
        }

        return redirect()->route('admin.logistics-staff.index')->with('success', 'Logistics staff account created successfully.');
    }

    public function edit(LogisticsStaff $logisticsStaff)
    {
        $roles = Role::query()
            ->where('guard_name', 'logistics_staff')
            ->orderBy('name')
            ->get();
        $permissions = Permission::query()
            ->where('guard_name', 'logistics_staff')
            ->orderBy('name')
            ->get();

        return view('admin.logistics_staff.edit', [
            'staff' => $logisticsStaff->load('roles', 'permissions'),
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, LogisticsStaff $logisticsStaff)
    {
        $request->validate([
            'fullname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('logistics_staff', 'email')->ignore($logisticsStaff->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'role' => ['required', 'string', Rule::in(['logistics_manager', 'logistics_officer'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $logisticsStaff->fullname = $request->input('fullname');
        $logisticsStaff->email = $request->input('email');
        $logisticsStaff->is_active = $request->boolean('is_active');
        if ($request->filled('password')) {
            $logisticsStaff->password = Hash::make((string) $request->input('password'));
        }
        $logisticsStaff->save();

        $roleName = (string) $request->input('role');
        $role = Role::query()->where('guard_name', 'logistics_staff')->where('name', $roleName)->first();
        if ($role) {
            $logisticsStaff->syncRoles([$role]);
        } else {
            $logisticsStaff->syncRoles([]);
        }

        if ($roleName === 'logistics_officer') {
            $allowed = Permission::query()
                ->where('guard_name', 'logistics_staff')
                ->whereIn('name', (array) $request->input('permissions', []))
                ->pluck('name')
                ->all();
            $logisticsStaff->syncPermissions($allowed);
        } else {
            $logisticsStaff->syncPermissions([]);
        }

        $admin = auth('admin')->user();
        if ($admin instanceof \App\Models\Admin) {
            $admin->logActivity('logistics_staff.updated', "Updated logistics staff {$logisticsStaff->email} ({$roleName})");
        }

        return redirect()->route('admin.logistics-staff.index')->with('success', 'Logistics staff account updated successfully.');
    }

    public function destroy(LogisticsStaff $logisticsStaff)
    {
        $email = $logisticsStaff->email;
        $logisticsStaff->delete();

        $admin = auth('admin')->user();
        if ($admin instanceof \App\Models\Admin) {
            $admin->logActivity('logistics_staff.deleted', "Deleted logistics staff {$email}");
        }

        return redirect()->route('admin.logistics-staff.index')->with('success', 'Logistics staff account deleted successfully.');
    }
}
