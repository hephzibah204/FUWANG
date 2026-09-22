<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\ParcelAgent;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentsController extends Controller
{
    public function index(Request $request)
    {
        $query = ParcelAgent::query()->with(['user', 'shop'])->latest();
        if ($status = $request->string('approval_status')->trim()->value()) {
            $query->where('status', $status);
        }

        $agents = $query->paginate(20)->withQueryString();

        return view('logistics.ops.agents.index', [
            'agents' => $agents,
            'staff' => Auth::guard('logistics_staff')->user(),
        ]);
    }

    public function update(Request $request, ParcelAgent $agent)
    {
        $request->validate([
            'approval_status' => ['required', 'string', 'in:approved,rejected,pending'],
        ]);

        $agent->status = $request->input('approval_status');
        if ($agent->status === 'approved' && ! $agent->verified_at) {
            $agent->verified_at = now();
        }
        $agent->save();

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_agents.updated', "Updated parcel agent {$agent->id} status={$agent->status}");
        }

        return back()->with('success', 'Agent updated successfully.');
    }
}
