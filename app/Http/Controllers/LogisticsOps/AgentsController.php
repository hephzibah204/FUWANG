<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentsController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryAgent::query()->with('user')->latest();
        if ($status = $request->string('approval_status')->trim()->value()) {
            $query->where('approval_status', $status);
        }
        if ($availability = $request->string('availability_status')->trim()->value()) {
            $query->where('availability_status', $availability);
        }

        $agents = $query->paginate(20)->withQueryString();

        return view('logistics.ops.agents.index', [
            'agents' => $agents,
            'staff' => Auth::guard('logistics_staff')->user(),
        ]);
    }

    public function update(Request $request, DeliveryAgent $agent)
    {
        $request->validate([
            'approval_status' => ['required', 'string', 'in:approved,rejected,pending'],
            'availability_status' => ['nullable', 'string', 'in:available,on_delivery,offline'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        $agent->approval_status = $request->input('approval_status');
        if ($request->filled('availability_status')) {
            $agent->availability_status = $request->input('availability_status');
        }
        if ($request->filled('rating')) {
            $agent->rating = $request->input('rating');
        }
        $agent->save();

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_agents.updated', "Updated delivery agent {$agent->id} status={$agent->approval_status}");
        }

        return back()->with('success', 'Agent updated successfully.');
    }
}
