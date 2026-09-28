<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PreApprovedAgent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAgentRosterController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = PreApprovedAgent::with('claimedByUser')->latest();

        if ($status === 'claimed') {
            $query->where('is_claimed', true);
        } elseif ($status === 'unclaimed') {
            $query->where('is_claimed', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('agent_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $roster = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => PreApprovedAgent::count(),
            'claimed' => PreApprovedAgent::where('is_claimed', true)->count(),
            'unclaimed' => PreApprovedAgent::where('is_claimed', false)->count(),
        ];

        return view('admin.agents.roster.index', compact('roster', 'stats', 'status', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agent_code' => ['required', 'string', 'max:50', 'unique:pre_approved_agents,agent_code'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25'],
        ]);

        $code = strtoupper(trim($validated['agent_code']));
        $parts = explode(' ', trim($validated['full_name']));
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? end($parts) : '';

        PreApprovedAgent::create([
            'agent_code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($validated['full_name']),
            'email' => strtolower(trim($validated['email'])),
            'phone_number' => trim($validated['phone_number']),
            'is_claimed' => false,
        ]);

        return back()->with('success', "Agent {$code} successfully added to Master Roster.");
    }

    public function update(Request $request, $id)
    {
        $agent = PreApprovedAgent::findOrFail($id);

        $validated = $request->validate([
            'agent_code' => ['required', 'string', 'max:50', Rule::unique('pre_approved_agents', 'agent_code')->ignore($agent->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25'],
        ]);

        $code = strtoupper(trim($validated['agent_code']));
        $parts = explode(' ', trim($validated['full_name']));
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? end($parts) : '';

        $agent->update([
            'agent_code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($validated['full_name']),
            'email' => strtolower(trim($validated['email'])),
            'phone_number' => trim($validated['phone_number']),
        ]);

        return back()->with('success', "Pre-approved agent record {$code} updated successfully.");
    }

    public function unclaim($id)
    {
        $agent = PreApprovedAgent::findOrFail($id);
        $agent->update([
            'is_claimed' => false,
            'claimed_at' => null,
            'claimed_by_user_id' => null,
        ]);

        return back()->with('success', "Agent {$agent->agent_code} profile has been unlocked/unclaimed.");
    }

    public function destroy($id)
    {
        $agent = PreApprovedAgent::findOrFail($id);
        $code = $agent->agent_code;
        $agent->delete();

        return back()->with('success', "Agent {$code} removed from Master Roster.");
    }
}
