<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParcelAgent;
use Illuminate\Http\Request;

class ParcelAgentAdminController extends Controller
{
    public function index()
    {
        $agents = ParcelAgent::with(['user', 'shop'])->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.parcels.agents.index', compact('agents'));
    }

    public function updateStatus(Request $request, ParcelAgent $agent)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,suspended',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($agent, $request) {
            $agent->status = $request->input('status');
            if ($agent->status === 'approved' && is_null($agent->verified_at)) {
                $agent->verified_at = now();
            }
            $agent->save();

            // Ensure the associated shop is also active if approved
            if ($agent->status === 'approved') {
                $agent->shop->update(['is_active' => true]);
            } elseif ($agent->status === 'suspended') {
                $agent->shop->update(['is_active' => false]);
            }
        });

        return back()->with('success', "Agent status updated to {$agent->status}.");
    }
}
