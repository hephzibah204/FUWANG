<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $adminId = $request->query('admin_id');
        $action = trim((string) $request->query('action', ''));
        $from = $request->query('from');
        $to = $request->query('to');

        $logs = AdminAuditLog::query()
            ->with('admin')
            ->when($adminId, fn ($qq) => $qq->where('admin_id', $adminId))
            ->when($action !== '', fn ($qq) => $qq->where('action', 'like', $action . '%'))
            ->when($from, fn ($qq) => $qq->whereDate('created_at', '>=', $from))
            ->when($to, fn ($qq) => $qq->whereDate('created_at', '<=', $to))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($inner) use ($q) {
                    $inner->where('action', 'like', '%' . $q . '%')
                        ->orWhere('ip', 'like', '%' . $q . '%')
                        ->orWhere('user_agent', 'like', '%' . $q . '%')
                        ->orWhere('meta', 'like', '%' . $q . '%');
                });
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $admins = Admin::query()
            ->orderBy('username')
            ->get(['id', 'username', 'email']);

        return view('admin.audit_logs.index', compact('logs', 'admins', 'q', 'adminId', 'action', 'from', 'to'));
    }
}

