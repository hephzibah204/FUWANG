<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsCenter;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CentersController extends Controller
{
    public function index(Request $request)
    {
        $query = LogisticsCenter::query()->orderBy('state')->orderBy('type')->orderBy('city')->orderBy('name');

        if ($state = $request->string('state')->trim()->value()) {
            $query->where('state', $state);
        }
        if ($type = $request->string('type')->trim()->value()) {
            $query->where('type', $type);
        }

        $centers = $query->paginate(25)->withQueryString();

        return view('logistics.ops.centers.index', [
            'centers' => $centers,
            'staff' => Auth::guard('logistics_staff')->user(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['pickup', 'dropoff'])],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'availability_status' => ['required', 'string', Rule::in(['available', 'limited', 'closed'])],
            'capacity_per_day' => ['nullable', 'integer', 'min:0'],
        ]);

        $center = LogisticsCenter::query()->create([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'availability_status' => $request->input('availability_status'),
            'is_active' => true,
            'capacity_per_day' => $request->input('capacity_per_day'),
            'current_load' => 0,
        ]);

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_centers.created', "Created center {$center->id}");
        }

        return back()->with('success', 'Center created.');
    }

    public function update(Request $request, LogisticsCenter $center)
    {
        $request->validate([
            'availability_status' => ['required', 'string', Rule::in(['available', 'limited', 'closed'])],
            'is_active' => ['nullable', 'boolean'],
            'capacity_per_day' => ['nullable', 'integer', 'min:0'],
            'current_load' => ['nullable', 'integer', 'min:0'],
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        if ($request->filled('name')) {
            $center->name = $request->input('name');
        }
        if ($request->has('address')) {
            $center->address = $request->input('address');
        }
        if ($request->has('lat')) {
            $center->lat = $request->input('lat');
        }
        if ($request->has('lng')) {
            $center->lng = $request->input('lng');
        }
        $center->availability_status = $request->input('availability_status');
        $center->is_active = $request->boolean('is_active');
        $center->capacity_per_day = $request->input('capacity_per_day');
        $center->current_load = (int) $request->input('current_load', 0);
        $center->save();

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_centers.updated', "Updated center {$center->id} {$center->availability_status}");
        }

        return back()->with('success', 'Center updated.');
    }

    public function destroy(LogisticsCenter $center)
    {
        $id = $center->id;
        $center->delete();

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_centers.deleted', "Deleted center {$id}");
        }

        return back()->with('success', 'Center deleted.');
    }
}
