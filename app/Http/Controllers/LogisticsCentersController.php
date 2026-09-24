<?php

namespace App\Http\Controllers;

use App\Models\LogisticsCenter;
use App\Support\NigeriaLocations;
use Illuminate\Http\Request;

class LogisticsCentersController extends Controller
{
    public function states()
    {
        return response()->json([
            'status' => true,
            'states' => NigeriaLocations::stateNames(),
        ]);
    }

    public function index(Request $request)
    {
        $state = $request->string('state')->trim()->value();
        if ($state === '') {
            return response()->json([
                'status' => false,
                'message' => 'State is required.',
            ], 422);
        }

        $type = $request->string('type')->trim()->value();
        $availability = $request->string('availability')->trim()->value();

        $query = LogisticsCenter::query()
            ->where('state', $state)
            ->where('is_active', true)
            ->orderBy('city')
            ->orderBy('name');

        if ($type !== '') {
            $query->where('type', $type);
        }
        if ($availability !== '') {
            $query->where('availability_status', $availability);
        }

        $centers = $query->get()->map(function (LogisticsCenter $center) {
            $effective = $center->availability_status;
            if ($center->capacity_per_day !== null && $center->current_load >= $center->capacity_per_day) {
                $effective = 'closed';
            }

            return [
                'id' => 'center_' . $center->id,
                'name' => $center->name,
                'type' => $center->type ?? 'hub',
                'state' => $center->state,
                'city' => $center->city,
                'address' => $center->address,
                'lat' => $center->lat,
                'lng' => $center->lng,
                'availability_status' => $effective,
            ];
        });

        // Also fetch active Parcel Agent Shops for this state
        $agentShops = \App\Models\ParcelShop::where('state', $state)
            ->where('is_active', true)
            ->get()
            ->map(function ($shop) {
                return [
                    'id' => 'shop_' . $shop->id,
                    'name' => $shop->name . ' (Agent)',
                    'type' => 'agent',
                    'state' => $shop->state,
                    'city' => $shop->city,
                    'address' => $shop->address,
                    'lat' => $shop->lat,
                    'lng' => $shop->lng,
                    'availability_status' => 'available',
                ];
            });

        $allCenters = $centers->concat($agentShops);

        return response()->json([
            'status' => true,
            'centers' => $allCenters->values(),
        ]);
    }
}

