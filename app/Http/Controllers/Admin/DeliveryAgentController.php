<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use Illuminate\Http\Request;

class DeliveryAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agents = DeliveryAgent::with('user')->latest()->paginate(20);
        return view('admin.delivery_agents.index', compact('agents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryAgent $agent)
    {
        $request->validate([
            'approval_status' => ['required', 'string', 'in:approved,rejected'],
        ]);

        $agent->update(['approval_status' => $request->approval_status]);

        return back()->with('success', 'Agent status updated successfully.');
    }
}
